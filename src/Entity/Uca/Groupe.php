<?php

/*
 * Classe - Groupe:
 *
 * Cette entité définit les groupes de rôles utilisateurs.
*/

namespace App\Entity\Uca;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GroupeRepository")
 * @Gedmo\Loggable
 * @UniqueEntity("libelle", message="groupe.uniqueentity")
 */
class Groupe
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $name;

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
     * @ORM\Column(name="roles", type="json", nullable=false)
     */
    protected $roles = [];

    /** @ORM\ManyToMany(targetEntity="Utilisateur", mappedBy="groups")  */
    protected $utilisateurs;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="text")
     */
    protected $listeRoles;

    /**
     * Group constructor.
     *
     * @param string $name
     * @param array  $roles
     * @codeCoverageIgnore
     */
    public function __construct($name, $roles = [])
    {
        $this->name = $name;
        $this->roles = $roles;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getName()
    {
        return $this->name;
    }

    public function hasRole($role)
    {
        return in_array(strtoupper($role), $this->roles, true);
    }

    /**
     * @codeCoverageIgnore
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @codeCoverageIgnore
     *
     * @param mixed $role
     */
    public function removeRole($role)
    {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    /**
     * @codeCoverageIgnore
     *
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Add utilisateur.
     *
     * @return Groupe
     * @codeCoverageIgnore
     */
    public function addUtilisateur(Utilisateur $utilisateur)
    {
        $this->utilisateurs[] = $utilisateur;

        return $this;
    }

    /**
     * Remove utilisateur.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeUtilisateur(Utilisateur $utilisateur)
    {
        return $this->utilisateurs->removeElement($utilisateur);
    }

    /**
     * Get utilisateurs.
     *
     * @return \Doctrine\Common\Collections\Collection
     * @codeCoverageIgnore
     */
    public function getUtilisateurs()
    {
        return $this->utilisateurs;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * @param mixed $libelle
     * @codeCoverageIgnore
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * @param mixed $listeRoles
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     */
    public function getListeRoles()
    {
        return $this->listeRoles;
    }
}