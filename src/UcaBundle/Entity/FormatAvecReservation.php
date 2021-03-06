<?php

/*
 * Classe - FormatAvecReservation:
 *
 * L'un des trois formats d'activité disponible (hérité)
 * Ce format donne accès à la réservation de de ressources par les utilisateurs.
*/

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="UcaBundle\Repository\FormatAvecReservationRepository")
 * @Gedmo\Loggable
 * @ORM\EntityListeners({"UcaBundle\Service\Listener\Entity\FormatAvecReservationListener"})
 */
class FormatAvecReservation extends FormatActivite implements \UcaBundle\Entity\Interfaces\Article
{
    //region Propriétés
    /** @ORM\ManyToMany(targetEntity="Ressource", inversedBy="formatResa", fetch="LAZY")
     * @Assert\Expression("!this.getRessource().isEmpty()", message="formatactivite.reservation.ressource.notnull")
     */
    private $ressource;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="text")
     */
    private $listeRessources;

    //endregion

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->ressource = new \Doctrine\Common\Collections\ArrayCollection();
    }

    //endregion

    //region Méthodes

    public function updateListeRessources()
    {
        $this->listeRessources = '';
        foreach ($this->getRessource() as $ressource) {
            if (!empty($this->listeRessources)) {
                $this->listeRessources .= ', ';
            }
            $this->listeRessources .= $ressource->getLibelle();
        }

        return $this;
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
     * Add lieu.
     *
     * @param \UcaBundle\Entity\Lieu $lieu
     *
     * @return FormatAvecReservation
     */
    public function addRessource(Ressource $ressource)
    {
        $this->ressource[] = $ressource;

        return $this;
    }

    /**
     * Remove lieu.
     *
     * @param \UcaBundle\Entity\Ressource $ressource
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     */
    public function removeRessource(Ressource $ressource)
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
     * Set listeRessources.
     *
     * @param string $listeRessources
     *
     * @return FormatAvecReservation
     */
    public function setListeRessources($listeRessources)
    {
        $this->listeRessources = $listeRessources;

        return $this;
    }

    /**
     * Get listeRessources.
     *
     * @return string
     */
    public function getListeRessources()
    {
        return $this->listeRessources;
    }
}
