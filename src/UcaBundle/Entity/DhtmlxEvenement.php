<?php

/*
 * Classe - DhtmlxEvenement
 *
 * Interagit avec la librairie scheduler (hérité)
 * Il s'agit d'un occurence au sein d'une série.
*/

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="UcaBundle\Repository\DhtmlxEvenementRepository")
 * @Gedmo\Loggable
 */
class DhtmlxEvenement extends DhtmlxDate implements \UcaBundle\Entity\Interfaces\JsonSerializable
{
    use \UcaBundle\Entity\Traits\JsonSerializable;

    /** @ORM\OneToOne(targetEntity="FormatSimple", inversedBy="evenement") */
    protected $formatSimple;

    /** @ORM\Column(type="text") */
    protected $description;

    /** @ORM\OneToMany(targetEntity="Appel", mappedBy="dhtmlxEvenement", cascade={"persist"}, fetch="EAGER", orphanRemoval=true) */
    protected $appels;
    //region Propriétés

    /** @ORM\ManyToOne(targetEntity="DhtmlxSerie", inversedBy="evenements", fetch="EAGER") */
    private $serie;

    /** @ORM\Column(type="boolean", options={"default" : false}) */
    private $dependanceSerie;

    /** @ORM\Column(type="boolean", options={"default" : false}) */
    private $eligibleBonus;
    
    /** @ORM\OneToOne(targetEntity="Reservabilite", cascade={"persist", "remove"}, inversedBy="evenement") */
    protected $reservabilite;

    /** @ORM\Column(type="text", nullable=true) */
    protected $informations;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->appels = new \Doctrine\Common\Collections\ArrayCollection();
    }

    //endregion

    //region Méthodes

    public function jsonSerializeProperties()
    {
        return ['dateDebut', 'dateFin', 'dependanceSerie', 'formatSimple', 'description', 'oldId', 'action', 'serie', 'eligibleBonus', 'reservabilite', 'informations'];
    }

    //endregion

    /**
     * Set dependanceSerie.
     *
     * @param bool $dependanceSerie
     *
     * @return DhtmlxEvenement
     */
    public function setDependanceSerie($dependanceSerie)
    {
        $this->dependanceSerie = $dependanceSerie;

        return $this;
    }

    /**
     * Get dependanceSerie.
     *
     * @return bool
     */
    public function getDependanceSerie()
    {
        return $this->dependanceSerie;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return DhtmlxEvenement
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set serie.
     *
     * @param null|\UcaBundle\Entity\DhtmlxSerie $serie
     *
     * @return DhtmlxEvenement
     */
    public function setSerie(DhtmlxSerie $serie = null)
    {
        $this->serie = $serie;

        return $this;
    }

    /**
     * Get serie.
     *
     * @return null|\UcaBundle\Entity\DhtmlxSerie
     */
    public function getSerie()
    {
        return $this->serie;
    }


    /**
     * Set formatSimple.
     *
     * @param null|\UcaBundle\Entity\FormatSimple $formatSimple
     *
     * @return DhtmlxEvenement
     */
    public function setFormatSimple(FormatSimple $formatSimple = null)
    {
        $this->formatSimple = $formatSimple;

        return $this;
    }

    /**
     * Get formatSimple.
     *
     * @return null|\UcaBundle\Entity\FormatSimple
     */
    public function getFormatSimple()
    {
        return $this->formatSimple;
    }

    /**
     * Set appels.
     *
     * @param null|\UcaBundle\Entity\Appel $appels
     *
     * @return DhtmlxEvenement
     */
    public function setAppels(Appel $appels = null)
    {
        $this->appels = $appels;

        return $this;
    }

    /**
     * Get appels.
     *
     * @return null|\UcaBundle\Entity\Appel
     */
    public function getAppels()
    {
        return $this->appels;
    }

    /**
     * Add appel.
     *
     * @param \UcaBundle\Entity\Appel $appel
     *
     * @return DhtmlxEvenement
     */
    public function addAppel(Appel $appel)
    {
        $this->appels[] = $appel;

        return $this;
    }

    /**
     * Remove appel.
     *
     * @param \UcaBundle\Entity\Appel $appel
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeAppel(Appel $appel)
    {
        return $this->appels->removeElement($appel);
    }

    /**
     * Set eligibleBonus.
     *
     * @param bool $eligibleBonus
     *
     * @return DhtmlxEvenement
     */
    public function setEligibleBonus($eligibleBonus)
    {
        $this->eligibleBonus = $eligibleBonus;

        return $this;
    }

    /**
     * Get eligibleBonus.
     *
     * @return bool
     */
    public function getEligibleBonus()
    {
        return $this->eligibleBonus;
    }

    /**
     * Set reservabilite.
     *
     * @param null|\UcaBundle\Entity\Reservabilite $reservabilite
     *
     * @return DhtmlxSerie
     */
    public function setReservabilite(Reservabilite $reservabilite = null)
    {
        $this->reservabilite = $reservabilite;

        return $this;
    }

    /**
     * Get reservabilite.
     *
     * @return null|\UcaBundle\Entity\Reservabilite
     */
    public function getReservabilite()
    {
        return $this->reservabilite;
    }

    /**
     * Set informations.
     *
     * @param string $informations
     *
     * @return DhtmlxEvenement
     */
    public function setInformations($informations)
    {
        $this->informations = $informations;

        return $this;
    }

    /**
     * Get informations.
     *
     * @return string
     */
    public function getInformations()
    {
        return $this->informations;
    }

    /**
     * Get etablissement libelle if exist
     */
    public function getEtablissementLibelle(): string {
        if ($this->formatSimple) {
            return $this->formatSimple->getLieu()->first()->getEtablissement() ? $this->formatSimple->getLieu()->first()->getEtablissement()->getLibelle() : $this->formatSimple->getLieu()->first()->getLibelle();
        } elseif ($this->reservabilite) {
            return $this->reservabilite->getRessource()->getEtablissementLibelle();
        } elseif ($this->serie) {
            return $this->serie->getEtablissementLibelle();
        }

        return '';
    }
}