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
 * @UniqueEntity(fields="libelle", message="typeactivite.uniqueentity")
 */
class TypeActivite
{
    #region Propriétés
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

    /** @ORM\OneToMany(targetEntity="ClasseActivite",mappedBy="typeActivite") */
    protected $classeActivite;
    #endregion

    #region Méthodes
    function __toString()
    {
        return $this->libelle;
    }
    #endregion

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->classeActivite = new \Doctrine\Common\Collections\ArrayCollection();
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
    public function addClasseActivite(\UcaBundle\Entity\ClasseActivite $classeActivite)
    {
        $this->classeActivite[] = $classeActivite;

        return $this;
    }

    /**
     * Remove classeActivite.
     *
     * @param \UcaBundle\Entity\ClasseActivite $classeActivite
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeClasseActivite(\UcaBundle\Entity\ClasseActivite $classeActivite)
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
