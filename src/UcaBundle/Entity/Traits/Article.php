<?php

namespace UcaBundle\Entity\Traits;

trait Article
{
    private $montant;

    private $tva;

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
        if (is_a($this, "UcaBundle\Entity\Creneau")) {
            return !empty($this->getCapacite()) || $this->getInscriptions()->count() < $this->getCapacite();
        } else {
            if (is_a($this, "UcaBundle\Entity\Materiel")) {
                return !empty($this->getCapacite()) || $this->getInscriptions()->count() < $this->getCapacite();
            } else {
                return $this->getInscriptions()->count() < 1;
            }
        }
    }

    public function isFull()
    {
        return !$this->isNotFull();
    }

    public function getArticleAutorisations()
    {
        return $this->getAutorisations();
    }
}
