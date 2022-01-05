<?php

/*
 * Classe - NbUserByElement:
 *
 * Données sur le nombre d'utilisateur actifs par horaire et un element donné
*/

namespace StatistiqueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="nb_user_by_horaire_and_element")
 * @ORM\Entity(repositoryClass="StatistiqueBundle\Repository\NbUserByHoraireAndElementRepository")
 */
class NbUserByHoraireAndElement
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
     *      - 2 = par age
     *      - 3 = par genre
     */
    private $type;

    /** @ORM\Column(name="horaire", type="string", length=255, nullable=true)*/
    private $horaire;

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
     * Set horaire.
     *
     * @param string $horaire
     *
     * @return NbUserByElement
     */
    public function setHoraire($horaire)
    {
        $this->horaire = $horaire;

        return $this;
    }

    /**
     * Get horaire.
     *
     * @return string
     */
    public function getHoraire()
    {
        return $this->horaire;
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
