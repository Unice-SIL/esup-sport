<?php

/*
 * Classe - Appel:
 *
 * Elle permet la gestion de la présence des étudiants.
*/

namespace App\Entity\Uca;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AppelRepository")
 */
class Appel
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /** @ORM\ManyToOne(targetEntity="Utilisateur", inversedBy="appels") */
    private $utilisateur;

    /** @ORM\ManyToOne(targetEntity="DhtmlxEvenement", inversedBy="appels") */
    private $dhtmlxEvenement;

    /** @ORM\Column(type="boolean", nullable=false) */
    private $present;

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
     * Set present.
     *
     * @param bool $present
     *
     * @return Appel
     * @codeCoverageIgnore
     */
    public function setPresent($present)
    {
        $this->present = $present;

        return $this;
    }

    /**
     * Get present.
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function getPresent()
    {
        return $this->present;
    }

    /**
     * Set utilisateur.
     *
     * @return Appel
     * @codeCoverageIgnore
     */
    public function setUtilisateur(Utilisateur $utilisateur = null)
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    /**
     * Get utilisateur.
     *
     * @return null|Utilisateur
     * @codeCoverageIgnore
     */
    public function getUtilisateur()
    {
        return $this->utilisateur;
    }

    /**
     * Set dhtmlxEvenement.
     *
     * @return Appel
     * @codeCoverageIgnore
     */
    public function setDhtmlxEvenement(DhtmlxEvenement $dhtmlxEvenement = null)
    {
        $this->dhtmlxEvenement = $dhtmlxEvenement;

        return $this;
    }

    /**
     * Get dhtmlxEvenement.
     *
     * @return null|DhtmlxEvenement
     * @codeCoverageIgnore
     */
    public function getDhtmlxEvenement()
    {
        return $this->dhtmlxEvenement;
    }
}