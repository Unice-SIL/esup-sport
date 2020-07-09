<?php

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use UcaBundle\Annotations\CKEditor;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * Texte.
 *
 * @ORM\Table(name="logo_partenaire")
 * @ORM\Entity(repositoryClass="UcaBundle\Repository\LogoPartenaireRepository")
 * @Vich\Uploadable
 * @Gedmo\Loggable
 * @ORM\EntityListeners({"UcaBundle\Service\Listener\Entity\LogoPartenaireListener"})
 */
class LogoPartenaire
{
    //region Propriétés

    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @Gedmo\Translatable
     * @Gedmo\Versioned
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="logopartenaire.nom.notblank")
     */
    private $nom;

    /** @ORM\Column(type="string", length=255, nullable=false) */
    private $image;

    /** @Vich\UploadableField(mapping="map_image", fileNameProperty="image")
     * @Assert\File(
     *     mimeTypes = {"image/png", "image/jpeg", "image/tiff"},
     *     mimeTypesMessage = "logopartenaire.image.format"
     * )
     * @Assert\Expression("this.getImage() !== null || this.getImageFile() !== null", message="logopartenaire.image.notnull")
     */
    private $imageFile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Url
     */
    private $lien;

    /**
     * @Gedmo\Translatable
     * @Gedmo\Versioned
     * @ORM\Column(type="text", nullable=true)
     * @CKEditor
     */
    private $description;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ordre;

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
     * Set nom.
     *
     * @param string $nom
     *
     * @return LogoPartenaire
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom.
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set image.
     *
     * @param string $image
     *
     * @return LogoPartenaire
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
     * Set lien.
     *
     * @param null|string $lien
     *
     * @return LogoPartenaire
     */
    public function setLien($lien = null)
    {
        $this->lien = $lien;

        return $this;
    }

    /**
     * Get lien.
     *
     * @return null|string
     */
    public function getLien()
    {
        return $this->lien;
    }

    /**
     * Set description.
     *
     * @param null|string $description
     *
     * @return LogoPartenaire
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return null|string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set ordre.
     *
     * @param int $ordre
     *
     * @return Actualite
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
     */
    public function getOrdre()
    {
        return $this->ordre;
    }

    /**
     * Set updatedAt.
     *
     * @param null|\DateTime $updatedAt
     *
     * @return LogoPartenaire
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
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}
