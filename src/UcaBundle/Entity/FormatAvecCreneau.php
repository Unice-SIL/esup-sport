<?php

/*
 * Classe - FormatAvecCreneau:
 *
 * L'un des trois format d'activtié (hérité)
 * Il s'agit du format qui contiendra les creneaux auquels l'utilisateur pourra s'inscrire
 * C'est au niveau du format que plusieurs élément tels que l'étatblissement, le niveau sont définit.
*/

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="UcaBundle\Repository\FormatAvecCreneauRepository")
 * @Gedmo\Loggable
 */
class FormatAvecCreneau extends FormatActivite implements \UcaBundle\Entity\Interfaces\Article
{
    //region Propriétés
    /** @ORM\OneToMany(targetEntity="Creneau", mappedBy="formatActivite", fetch="LAZY") */
    private $creneaux;

    //endregion

    //region Méthodes
    //endregion

    /**
     * Constructor.
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
    public function addCreneaux(Creneau $creneaux)
    {
        $this->creneaux[] = $creneaux;

        return $this;
    }

    /**
     * Remove creneaux.
     *
     * @param \UcaBundle\Entity\Creneau $creneaux
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     */
    public function removeCreneaux(Creneau $creneaux)
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
