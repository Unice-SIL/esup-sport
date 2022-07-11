<?php

/*
 * Classe - TypeRubrique
 *
 * Permet de modifier le type de rubrique pour le SHNU, c'est un élement qui est déterminant sur le comportement
 * Ces élements ne sont pas éditable dans l'interface.
*/

namespace App\Entity\Uca;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TypeRubriqueRepository")
 * @UniqueEntity(fields="libelle", message="typerubrique.uniqueentity")
 */
class TypeRubrique implements \App\Entity\Uca\Interfaces\JsonSerializable
{
    use \App\Entity\Uca\Traits\JsonSerializable;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="typerubrique.libelle.notblank")
     */
    protected $libelle;

    /** @ORM\OneToMany(targetEntity="ShnuRubrique", mappedBy="type") */
    protected $rubriqueShnu;

    //region Propriétés
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    //endregion

    /**
     * Constructor.
     *
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        $this->rubriqueShnu = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return TypeRubrique
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
     * Add ShnuRubrique.
     *
     * @return TypeRubrique
     * @codeCoverageIgnore
     */
    public function addRubriqueShnu(ShnuRubrique $rubriqueShnu)
    {
        $this->rubriqueShnu[] = $rubriqueShnu;

        return $this;
    }

    /**
     * Remove rubriqueShnu.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeRubriqueShnu(ShnuRubrique $rubriqueShnu)
    {
        return $this->rubriqueShnu->removeElement($rubriqueShnu);
    }

    /**
     * Get rubriqueShnu.
     *
     * @return \Doctrine\Common\Collections\Collection
     * @codeCoverageIgnore
     */
    public function getRubriqueShnu()
    {
        return $this->rubriqueShnu;
    }
}
