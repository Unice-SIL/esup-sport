<?php

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @Gedmo\Loggable
 */
class Materiel extends Ressource
{
    #region Propriétés
    /** @Gedmo\Versioned
     *  @ORM\Column(type="integer", nullable=true)
     *  @Assert\NotBlank(message="materiel.quantite.notblank")
     *  @Assert\Regex(pattern="/^\d+$/", message="message.typeinvalide.entier")
     *  @Assert\GreaterThanOrEqual(value = 0)
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
