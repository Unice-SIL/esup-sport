<?php

/*
 * Classe - RessourceProfilUtilisateur:
 *
 * Entité technique permettant de saisir la capacité d'une ressource en fonction du profils des utilisateurs.
*/

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @Gedmo\Loggable
 * @UniqueEntity(fields={"ressource","profilUtilisateur"}, message="capaciteFormat.uniqueentity")
 */
class RessourceProfilUtilisateur implements \UcaBundle\Entity\Interfaces\JsonSerializable
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
     * @ORM\ManyToOne(targetEntity="Ressource" , inversedBy="profilsUtilisateurs")
     */
    protected $ressource;

    /**
     * @ORM\ManyToOne(targetEntity="ProfilUtilisateur" , inversedBy="ressources")
     */
    protected $profilUtilisateur;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"default":0})
     */
    protected $capaciteProfil;

    // endregion

    // region Methodes
    public function __construct(Ressource $ressource, ProfilUtilisateur $profil, $capacite)
    {
        $this->capaciteProfil = $capacite ? $capacite : 0;
        $this->profilUtilisateur = $profil;
        $this->ressource = $ressource;
    }

    public function jsonSerializeProperties()
    {
        return [
            'ressource' => 'ressource',
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
     * @return RessourceProfilUtilisateur
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
     * Set ressource.
     *
     * @param null|\UcaBundle\Entity\Ressource $ressource
     *
     * @return RessourceProfilUtilisateur
     */
    public function setRessource(Ressource $ressource = null)
    {
        $this->ressource = $ressource;

        return $this;
    }

    /**
     * Get ressource.
     *
     * @return null|\UcaBundle\Entity\Ressource
     */
    public function getRessource()
    {
        return $this->ressource;
    }

    /**
     * Set profilUtilisateur.
     *
     * @param null|\UcaBundle\Entity\ProfilUtilisateur $profilUtilisateur
     *
     * @return RessourceProfilUtilisateur
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
}
