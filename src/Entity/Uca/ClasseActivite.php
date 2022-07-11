<?php

/*
 * Classe - Classe d'Activité:
 *
 * Une classe d'activté (ex: sport en salle) contient les activités associées
 * Elle appartient au type d'activité (ex: le sport).
*/

namespace App\Entity\Uca;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ClasseActiviteRepository")
 * @Vich\Uploadable
 * @Gedmo\Loggable
 * @UniqueEntity(fields="libelle", message="classeactivite.uniqueentity")
 */
class ClasseActivite implements \App\Entity\Uca\Interfaces\JsonSerializable
{
    use \App\Entity\Uca\Traits\JsonSerializable;

    /**
     * @Gedmo\Translatable
     * @Gedmo\Versioned
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="classeactivite.libelle.notblank")
     */
    protected $libelle;

    //region Propriétés
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="TypeActivite" , inversedBy="classeActivite")
     * @Assert\NotNull(message="classeactivite.typeactivite.notnull")
     */
    private $typeActivite;

    /**
     * @ORM\OneToMany(targetEntity="Activite", mappedBy="classeActivite" , fetch="EXTRA_LAZY")
     */
    private $activites;

    /** @ORM\Column(type="string", length=255) */
    private $image;

    /** @Vich\UploadableField(mapping="map_image", fileNameProperty="image")
     * @Assert\File(
     *     mimeTypes = {"image/png", "image/jpeg", "image/tiff"},
     *     mimeTypesMessage = "classeactivite.image.format"
     * )
     * @Assert\Expression("this.getImage() !== null || this.getImageFile() !== null", message="classeactivite.image.notnull")
     */
    private $imageFile;

    /** @ORM\Column(type="datetime",nullable=true) */
    private $updatedAt;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="text")
     */
    private $typeActiviteLibelle;
    //endregion

    /**
     * Constructor.
     *
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        $this->formatsActivite = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString()
    {
        return $this->libelle;
    }

    //endregion

    //region Méthodes
    public function jsonSerializeProperties()
    {
        return ['id', 'libelle'];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getClasseActiviteLibelle()
    {
        return $this->getLibelle();
    }

    public function updateTypeActiviteLibelle()
    {
        $this->typeActiviteLibelle = $this->getTypeActivite()->getLibelle();

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
     * Set libelle.
     *
     * @param string $libelle
     *
     * @return ClasseActivite
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
     * Set typeActivite.
     *
     * @return ClasseActivite
     * @codeCoverageIgnore
     */
    public function setTypeActivite(TypeActivite $typeActivite = null)
    {
        $this->typeActivite = $typeActivite;

        return $this;
    }

    /**
     * Get typeActivite.
     *
     * @return null|TypeActivite
     * @codeCoverageIgnore
     */
    public function getTypeActivite()
    {
        return $this->typeActivite;
    }

    /**
     * Add activite.
     *
     * @return ClasseActivite
     * @codeCoverageIgnore
     */
    public function addActivite(Activite $activite)
    {
        $this->activite[] = $activite;

        return $this;
    }

    /**
     * Remove activite.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeActivite(Activite $activite)
    {
        return $this->activite->removeElement($activite);
    }

    /**
     * Get activite.
     *
     * @return \Doctrine\Common\Collections\Collection
     * @codeCoverageIgnore
     */
    public function getActivite()
    {
        return $this->activite;
    }

    /**
     * Get activites.
     *
     * @return \Doctrine\Common\Collections\Collection
     * @codeCoverageIgnore
     */
    public function getActivites()
    {
        return $this->activites;
    }

    public function setImageFile(File $image = null)
    {
        $this->imageFile = $image;
        if ($image) {
            $this->updatedAt = new \DateTime('now');
        }
    }

    /**
     * @codeCoverageIgnore
     */
    public function getImageFile()
    {
        return $this->imageFile;
    }

    /**
     * @codeCoverageIgnore
     *
     * @param mixed $image
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set updatedAt.
     *
     * @param null|\DateTime $updatedAt
     *
     * @return Activite
     * @codeCoverageIgnore
     */
    public function setUpdatedAt($updatedAt = null)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt.
     *
     * @return null|\DateTime
     * @codeCoverageIgnore
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set typeActiviteLibelle.
     *
     * @param string $typeActiviteLibelle
     *
     * @return ClasseActivite
     * @codeCoverageIgnore
     */
    public function setTypeActiviteLibelle($typeActiviteLibelle)
    {
        $this->typeActiviteLibelle = $typeActiviteLibelle;

        return $this;
    }

    /**
     * Get typeActiviteLibelle.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getTypeActiviteLibelle()
    {
        return $this->typeActiviteLibelle;
    }
}