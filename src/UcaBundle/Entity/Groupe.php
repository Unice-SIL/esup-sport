<?php

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\Group;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="UcaBundle\Repository\GroupeRepository")
 * @ORM\EntityListeners({"UcaBundle\Service\Listener\Entity\GroupeListener"})
 * @Gedmo\Loggable
 * @UniqueEntity("libelle", message="groupe.uniqueentity")
 */
class Groupe extends Group
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @Gedmo\Versioned
     * @Gedmo\Translatable
     * @ORM\Column(type="string")
     * @Assert\NotNull(message="groupe.name.notnull")
     * @Assert\Length(min = 2, max = 180, minMessage = "groupe.name.tropPetit", maxMessage = "groupe.name.tropLong")
     */
    protected $libelle;
    /**
     * @Assert\NotNull(message="groupe.roles.notnull")
     * @Assert\Count(min = 1, minMessage = "groupe.roles.notnull")
     */
    protected $roles;

    /** @ORM\ManyToMany(targetEntity="UcaBundle\Entity\Utilisateur", mappedBy="groups")  */
    protected $utilisateurs;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="text")
     */
    protected $listeRoles;

    /**
     * Add utilisateur.
     *
     * @param \UcaBundle\Entity\Utilisateur $utilisateur
     *
     * @return Groupe
     */
    public function addUtilisateur(Utilisateur $utilisateur)
    {
        $this->utilisateurs[] = $utilisateur;

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
        return $this->utilisateurs->removeElement($utilisateur);
    }

    /**
     * Get utilisateurs.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUtilisateurs()
    {
        return $this->utilisateurs;
    }

    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * @param mixed $libelle
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * @param mixed $listeRoles
     */
    public function setListeRoles($listeRoles)
    {
        $this->listeRoles = $listeRoles;

        return $this;
    }

    /**
     * Get listeRoles.
     *
     * @return string
     */
    public function getListeRoles()
    {
        return $this->listeRoles;
    }
}
