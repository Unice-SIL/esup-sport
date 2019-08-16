<?php

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

/**
 * @ORM\Entity
 * @Gedmo\Loggable 
 */
class Reservabilite implements \UcaBundle\Entity\Interfaces\JsonSerializable
{

    use \UcaBundle\Entity\Traits\JsonSerializable;

    #region Propriétés
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** @ORM\ManyToMany(targetEntity="Ressource") */
    private $ressource;

    /**
     * @ORM\OneToOne(targetEntity="DhtmlxEvenement", mappedBy="reservabilite")
     */
    private $evenement;

    /** @ORM\OneToMany(targetEntity="Reservation", mappedBy="reservabilite") */
    protected $reservations;


    #endregion
    public function isFull(){
        return !(count($this->reservations) < $this->ressource[0]->getFormatResa()[0]->getCapacite());
    }
    #region Méthodes

    public function jsonSerializeProperties()
    {
        return [];
    }

    #endregion


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ressource = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Add ressource.
     *
     * @param \UcaBundle\Entity\Ressource $ressource
     *
     * @return Reservabilite
     */
    public function addRessource(\UcaBundle\Entity\Ressource $ressource)
    {
        $this->ressource[] = $ressource;

        return $this;
    }

    /**
     * Remove ressource.
     *
     * @param \UcaBundle\Entity\Ressource $ressource
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeRessource(\UcaBundle\Entity\Ressource $ressource)
    {
        return $this->ressource->removeElement($ressource);
    }

    /**
     * Get ressource.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRessource()
    {
        return $this->ressource;
    }

    /**
     * Set evenement.
     *
     * @param \UcaBundle\Entity\DhtmlxEvenement|null $evenement
     *
     * @return Reservabilite
     */
    public function setEvenement(\UcaBundle\Entity\DhtmlxEvenement $evenement = null)
    {
        $this->evenement = $evenement;

        return $this;
    }

    /**
     * Get evenement.
     *
     * @return \UcaBundle\Entity\DhtmlxEvenement|null
     */
    public function getEvenement()
    {
        return $this->evenement;
    }
}
