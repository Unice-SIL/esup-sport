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
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\NiveauSportifRepository")
 * @Gedmo\Loggable
 * @UniqueEntity(fields="libelle", message="niveausportif.uniqueentity")
 */
class NiveauSportif implements \App\Entity\Uca\Interfaces\JsonSerializable
{
    use \App\Entity\Uca\Traits\JsonSerializable;

    /** @ORM\ManyToMany(targetEntity="Creneau", mappedBy="niveauxSportifs") */
    protected $creneaux;

    /** @ORM\OneToMany(targetEntity="FormatActiviteNiveauSportif", mappedBy="niveauSportif") */
    protected $formatsActivite;

    //region Propriétés
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Gedmo\Translatable
     * @Gedmo\Versioned
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="niveausportif.libelle.notblank")
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
        $this->formatsActivite = new \Doctrine\Common\Collections\ArrayCollection();
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

    /**
     * Add creneaux.
     *
     * @return NiveauSportif
     * @codeCoverageIgnore
     */
    public function addFormatActivite(FormatActivite $format)
    {
        $this->formatsActivite[] = $format;

        return $this;
    }

    /**
     * Remove creneaux.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeFormatActivite(FormatActivite $formatActivite)
    {
        return $this->creneaux->removeElement($formatActivite);
    }

    /**
     * Get creneaux.
     *
     * @return \Doctrine\Common\Collections\Collection
     * @codeCoverageIgnore
     */
    public function getFormatsActivite()
    {
        return $this->formatsActivite;
    }
}
