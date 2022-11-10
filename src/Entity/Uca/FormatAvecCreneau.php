<?php

/*
 * Classe - FormatAvecCreneau:
 *
 * L'un des trois format d'activtié (hérité)
 * Il s'agit du format qui contiendra les creneaux auquels l'utilisateur pourra s'inscrire
 * C'est au niveau du format que plusieurs élément tels que l'étatblissement, le niveau sont définit.
*/

namespace App\Entity\Uca;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FormatAvecCreneauRepository")
 * @Gedmo\Loggable
 */
class FormatAvecCreneau extends FormatActivite implements \App\Entity\Uca\Interfaces\Article
{
    //region Propriétés
    /** @ORM\OneToMany(targetEntity="Creneau", mappedBy="formatActivite", fetch="LAZY") */
    private $creneaux;

    //endregion

    //region Méthodes
    //endregion

    /**
     * Constructor.
     *
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add creneaux.
     *
     * @return FormatAvecCreneau
     * @codeCoverageIgnore
     */
    public function addCreneaux(Creneau $creneaux)
    {
        $this->creneaux[] = $creneaux;

        return $this;
    }

    /**
     * Remove creneaux.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeCreneaux(Creneau $creneaux)
    {
        return $this->creneaux->removeElement($creneaux);
    }

    /**
     * Get creneaux.
     *
     * @return \Doctrine\Common\Collections\Collection
     * @codeCoverageIgnore
     */
    public function getCreneaux()
    {
        return $this->creneaux;
    }
}