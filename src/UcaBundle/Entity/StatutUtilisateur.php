<?php

/*
 * Classe - StatutUtilisateur
 *
 * Permet de modifier le statut d'un utilisateur, c'est un élement de contrôel pour l'accès au site
 * Ces élements ne sont pas éditable dans l'interface.
*/

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @UniqueEntity(fields="libelle", message="statututilisateur.uniqueentity")
 */
class StatutUtilisateur implements \UcaBundle\Entity\Interfaces\JsonSerializable
{
    use \UcaBundle\Entity\Traits\JsonSerializable;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="statututilisateur.libelle.notblank")
     */
    protected $libelle;

    /** @ORM\OneToMany(targetEntity="Utilisateur", mappedBy="statut") */
    protected $utilisateur;

    //region Propriétés
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    //endregion

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->utilisateur = new \Doctrine\Common\Collections\ArrayCollection();
    }

    //endregion

    //region Méthodes

    public function jsonSerializeProperties()
    {
        return ['libelle'];
    }

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
     * Set libelle.
     *
     * @param string $libelle
     *
     * @return StatutUtilisateur
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelle.
     *
     * @return string
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Add utilisateur.
     *
     * @param \UcaBundle\Entity\Utilisateur $utilisateur
     *
     * @return StatutUtilisateur
     */
    public function addUtilisateur(Utilisateur $utilisateur)
    {
        $this->utilisateur[] = $utilisateur;

        return $this;
    }

    /**
     * Remove utilisateur.
     *
     * @param \UcaBundle\Entity\Utilisateur $utilisateur
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeUtilisateur(Utilisateur $utilisateur)
    {
        return $this->utilisateur->removeElement($utilisateur);
    }

    /**
     * Get utilisateur.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUtilisateur()
    {
        return $this->utilisateur;
    }
}
