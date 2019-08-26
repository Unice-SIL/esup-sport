<?php

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use UcaBundle\Service\Common\Previsualisation;
use Doctrine\Common\Collections\Criteria;

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
 * @ORM\HasLifecycleCallbacks
 * @ORM\EntityListeners({"UcaBundle\Service\Listener\Entity\FormatActiviteListener"})
 */
abstract class FormatActivite implements \UcaBundle\Entity\Interfaces\JsonSerializable
{
    use \UcaBundle\Entity\Traits\JsonSerializable;
    use \UcaBundle\Entity\Traits\Article;

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
     * @ORM\ManyToOne(targetEntity="Activite", inversedBy="formatsActivite", fetch="EAGER")
     */
    private $activite;

    /** 
     * @ORM\Column(type="string", nullable=true) 
     */
    private $lienHtml;

    /** 
     * @ORM\Column(type="string", nullable=true)
     */
    private $lienPdf;

    /** 
     * @Gedmo\Versioned
     * @ORM\Column(type="datetime") 
     * @Assert\NotBlank(message="formatactivite.dateDebutPublication.notblank") 
     */
    private $dateDebutPublication;

    /** 
     * @Gedmo\Versioned
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank(message="formatactivite.dateFinPublication.notblank") 
     * @Assert\Expression("this.getDateFinPublication() >= this.getDateDebutPublication()", message="message.erreur.datefin")
     */
    private $dateFinPublication;

    /** 
     * @Gedmo\Versioned
     * @ORM\Column(type="datetime") 
     * @Assert\NotBlank(message="formatactivite.dateDebutInscription.notblank") 
     */
    private $dateDebutInscription;

    /** 
     * @Gedmo\Versioned
     * @ORM\Column(type="datetime") 
     * @Assert\NotBlank(message="formatactivite.dateFinInscription.notblank") 
     * @Assert\Expression("this.getDateFinInscription() >= this.getDateDebutInscription()", message="message.erreur.datefin")
     */
    private $dateFinInscription;

    /** 
     * @Gedmo\Versioned
     * @ORM\Column(type="datetime") 
     * @Assert\NotBlank(message="formatactivite.dateDebutEffective.notblank") 
     * @Assert\Expression("this.getDateFinEffective() > this.getDateDebutEffective()", message="message.erreur.datedebut")
     */
    private $dateDebutEffective;

    /** 
     * @Gedmo\Versioned
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank(message="formatactivite.dateFinEffective.notblank") 
     * @Assert\Expression("this.getDateFinEffective() > this.getDateDebutEffective()", message="message.erreur.datefin")
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
     * @Assert\NotNull(message="formatactivite.lieu.notnull")
     * @Assert\Count(min = 1, minMessage = "formatactivite.lieu.notnull")
     */
    private $lieu;

    protected $type;
    #endregion

    #region Propriétés communes Creneaux

    /** @ORM\ManyToMany(targetEntity="TypeAutorisation", inversedBy="formatsActivite") */
    private $autorisations;

    /** @ORM\ManyToMany(targetEntity="NiveauSportif") 
     * @Assert\NotBlank(message="complement.niveauxsportifs.notblank")
     * @Assert\Count(min = 1, minMessage = "complement.niveauxsportifs.notblank")
     */
    private $niveauxSportifs;

    /** @ORM\ManyToMany(targetEntity="ProfilUtilisateur", inversedBy="formatsActivite", fetch="EAGER")
     * @Assert\NotBlank(message="complement.profilsutilisateurs.notblank")
     * @Assert\Count(min = 1, minMessage = "complement.profilsutilisateurs.notblank")
     */
    private $profilsUtilisateurs;

    /** @Gedmo\Versioned
     * @ORM\Column(type="boolean", nullable=false, options={"default":0}) */
    private $estPayant;

    /** 
     * @ORM\ManyToOne(targetEntity="Tarif", inversedBy="formatsActivite", fetch="EAGER") 
     * @Assert\Expression("!this.getEstPayant() || this.getTarif()", message="complement.tarif.notblank")
     */
    private $tarif;

    /** @Gedmo\Versioned
     * @ORM\Column(type="boolean", nullable=false, options={"default":0}) */
    private $estEncadre;

    /** 
     * @ORM\ManyToMany(targetEntity="Utilisateur", inversedBy="formatsActivite") 
     * @Assert\Expression("!this.getEstEncadre() || !this.getEncadrants().isEmpty()", message="complement.encadrant.notblank")
     */
    private $encadrants;

    /** 
     * @Gedmo\Versioned
     * @ORM\Column(type="integer") 
     * @Assert\NotBlank(message="complement.capacite.notblank")
     * @Assert\Regex(pattern="/^\d+$/", message="message.typeinvalide.entier")
     */
    protected $capacite;

    /** @Gedmo\Versioned
     * @ORM\Column(type="integer") */
    private $statut = false;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="text")
     */
    private $tarifLibelle;
    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="text")
     */
    private $listeLieux;
    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="text")
     */
    private $listeAutorisations;
    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="text")
     */
    private $listeNiveauxSportifs;
    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="text")
     */
    private $listeProfils;
    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="text")
     */
    private $listeEncadrants;

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
        return  ['libelle', 'description', 'type', 'estEncadre', 'profilsUtilisateurs', 'niveauxSportifs', 'encadrants', 'lieu'];
    }

    public function getType()
    {
        return $this->type;
    }

    public function getFormat()
    {
        return (new \ReflectionClass($this))->getShortName();
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

    public function getArticleMontant($utilisateur)
    {
        if (!$this->estPayant) {
            return 0;
        } else {
            return $this->getArticleMontantDefaut($utilisateur);
        }
    }

    public function getArticleLibelle()
    {
        return $this->getLibelle();
    }

    public function getArticleDescription()
    {
        return $this->getDescription();
    }

    public function dateInscriptionValid()
    {

        $now = new \DateTime("now");
        return $now > $this->getDateDebutInscription() && $now < $this->getDateFinInscription();
    }

    public function hasProfil($user)
    {
        if ($user === null) {
            return true;
        }

        return $this->profilsUtilisateurs->contains($user->getProfil());
    }

    public function verifieCoherenceDonnees()
    {
        if (!$this->estPayant) {
            $this->tarif = null;
        }
        if (!$this->estEncadre) {
            $this->encadrants->clear();
        }
    }

    public function isDisponible($user)
    {
        return $this->dateInscriptionValid() && $this->hasProfil($user) && $this->getArticleMontant($user) >= 0;
    }

    public function userIsInscrit($user)
    {
        if ($user === null)
            return false;

        return $user->hasInscription($this);
    }

    public function updateTarifLibelle()
    {
        if ($this->getTarif() != null) {
            $this->tarifLibelle = $this->getTarif()->getLibelle();
        } else {
            $this->tarifLibelle = '';
        }

        return $this;
    }

    public function updateListeLieux()
    {
        $this->listeLieux = '';
        foreach ($this->getLieu() as $lieu) {
            if (!empty($this->listeLieux)) {
                $this->listeLieux .= ", ";
            }
            $this->listeLieux .= $lieu->getLibelle();
        }

        return $this;
    }

    public function updateListeAutorisations()
    {
        $this->listeAutorisations = '';
        foreach ($this->getAutorisations() as $autorisation) {
            if (!empty($this->listeAutorisations)) {
                $this->listeAutorisations .= ", ";
            }
            $this->listeAutorisations .= $autorisation->getLibelle();
        }

        return $this;
    }

    public function updateListeNiveauxSportifs()
    {
        $this->listeNiveauxSportifs = '';
        foreach ($this->getNiveauxSportifs() as $niveauSportif) {
            if (!empty($this->listeNiveauxSportifs)) {
                $this->listeNiveauxSportifs .= ", ";
            }
            $this->listeNiveauxSportifs .= $niveauSportif->getLibelle();
        }

        return $this;
    }

    public function updateListeProfils()
    {
        $this->listeProfils = '';
        foreach ($this->getProfilsUtilisateurs() as $profil) {
            if (!empty($this->listeProfils)) {
                $this->listeProfils .= ", ";
            }
            $this->listeProfils .= $profil->getLibelle();
        }

        return $this;
    }

    public function updateListeEncadrants()
    {
        $this->listeEncadrants = '';
        foreach ($this->getEncadrants() as $encadrant) {
            if (!empty($this->listeEncadrants)) {
                $this->listeEncadrants .= ", ";
            }
            $this->listeEncadrants .= $encadrant->getPrenom() . ' ' . $encadrant->getNom();
        }

        return $this;
    }
    
    public function getInscriptionsValidee(){
        $criteria = Criteria::create()
        ->andWhere(Criteria::expr()->eq('statut', "valide"));

        return $this->getInscriptions()->matching($criteria);
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

    /**
     * Set tarifLibelle.
     *
     * @param string $tarifLibelle
     *
     * @return FormatActivite
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
     */
    public function getTarifLibelle()
    {
        return $this->tarifLibelle;
    }

    /**
     * Set listeLieux.
     *
     * @param string $listeLieux
     *
     * @return FormatActivite
     */
    public function setListeLieux($listeLieux)
    {
        $this->listeLieux = $listeLieux;

        return $this;
    }

    /**
     * Get listeLieux.
     *
     * @return string
     */
    public function getListeLieux()
    {
        return $this->listeLieux;
    }

    /**
     * Set listeAutorisations.
     *
     * @param string $listeAutorisations
     *
     * @return FormatActivite
     */
    public function setListeAutorisations($listeAutorisations)
    {
        $this->listeAutorisations = $listeAutorisations;

        return $this;
    }

    /**
     * Get listeAutorisations.
     *
     * @return string
     */
    public function getListeAutorisations()
    {
        return $this->listeAutorisations;
    }

    /**
     * Set listeNiveauxSportifs.
     *
     * @param string $listeNiveauxSportifs
     *
     * @return FormatActivite
     */
    public function setListeNiveauxSportifs($listeNiveauxSportifs)
    {
        $this->listeNiveauxSportifs = $listeNiveauxSportifs;

        return $this;
    }

    /**
     * Get listeNiveauxSportifs.
     *
     * @return string
     */
    public function getListeNiveauxSportifs()
    {
        return $this->listeNiveauxSportifs;
    }

    /**
     * Set listeProfils.
     *
     * @param string $listeProfils
     *
     * @return FormatActivite
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
     */
    public function getListeProfils()
    {
        return $this->listeProfils;
    }

    /**
     * Set listeEncadrants.
     *
     * @param string $listeEncadrants
     *
     * @return FormatActivite
     */
    public function setListeEncadrants($listeEncadrants)
    {
        $this->listeEncadrants = $listeEncadrants;

        return $this;
    }

    /**
     * Get listeEncadrants.
     *
     * @return string
     */
    public function getListeEncadrants()
    {
        return $this->listeEncadrants;
    }
}
