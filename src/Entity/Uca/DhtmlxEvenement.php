<?php

/*
 * Classe - DhtmlxEvenement
 *
 * Interagit avec la librairie scheduler (hérité)
 * Il s'agit d'un occurence au sein d'une série.
*/

namespace App\Entity\Uca;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DhtmlxEvenementRepository")
 * @Gedmo\Loggable
 */
class DhtmlxEvenement extends DhtmlxDate implements \App\Entity\Uca\Interfaces\JsonSerializable
{
    use \App\Entity\Uca\Traits\JsonSerializable;

    /** @ORM\OneToOne(targetEntity="FormatSimple", inversedBy="evenement") */
    protected $formatSimple;

    /** @ORM\Column(type="text") */
    protected $description;

    /** @ORM\OneToMany(targetEntity="Appel", mappedBy="dhtmlxEvenement", cascade={"persist"}, fetch="EAGER", orphanRemoval=true) */
    protected $appels;

    /** @ORM\OneToOne(targetEntity="Reservabilite", cascade={"persist", "remove"}, inversedBy="evenement") */
    protected $reservabilite;

    /** @ORM\Column(type="text", nullable=true) */
    protected $informations;
    //region Propriétés

    /** @ORM\ManyToOne(targetEntity="DhtmlxSerie", inversedBy="evenements", fetch="EAGER") */
    private $serie;

    /** @ORM\Column(type="boolean", options={"default" : false}) */
    private $dependanceSerie;

    /** @ORM\Column(type="boolean", options={"default" : false}) */
    private $eligibleBonus;

    /**
     * Constructor.
     *
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set serie.
     *
     * @return DhtmlxEvenement
     * @codeCoverageIgnore
     */
    public function setSerie(DhtmlxSerie $serie = null)
    {
        $this->serie = $serie;

        return $this;
    }

    /**
     * Get serie.
     *
     * @return null|DhtmlxSerie
     * @codeCoverageIgnore
     */
    public function getSerie()
    {
        return $this->serie;
    }

    /**
     * Set formatSimple.
     *
     * @return DhtmlxEvenement
     * @codeCoverageIgnore
     */
    public function setFormatSimple(FormatSimple $formatSimple = null)
    {
        $this->formatSimple = $formatSimple;

        return $this;
    }

    /**
     * Get formatSimple.
     *
     * @return null|FormatSimple
     * @codeCoverageIgnore
     */
    public function getFormatSimple()
    {
        return $this->formatSimple;
    }

    /**
     * Set appels.
     *
     * @return DhtmlxEvenement
     * @codeCoverageIgnore
     */
    public function setAppels(Appel $appels = null)
    {
        $this->appels = $appels;

        return $this;
    }

    /**
     * Get appels.
     *
     * @return null|Appel
     * @codeCoverageIgnore
     */
    public function getAppels()
    {
        return $this->appels;
    }

    /**
     * Add appel.
     *
     * @return DhtmlxEvenement
     * @codeCoverageIgnore
     */
    public function addAppel(Appel $appel)
    {
        $this->appels[] = $appel;

        return $this;
    }

    /**
     * Remove appel.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     */
    public function getEligibleBonus()
    {
        return $this->eligibleBonus;
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

    /**
     * Set informations.
     *
     * @param string $informations
     *
     * @return DhtmlxEvenement
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     */
    public function getInformations()
    {
        return $this->informations;
    }

    /**
     * Get etablissement libelle if exist.
     */
    public function getEtablissementLibelle(): string
    {
        if ($this->formatSimple) {
            return $this->formatSimple->getLieu()->first()->getEtablissement() ? $this->formatSimple->getLieu()->first()->getEtablissement()->getLibelle() : $this->formatSimple->getLieu()->first()->getLibelle();
        }
        if ($this->reservabilite) {
            return $this->reservabilite->getRessource()->getEtablissementLibelle();
        }
        if ($this->serie) {
            return $this->serie->getEtablissementLibelle();
        }

        return '';
    }

    /**
     * Get libellé format d'activité.
     */
    public function getFormatActiviteLibelle(): string
    {
        if ($this->formatSimple) {
            return $this->formatSimple->getLibelle();
        }
        if ($this->reservabilite) {
            return $this->reservabilite->getRessource()->getLibelle();
        }
        if ($this->serie) {
            return $this->serie->getFormatActiviteLibelle();
        }

        return '';
    }

    /**
     * Get libellé activité.
     */
    public function getActiviteLibelle(): string
    {
        if ($this->formatSimple) {
            return $this->formatSimple->getActiviteLibelle();
        }
        if ($this->reservabilite) {
            return $this->reservabilite->getRessource()->getLibelle();
        }
        if ($this->serie) {
            return $this->serie->getActiviteLibelle();
        }

        return '';
    }

    /**
     * @codeCoverageIgnore
     */
    public function getLibelle(): string
    {
        return $this->getFormatActiviteLibelle();
    }
}