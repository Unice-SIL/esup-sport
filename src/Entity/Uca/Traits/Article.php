<?php

/*
 * Trait - Article:
 *
 * Un article correspond à un élements de la comamnde (à l'instar du e-commerce)
 * Ce trait est donc réexploité à plusieurs emplacement.
*/

namespace App\Entity\Uca\Traits;

use App\Entity\Uca\Creneau;
use App\Entity\Uca\CreneauProfilUtilisateur;
use App\Entity\Uca\FormatActivite;
use App\Entity\Uca\FormatAvecReservation;
use App\Entity\Uca\Inscription;
use App\Entity\Uca\ProfilUtilisateur;
use App\Entity\Uca\Reservabilite;
use App\Repository\EntityRepository;
use App\Service\Common\Fctn;
use App\Service\Common\Previsualisation;

trait Article
{
    private $montant;

    private $tva;

    public function getArticleType()
    {
        return Fctn::getShortClassName($this);
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
        if ($format && !$format instanceof FormatAvecReservation) {
            $item = $format;   
        } else {
            $item = $this;
        }
        
        $profilUtilisateur = $usr->getProfil();
        $nbTotalInscrits = 0;
        foreach ($item->profilsUtilisateurs as $itemProfilUtilisateur) {
            $profil = $itemProfilUtilisateur->getProfilUtilisateur();
            $capaciteProfil = $itemProfilUtilisateur->getCapaciteProfil();
            $nbInscritsProfil = $itemProfilUtilisateur->getNbInscrits();
            $nbTotalInscrits += $nbInscritsProfil;
            if (($profil == $profilUtilisateur || $profil == $profilUtilisateur->getParent()) && $capaciteProfil > 0 && $nbInscritsProfil >= $capaciteProfil) {
                return false;
            }
        }

        return $nbTotalInscrits < $item->getCapacite();
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
        foreach ($this->profilsUtilisateurs as $formatProfilUtilisateur) {
            $profil = $formatProfilUtilisateur->getProfilUtilisateur();
            if (($profil == $profilUtilisateur || $profil == $profilUtilisateur->getParent()) && $formatProfilUtilisateur->getCapaciteProfil() > 0) {
                return true;
            }
        }

        return false;
    }

    // TODO
    public function getInscriptionInformations($utilisateur, $format = null, int $maxCreneau = null, $event = null)
    {
        $resultat['montant'] = ['article' => -1, 'total' => -1];
        $estResa = $this instanceof Reservabilite;

        if ($this instanceof FormatActivite) {
            $formatReference = $this;
        } elseif (empty($format)) {
            $formatReference = $this->getFormatActivite();
        } else {
            $formatReference = $format;
        }

        if (empty($utilisateur)) {
            $resultat['statut'] = 'nonconnecte';
        } elseif (!$this->autoriseProfil($utilisateur->getProfil())) {
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
            } elseif (!$inscriptions->isEmpty()) {
                if ('valide' == $inscriptions->first()->getStatut()) {
                    $resultat['statut'] = 'inscrit';
                } else {
                    $resultat['statut'] = 'preinscrit';
                }
            } elseif ($this->isFull($utilisateur, $format) || (!$estResa && sizeof($this->getAllInscriptions()) >= $this->getCapacite())) {
                $resultat['statut'] = 'complet';
            } elseif ($this instanceof Creneau && (null !== $maxCreneau && $utilisateur->getNbInscriptionCreneau() >= $maxCreneau)) {
                $resultat['statut'] = 'nbcreneaumaxatteint';
            } elseif ($this instanceof Reservabilite && (null === $this->getEvenement() || $utilisateur->nbRessourceMaximumAtteint($this))) {
                $resultat['statut'] = 'nbressourcemaxatteint';
            } elseif ($formatReference->inscriptionsTerminees()) {
                $resultat['statut'] = 'inscriptionsterminees';
            } elseif ($formatReference->inscriptionsAVenir()) {
                $resultat['statut'] = 'inscriptionsavenir';
            } elseif ($estResa && $event !== null && $this->dateReservationPasse($event)) {
                $resultat['statut'] = 'inscriptionsterminees';
            } elseif ($resultat['montant']['total'] < 0) {
                $resultat['statut'] = 'montantincorrect';
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
