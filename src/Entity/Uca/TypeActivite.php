<?php

/*
 * Classe - TypeActivite:
 *
 * Niveau de le plus large de la taxonomie.
 * Pour l'instant un seul type existe : le sport.
*/

namespace App\Entity\Uca;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TypeActiviteRepository")
 * @Gedmo\Loggable
 * @UniqueEntity(fields="libelle", message="typeactivite.uniqueentity")
 */
class TypeActivite
{
    /** @ORM\OneToMany(targetEntity="ClasseActivite",mappedBy="typeActivite") */
    protected $classeActivite;
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
     * @Assert\NotBlank(message="typeactivite.libelle.notblank")
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
        $this->classeActivite = new \Doctrine\Common\Collections\ArrayCollection();
    }

    //endregion

    //region Méthodes
    public function __toString()
    {
        return $this->libelle;
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
     * @return TypeActivite
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
     * Add classeActivite.
     *
     * @return TypeActivite
     * @codeCoverageIgnore
     */
    public function addClasseActivite(ClasseActivite $classeActivite)
    {
        $this->classeActivite[] = $classeActivite;

        return $this;
    }

    /**
     * Remove classeActivite.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeClasseActivite(ClasseActivite $classeActivite)
    {
        return $this->classeActivite->removeElement($classeActivite);
    }

    /**
     * Get classeActivite.
     *
     * @return \Doctrine\Common\Collections\Collection
     * @codeCoverageIgnore
     */
    public function getClasseActivite()
    {
        return $this->classeActivite;
    }
}