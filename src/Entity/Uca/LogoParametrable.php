<?php

/*
 * Classe - LogoPartenaire:
 *
 * Entité correspondant au logos des LogoPartenaireListener
 * Ces éléments sont organisables.
*/

namespace App\Entity\Uca;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Table(name="logo_parametrable")
 * @ORM\Entity(repositoryClass="App\Repository\LogoParametrableRepository")
 * @Vich\Uploadable
 * @Gedmo\Loggable
 */
class LogoParametrable
{
    //region Propriétés

    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @ORM\Column(type="string", length=255, nullable=false) */
    private $image;

    /** @ORM\Column(type="string", length=255, nullable=true) */
    private $description;

    /** @Vich\UploadableField(mapping="map_logo", fileNameProperty="image")
     * @Assert\File(
     *     mimeTypes = {"image/png", "image/jpeg", "image/tiff"},
     *     mimeTypesMessage = "logoparametrable.image.format"
     * )
     * @Assert\Expression("this.getImage() !== null || this.getImageFile() !== null", message="logoparametrable.image.notnull")
     */
    private $imageFile;

    /** @Gedmo\Versioned
     * @ORM\Column(type="datetime", nullable=true) */
    private $updatedAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $emplacement;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $actif;
    //endregion

    //region Méthodes

    /**
     * @codeCoverageIgnore
     */
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
     * Set image.
     *
     * @param string $image
     *
     * @return self
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
     * @return LogoPartenaire
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
     * @codeCoverageIgnore
     */
    public function getEmplacement(): ?string
    {
        return $this->emplacement;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setEmplacement(string $emplacement): self
    {
        $this->emplacement = $emplacement;

        return $this;
    }

    /**
     * Get the value of description
     * @codeCoverageIgnore
     */ 
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the value of description
     * @codeCoverageIgnore
     * @return  self
     */ 
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     * 
     * Get the value of actif
     */ 
    public function getActif()
    {
        return $this->actif;
    }

    /**
     * @codeCoverageIgnore
     * 
     * Set the value of actif
     *
     * @return  self
     */ 
    public function setActif($actif)
    {
        $this->actif = $actif;

        return $this;
    }
}