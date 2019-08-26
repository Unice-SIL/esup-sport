<?php

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;
use Gedmo\Translatable\Translatable;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="UcaBundle\Repository\ClasseActiviteRepository")
 * @ORM\EntityListeners({"UcaBundle\Service\Listener\Entity\ClasseActiviteListener"})
 * @Vich\Uploadable
 * @Gedmo\Loggable
 * @UniqueEntity(fields="libelle", message="classeactivite.uniqueentity")
 */
class ClasseActivite
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
     * @Assert\NotBlank(message="classeactivite.libelle.notblank")
     */
    protected $libelle;

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
    #endregion

    #region Méthodes
    public function getClasseActiviteLibelle()
    {
        return $this->getLibelle();
    }
    function __toString()
    {
        return $this->libelle;
    }
    public function updateTypeActiviteLibelle()
    {
        $this->typeActiviteLibelle = $this->getTypeActivite()->getLibelle();

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
     * @return ClasseActivite
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
     * Set typeActivite.
     *
     * @param \UcaBundle\Entity\TypeActivite|null $typeActivite
     *
     * @return ClasseActivite
     */
    public function setTypeActivite(\UcaBundle\Entity\TypeActivite $typeActivite = null)
    {
        $this->typeActivite = $typeActivite;

        return $this;
    }

    /**
     * Get typeActivite.
     *
     * @return \UcaBundle\Entity\TypeActivite|null
     */
    public function getTypeActivite()
    {
        return $this->typeActivite;
    }

    /**
     * Add activite.
     *
     * @param \UcaBundle\Entity\Activite $activite
     *
     * @return ClasseActivite
     */
    public function addActivite(\UcaBundle\Entity\Activite $activite)
    {
        $this->activite[] = $activite;

        return $this;
    }

    /**
     * Remove activite.
     *
     * @param \UcaBundle\Entity\Activite $activite
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeActivite(\UcaBundle\Entity\Activite $activite)
    {
        return $this->activite->removeElement($activite);
    }

    /**
     * Get activite.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getActivite()
    {
        return $this->activite;
    }

    /**
     * Get activites.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getActivites()
    {
        return $this->activites;
    }

    public function setImageFile(File $image = null)
    {
        $this->imageFile = $image;
        if ($image) 
            $this->updatedAt = new \DateTime('now');
    }

    public function getImageFile()
    {
        return $this->imageFile;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }

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
     * Set typeActiviteLibelle.
     *
     * @param string $typeActiviteLibelle
     *
     * @return ClasseActivite
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
     */
    public function getTypeActiviteLibelle()
    {
        return $this->typeActiviteLibelle;
    }
}
