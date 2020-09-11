<?php

/*
 * Classe - LogConnexion:
 *
 * ENtité technique permettant d'avoir l'historique des logs.
*/

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class LogConnexion
{
    //region Proprietes
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Utilisateur", cascade={"persist"})
     */
    private $utilisateur;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateConnexion;
    //endregion

    /**
     * Constructor.
     *
     * @param mixed $utilisateur
     */
    public function __construct($utilisateur)
    {
        $this->utilisateur = $utilisateur;
        $this->dateConnexion = new \DateTime('now');
    }

    //endregion

    //region Methodes
    //endregion

    //region Getter/Setter

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
     * Set utilisateur.
     *
     * @param string $utilisateur
     *
     * @return LogConnexion
     */
    public function setUtilisateur($utilisateur)
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    /**
     * Get utilisateur.
     *
     * @return string
     */
    public function getUtilisateur()
    {
        return $this->utilisateur;
    }

    /**
     * Set dateConnexion.
     *
     * @param null|\DateTime $dateConnexion
     *
     * @return LogConnexion
     */
    public function setDateConnexion($dateConnexion = null)
    {
        $this->dateConnexion = $dateConnexion;

        return $this;
    }

    /**
     * Get dateConnexion.
     *
     * @return null|\DateTime
     */
    public function getDateConnexion()
    {
        return $this->dateConnexion;
    }

    //endregion
}
