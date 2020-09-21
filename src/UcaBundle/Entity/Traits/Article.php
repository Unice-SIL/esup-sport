<?php

/*
 * Trait - Article:
 *
 * Un article correspond à un élements de la comamnde (à l'instar du e-commerce)
 * Ce trait est donc réexploité à plusieurs emplacement.
*/

namespace UcaBundle\Entity\Traits;

use UcaBundle\Entity\Creneau;
use UcaBundle\Entity\FormatActivite;
use UcaBundle\Entity\Inscription;
use UcaBundle\Entity\Reservabilite;
use UcaBundle\Repository\EntityRepository;
use UcaBundle\Service\Common\Fn;
use UcaBundle\Service\Common\Previsualisation;

trait Article
{
    private $montant;

    private $tva;

    public function getArticleType()
    {
        return Fn::getShortClassName($this);
    }

    public function getArticleMontantDefaut($utilisateur)
    {
        if (!empty($this->getTarif())) {
            return $this->getTarif()->getMontantUtilisateur($utilisateur);
        }

        return -1;
    }

    public function getArticleTva($utilisateur)
    {
        if (!empty($this->getTarif())) {
            return $this->getTarif()->getTvaUtilisateur($utilisateur);
        }

        return 0;
    }

    public function inscriptionsTerminees()
    {
        return new \DateTime() > $this->getDateFinInscription();
    }

    public function inscriptionsAVenir()
    {
        return new \DateTime() < $this->getDateDebutInscription();
    }

    public function isNotFull($usr, $format = null)
    {
        $totalInscrits = 0;
        $criterias = EntityRepository::criteriaBy([['statut', 'notIn', ['annule', 'desinscrit', 'ancienneinscription', 'desinscriptionadministrative']]]);

        foreach ($this->getInscriptions()->matching($criterias) as $inscription) {
            if ($inscription->getUtilisateur()->getProfil() === $usr->getProfil()) {
                ++$totalInscrits;
            }
        }
        if ($format) {
            return !empty($format->getCapaciteProfil($usr->getProfil())) && $totalInscrits < $format->getCapaciteProfil($usr->getProfil());
        }

        return !empty($this->getCapaciteProfil($usr->getProfil())) && $totalInscrits < $this->getCapaciteProfil($usr->getProfil());
    }

    public function isFull($usr, $format)
    {
        return !$this->isNotFull($usr, $format);
    }

    public function getArticleAutorisations()
    {
        return $this->getAutorisations();
    }

    public function autoriseProfil($profilUtilisateur)
    {
        foreach ($this->profilsUtilisateurs as $formatProfil) {
            if ($profilUtilisateur == $formatProfil->getProfilUtilisateur()) {
                $profil = $formatProfil;
            }
        }

        return isset($profil) && !in_array($profil->getCapaciteProfil(), [null, 0]);
    }

    public function getInscriptionInformations($utilisateur, $format = null)
    {
        $resultat['montant'] = ['article' => -1, 'total' => -1];
        if (is_a($this, FormatActivite::class)) {
            $formatReference = $this;
        } elseif (empty($format)) {
            $formatReference = $this->getFormatActivite();
        } else {
            $formatReference = $format;
        }

        if (empty($utilisateur)) {
            $resultat['statut'] = 'nonconnecte';
        } elseif (is_a($this, Reservabilite::class) && !$formatReference->autoriseProfil($utilisateur->getProfil())) {
            $resultat['statut'] = 'profilinvalide';
        } elseif (!is_a($this, Reservabilite::class) && !$this->autoriseProfil($utilisateur->getProfil())) {
            $resultat['statut'] = 'profilinvalide';
        } elseif (!$utilisateur->getCgvAcceptees()) {
            $resultat['statut'] = 'cgvnonacceptees';
        } else {
            $resultat['montant'] = $this->getArticleArrayMontant($utilisateur, $format);
            $inscriptions = $utilisateur->getInscriptionsByCriteria([
                [Inscription::getItemColumn($this), 'eq', $this],
                ['statut', 'notIn', ['annule', 'desinscrit', 'ancienneinscription', 'desinscriptionadministrative']],
            ]);
            if (Previsualisation::$IS_ACTIVE) {
                $resultat['statut'] = 'previsualisation';
            } elseif (!$inscriptions->isEmpty() && 'valide' == $inscriptions->first()->getStatut()) {
                $resultat['statut'] = 'inscrit';
            } elseif (!$inscriptions->isEmpty() && 'valide' != $inscriptions->first()->getStatut()) {
                $resultat['statut'] = 'preinscrit';
            } elseif ($this->isFull($utilisateur, $format)) {
                $resultat['statut'] = 'complet';
            } elseif (is_a($this, Creneau::class) && $utilisateur->nbCreneauMaximumAtteint()) {
                $resultat['statut'] = 'nbcreneaumaxatteint';
            } elseif ($formatReference->inscriptionsTerminees()) {
                $resultat['statut'] = 'inscriptionsterminees';
            } elseif ($formatReference->inscriptionsAVenir()) {
                $resultat['statut'] = 'inscriptionsavenir';
            } elseif (is_a($this, Reservabilite::class) && $this->dateReservationPasse()) {
                $resultat['statut'] = 'inscriptionsterminees';
            } elseif ($resultat['montant']['total'] < 0) {
                $resultat['statut'] = 'montantincorrect';
            } elseif (!is_a($this, Reservabilite::class) && sizeof($this->getAllInscriptions()) >= $this->getCapacite()) {
                $resultat['statut'] = 'complet';
            } else {
                $resultat['statut'] = 'disponible';
            }
        }

        return $resultat;
    }

    public function getMontantAutorisations($utilisateur)
    {
        $montant = 0;
        foreach ($this->getArticleAutorisations()->getIterator() as $autorisation) {
            $m = 0;
            if (!$utilisateur->hasAutorisation($autorisation)) {
                $m = $autorisation->getArticleMontant($utilisateur);
            }
            if ($m >= 0) {
                $montant += $m;
            } else {
                return $m;
            }
        }

        return $montant;
    }

    public function getArticleArrayMontant($utilisateur, $format = null)
    {
        $montant['total'] = -1;
        $montant['article'] = $this->getArticleMontant($utilisateur);
        if (is_a($this, Reservabilite::class)) {
            $montant['autorisations'] = $format->getMontantAutorisations($utilisateur);
        } else {
            $montant['autorisations'] = $this->getMontantAutorisations($utilisateur);
        }
        if (!empty($format) && !$utilisateur->hasInscriptionsByCriteria([
            ['formatActivite', 'eq', $format],
            ['creneau', 'eq', null],
            ['reservabilite', 'eq', null],
            ['statut', 'eq', 'valide'],
        ])) {
            $montant['format'] = $format->getArticleMontant($utilisateur);
        } else {
            $montant['format'] = 0;
        }
        if (-1 != $montant['article'] && -1 != $montant['autorisations'] && -1 != $montant['format']) {
            $montant['total'] = $montant['article'] + $montant['autorisations'] + $montant['format'];
        }

        return $montant;
    }
}
