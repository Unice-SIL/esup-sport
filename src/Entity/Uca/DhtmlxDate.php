<?php

/*
 * Classe - DhtmlxDate:
 *
 * La classe mère permettant d'avoir les données de dates communes.
*/

namespace App\Entity\Uca;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DhtmlxDateRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="format", type="string")
 * @ORM\DiscriminatorMap( {
 *   "DhtmlxEvenement" = "DhtmlxEvenement",
 *   "DhtmlxSerie" = "DhtmlxSerie"
 * } )
 * @Gedmo\Loggable
 */
abstract class DhtmlxDate
{
    public $oldId;
    public $action;
    //region Propriétés
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /** @ORM\Column(type="datetime") */
    protected $dateDebut;

    /** @ORM\Column(type="datetime") */
    protected $dateFin;
    //endregion

    //region Méthodes
    //endregion

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
     * @return DhtmlxDate
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
     * @return DhtmlxDate
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
}