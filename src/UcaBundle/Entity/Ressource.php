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
 * @ORM\Entity(repositoryClass="UcaBundle\Repository\RessourceRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="format", type="string")
 * @ORM\DiscriminatorMap( {
 *   "Lieu" = "Lieu", 
 *   "Materiel" = "Materiel"
 * } )
 * @Gedmo\Loggable
 * @Vich\Uploadable
 */

abstract class Ressource implements \UcaBundle\Entity\Interfaces\JsonSerializable
{
    use \UcaBundle\Entity\Traits\JsonSerializable;

    #region Propriétés
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** @Gedmo\Translatable
     * @Gedmo\Versioned 
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="lieu.libelle.notblank") 
     */
    private $libelle;

    /** @Gedmo\Translatable
     * @Gedmo\Versioned
     * @ORM\Column(type="string", nullable=true) */
    private $description;

    /** @ORM\Column(type="boolean", nullable=true) */
    private $sourceReferentiel;

    /** 
     * @Gedmo\Versioned
     * @ORM\ManyToOne(targetEntity="Etablissement", inversedBy="ressources") */
    private $etablissement;

    /** 
     * @Gedmo\Versioned
     * @ORM\ManyToOne(targetEntity="Tarif",inversedBy="ressources") */
    private $tarif;

    /** @ORM\ManyToMany(targetEntity="FormatAvecReservation", mappedBy="ressource") */
    private $formatResa = array();

    /** @ORM\Column(type="string", length=255) */
    private $image;

    /** @Vich\UploadableField(mapping="map_image", fileNameProperty="image") 
     * @Assert\File(
     *     mimeTypes = {"image/png", "image/jpeg", "image/tiff"},
     *     mimeTypesMessage = "ressource.image.format"
     * )
     * @Assert\Expression("this.getImage() !== null || this.getImageFile() !== null", message="ressource.image.notnull")
     */
    private $imageFile;

    /** @ORM\Column(type="datetime",nullable=true) */
    private $updatedAt;
    #endregion


    #region Méthodes

    public static function formatIsValid($format)
    {
        return in_array($format, ['Lieu', 'Materiel']);
    }

    public function jsonSerializeProperties()
    {
        return ['libelle', 'description'];
    }

    #endregion

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
     * @return Ressource
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
     * @param string|null $description
     *
     * @return Ressource
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set sourceReferentiel.
     *
     * @param bool|null $sourceReferentiel
     *
     * @return Ressource
     */
    public function setSourceReferentiel($sourceReferentiel = null)
    {
        $this->sourceReferentiel = $sourceReferentiel;

        return $this;
    }

    /**
     * Get sourceReferentiel.
     *
     * @return bool|null
     */
    public function getSourceReferentiel()
    {
        return $this->sourceReferentiel;
    }

    /**
     * Set etablissement.
     *
     * @param \UcaBundle\Entity\Etablissement|null $etablissement
     *
     * @return Ressource
     */
    public function setEtablissement(\UcaBundle\Entity\Etablissement $etablissement = null)
    {
        $this->etablissement = $etablissement;

        return $this;
    }

    /**
     * Get etablissement.
     *
     * @return \UcaBundle\Entity\Etablissement|null
     */
    public function getEtablissement()
    {
        return $this->etablissement;
    }

    /**
     * Set tarif.
     *
     * @param \UcaBundle\Entity\Tarif|null $tarif
     *
     * @return Ressource
     */
    public function setTarif(\UcaBundle\Entity\Tarif $tarif = null)
    {
        $this->tarif = $tarif;

        return $this;
    }

    /**
     * Get tarif.
     *
     * @return \UcaBundle\Entity\Tarif|null
     */
    public function getTarif()
    {
        return $this->tarif;
    }


    /**
     * Add formatResa.
     *
     * @param \UcaBundle\Entity\FormatAvecReservation $formatResa
     *
     * @return Ressource
     */
    public function addFormatAvecReservation(\UcaBundle\Entity\FormatAvecReservation $formatResa)
    {
        $this->formatResa[] = $formatResa;

        return $this;
    }

    /**
     * Remove formatResa.
     *
     * @param \UcaBundle\Entity\FormatAvecReservation $formatResa
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeformatAvecReservation(\UcaBundle\Entity\FormatAvecReservation $formatResa)
    {
        return $this->formatResa->removeElement($formatResa);
    }

    /**
     * Get formatResa.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFormatAvecReservations()
    {
        return $this->formatResa;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->formatResa = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add formatResa.
     *
     * @param \UcaBundle\Entity\FormatAvecReservation $formatResa
     *
     * @return Ressource
     */
    public function addFormatResa(\UcaBundle\Entity\FormatAvecReservation $formatResa)
    {
        $this->formatResa[] = $formatResa;

        return $this;
    }

    /**
     * Remove formatResa.
     *
     * @param \UcaBundle\Entity\FormatAvecReservation $formatResa
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeFormatResa(\UcaBundle\Entity\FormatAvecReservation $formatResa)
    {
        return $this->formatResa->removeElement($formatResa);
    }

    /**
     * Get formatResa.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFormatResa()
    {
        return $this->formatResa;
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
}
