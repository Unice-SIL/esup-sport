<?php

/*
 * Classe - ImageFond:
 *
 * Entité technique permettant d'enregesitrer et de nommer les images de fonds.
*/

namespace App\Entity\Uca;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Table(name="image_fond")
 * @ORM\Entity(repositoryClass="App\Repository\ImageFondRepository")
 * @Vich\Uploadable
 * @Gedmo\Loggable
 */
class ImageFond
{
    //region Propriétés

    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $emplacement;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="imagefond.titre.notnull")
     */
    private $titre;

    /** @ORM\Column(type="string", length=255, nullable=false) */
    private $image;

    /** @Vich\UploadableField(mapping="map_image", fileNameProperty="image")
     * @Assert\File(
     *     mimeTypes = {"image/png", "image/jpeg", "image/tiff"},
     *     mimeTypesMessage = "imagefond.image.format"
     * )
     * @Assert\Expression("this.getImage() !== null || this.getImageFile() !== null", message="imagefond.image.notnull")
     */
    private $imageFile;

    /** @Gedmo\Versioned
     * @ORM\Column(type="datetime", nullable=true) */
    private $updatedAt;
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

    /**
     * @codeCoverageIgnore
     */
    public function getImageFile()
    {
        return $this->imageFile;
    }

    //endregion

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
     * Set emplacement.
     *
     * @param string $emplacement
     *
     * @return ImageFond
     * @codeCoverageIgnore
     */
    public function setEmplacement($emplacement)
    {
        $this->emplacement = $emplacement;

        return $this;
    }

    /**
     * Get emplacement.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getEmplacement()
    {
        return $this->emplacement;
    }

    /**
     * Set titre.
     *
     * @param string $titre
     *
     * @return ImageFond
     * @codeCoverageIgnore
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;

        return $this;
    }

    /**
     * Get titre.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * Set image.
     *
     * @param string $image
     *
     * @return ImageFond
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
     * @return ImageFond
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
}