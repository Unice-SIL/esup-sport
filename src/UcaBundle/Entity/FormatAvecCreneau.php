<?php

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

/**
 * @ORM\Entity
 * @Gedmo\Loggable
 */
class FormatAvecCreneau extends FormatActivite implements \UcaBundle\Entity\Interfaces\Article 
{
    #region Propriétés
    /** @ORM\OneToMany(targetEntity="Creneau", mappedBy="formatActivite") */
    private $creneaux;

    #endregion

    #region Méthodes
    #endregion
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->creneaux = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Add creneaux.
     *
     * @param \UcaBundle\Entity\Creneau $creneaux
     *
     * @return FormatAvecCreneau
     */
    public function addCreneaux(\UcaBundle\Entity\Creneau $creneaux)
    {
        $this->creneaux[] = $creneaux;

        return $this;
    }

    /**
     * Remove creneaux.
     *
     * @param \UcaBundle\Entity\Creneau $creneaux
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeCreneaux(\UcaBundle\Entity\Creneau $creneaux)
    {
        return $this->creneaux->removeElement($creneaux);
    }

    /**
     * Get creneaux.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCreneaux()
    {
        return $this->creneaux;
    }
}
