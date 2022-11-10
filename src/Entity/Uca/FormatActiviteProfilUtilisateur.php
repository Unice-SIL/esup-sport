<?php

/*
 * Classe - FormatActiviteProfilUtilisateur:
 *
 * Entité technique permettant de saisir la capacité d'un foramt d'activité en fonction du profils des utilisateurs.
*/

namespace App\Entity\Uca;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FormatActiviteProfilUtilisateurRepository")
 * @Gedmo\Loggable
 * @UniqueEntity(fields={"formatActivite","profilUtilisateur"}, message="capaciteFormat.uniqueentity")
 */
class FormatActiviteProfilUtilisateur implements \App\Entity\Uca\Interfaces\JsonSerializable
{
    use \App\Entity\Uca\Traits\JsonSerializable;

    // region Propriétés

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\ManyToOne(targetEntity="FormatActivite" , inversedBy="profilsUtilisateurs", fetch="LAZY")
     */
    protected $formatActivite;

    /**
     * @ORM\ManyToOne(targetEntity="ProfilUtilisateur" , inversedBy="formatsActivite", fetch="LAZY")
     */
    protected $profilUtilisateur;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"default":0})
     */
    protected $capaciteProfil;

    /** @ORM\Column(type="integer", nullable=true, options={"default":0})*/
    protected $nbInscrits;

    // endregion

    // region Methodes
    /**
     * @codeCoverageIgnore
     *
     * @param mixed $capacite
     */
    public function __construct(FormatActivite $format, ProfilUtilisateur $profil, $capacite)
    {
        $this->capaciteProfil = $capacite ? $capacite : 0;
        $this->profilUtilisateur = $profil;
        $this->formatActivite = $format;
        $this->nbInscrits = 0;
    }

    public function jsonSerializeProperties()
    {
        return [
            'formatActivite' => 'formatActivite',
            'profilUtilisateur' => 'profilUtilisateur',
            'capaciteProfil' => 'capaciteProfil',
            'libelle' => $this->getProfilUtilisateur()->getLibelle(),
        ];
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
     * Set capaciteProfil.
     *
     * @param null|int $capaciteProfil
     *
     * @return FormatActiviteProfilUtilisateur
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
     * Set formatActivite.
     *
     * @return FormatActiviteProfilUtilisateur
     * @codeCoverageIgnore
     */
    public function setFormatActivite(FormatActivite $formatActivite = null)
    {
        $this->formatActivite = $formatActivite;

        return $this;
    }

    /**
     * Get formatActivite.
     *
     * @return null|FormatActivite
     * @codeCoverageIgnore
     */
    public function getFormatActivite()
    {
        return $this->formatActivite;
    }

    /**
     * Set profilUtilisateur.
     *
     * @return FormatActiviteProfilUtilisateur
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