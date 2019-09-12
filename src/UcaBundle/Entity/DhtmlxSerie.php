<?php

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

/**
 * @ORM\Entity(repositoryClass="UcaBundle\Repository\DhtmlxSerieRepository")
 * @Gedmo\Loggable
 */
class DhtmlxSerie extends DhtmlxDate implements \UcaBundle\Entity\Interfaces\JsonSerializable
{
    use \UcaBundle\Entity\Traits\JsonSerializable;
    #region Propriétés

    /** @ORM\OneToMany(targetEntity="DhtmlxEvenement", mappedBy="serie", cascade={"persist", "remove"})) */
    private $evenements;

    /** @ORM\Column(type="string") */
    private $recurrence;

    /** @ORM\Column(type="datetime") */
    private $dateFinSerie;

    /** @ORM\OneToOne(targetEntity="Creneau", cascade={"persist", "remove"}, inversedBy="serie") */
    protected $creneau;

    #endregion

    #region Méthodes

    public function __construct()
    {
        $this->evenements = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function jsonSerializeProperties()
    {
        return ['dateDebut', 'dateFin', 'evenements', 'recurrence', 'dateFinSerie', 'creneau', 'oldId', 'action'];
    }

    #endregion

    /**
     * Set recurrence.
     *
     * @param string $recurrence
     *
     * @return DhtmlxSerie
     */
    public function setRecurrence($recurrence)
    {
        $this->recurrence = $recurrence;

        return $this;
    }

    /**
     * Get recurrence.
     *
     * @return string
     */
    public function getRecurrence()
    {
        return $this->recurrence;
    }

    /**
     * Set dateFinSerie.
     *
     * @param \DateTime $dateFinSerie
     *
     * @return DhtmlxSerie
     */
    public function setDateFinSerie($dateFinSerie)
    {
        $this->dateFinSerie = $dateFinSerie;

        return $this;
    }

    /**
     * Get dateFinSerie.
     *
     * @return \DateTime
     */
    public function getDateFinSerie()
    {
        return $this->dateFinSerie;
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
     * @return DhtmlxSerie
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
     * @return DhtmlxSerie
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
     * Add evenement.
     *
     * @param \UcaBundle\Entity\DhtmlxEvenement $evenement
     *
     * @return DhtmlxSerie
     */
    public function addEvenement(\UcaBundle\Entity\DhtmlxEvenement $evenement)
    {
        $this->evenements[] = $evenement;

        return $this;
    }

    /**
     * Remove evenement.
     *
     * @param \UcaBundle\Entity\DhtmlxEvenement $evenement
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeEvenement(\UcaBundle\Entity\DhtmlxEvenement $evenement)
    {
        return $this->evenements->removeElement($evenement);
    }

    /**
     * Get evenements.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEvenements()
    {
        return $this->evenements;
    }

    /**
     * Set creneau.
     *
     * @param \UcaBundle\Entity\Creneau|null $creneau
     *
     * @return DhtmlxSerie
     */
    public function setCreneau(\UcaBundle\Entity\Creneau $creneau = null)
    {
        $this->creneau = $creneau;

        return $this;
    }

    /**
     * Get creneau.
     *
     * @return \UcaBundle\Entity\Creneau|null
     */
    public function getCreneau()
    {
        return $this->creneau;
    }
}
