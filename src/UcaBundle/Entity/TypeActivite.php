<?php

/*
 * Classe - TypeActivite:
 *
 * Niveau de le plus large de la taxonomie.
 * Pour l'instant un seul type existe : le sport.
*/

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="UcaBundle\Repository\TypeActiviteRepository")
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
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Add classeActivite.
     *
     * @param \UcaBundle\Entity\ClasseActivite $classeActivite
     *
     * @return TypeActivite
     */
    public function addClasseActivite(ClasseActivite $classeActivite)
    {
        $this->classeActivite[] = $classeActivite;

        return $this;
    }

    /**
     * Remove classeActivite.
     *
     * @param \UcaBundle\Entity\ClasseActivite $classeActivite
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeClasseActivite(ClasseActivite $classeActivite)
    {
        return $this->classeActivite->removeElement($classeActivite);
    }

    /**
     * Get classeActivite.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getClasseActivite()
    {
        return $this->classeActivite;
    }
}
