<?php

namespace UcaBundle\Entity\Traits;

use UcaBundle\Entity\Creneau;
use UcaBundle\Entity\FormatActivite;
use UcaBundle\Entity\Inscription;
use UcaBundle\Entity\Reservabilite;
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
        } else {
            return -1;
        }
    }

    public function getArticleTva($utilisateur)
    {
        if (!empty($this->getTarif())) {
            return $this->getTarif()->getTvaUtilisateur($utilisateur);
        } else {
            return 0;
        }
    }

    public function isNotFull()
    {
        return !empty($this->getCapacite()) || $this->getInscriptions()->count() < $this->getCapacite();
    }

    public function isFull()
    {
        return !$this->isNotFull();
    }

    public function getArticleAutorisations()
    {
        return $this->getAutorisations();
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
        } elseif (!$this->hasProfil($utilisateur)) {
            $resultat['statut'] = 'profilinvalide';
        } else {
            $resultat['montant'] = $this->getArticleArrayMontant($utilisateur, $format);
            $inscriptions = $utilisateur->getInscriptionsByCriteria([
                [Inscription::getItemColumn($this), 'eq', $this],
                ['statut', 'notIn', ['annule', 'desinscrit']]
            ]);
            if (Previsualisation::$IS_ACTIVE) {
                $resultat['statut'] = 'previsualisation';
            } elseif (!$inscriptions->isEmpty() && $inscriptions->first()->getStatut() == 'valide') {
                $resultat['statut'] = 'inscrit';
            } elseif (!$inscriptions->isEmpty() && $inscriptions->first()->getStatut() != 'valide') {
                $resultat['statut'] = 'preinscrit';
            } elseif ($this->isFull()) {
                $resultat['statut'] = 'complet';
            } elseif (is_a($this, Creneau::class) && $utilisateur->nbCreneauMaximumAtteint()) {
                $resultat['statut'] = 'nbcreneaumaxatteint';
            } elseif (!$formatReference->dateInscriptionValid() || !$this->dateInscriptionValid()) {
                $resultat['statut'] = 'inscriptionferme';
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
            if (!$utilisateur->hasAutorisation($autorisation))
                $m = $autorisation->getArticleMontant($utilisateur);
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
            ['statut', 'eq', 'valide']
        ])) {
            $montant['format'] = $format->getArticleMontant($utilisateur);
        } else {
            $montant['format'] = 0;
        }
        if ($montant['article'] != -1 && $montant['autorisations'] != -1 && $montant['format'] != -1) {
            $montant['total'] = $montant['article'] + $montant['autorisations'] + $montant['format'];
        }

        return $montant;
    }
}
