<?php

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Doctrine\ORM\Event\PreUpdateEventArgs;

/**
 * @ORM\Entity(repositoryClass="UcaBundle\Repository\FormatSimpleRepository")
 * @Gedmo\Loggable
 */
class FormatSimple extends FormatActivite implements \UcaBundle\Entity\Interfaces\Article
{
    #region Propriétés

    /** @Gedmo\Versioned
     * @ORM\Column(type="boolean", nullable=false) */
    private $promouvoir = false;

    /** @ORM\OneToOne(targetEntity="DhtmlxEvenement", cascade={"persist", "remove"}, mappedBy="formatSimple") */
    private $evenement;

    #endregion

    #region Méthodes
    public function __construct()
    {
        parent::__construct();
        $this->evenement = new DhtmlxEvenement();
        $this->evenement->setFormatSimple($this);
    }

    public function getArticleLibelle()
    {
        return $this->getLibelle();
    }

    public function getArticleDescription()
    {
        return $this->getDescription();
    }

    public function setDateDebutEffective($dateDebutEffective)
    {
        $this->evenement->setDateDebut($dateDebutEffective);
        parent::setDateDebutEffective($dateDebutEffective);
        return $this;
    }

    public function setDateFinEffective($dateFinEffective)
    {
        $this->evenement->setDateFin($dateFinEffective);
        parent::setDateFinEffective($dateFinEffective);
        return $this;
    }

    public function setLibelle($libelle)
    {
        $this->evenement->setDescription($libelle);
        parent::setLibelle($libelle);
        return $this;
    }

    #endregion


    /**
     * Set promouvoir.
     *
     * @param bool $promouvoir
     *
     * @return FormatSimple
     */
    public function setPromouvoir($promouvoir)
    {
        $this->promouvoir = $promouvoir;

        return $this;
    }

    /**
     * Get promouvoir.
     *
     * @return bool
     */
    public function getPromouvoir()
    {
        return $this->promouvoir;
    }

    /**
     * Set evenement.
     *
     * @param \UcaBundle\Entity\DhtmlxEvenement|null $evenement
     *
     * @return FormatSimple
     */
    public function setEvenement(\UcaBundle\Entity\DhtmlxEvenement $evenement = null)
    {
        $this->evenement = $evenement;

        return $this;
    }

    /**
     * Get evenement.
     *
     * @return \UcaBundle\Entity\DhtmlxEvenement|null
     */
    public function getEvenement()
    {
        return $this->evenement;
    }
}
