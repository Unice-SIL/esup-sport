<?php

/*
 * Classe - Fichier:
 *
 * Entité technique permettant de stocker des fichiers
*/

namespace App\Entity\Uca;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FichierRepository")
 * @Vich\Uploadable
 */
class Fichier
{
    //region Propriétés
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** @ORM\Column(type="string") */
    private $code;

    /** @Vich\UploadableField(mapping="referentiel_immobilier", fileNameProperty="image", size="size", mimeType="mimeType") */
    private $imageFile;

    /** @ORM\Column(type="string") */
    private $image;

    /** @ORM\Column(type="integer") */
    private $size;

    /** @ORM\Column(type="string") */
    private $mimeType;

    /** @ORM\Column(type="datetime",nullable=true) */
    private $updatedAt;
    //endregion

    //region Méthodes
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
     * Set size.
     *
     * @param int $size
     *
     * @return Fichier
     * @codeCoverageIgnore
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size.
     *
     * @return int
     * @codeCoverageIgnore
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set mimeType.
     *
     * @param int $mimeType
     *
     * @return Fichier
     * @codeCoverageIgnore
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * Get mimeType.
     *
     * @return int
     * @codeCoverageIgnore
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * Set code.
     *
     * @param string $code
     *
     * @return Fichier
     * @codeCoverageIgnore
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getCode()
    {
        return $this->code;
    }
}