<?php

/*
 * Classe - ReservabiliteProfilUtilisateur:
 *
 * Entité Technique permettant d'enregistre la capacité par profil par créneau.
*/

namespace App\Entity\Uca;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @Gedmo\Loggable
 * @UniqueEntity(fields={"reservabilite","profilUtilisateur"}, message="reservabilite.profilutilisateur.uniqueentity")
 */
class ReservabiliteProfilUtilisateur implements \App\Entity\Uca\Interfaces\JsonSerializable
{
    use \App\Entity\Uca\Traits\JsonSerializable;

    /**
     * @ORM\ManyToOne(targetEntity="Reservabilite" , inversedBy="profilsUtilisateurs", fetch="LAZY")
     * @Assert\NotBlank(message="reservabilite.notblank")
     */
    protected $reservabilite;

    /**
     * @ORM\ManyToOne(targetEntity="ProfilUtilisateur" , inversedBy="reservabilites", fetch="LAZY")
     * @Assert\NotBlank(message="complement.profilsutilisateurs.notblank")
     */
    protected $profilUtilisateur;

    /** @ORM\Column(type="integer", nullable=true, options={"default":0})*/
    protected $capaciteProfil;

    /** @ORM\Column(type="integer", nullable=true, options={"default":0})*/
    protected $nbInscrits;
    // region propriétés

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    // endregion

    // region Methodes
    public function __construct(Reservabilite $reservabilite, ProfilUtilisateur $profil, $capacite)
    {
        $this->capaciteProfil = $capacite ? $capacite : 0;
        $this->profilUtilisateur = $profil;
        $this->reservabilite = $reservabilite;
        $this->nbInscrits = 0;
    }

    public function jsonSerializeProperties()
    {
        return ['profilUtilisateur', 'capaciteProfil'];
    }

    public function getLibelle()
    {
        return $this->profilUtilisateur->getLibelle();
    }

    // endregion

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
     * Set reservabilite.
     *
     * @param null|\App\Entity\Uca\Reservabilite $reservabilite
     *
     * @return ReservabiliteProfilUtilisateur
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
     * Set profilUtilisateur.
     *
     * @param null|\App\Entity\Uca\ProfilUtilisateur $profilUtilisateur
     *
     * @return ReservabiliteProfilUtilisateur
     * @codeCoverageIgnore
     */
    public function setProfilUtilisateur(ProfilUtilisateur $profilUtilisateur = null)
    {
        $this->profilUtilisateur = $profilUtilisateur;

        return $this;
    }

    /**
     * Get profilUtilisateur.
     *
     * @return null|\App\Entity\Uca\ProfilUtilisateur
     * @codeCoverageIgnore
     */
    public function getProfilUtilisateur()
    {
        return $this->profilUtilisateur;
    }

    /**
     * Set capaciteProfil.
     *
     * @param null|mixed $capaciteProfil
     *
     * @return ReservabiliteProfilUtilisateur
     * @codeCoverageIgnore
     */
    public function setCapaciteProfil($capaciteProfil = null)
    {
        $this->capaciteProfil = $capaciteProfil;

        return $this;
    }

    /**
     * Get capaciteProfil.
     *
     * @return null|int
     * @codeCoverageIgnore
     */
    public function getCapaciteProfil()
    {
        return $this->capaciteProfil;
    }

    /**
     * Set nbInscrits.
     *
     * @param null|mixed $nbInscrits
     *
     * @return ReservabiliteProfilUtilisateur
     * @codeCoverageIgnore
     */
    public function setNbInscrits($nbInscrits = null)
    {
        $this->nbInscrits = $nbInscrits;

        return $this;
    }

    /**
     * Get nbInscrits.
     *
     * @return null|int
     * @codeCoverageIgnore
     */
    public function getNbInscrits()
    {
        return $this->nbInscrits;
    }
}