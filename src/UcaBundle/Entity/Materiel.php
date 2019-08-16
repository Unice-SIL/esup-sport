<?php

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 */
class Materiel extends Ressource
{
    #region Propriétés
    /** @ORM\Column(type="integer", nullable=true)
     *  @Assert\NotBlank(message="materiel.quantite.notblank") 
     */
    private $quantiteDisponible;
    #endregion

    #region Méthodes
    #endregion

    /**
     * Set quantiteDisponible.
     *
     * @param int|null $quantiteDisponible
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
