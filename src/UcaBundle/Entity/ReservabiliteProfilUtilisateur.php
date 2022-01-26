<?php

/*
 * Classe - ReservabiliteProfilUtilisateur:
 *
 * Entité Technique permettant d'enregistre la capacité par profil par créneau.
*/

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @Gedmo\Loggable
 * @UniqueEntity(fields={"reservabilite","profilUtilisateur"}, message="reservabilite.profilutilisateur.uniqueentity")
 */
class ReservabiliteProfilUtilisateur implements \UcaBundle\Entity\Interfaces\JsonSerializable
{
    use \UcaBundle\Entity\Traits\JsonSerializable;

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
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set reservabilite.
     *
     * @param null|\UcaBundle\Entity\Reservabilite $reservabilite
     *
     * @return ReservabiliteProfilUtilisateur
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
     * Set profilUtilisateur.
     *
     * @param null|\UcaBundle\Entity\ProfilUtilisateur $profilUtilisateur
     *
     * @return ReservabiliteProfilUtilisateur
     */
    public function setProfilUtilisateur(ProfilUtilisateur $profilUtilisateur = null)
    {
        $this->profilUtilisateur = $profilUtilisateur;

        return $this;
    }

    /**
     * Get profilUtilisateur.
     *
     * @return null|\UcaBundle\Entity\ProfilUtilisateur
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
     */
    public function getNbInscrits()
    {
        return $this->nbInscrits;
    }
}
