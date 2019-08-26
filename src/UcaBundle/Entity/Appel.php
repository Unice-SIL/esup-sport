<?php

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Appel
{

    /**
     * 
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
     */
    public function getPresent()
    {
        return $this->present;
    }

    /**
     * Set utilisateur.
     *
     * @param \UcaBundle\Entity\Utilisateur|null $utilisateur
     *
     * @return Appel
     */
    public function setUtilisateur(\UcaBundle\Entity\Utilisateur $utilisateur = null)
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    /**
     * Get utilisateur.
     *
     * @return \UcaBundle\Entity\Utilisateur|null
     */
    public function getUtilisateur()
    {
        return $this->utilisateur;
    }

    /**
     * Set dhtmlxEvenement.
     *
     * @param \UcaBundle\Entity\DhtmlxEvenement|null $dhtmlxEvenement
     *
     * @return Appel
     */
    public function setDhtmlxEvenement(\UcaBundle\Entity\DhtmlxEvenement $dhtmlxEvenement = null)
    {
        $this->dhtmlxEvenement = $dhtmlxEvenement;

        return $this;
    }

    /**
     * Get dhtmlxEvenement.
     *
     * @return \UcaBundle\Entity\DhtmlxEvenement|null
     */
    public function getDhtmlxEvenement()
    {
        return $this->dhtmlxEvenement;
    }
}
