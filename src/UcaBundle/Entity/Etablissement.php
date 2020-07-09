<?php

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="UcaBundle\Repository\EtablissementRepository")
 * @Gedmo\Loggable
 * @Vich\Uploadable
 * @UniqueEntity(fields="libelle", message="etablissement.uniqueentity")
 */
class Etablissement
{
    //region Propriétés
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="etablissement.code.notblank")
     */
    private $code;

    /**
     * @Gedmo\Translatable
     * @Gedmo\Versioned
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="etablissement.libelle.notblank")
     */
    private $libelle;

    /** @ORM\OneToMany(targetEntity="Ressource", mappedBy="etablissement") */
    private $ressources;

    /**
     * @ORM\Column(type="string")
     * @Gedmo\Versioned
     * @Assert\NotBlank(message="etablissement.adresse.notblank")
     */
    private $adresse;

    /**
     * @ORM\Column(type="string", length=5)
     * @Gedmo\Versioned
     * @Assert\NotBlank(message="etablissement.codePostal.notblank")
     * @Assert\Regex(pattern="/^[0-9]{5}$/", message="lieu.codepostal.invalide")
     */
    private $codePostal;

    /**
     * @ORM\Column(type="string")
     * @Gedmo\Versioned
     * @Assert\NotBlank(message="etablissement.ville.notblank")
     */
    private $ville;

    /** @ORM\Column(type="string", length=255) */
    private $image;

    /** @Vich\UploadableField(mapping="map_image", fileNameProperty="image")
     * @Assert\File(
     *     mimeTypes = {"image/png", "image/jpeg", "image/tiff"},
     *     mimeTypesMessage = "etablissement.image.format"
     * )
     * @Assert\Expression("this.getImage() !== null || this.getImageFile() !== null", message="etablissement.image.notnull")
     */
    private $imageFile;

    /** @ORM\Column(type="datetime",nullable=true) */
    private $updatedAt;

    /** @ORM\Column(type="string",nullable=true)
     *  @Gedmo\Versioned
     *  @Assert\Email(message="etablissement.email.invalide")
     */
    private $email;

    /** @ORM\Column(type="string",nullable=true)
     *  @Gedmo\Versioned
     *  @Assert\Length(min = 10, max = 10, minMessage = "etablissement.telephone.invalide", maxMessage = "etablissement.telephone.invalide")
     *  @Assert\Regex(pattern="/^0[0-9]([-. ]?[0-9]{2}){4}$/", message="etablissement.telephone.invalide")
     */
    private $telephone;

    /** @Gedmo\Versioned
     * @ORM\Column(type="text",nullable=true)
     */
    private $horairesOuverture;

    /** @ORM\OneToMany(targetEntity="CommandeDetail", mappedBy="etablissementRetraitCarte") */
    private $cartesRetirees;
    //endregion

    //region Méthodes
    //endregion

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->utilisateurs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->ressources = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set code.
     *
     * @param string $code
     *
     * @return Etablissement
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
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set libelle.
     *
     * @param string $libelle
     *
     * @return Etablissement
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
     * Add utilisateur.
     *
     * @param \UcaBundle\Entity\Utilisateur $utilisateur
     *
     * @return Etablissement
     */
    public function addUtilisateur(Utilisateur $utilisateur)
    {
        $this->utilisateurs[] = $utilisateur;

        return $this;
    }

    /**
     * Remove utilisateur.
     *
     * @param \UcaBundle\Entity\Utilisateur $utilisateur
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeUtilisateur(Utilisateur $utilisateur)
    {
        return $this->utilisateurs->removeElement($utilisateur);
    }

    /**
     * Get utilisateurs.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUtilisateurs()
    {
        return $this->utilisateurs;
    }

    /**
     * Add ressource.
     *
     * @param \UcaBundle\Entity\Ressource $ressource
     *
     * @return Etablissement
     */
    public function addRessource(Ressource $ressource)
    {
        $this->ressources[] = $ressource;

        return $this;
    }

    /**
     * Remove ressource.
     *
     * @param \UcaBundle\Entity\Ressource $ressource
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeRessource(Ressource $ressource)
    {
        return $this->ressources->removeElement($ressource);
    }

    /**
     * Get ressources.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRessources()
    {
        return $this->ressources;
    }

    /**
     * Set adresse.
     *
     * @param null|string $adresse
     *
     * @return Etablissement
     */
    public function setAdresse($adresse = null)
    {
        $this->adresse = $adresse;

        return $this;
    }

    /**
     * Get adresse.
     *
     * @return string|null
     */
    public function getAdresse()
    {
        return $this->adresse;
    }

    /**
     * Set codePostal.
     *
     * @param null|string $codePostal
     *
     * @return Etablissement
     */
    public function setCodePostal($codePostal = null)
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    /**
     * Get codePostal.
     *
     * @return string|null
     */
    public function getCodePostal()
    {
        return $this->codePostal;
    }

    /**
     * Set ville.
     *
     * @param null|string $ville
     *
     * @return Etablissement
     */
    public function setVille($ville = null)
    {
        $this->ville = $ville;

        return $this;
    }

    /**
     * Get ville.
     *
     * @return string|null
     */
    public function getVille()
    {
        return $this->ville;
    }

    public function setImageFile(File $image = null)
    {
        $this->imageFile = $image;
        if ($image) {
            $this->updatedAt = new \DateTime('now');
        }
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
     * @param null|\DateTime $updatedAt
     *
     * @return Etablissement
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
     * Set email.
     *
     * @param null|string $email
     *
     * @return Etablissement
     */
    public function setEmail($email = null)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set telephone.
     *
     * @param null|string $telephone
     *
     * @return Etablissement
     */
    public function setTelephone($telephone = null)
    {
        $this->telephone = $telephone;

        return $this;
    }

    /**
     * Get telephone.
     *
     * @return string|null
     */
    public function getTelephone()
    {
        return $this->telephone;
    }

    /**
     * Set horairesOuverture.
     *
     * @param null|string $horairesOuverture
     *
     * @return Etablissement
     */
    public function setHorairesOuverture($horairesOuverture = null)
    {
        $this->horairesOuverture = $horairesOuverture;

        return $this;
    }

    /**
     * Get horairesOuverture.
     *
     * @return string|null
     */
    public function getHorairesOuverture()
    {
        return $this->horairesOuverture;
    }

    /**
     * Add cartesRetiree.
     *
     * @param \UcaBundle\Entity\CommandeDetail $cartesRetiree
     *
     * @return Etablissement
     */
    public function addCartesRetiree(\UcaBundle\Entity\CommandeDetail $cartesRetiree)
    {
        $this->cartesRetirees[] = $cartesRetiree;

        return $this;
    }

    /**
     * Remove cartesRetiree.
     *
     * @param \UcaBundle\Entity\CommandeDetail $cartesRetiree
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeCartesRetiree(\UcaBundle\Entity\CommandeDetail $cartesRetiree)
    {
        return $this->cartesRetirees->removeElement($cartesRetiree);
    }

    /**
     * Get cartesRetirees.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCartesRetirees()
    {
        return $this->cartesRetirees;
    }
}
