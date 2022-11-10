<?php

/*
 * Classe - NiveauSportif:
 *
 * Indique le niveau sportif d'un format d'activité
 * Aucune interface ne permet de modifier cette donnée.
*/

namespace App\Entity\Uca;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\NiveauSportifRepository")
 * @Gedmo\Loggable
 */
class NiveauSportif implements \App\Entity\Uca\Interfaces\JsonSerializable
{
    use \App\Entity\Uca\Traits\JsonSerializable;

    /** @ORM\ManyToMany(targetEntity="Creneau", mappedBy="niveauxSportifs") */
    protected $creneaux;

    //region Propriétés
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="string")
     */
    private $libelle;

    //endregion

    /**
     * Constructor.
     *
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        $this->creneaux = new \Doctrine\Common\Collections\ArrayCollection();
    }

    //endregion

    //region Méthodes

    public function jsonSerializeProperties()
    {
        return ['libelle'];
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
     * Set libelle.
     *
     * @param string $libelle
     *
     * @return NiveauSportif
     * @codeCoverageIgnore
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelle.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Add creneaux.
     *
     * @return NiveauSportif
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