<?php

/*
 * Classe - LogConnexion:
 *
 * ENtité technique permettant d'avoir l'historique des logs.
*/

namespace App\Entity\Uca;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LogConnexionRepository")
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     */
    public function getDateConnexion()
    {
        return $this->dateConnexion;
    }

    //endregion
}