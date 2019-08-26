<?php

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * @ORM\Table(name="activite")
 * @ORM\Entity(repositoryClass="UcaBundle\Repository\ActiviteRepository")
 * @Vich\Uploadable
 * @Gedmo\Loggable   
 * @UniqueEntity(fields="libelle", message="activite.uniqueentity")
 * @ORM\EntityListeners({"UcaBundle\Service\Listener\Entity\ActiviteListener"})
 */
class Activite
{
    #region Propriétés
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

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
     * @ORM\ManyToOne(targetEntity="ClasseActivite" , inversedBy="activites")
     * @Assert\NotNull(message="activite.classeactivite.notnull") */
    private $classeActivite;

    /** @ORM\OneToMany(targetEntity="FormatActivite", mappedBy="activite")  */
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
    #endregion


    #region Méthodes
    public function getClasseActiviteLibelle()
    {
        return $this->classeActivite->getClasseActiviteLibelle();
    }
    public function getActiviteLibelle()
    {
        return $this->getLibelle();
    }
    public function setImageFile(File $imageFile = null)
    {
        $this->imageFile = $imageFile;
        if ($imageFile)
            $this->updatedAt = new \DateTime('now');
        return $this;
    }
    public function getImageFile()
    {
        return $this->imageFile;
    }
    public function updateClasseActiviteLibelle()
    {
        $this->classeActiviteLibelle = $this->getClasseActivite()->getLibelle();

        return $this;
    }
    #endregion

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->formatsActivite = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Activite
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
     * Set description.
     *
     * @param string $description
     *
     * @return Activite
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
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set updatedAt.
     *
     * @param \DateTime|null $updatedAt
     *
     * @return Activite
     */
    public function setUpdatedAt($updatedAt = null)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt.
     *
     * @return \DateTime|null
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set classeActivite.
     *
     * @param \UcaBundle\Entity\ClasseActivite|null $classeActivite
     *
     * @return Activite
     */
    public function setClasseActivite(\UcaBundle\Entity\ClasseActivite $classeActivite = null)
    {
        $this->classeActivite = $classeActivite;

        return $this;
    }

    /**
     * Get classeActivite.
     *
     * @return \UcaBundle\Entity\ClasseActivite|null
     */
    public function getClasseActivite()
    {
        return $this->classeActivite;
    }

    /**
     * Add formatsActivite.
     *
     * @param \UcaBundle\Entity\FormatActivite $formatsActivite
     *
     * @return Activite
     */
    public function addFormatsActivite(\UcaBundle\Entity\FormatActivite $formatsActivite)
    {
        $this->formatsActivite[] = $formatsActivite;

        return $this;
    }

    /**
     * Remove formatsActivite.
     *
     * @param \UcaBundle\Entity\FormatActivite $formatsActivite
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeFormatsActivite(\UcaBundle\Entity\FormatActivite $formatsActivite)
    {
        return $this->formatsActivite->removeElement($formatsActivite);
    }

    /**
     * Get formatsActivite.
     *
     * @return \Doctrine\Common\Collections\Collection
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
     */
    public function setClasseActiviteLibelle($classeActiviteLibelle)
    {
        $this->classeActiviteLibelle = $classeActiviteLibelle;

        return $this;
    }
}
