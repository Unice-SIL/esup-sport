<?php

/*
 * Classe - DhtmlxDate:
 *
 * Interagit avec la librairie scheduler (hérité)
 * Les creneaux sont des séries.
*/

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="UcaBundle\Repository\DhtmlxSerieRepository")
 * @Gedmo\Loggable
 */
class DhtmlxSerie extends DhtmlxDate implements \UcaBundle\Entity\Interfaces\JsonSerializable
{
    use \UcaBundle\Entity\Traits\JsonSerializable;

    /** @ORM\OneToOne(targetEntity="Creneau", cascade={"persist", "remove"}, inversedBy="serie") */
    protected $creneau;

    /** @ORM\OneToOne(targetEntity="Reservabilite", cascade={"persist", "remove"}, inversedBy="serie") */
    protected $reservabilite;
    //region Propriétés

    /** @ORM\OneToMany(targetEntity="DhtmlxEvenement", mappedBy="serie", cascade={"persist", "remove"})) */
    private $evenements;

    /** @ORM\Column(type="string") */
    private $recurrence;

    /** @ORM\Column(type="datetime") */
    private $dateFinSerie;

    //endregion

    //region Méthodes

    public function __construct()
    {
        $this->evenements = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function jsonSerializeProperties()
    {
        return ['dateDebut', 'dateFin', 'evenements', 'recurrence', 'dateFinSerie', 'creneau', 'oldId', 'action', 'reservabilite'];
    }

    //endregion

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
    public function addEvenement(DhtmlxEvenement $evenement)
    {
        $this->evenements[] = $evenement;

        return $this;
    }

    /**
     * Remove evenement.
     *
     * @param \UcaBundle\Entity\DhtmlxEvenement $evenement
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeEvenement(DhtmlxEvenement $evenement)
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
     * @param null|\UcaBundle\Entity\Creneau $creneau
     *
     * @return DhtmlxSerie
     */
    public function setCreneau(Creneau $creneau = null)
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

    public function getEtablissementLibelle(): string {
        if ($this->creneau) {
            return $this->creneau->getLieu()->getEtablissement() ? $this->creneau->getLieu()->getEtablissement()->getLibelle() : $this->creneau->getLieu()->getLibelle();
        } elseif ($this->reservabilite) {
            return $this->reservabilite->getRessource()->getEtablissementLibelle();
        }

        return '';
    }

    public function getFormatActiviteLibelle(): string {
        if ($this->creneau) {
            return $this->creneau->getFormatActivite()->getLibelle();
        } elseif ($this->reservabilite) {
            return $this->reservabilite->getRessource()->getLibelle();
        }

        return '';
    }

    public function getActiviteLibelle(): string {
        if ($this->creneau) {
            return $this->creneau->getFormatActivite()->getActiviteLibelle();
        } elseif ($this->reservabilite) {
            return $this->reservabilite->getRessource()->getLibelle();
        }

        return '';
    }
}
