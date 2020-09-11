<?php
/*
 * Classe - Materiel:
 *
 * Il s'agit d'une ressource (hérité)
 * Un matériel est réservable par un utilisateur.
*/

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @Gedmo\Loggable
 */
class Materiel extends Ressource
{
    //region Propriétés
    /** @Gedmo\Versioned
     *  @ORM\Column(type="integer", nullable=true)
     *  @Assert\NotBlank(message="materiel.quantite.notblank")
     *  @Assert\Regex(pattern="/^\d+$/", message="message.typeinvalide.entier")
     *  @Assert\GreaterThanOrEqual(value = 0)
     */
    private $quantiteDisponible;
    //endregion

    //region Méthodes

    public function getCapacite()
    {
        return $this->getQuantiteDisponible();
    }

    //endregion

    /**
     * Set quantiteDisponible.
     *
     * @param null|int $quantiteDisponible
     *
     * @return Materiel
     */
    public function setQuantiteDisponible($quantiteDisponible = null)
    {
        $this->quantiteDisponible = $quantiteDisponible;

        return $this;
    }

    /**
     * Get quantiteDisponible.
     *
     * @return int|null
     */
    public function getQuantiteDisponible()
    {
        return $this->quantiteDisponible;
    }
}
