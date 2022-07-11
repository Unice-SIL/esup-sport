<?php

/*
 * Classe - Ressources:
 *
 * Enttté mère contenant les éléments commun à toutes les ressources.
*/

namespace App\Entity\Uca;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RessourceRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="format", type="string")
 * @ORM\DiscriminatorMap( {
 *   "Lieu" = "Lieu",
 *   "Materiel" = "Materiel"
 * } )
 * @Gedmo\Loggable
 * @Vich\Uploadable
 */
abstract class Ressource implements \App\Entity\Uca\Interfaces\JsonSerializable
{
    use \App\Entity\Uca\Traits\JsonSerializable;

    //region Propriétés
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
     * @ORM\ManyToOne(targetEntity="Etablissement", inversedBy="ressources") */
    private $etablissement;

    /**
     * @ORM\ManyToOne(targetEntity="Tarif",inversedBy="ressources") */
    private $tarif;

    /** @ORM\ManyToMany(targetEntity="FormatAvecReservation", mappedBy="ressource") */
    private $formatResa = [];

    /** @ORM\OneToMany(targetEntity="Reservabilite", mappedBy="ressource") */
    private $reservabilites;

    /** @ORM\Column(type="string", length=255) */
    private $image;

    /** @Vich\UploadableField(mapping="map_image", fileNameProperty="image")
     * @Assert\File(
     *     mimeTypes = {"image/png", "image/jpeg", "image/tiff"},
     *     mimeTypesMessage = "ressource.image.format"
     * )
     * @Assert\Expression("this.getImage() !== null || this.getImageFile() !== null", message="ressource.image.notnull")
     * @Assert\Valid()
     */
    private $imageFile;

    /** @ORM\Column(type="datetime",nullable=true) */
    private $updatedAt;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="text")
     */
    private $tarifLibelle;
    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="text")
     */
    private $etablissementLibelle;

    /**
     * @ORM\OneToMany(targetEntity="RessourceProfilUtilisateur", mappedBy="ressource", fetch="LAZY", cascade={"persist", "remove"})
     * @Assert\Valid()
     */
    private $profilsUtilisateurs;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="text")
     */
    private $listeProfils;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="integer", options={"default":0}))
     */
    private $nbPartenaires;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="integer", options={"default":0}))
     * @Assert\Expression("this.getNbPartenaires() <= this.getNbPartenairesMax()", message="ressource.nbpartenairesmax.minval")
     */
    private $nbPartenairesMax;

    //endregion

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->formatResa = new \Doctrine\Common\Collections\ArrayCollection();
        $this->reservabilites = new \Doctrine\Common\Collections\ArrayCollection();
        $this->profilsUtilisateurs = new \Doctrine\Common\Collections\ArrayCollection();
    }

    //endregion

    //region Méthodes

    public static function formatIsValid($format)
    {
        return in_array($format, ['Lieu', 'Materiel']);
    }

    public function jsonSerializeProperties()
    {
        return ['libelle', 'description', 'etablissementLibelle', 'profilsUtilisateurs'];
    }

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

    public function updateTarifLibelle()
    {
        if (null != $this->getTarif()) {
            $this->tarifLibelle = $this->getTarif()->getLibelle();
        } else {
            $this->tarifLibelle = '';
        }

        return $this;
    }

    public function updateEtablissementLibelle()
    {
        if (null != $this->getEtablissement()) {
            $this->etablissementLibelle = $this->getEtablissement()->getLibelle();
        } else {
            $this->etablissementLibelle = '';
        }

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
     * @return Ressource
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
     * @param null|string $description
     *
     * @return Ressource
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set sourceReferentiel.
     *
     * @param null|bool $sourceReferentiel
     *
     * @return Ressource
     * @codeCoverageIgnore
     */
    public function setSourceReferentiel($sourceReferentiel = null)
    {
        $this->sourceReferentiel = $sourceReferentiel;

        return $this;
    }

    /**
     * Get sourceReferentiel.
     *
     * @return null|bool
     * @codeCoverageIgnore
     */
    public function getSourceReferentiel()
    {
        return null != $this->sourceReferentiel ? $this->sourceReferentiel : false;
    }

    /**
     * Set image.
     *
     * @param string $image
     *
     * @return Ressource
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
     * @return Ressource
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
     * Set etablissement.
     *
     * @return Ressource
     * @codeCoverageIgnore
     */
    public function setEtablissement(Etablissement $etablissement = null)
    {
        $this->etablissement = $etablissement;

        return $this;
    }

    /**
     * Get etablissement.
     *
     * @return null|Etablissement
     * @codeCoverageIgnore
     */
    public function getEtablissement()
    {
        return $this->etablissement;
    }

    /**
     * Set tarif.
     *
     * @return Ressource
     * @codeCoverageIgnore
     */
    public function setTarif(Tarif $tarif = null)
    {
        $this->tarif = $tarif;

        return $this;
    }

    /**
     * Get tarif.
     *
     * @return null|Tarif
     * @codeCoverageIgnore
     */
    public function getTarif()
    {
        return $this->tarif;
    }

    /**
     * Add formatResa.
     *
     * @return Ressource
     * @codeCoverageIgnore
     */
    public function addFormatResa(FormatAvecReservation $formatResa)
    {
        $this->formatResa[] = $formatResa;

        return $this;
    }

    /**
     * Remove formatResa.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeFormatResa(FormatAvecReservation $formatResa)
    {
        return $this->formatResa->removeElement($formatResa);
    }

    /**
     * Get formatResa.
     *
     * @return \Doctrine\Common\Collections\Collection
     * @codeCoverageIgnore
     */
    public function getFormatResa()
    {
        return $this->formatResa;
    }

    /**
     * Add reservabilite.
     *
     * @return Ressource
     * @codeCoverageIgnore
     */
    public function addReservabilite(Reservabilite $reservabilite)
    {
        $this->reservabilites[] = $reservabilite;

        return $this;
    }

    /**
     * Remove reservabilite.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeReservabilite(Reservabilite $reservabilite)
    {
        return $this->reservabilites->removeElement($reservabilite);
    }

    /**
     * Get reservabilites.
     *
     * @return \Doctrine\Common\Collections\Collection
     * @codeCoverageIgnore
     */
    public function getReservabilites()
    {
        return $this->reservabilites;
    }

    /**
     * Set tarifLibelle.
     *
     * @param string $tarifLibelle
     *
     * @return Ressource
     * @codeCoverageIgnore
     */
    public function setTarifLibelle($tarifLibelle)
    {
        $this->tarifLibelle = $tarifLibelle;

        return $this;
    }

    /**
     * Get tarifLibelle.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getTarifLibelle()
    {
        return $this->tarifLibelle;
    }

    /**
     * Set etablissementLibelle.
     *
     * @param string $etablissementLibelle
     *
     * @return Ressource
     * @codeCoverageIgnore
     */
    public function setEtablissementLibelle($etablissementLibelle)
    {
        $this->etablissementLibelle = $etablissementLibelle;

        return $this;
    }

    /**
     * Get etablissementLibelle.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getEtablissementLibelle()
    {
        return $this->etablissementLibelle;
    }

    /**
     * Add profilsUtilisateur.
     *
     * @param \App\Entity\Uca\ProfilUtilisateur $profilsUtilisateur
     *
     * @return Ressource
     * @codeCoverageIgnore
     */
    public function addProfilsUtilisateur(RessourceProfilUtilisateur $profilsUtilisateur)
    {
        $this->profilsUtilisateurs[] = $profilsUtilisateur;

        return $this;
    }

    /**
     * Remove profilsUtilisateur.
     *
     * @param \App\Entity\Uca\ProfilUtilisateur $profilsUtilisateur
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeProfilsUtilisateur(RessourceProfilUtilisateur $profilsUtilisateur)
    {
        return $this->profilsUtilisateurs->removeElement($profilsUtilisateur);
    }

    /**
     * Get profilsUtilisateurs.
     *
     * @return \Doctrine\Common\Collections\Collection
     * @codeCoverageIgnore
     */
    public function getProfilsUtilisateurs()
    {
        return $this->profilsUtilisateurs;
    }

    public function updateListeProfils()
    {
        $this->listeProfils = '';
        foreach ($this->getProfilsUtilisateurs() as $ressourceProfil) {
            if (!empty($this->listeProfils)) {
                $this->listeProfils .= ', ';
            }
            $this->listeProfils .= $ressourceProfil->getProfilUtilisateur()->getLibelle();
        }

        return $this;
    }

    /**
     * Set listeProfils.
     *
     * @param string $listeProfils
     *
     * @return Ressource
     * @codeCoverageIgnore
     */
    public function setListeProfils($listeProfils)
    {
        $this->listeProfils = $listeProfils;

        return $this;
    }

    /**
     * Get listeProfils.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getListeProfils()
    {
        return $this->listeProfils;
    }

    /**
     * Fonction qui permet de savoir si la ressource concerne un profil donné.
     */
    public function hasProfil(ProfilUtilisateur $profil): bool
    {
        foreach ($this->profilsUtilisateurs as $profilsUtilisateur) {
            if ($profilsUtilisateur->getProfilUtilisateur()->getId() == $profil->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get nbPartenaires.
     *
     * @codeCoverageIgnore
     */
    public function getNbPartenaires(): int
    {
        return $this->nbPartenaires;
    }

    /**
     * Set nbPartenaires.
     *
     * @codeCoverageIgnore
     */
    public function setNbPartenaires(int $nbPartenaires): Ressource
    {
        $this->nbPartenaires = $nbPartenaires;

        return $this;
    }

    /**
     * Get nbPartenairesMax.
     *
     * @codeCoverageIgnore
     */
    public function getNbPartenairesMax(): int
    {
        return $this->nbPartenairesMax;
    }

    /**
     * Set nbPartenairesMax.
     *
     * @codeCoverageIgnore
     */
    public function setNbPartenairesMax(int $nbPartenairesMax): Ressource
    {
        $this->nbPartenairesMax = $nbPartenairesMax;

        return $this;
    }
}