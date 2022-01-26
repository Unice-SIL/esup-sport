<?php

/*
 * Classe - FormatActiviteProfilUtilisateur:
 *
 * Entité technique permettant de saisir la capacité d'un foramt d'activité en fonction du profils des utilisateurs.
*/

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @Gedmo\Loggable
 * @UniqueEntity(fields={"formatActivite","profilUtilisateur"}, message="capaciteFormat.uniqueentity")
 */
class FormatActiviteProfilUtilisateur implements \UcaBundle\Entity\Interfaces\JsonSerializable
{
    use \UcaBundle\Entity\Traits\JsonSerializable;

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
     * Set formatActivite.
     *
     * @param null|\UcaBundle\Entity\FormatActivite $formatActivite
     *
     * @return FormatActiviteProfilUtilisateur
     */
    public function setFormatActivite(FormatActivite $formatActivite = null)
    {
        $this->formatActivite = $formatActivite;

        return $this;
    }

    /**
     * Get formatActivite.
     *
     * @return null|\UcaBundle\Entity\FormatActivite
     */
    public function getFormatActivite()
    {
        return $this->formatActivite;
    }

    /**
     * Set profilUtilisateur.
     *
     * @param null|\UcaBundle\Entity\ProfilUtilisateur $profilUtilisateur
     *
     * @return FormatActiviteProfilUtilisateur
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
     * Set nbInscrits.
     *
     * @param null|mixed $nbInscrits
     *
     * @return CreneauProfilUtilisateur
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
