<?php

/*
 * Classe - ShnuRubrique:
 *
 * Permet d'enregistrer les rubriques de la partie sport de haut niveau
 * Les éléments sont organisables.
*/

namespace App\Entity\Uca;

use App\Annotations\CKEditor;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ShnuRubriqueRepository")
 * @Vich\Uploadable
 * @Gedmo\Loggable
 * @ORM\EntityListeners({"App\Service\Listener\Entity\ShnuRubriqueListener"})
 */
class ShnuRubrique
{
    //region Propriétés

    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ordre;

    /**
     * @Assert\NotBlank(message="shnurubrique.titre.notnull")
     * @Gedmo\Translatable
     * @Gedmo\Versioned
     * @ORM\Column(type="string", nullable=false)
     */
    private $titre;

    /**
     * @Gedmo\Versioned
     * @ORM\ManyToOne(targetEntity="TypeRubrique", inversedBy="rubriqueShnu", cascade={"persist"})
     */
    private $type;

    /**
     * @Gedmo\Translatable
     * @Gedmo\Versioned
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Url
     */
    private $lien;

    /**
     * @Gedmo\Translatable
     * @Gedmo\Versioned
     * @ORM\Column(type="text", nullable=true)
     * @CKEditor
     */
    private $texte;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @Vich\UploadableField(mapping="map_image", fileNameProperty="image")
     * @Assert\File(
     *     mimeTypes = {"image/png", "image/jpeg", "image/tiff"},
     *     mimeTypesMessage = "activite.image.format"
     * )
     * @Assert\Expression("this.getImage() !== null || this.getImageFile() !== null", message="shnurubrique.image.notnull")
     */
    private $imageFile;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    //endregion

    // region methods

    public function setImageFile(File $imageFile = null)
    {
        $this->imageFile = $imageFile;
        if ($imageFile) {
            $this->updatedAt = new \DateTime('now');
        }

        return $this;
    }

    // endregion

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
     * Set ordre.
     *
     * @param int $ordre
     *
     * @return ShnuHighlight
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
     * @return null|int
     * @codeCoverageIgnore
     */
    public function getOrdre()
    {
        return $this->ordre;
    }

    /**
     * Set titre.
     *
     * @param string $titre
     *
     * @return ShnuHighlight
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
     * Set texte.
     *
     * @param string $texte
     *
     * @return ShnuHighlight
     * @codeCoverageIgnore
     */
    public function setTexte($texte)
    {
        $this->texte = $texte;

        return $this;
    }

    /**
     * Get texte.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getTexte()
    {
        return $this->texte;
    }

    /**
     * Set updatedAt.
     *
     * @param null|\DateTime $updatedAt
     *
     * @return ShnuHighlight
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
     * Get imageFile.
     *
     * @return null|File
     * @codeCoverageIgnore
     */
    public function getImageFile()
    {
        return $this->imageFile;
    }

    /**
     * Set image.
     *
     * @param string $image
     *
     * @return ShnuHighlight
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
     * @codeCoverageIgnore
     */
    public function getType(): ?TypeRubrique
    {
        return $this->type;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setType(TypeRubrique $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getLien(): ?string
    {
        return $this->lien;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setLien(?string $lien): self
    {
        $this->lien = $lien;

        return $this;
    }
}
