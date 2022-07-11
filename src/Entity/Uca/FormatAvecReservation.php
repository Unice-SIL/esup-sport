<?php

/*
 * Classe - FormatAvecReservation:
 *
 * L'un des trois formats d'activité disponible (hérité)
 * Ce format donne accès à la réservation de de ressources par les utilisateurs.
*/

namespace App\Entity\Uca;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FormatAvecReservationRepository")
 * @Gedmo\Loggable
 * @ORM\EntityListeners({"App\Service\Listener\Entity\FormatAvecReservationListener"})
 */
class FormatAvecReservation extends FormatActivite implements \App\Entity\Uca\Interfaces\Article
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
     *
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add lieu.
     *
     * @param Lieu $lieu
     *
     * @return FormatAvecReservation
     * @codeCoverageIgnore
     */
    public function addRessource(Ressource $ressource)
    {
        $this->ressource[] = $ressource;

        return $this;
    }

    /**
     * Remove lieu.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeRessource(Ressource $ressource)
    {
        return $this->ressource->removeElement($ressource);
    }

    /**
     * Get ressource.
     *
     * @return \Doctrine\Common\Collections\Collection
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     */
    public function getListeRessources()
    {
        return $this->listeRessources;
    }

    /**
     * Fonction qui permet de savoir si un FormatAvecReservation nécessite des partenaires.
     *
     * @codeCoverageIgnore
     */
    public function formatAvecPartenaires(): bool
    {
        foreach ($this->ressource as $ressource) {
            if ($ressource->getNbPartenaires() > 0) {
                return true;
            }
        }

        return false;
    }
}
