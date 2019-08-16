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
 * @ORM\Entity(repositoryClass="UcaBundle\Repository\FormatActiviteRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="format", type="string")
 * @ORM\DiscriminatorMap( {
 *   "FormatSimple" = "FormatSimple", 
 *   "FormatAvecCreneau" = "FormatAvecCreneau",
 *   "FormatAvecReservation" = "FormatAvecReservation",
 *   "FormatAchatCarte" = "FormatAchatCarte"
 * } )
 * @Gedmo\Loggable
 * @Vich\Uploadable
 * @UniqueEntity(fields={"libelle","activite"}, message="formatactivite.uniqueentity")
 * @ORM\HasLifecycleCallbacks
 */
abstract class FormatActivite implements \UcaBundle\Entity\Interfaces\JsonSerializable, \UcaBundle\Entity\Interfaces\Tarifable
{
    use \UcaBundle\Entity\Traits\JsonSerializable;

    #region Propriétés
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Gedmo\Translatable
     * @Gedmo\Versioned
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="formatactivite.libelle.notblank")
     */
    private $libelle;

    /**
     * @Gedmo\Translatable
     * @Gedmo\Versioned
     * @ORM\Column(type="text")
     * @Assert\NotBlank(message="formatactivite.description.notblank") 
     */
    private $description;

    /** 
     * @Gedmo\Versioned
     * @ORM\ManyToOne(targetEntity="Activite", inversedBy="formatsActivite", fetch="EAGER")
     */
    private $activite;

    /** 
     * @Gedmo\Versioned
     * @ORM\Column(type="string", nullable=true) 
     */
    private $lienHtml;

    /** 
     * @Gedmo\Versioned
     * @ORM\Column(type="string", nullable=true)
     */
    private $lienPdf;

    /** 
     * @Gedmo\Versioned
     * @ORM\Column(type="datetime", options={"default": "2019-10-01"}) 
     */
    private $dateDebutPublication;

    /** 
     * @Gedmo\Versioned
     * @ORM\Column(type="datetime", options={"default": "2020-04-01"})
     * @Assert\Expression("this.getDateFinPublication() >= this.getDateDebutPublication()", message="message.erreur.datefin")
     */
    private $dateFinPublication;

    /** 
     * @Gedmo\Versioned
     * @ORM\Column(type="datetime") 
     */
    private $dateDebutInscription;

    /** 
     * @Gedmo\Versioned
     * @ORM\Column(type="datetime") 
     * @Assert\Expression("this.getDateFinInscription() >= this.getDateDebutInscription()", message="message.erreur.datefin")
     */
    private $dateFinInscription;

    /** 
     * @Gedmo\Versioned
     * @ORM\Column(type="datetime") 
     */
    private $dateDebutEffective;

    /** 
     * @Gedmo\Versioned
     * @ORM\Column(type="datetime")
     * @Assert\Expression("this.getDateFinEffective() >= this.getDateDebutEffective()", message="message.erreur.datefin")
     */
    private $dateFinEffective;

    /** 
     * @ORM\OneToMany(targetEntity="Inscription", mappedBy="formatActivite") 
     */
    protected $inscriptions;


    /** @ORM\Column(type="string", length=255) */
    private $image;

    /** @Vich\UploadableField(mapping="map_image", fileNameProperty="image") 
     *  @Assert\File(
     *      mimeTypes = {"image/png", "image/jpeg", "image/tiff"},
     *      mimeTypesMessage = "formatactivite.image.format"
     *  )
     * @Assert\Expression("this.getImage() !== null || this.getImageFile() !== null", message="formatactivite.image.notnull")
     */
    private $imageFile;

    /** @ORM\Column(type="datetime",nullable=true) */
    private $updatedAt;

    /** @ORM\ManyToMany(targetEntity="Lieu", inversedBy="formatsActivite") 
     * @Assert\Expression("!this.getLieu().isEmpty()", message="formatactivite.lieu.notnull") */
    private $lieu;

    protected $type;
    #endregion

    #region Propriétés communes Creneaux

    /** @ORM\ManyToMany(targetEntity="TypeAutorisation", inversedBy="formatsActivite") */
    private $autorisations;

    /** @ORM\ManyToMany(targetEntity="NiveauSportif") 
     * @Assert\NotBlank(message="complement.niveauxsportifs.notblank") */
    private $niveauxSportifs;

    /** @ORM\ManyToMany(targetEntity="ProfilUtilisateur", inversedBy="formatsActivite", fetch="EAGER")
     * @Assert\NotBlank(message="complement.profilsutilisateurs.notblank") */
    private $profilsUtilisateurs;

    /** @ORM\Column(type="boolean", nullable=false, options={"default":0}) */
    private $estPayant;

    /** 
     * @Gedmo\Versioned
     * @ORM\ManyToOne(targetEntity="Tarif", inversedBy="formatsActivite", fetch="EAGER") 
     * @Assert\Expression("!this.getEstPayant() || this.getTarif()", message="complement.tarif.notblank")
     */
    private $tarif;

    /** @ORM\Column(type="boolean", nullable=false, options={"default":0}) */
    private $estEncadre;

    /** 
     * @ORM\ManyToMany(targetEntity="Utilisateur", inversedBy="formatsActivite") 
     * @Assert\Expression("!this.getEstEncadre() || this.getEncadrants()", message="complement.encadrant.notblank") */
    private $encadrants;

    /** 
     * @Gedmo\Versioned
     * @ORM\Column(type="integer") 
     * @Assert\NotBlank(message="complement.capacite.notblank") 
     */
    protected $capacite;

    /** @ORM\Column(type="integer", options={"default":0}) */
    private $statut;

    #endregion

    #region Méthodes

    public function getClasseActiviteLibelle()
    {
        return $this->activite->getClasseActiviteLibelle();
    }

    public static function formatIsValid($format)
    {
        return in_array($format, ['FormatSimple', 'FormatAvecCreneau', 'FormatAvecReservation', 'FormatAchatCarte']);
    }

    public function getActiviteLibelle()
    {
        return $this->getActivite()->getActiviteLibelle();
    }

    public function jsonSerializeProperties()
    {
        return  ['libelle', 'description', 'type', 'estEncadre', 'profilsUtilisateurs', 'niveauxSportifs'];
    }

    public function getType()
    {
        return $this->type;
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

    public function getIdA()
    {
        return $this->getActivite()->getId();
    }

    public function getIdCa()
    {
        return $this->getActivite()->getClasseActivite()->getId();
    }

    public function getMontant($user)
    {
        if ($this->estPayant) {
            return $this->getTarif()->getUserMontant($user->getProfil()->getId())->getMontant();
        } else {
            return 0;
        }
    }

    #endregion

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->inscriptions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->lieu = new \Doctrine\Common\Collections\ArrayCollection();
        $this->autorisations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->niveauxSportifs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->profilsUtilisateurs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->encadrants = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return FormatActivite
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
     * @param string $description
     *
     * @return FormatActivite
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set lienHtml.
     *
     * @param string|null $lienHtml
     *
     * @return FormatActivite
     */
    public function setLienHtml($lienHtml = null)
    {
        $this->lienHtml = $lienHtml;

        return $this;
    }

    /**
     * Get lienHtml.
     *
     * @return string|null
     */
    public function getLienHtml()
    {
        return $this->lienHtml;
    }

    /**
     * Set lienPdf.
     *
     * @param string|null $lienPdf
     *
     * @return FormatActivite
     */
    public function setLienPdf($lienPdf = null)
    {
        $this->lienPdf = $lienPdf;

        return $this;
    }

    /**
     * Get lienPdf.
     *
     * @return string|null
     */
    public function getLienPdf()
    {
        return $this->lienPdf;
    }

    /**
     * Set dateDebutPublication.
     *
     * @param \DateTime $dateDebutPublication
     *
     * @return FormatActivite
     */
    public function setDateDebutPublication($dateDebutPublication)
    {
        $this->dateDebutPublication = $dateDebutPublication;

        return $this;
    }

    /**
     * Get dateDebutPublication.
     *
     * @return \DateTime
     */
    public function getDateDebutPublication()
    {
        return $this->dateDebutPublication;
    }

    /**
     * Set dateFinPublication.
     *
     * @param \DateTime $dateFinPublication
     *
     * @return FormatActivite
     */
    public function setDateFinPublication($dateFinPublication)
    {
        $this->dateFinPublication = $dateFinPublication;

        return $this;
    }

    /**
     * Get dateFinPublication.
     *
     * @return \DateTime
     */
    public function getDateFinPublication()
    {
        return $this->dateFinPublication;
    }

    /**
     * Set dateDebutInscription.
     *
     * @param \DateTime $dateDebutInscription
     *
     * @return FormatActivite
     */
    public function setDateDebutInscription($dateDebutInscription)
    {
        $this->dateDebutInscription = $dateDebutInscription;

        return $this;
    }

    /**
     * Get dateDebutInscription.
     *
     * @return \DateTime
     */
    public function getDateDebutInscription()
    {
        return $this->dateDebutInscription;
    }

    /**
     * Set dateFinInscription.
     *
     * @param \DateTime $dateFinInscription
     *
     * @return FormatActivite
     */
    public function setDateFinInscription($dateFinInscription)
    {
        $this->dateFinInscription = $dateFinInscription;

        return $this;
    }

    /**
     * Get dateFinInscription.
     *
     * @return \DateTime
     */
    public function getDateFinInscription()
    {
        return $this->dateFinInscription;
    }

    /**
     * Set dateDebutEffective.
     *
     * @param \DateTime $dateDebutEffective
     *
     * @return FormatActivite
     */
    public function setDateDebutEffective($dateDebutEffective)
    {
        $this->dateDebutEffective = $dateDebutEffective;

        return $this;
    }

    /**
     * Get dateDebutEffective.
     *
     * @return \DateTime
     */
    public function getDateDebutEffective()
    {
        return $this->dateDebutEffective;
    }

    /**
     * Set dateFinEffective.
     *
     * @param \DateTime $dateFinEffective
     *
     * @return FormatActivite
     */
    public function setDateFinEffective($dateFinEffective)
    {
        $this->dateFinEffective = $dateFinEffective;

        return $this;
    }

    /**
     * Get dateFinEffective.
     *
     * @return \DateTime
     */
    public function getDateFinEffective()
    {
        return $this->dateFinEffective;
    }

    /**
     * Set image.
     *
     * @param string $image
     *
     * @return FormatActivite
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
     * @param \DateTime|null $updatedAt
     *
     * @return FormatActivite
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
     * Set estPayant.
     *
     * @param bool $estPayant
     *
     * @return FormatActivite
     */
    public function setEstPayant($estPayant)
    {
        $this->estPayant = $estPayant;

        return $this;
    }

    /**
     * Get estPayant.
     *
     * @return bool
     */
    public function getEstPayant()
    {
        return $this->estPayant;
    }

    /**
     * Set estEncadre.
     *
     * @param bool $estEncadre
     *
     * @return FormatActivite
     */
    public function setEstEncadre($estEncadre)
    {
        $this->estEncadre = $estEncadre;

        return $this;
    }

    /**
     * Get estEncadre.
     *
     * @return bool
     */
    public function getEstEncadre()
    {
        return $this->estEncadre;
    }

    /**
     * Set capacite.
     *
     * @param int $capacite
     *
     * @return FormatActivite
     */
    public function setCapacite($capacite)
    {
        $this->capacite = $capacite;

        return $this;
    }

    /**
     * Get capacite.
     *
     * @return int
     */
    public function getCapacite()
    {
        return $this->capacite;
    }

    /**
     * Set statut.
     *
     * @param int $statut
     *
     * @return FormatActivite
     */
    public function setStatut($statut)
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * Get statut.
     *
     * @return int
     */
    public function getStatut()
    {
        return $this->statut;
    }

    /**
     * Set activite.
     *
     * @param \UcaBundle\Entity\Activite|null $activite
     *
     * @return FormatActivite
     */
    public function setActivite(\UcaBundle\Entity\Activite $activite = null)
    {
        $this->activite = $activite;

        return $this;
    }

    /**
     * Get activite.
     *
     * @return \UcaBundle\Entity\Activite|null
     */
    public function getActivite()
    {
        return $this->activite;
    }

    /**
     * Add inscription.
     *
     * @param \UcaBundle\Entity\Inscription $inscription
     *
     * @return FormatActivite
     */
    public function addInscription(\UcaBundle\Entity\Inscription $inscription)
    {
        $this->inscriptions[] = $inscription;

        return $this;
    }

    /**
     * Remove inscription.
     *
     * @param \UcaBundle\Entity\Inscription $inscription
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeInscription(\UcaBundle\Entity\Inscription $inscription)
    {
        return $this->inscriptions->removeElement($inscription);
    }

    /**
     * Get inscriptions.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInscriptions()
    {
        return $this->inscriptions;
    }

    /**
     * Add lieu.
     *
     * @param \UcaBundle\Entity\Lieu $lieu
     *
     * @return FormatActivite
     */
    public function addLieu(\UcaBundle\Entity\Lieu $lieu)
    {
        $this->lieu[] = $lieu;

        return $this;
    }

    /**
     * Remove lieu.
     *
     * @param \UcaBundle\Entity\Lieu $lieu
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeLieu(\UcaBundle\Entity\Lieu $lieu)
    {
        return $this->lieu->removeElement($lieu);
    }

    /**
     * Get lieu.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLieu()
    {
        return $this->lieu;
    }

    /**
     * Add autorisation.
     *
     * @param \UcaBundle\Entity\TypeAutorisation $autorisation
     *
     * @return FormatActivite
     */
    public function addAutorisation(\UcaBundle\Entity\TypeAutorisation $autorisation)
    {
        $this->autorisations[] = $autorisation;

        return $this;
    }

    /**
     * Remove autorisation.
     *
     * @param \UcaBundle\Entity\TypeAutorisation $autorisation
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeAutorisation(\UcaBundle\Entity\TypeAutorisation $autorisation)
    {
        return $this->autorisations->removeElement($autorisation);
    }

    /**
     * Get autorisations.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAutorisations()
    {
        return $this->autorisations;
    }

    /**
     * Add niveauxSportif.
     *
     * @param \UcaBundle\Entity\NiveauSportif $niveauxSportif
     *
     * @return FormatActivite
     */
    public function addNiveauxSportif(\UcaBundle\Entity\NiveauSportif $niveauxSportif)
    {
        $this->niveauxSportifs[] = $niveauxSportif;

        return $this;
    }

    /**
     * Remove niveauxSportif.
     *
     * @param \UcaBundle\Entity\NiveauSportif $niveauxSportif
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeNiveauxSportif(\UcaBundle\Entity\NiveauSportif $niveauxSportif)
    {
        return $this->niveauxSportifs->removeElement($niveauxSportif);
    }

    /**
     * Get niveauxSportifs.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNiveauxSportifs()
    {
        return $this->niveauxSportifs;
    }

    /**
     * Add profilsUtilisateur.
     *
     * @param \UcaBundle\Entity\ProfilUtilisateur $profilsUtilisateur
     *
     * @return FormatActivite
     */
    public function addProfilsUtilisateur(\UcaBundle\Entity\ProfilUtilisateur $profilsUtilisateur)
    {
        $this->profilsUtilisateurs[] = $profilsUtilisateur;

        return $this;
    }

    /**
     * Remove profilsUtilisateur.
     *
     * @param \UcaBundle\Entity\ProfilUtilisateur $profilsUtilisateur
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeProfilsUtilisateur(\UcaBundle\Entity\ProfilUtilisateur $profilsUtilisateur)
    {
        return $this->profilsUtilisateurs->removeElement($profilsUtilisateur);
    }

    /**
     * Get profilsUtilisateurs.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProfilsUtilisateurs()
    {
        return $this->profilsUtilisateurs;
    }

    /**
     * Set tarif.
     *
     * @param \UcaBundle\Entity\Tarif|null $tarif
     *
     * @return FormatActivite
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
     * Add encadrant.
     *
     * @param \UcaBundle\Entity\Utilisateur $encadrant
     *
     * @return FormatActivite
     */
    public function addEncadrant(\UcaBundle\Entity\Utilisateur $encadrant)
    {
        $this->encadrants[] = $encadrant;

        return $this;
    }

    /**
     * Remove encadrant.
     *
     * @param \UcaBundle\Entity\Utilisateur $encadrant
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeEncadrant(\UcaBundle\Entity\Utilisateur $encadrant)
    {
        return $this->encadrants->removeElement($encadrant);
    }

    /**
     * Get encadrants.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEncadrants()
    {
        return $this->encadrants;
    }
}
