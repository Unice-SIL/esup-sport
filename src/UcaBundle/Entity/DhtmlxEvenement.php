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

    /** @ORM\Column(type="string") */
    protected $description;

    #endregion

    #region Méthodes

    public function jsonSerializeProperties()
    {
        return ['dateDebut', 'dateFin', 'serie', 'dependanceSerie', 'reservabilite', 'description', 'oldId', 'action'];
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
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set dateDebut.
     *
     * @param \DateTime $dateDebut
     *
     * @return DhtmlxEvenement
     */
    public function setDateDebut($dateDebut)
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    /**
     * Get dateDebut.
     *
     * @return \DateTime
     */
    public function getDateDebut()
    {
        return $this->dateDebut;
    }

    /**
     * Set dateFin.
     *
     * @param \DateTime $dateFin
     *
     * @return DhtmlxEvenement
     */
    public function setDateFin($dateFin)
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    /**
     * Get dateFin.
     *
     * @return \DateTime
     */
    public function getDateFin()
    {
        return $this->dateFin;
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
     * Set description.
     *
     * @param String $description
     *
     * @return DhtmlxEvenement
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return String
     */
    public function getDescription()
    {
        return $this->description;
    }
}
