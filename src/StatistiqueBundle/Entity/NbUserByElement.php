<?php

/*
 * Classe - NbUserByElement:
 *
 * Données sur le nombre d'utilisateur actifs pour un element donné
*/

namespace StatistiqueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="nb_user_by_element")
 * @ORM\Entity(repositoryClass="StatistiqueBundle\Repository\NbUserByElementRepository")
 */
class NbUserByElement
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="type", type="integer", length=3)
     * Valeurs possible:
     *      - 1 = Par profil
     *      - 2 = par niveau d'étude
     */
    private $type;

    /** @ORM\Column(name="libelle", type="string", length=255)*/
    private $libelle;

    /** @ORM\Column(name="nombre_user", type="integer", length=11) */
    private $nombreUser;

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
     * Set type.
     *
     * @param string $type
     *
     * @return NbUserByElement
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set libelle.
     *
     * @param string $libelle
     *
     * @return NbUserByElement
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
     * Set nombreUser.
     *
     * @param mixed $nombreUser
     *
     * @return NbUserByElement
     */
    public function setNombreUser($nombreUser)
    {
        $this->nombreUser = $nombreUser;

        return $this;
    }

    /**
     * Get nombreUser.
     *
     * @return \int
     */
    public function getNombreUser()
    {
        return $this->nombreUser;
    }
}
