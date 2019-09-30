<?php

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

/**
 * @ORM\Entity(repositoryClass="UcaBundle\Repository\DhtmlxEvenementRepository")
 * @Gedmo\Loggable
 */
class DhtmlxEvenement extends DhtmlxDate implements \UcaBundle\Entity\Interfaces\JsonSerializable
{
    use \UcaBundle\Entity\Traits\JsonSerializable;
    #region Propriétés

    /** @ORM\ManyToOne(targetEntity="DhtmlxSerie", inversedBy="evenements", fetch="EAGER") */
    private $serie;

    /** @ORM\Column(type="boolean", options={"default" : false}) */
    private $dependanceSerie;

    /** @ORM\OneToOne(targetEntity="Reservabilite", cascade={"persist", "remove"}, inversedBy="evenement") */
    protected $reservabilite;

    /** @ORM\OneToOne(targetEntity="FormatSimple", inversedBy="evenement") */
    protected $formatSimple;

    /** @ORM\Column(type="text") */
    protected $description;

    /** @ORM\OneToMany(targetEntity="Appel", mappedBy="dhtmlxEvenement", cascade={"persist"}, fetch="EAGER") */
    protected $appels;
    #endregion

    #region Méthodes

    public function jsonSerializeProperties()
    {
        return ['dateDebut', 'dateFin', 'dependanceSerie', 'reservabilite', 'formatSimple', 'description', 'oldId', 'action'];
    }

    #endregion

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
     * @param \UcaBundle\Entity\DhtmlxSerie|null $serie
     *
     * @return DhtmlxEvenement
     */
    public function setSerie(\UcaBundle\Entity\DhtmlxSerie $serie = null)
    {
        $this->serie = $serie;

        return $this;
    }

    /**
     * Get serie.
     *
     * @return \UcaBundle\Entity\DhtmlxSerie|null
     */
    public function getSerie()
    {
        return $this->serie;
    }

    /**
     * Set reservabilite.
     *
     * @param \UcaBundle\Entity\Reservabilite|null $reservabilite
     *
     * @return DhtmlxEvenement
     */
    public function setReservabilite(\UcaBundle\Entity\Reservabilite $reservabilite = null)
    {
        $this->reservabilite = $reservabilite;

        return $this;
    }

    /**
     * Get reservabilite.
     *
     * @return \UcaBundle\Entity\Reservabilite|null
     */
    public function getReservabilite()
    {
        return $this->reservabilite;
    }

    /**
     * Set formatSimple.
     *
     * @param \UcaBundle\Entity\FormatSimple|null $formatSimple
     *
     * @return DhtmlxEvenement
     */
    public function setFormatSimple(\UcaBundle\Entity\FormatSimple $formatSimple = null)
    {
        $this->formatSimple = $formatSimple;

        return $this;
    }

    /**
     * Get formatSimple.
     *
     * @return \UcaBundle\Entity\FormatSimple|null
     */
    public function getFormatSimple()
    {
        return $this->formatSimple;
    }

    /**
     * Set appels.
     *
     * @param \UcaBundle\Entity\Appel|null $appels
     *
     * @return DhtmlxEvenement
     */
    public function setAppels(\UcaBundle\Entity\Appel $appels = null)
    {
        $this->appels = $appels;

        return $this;
    }

    /**
     * Get appels.
     *
     * @return \UcaBundle\Entity\Appel|null
     */
    public function getAppels()
    {
        return $this->appels;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->appels = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add appel.
     *
     * @param \UcaBundle\Entity\Appel $appel
     *
     * @return DhtmlxEvenement
     */
    public function addAppel(\UcaBundle\Entity\Appel $appel)
    {
        $this->appels[] = $appel;

        return $this;
    }

    /**
     * Remove appel.
     *
     * @param \UcaBundle\Entity\Appel $appel
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeAppel(\UcaBundle\Entity\Appel $appel)
    {
        return $this->appels->removeElement($appel);
    }
}
