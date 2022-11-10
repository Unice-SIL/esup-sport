<?php

/*
 * Classe - CreneauProfilUtilisateur:
 *
 * Entité Technique permettant d'enregistre la capacité par profil par créneau.
*/

namespace App\Entity\Uca;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CreneauProfilUtilisateurRepository")
 * @Gedmo\Loggable
 * @UniqueEntity(fields={"creneau","profilUtilisateur"}, message="creneau.profilutilisateur.uniqueentity")
 */
class CreneauProfilUtilisateur implements \App\Entity\Uca\Interfaces\JsonSerializable
{
    use \App\Entity\Uca\Traits\JsonSerializable;

    /**
     * @ORM\ManyToOne(targetEntity="Creneau" , inversedBy="profilsUtilisateurs", fetch="LAZY")
     * @Assert\NotBlank(message="creneau.notblank")
     */
    protected $creneau;

    /**
     * @ORM\ManyToOne(targetEntity="ProfilUtilisateur" , inversedBy="creneaux", fetch="LAZY")
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

    /**
     * @codeCoverageIgnore
     *
     * @param mixed $capacite
     */
    public function __construct(Creneau $creneau, ProfilUtilisateur $profil, $capacite)
    {
        $this->capaciteProfil = $capacite ? $capacite : 0;
        $this->profilUtilisateur = $profil;
        $this->creneau = $creneau;
        $this->nbInscrits = 0;
    }

    public function jsonSerializeProperties()
    {
        return ['creneau', 'profilUtilisateur', 'capaciteProfil'];
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
     * Set creneau.
     *
     * @return CreneauProfilUtilisateur
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
     * Set profilUtilisateur.
     *
     * @return CreneauProfilUtilisateur
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
     * @return null|ProfilUtilisateur
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
     * @return CreneauProfilUtilisateur
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
     * @return CreneauProfilUtilisateur
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