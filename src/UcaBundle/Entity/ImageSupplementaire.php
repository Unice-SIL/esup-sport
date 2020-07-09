<?php

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity
 * @Vich\Uploadable
 */
class ImageSupplementaire
{
    // region Propriétés

    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @ORM\Column(type="string", length=255, nullable=false)*/
    private $image;

    /** @Vich\UploadableField(mapping="map_image", fileNameProperty="image")
     * @Assert\File(
     *     mimeTypes = {"image/png", "image/jpeg","image/jpg", "image/tiff"},
     *     mimeTypesMessage = "imagesupplementaire.image.format"
     * )
     * @Assert\Expression("this.getImage() !== null || this.getImageFile() !== null", message="imagefond.image.notnull")
     */
    private $imageFile;

    /**
     * @ORM\Column(type="datetime", nullable=true) */
    private $updatedAt;

    /** @ORM\ManyToOne(targetEntity="Lieu" ,inversedBy="imagesSupplementaires") */
    private $lieu;

    //endregion

    //region Méthodes
    public function setImageFile(File $imageFile = null)
    {
        $this->imageFile = $imageFile;
        if ($imageFile) {
            $this->updatedAt = new \DateTime('now');
        }

        return $this;
    }

    public function getImageFile()
    {
        return $this->imageFile;
    }

    //endregion

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
     * Set image.
     *
     * @param string $image
     *
     * @return ImageSupplementaire
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
     * @param null|\DateTime $updatedAt
     *
     * @return ImageSupplementaire
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
     * Set lieu.
     *
     * @param null|\UcaBundle\Entity\Lieu $lieu
     *
     * @return ImageSupplementaire
     */
    public function setLieu(Lieu $lieu = null)
    {
        $this->lieu = $lieu;

        return $this;
    }

    /**
     * Get lieu.
     *
     * @return \UcaBundle\Entity\Lieu|null
     */
    public function getLieu()
    {
        return $this->lieu;
    }
}
