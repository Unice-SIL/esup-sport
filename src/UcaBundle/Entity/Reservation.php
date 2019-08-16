<?php

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

/**
 * @ORM\Entity
 */
class Reservation
{
    #region Propriétés
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** @ORM\ManyToOne(targetEntity="Utilisateur", inversedBy="reservations") */
    private $utilisateur;

    /** @ORM\ManyToOne(targetEntity="Reservabilite", inversedBy="reservations") */
    private $reservabilite;

    /** @ORM\Column(type="datetime") */
    private $date;

    /** @ORM\Column(type="string") */
    private $statut;
    #endregion

    #region Méthodes
    #endregion


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
     * Set date.
     *
     * @param \DateTime $date
     *
     * @return Reservation
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date.
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set statut.
     *
     * @param string $statut
     *
     * @return Reservation
     */
    public function setStatut($statut)
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * Get statut.
     *
     * @return string
     */
    public function getStatut()
    {
        return $this->statut;
    }

    /**
     * Set utilisateur.
     *
     * @param \UcaBundle\Entity\Utilisateur|null $utilisateur
     *
     * @return Reservation
     */
    public function setUtilisateur(\UcaBundle\Entity\Utilisateur $utilisateur = null)
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    /**
     * Get utilisateur.
     *
     * @return \UcaBundle\Entity\Utilisateur|null
     */
    public function getUtilisateur()
    {
        return $this->utilisateur;
    }

    /**
     * Set formatAvecReservation.
     *
     * @param \UcaBundle\Entity\FormatAvecReservation|null $formatAvecReservation
     *
     * @return Reservation
     */
    public function setFormatAvecReservation(\UcaBundle\Entity\FormatAvecReservation $formatAvecReservation = null)
    {
        $this->formatAvecReservation = $formatAvecReservation;

        return $this;
    }

    /**
     * Get formatAvecReservation.
     *
     * @return \UcaBundle\Entity\FormatAvecReservation|null
     */
    public function getFormatAvecReservation()
    {
        return $this->formatAvecReservation;
    }
}
