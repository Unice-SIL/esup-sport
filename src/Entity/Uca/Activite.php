<?php

/*
 * Classe - Activité:
 *
 * Une activité correspond à la discipline (ex: ski)
 * Un utilisateur ne s'inscrit pas à une activité mais à un format.
*/

namespace App\Entity\Uca;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Table(name="activite")
 * @ORM\Entity(repositoryClass="App\Repository\ActiviteRepository")
 * @Vich\Uploadable
 * @Gedmo\Loggable
 * @UniqueEntity(fields="libelle", message="activite.uniqueentity")
 */
class Activite implements \App\Entity\Uca\Interfaces\JsonSerializable
{
    use \App\Entity\Uca\Traits\JsonSerializable;

    //region Propriétés
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ordre;

    /** @Gedmo\Translatable
     * @Gedmo\Versioned
     * @ORM\Column(name="libelle", type="string", length=100)
     * @Assert\NotBlank(message="activite.libelle.notblank") */
    private $libelle;

    /** @Gedmo\Translatable
     * @Gedmo\Versioned
     * @ORM\Column(name="description", type="text")
     * @Assert\NotBlank(message="activite.description.notblank") */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="ClasseActivite" , inversedBy="activites", fetch="LAZY")
     * @Assert\NotNull(message="activite.classeactivite.notnull") */
    private $classeActivite;

    /** @ORM\OneToMany(targetEntity="FormatActivite", mappedBy="activite", fetch="LAZY")  */
    private $formatsActivite;

    /** @ORM\Column(type="string", length=255) */
    private $image;

    /** @Vich\UploadableField(mapping="map_image", fileNameProperty="image")
     * @Assert\File(
     *     mimeTypes = {"image/png", "image/jpeg", "image/tiff"},
     *     mimeTypesMessage = "activite.image.format"
     * )
     * @Assert\Expression("this.getImage() !== null || this.getImageFile() !== null", message="activite.image.notnull")
     */
    private $imageFile;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $updatedAt;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="text")
     */
    private $classeActiviteLibelle;

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

    //endregion

    //region Méthodes
    public function jsonSerializeProperties()
    {
        return ['id', 'libelle', 'description', 'image', 'classeActivite'];
    }

    public function getClasseActiviteLibelle()
    {
        return $this->classeActivite->getClasseActiviteLibelle();
    }

    /**
     * @codeCoverageIgnore
     */
    public function getActiviteLibelle()
    {
        return $this->getLibelle();
    }

    public function setImageFile(File $imageFile = null)
    {
        $this->imageFile = $imageFile;
        if ($imageFile) {
            $this->updatedAt = new \DateTime('now');
        }

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getImageFile()
    {
        return $this->imageFile;
    }

    public function updateClasseActiviteLibelle()
    {
        $this->classeActiviteLibelle = $this->getClasseActivite()->getLibelle();

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
     * @return Activite
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
     * Set description.
     *
     * @param string $description
     *
     * @return Activite
     * @codeCoverageIgnore
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set image.
     *
     * @param string $image
     *
     * @return Activite
     * @codeCoverageIgnore
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image.
     *
     * @return string
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
     * Set classeActivite.
     *
     * @return Activite
     * @codeCoverageIgnore
     */
    public function setClasseActivite(ClasseActivite $classeActivite = null)
    {
        $this->classeActivite = $classeActivite;

        return $this;
    }

    /**
     * Get classeActivite.
     *
     * @return null|ClasseActivite
     * @codeCoverageIgnore
     */
    public function getClasseActivite()
    {
        return $this->classeActivite;
    }

    /**
     * Add formatsActivite.
     *
     * @return Activite
     * @codeCoverageIgnore
     */
    public function addFormatsActivite(FormatActivite $formatsActivite)
    {
        $this->formatsActivite[] = $formatsActivite;

        return $this;
    }

    /**
     * Remove formatsActivite.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeFormatsActivite(FormatActivite $formatsActivite)
    {
        return $this->formatsActivite->removeElement($formatsActivite);
    }

    /**
     * Get formatsActivite.
     *
     * @return \Doctrine\Common\Collections\Collection
     * @codeCoverageIgnore
     */
    public function getFormatsActivite()
    {
        return $this->formatsActivite;
    }

    /**
     * Set classeActiviteLibelle.
     *
     * @param string $classeActiviteLibelle
     *
     * @return Activite
     * @codeCoverageIgnore
     */
    public function setClasseActiviteLibelle($classeActiviteLibelle)
    {
        $this->classeActiviteLibelle = $classeActiviteLibelle;

        return $this;
    }

    /**
     * Set ordre.
     *
     * @param int $ordre
     *
     * @return Activite
     * @codeCoverageIgnore
     */
    public function setOrdre($ordre)
    {
        $this->ordre = $ordre;

        return $this;
    }

    /**
     * Get ordre.
     *
     * @return int
     * @codeCoverageIgnore
     */
    public function getOrdre()
    {
        return $this->ordre;
    }
}