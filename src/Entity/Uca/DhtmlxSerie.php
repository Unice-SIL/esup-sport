<?php

/*
 * Classe - DhtmlxDate:
 *
 * Interagit avec la librairie scheduler (hérité)
 * Les creneaux sont des séries.
*/

namespace App\Entity\Uca;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DhtmlxSerieRepository")
 * @Gedmo\Loggable
 */
class DhtmlxSerie extends DhtmlxDate implements \App\Entity\Uca\Interfaces\JsonSerializable
{
    use \App\Entity\Uca\Traits\JsonSerializable;

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

    /**
     * @codeCoverageIgnore
     */
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     */
    public function getDateFinSerie()
    {
        return $this->dateFinSerie;
    }

    /**
     * Get id.
     *
     * @return int
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     */
    public function getDateFin()
    {
        return $this->dateFin;
    }

    /**
     * Add evenement.
     *
     * @return DhtmlxSerie
     * @codeCoverageIgnore
     */
    public function addEvenement(DhtmlxEvenement $evenement)
    {
        $this->evenements[] = $evenement;

        return $this;
    }

    /**
     * Remove evenement.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeEvenement(DhtmlxEvenement $evenement)
    {
        return $this->evenements->removeElement($evenement);
    }

    /**
     * Get evenements.
     *
     * @return \Doctrine\Common\Collections\Collection
     * @codeCoverageIgnore
     */
    public function getEvenements()
    {
        return $this->evenements;
    }

    /**
     * Set creneau.
     *
     * @return DhtmlxSerie
     * @codeCoverageIgnore
     */
    public function setCreneau(Creneau $creneau = null)
    {
        $this->creneau = $creneau;

        return $this;
    }

    /**
     * Get creneau.
     *
     * @return null|Creneau
     * @codeCoverageIgnore
     */
    public function getCreneau()
    {
        return $this->creneau;
    }

    /**
     * Set reservabilite.
     *
     * @param null|\App\Entity\Uca\Reservabilite $reservabilite
     *
     * @return DhtmlxSerie
     * @codeCoverageIgnore
     */
    public function setReservabilite(Reservabilite $reservabilite = null)
    {
        $this->reservabilite = $reservabilite;

        return $this;
    }

    /**
     * Get reservabilite.
     *
     * @return null|\App\Entity\Uca\Reservabilite
     * @codeCoverageIgnore
     */
    public function getReservabilite()
    {
        return $this->reservabilite;
    }

    public function getEtablissementLibelle(): string
    {
        if ($this->creneau) {
            return $this->creneau->getLieu()->getEtablissement() ? $this->creneau->getLieu()->getEtablissement()->getLibelle() : $this->creneau->getLieu()->getLibelle();
        }
        if ($this->reservabilite) {
            return $this->reservabilite->getRessource()->getEtablissementLibelle();
        }

        return '';
    }

    public function getFormatActiviteLibelle(): string
    {
        if ($this->creneau) {
            return $this->creneau->getFormatActivite()->getLibelle();
        }
        if ($this->reservabilite) {
            return $this->reservabilite->getRessource()->getLibelle();
        }

        return '';
    }

    public function getActiviteLibelle(): string
    {
        if ($this->creneau) {
            return $this->creneau->getFormatActivite()->getActiviteLibelle();
        }
        if ($this->reservabilite) {
            return $this->reservabilite->getRessource()->getLibelle();
        }

        return '';
    }
}