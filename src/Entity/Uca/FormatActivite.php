<?php

/*
 * Classe - Format d'activité:
 *
 * Le format d'activité va correspondre au choix d'inscriptions proposés à l'utilisateur
 * Il s'agit de la classe mère de tous les formats qui contient donc les informations génériques.
*/

namespace App\Entity\Uca;

use App\Service\Common\Fctn;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FormatActiviteRepository")
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
 */
abstract class FormatActivite implements \App\Entity\Uca\Interfaces\JsonSerializable
{
    use \App\Entity\Uca\Traits\JsonSerializable;
    use \App\Entity\Uca\Traits\Article;

    //region Propriétés
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="Inscription", mappedBy="formatActivite")
     */
    protected $inscriptions;

    protected $type;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="complement.capacite.notblank")
     * @Assert\Regex(pattern="/^\d+$/", message="message.typeinvalide.entier")
     * @Assert\Expression("this.getMaxCapaciteProfil() <= this.getCapacite()", message="formatactivite.capaciteprofil.invalide" )
     */
    protected $capacite;

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
     * @ORM\ManyToOne(targetEntity="Activite", inversedBy="formatsActivite", fetch="LAZY")
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
     * @Assert\Expression("this.getDateDebutPublication()<=this.getDateDebutInscription()", message="message.erreur.datedebutinscription.publication")
     */
    private $dateDebutInscription;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank(message="formatactivite.dateFinInscription.notblank")
     * @Assert\Expression("this.getDateFinInscription() >= this.getDateDebutInscription()", message="message.erreur.datefin")
     * @Assert\Expression("this.getDateFinInscription()<=this.getDateFinPublication()", message="message.erreur.datefininscription.publication")
     * @Assert\Expression("this.getDateFinInscription()<=this.getDateFinEffective()",message="message.erreur.datefininscription.effective")
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

    /** @ORM\ManyToMany(targetEntity="Lieu", inversedBy="formatsActivite", fetch="LAZY")
     * @Assert\NotNull(message="formatactivite.lieu.notnull")
     * @Assert\Count(min = 1, minMessage = "formatactivite.lieu.notnull")
     */
    private $lieu;
    //endregion

    //region Propriétés communes Creneaux

    /** @ORM\ManyToMany(targetEntity="TypeAutorisation", inversedBy="formatsActivite", fetch="LAZY") */
    private $autorisations;

    /** @ORM\ManyToMany(targetEntity="NiveauSportif", fetch="LAZY")
     * @Assert\NotBlank(message="complement.niveauxsportifs.notblank")
     * @Assert\Count(min = 1, minMessage = "complement.niveauxsportifs.notblank")
     */
    private $niveauxSportifs;

    /**
     * @ORM\OneToMany(targetEntity="FormatActiviteProfilUtilisateur", mappedBy="formatActivite", fetch="LAZY", cascade={"persist", "remove"})
     * @Assert\Valid()
     */
    private $profilsUtilisateurs;

    /** @Gedmo\Versioned
     * @ORM\Column(type="boolean", nullable=false, options={"default":0}) */
    private $estPayant;

    /**
     * @ORM\ManyToOne(targetEntity="Tarif", inversedBy="formatsActivite", fetch="LAZY")
     * @Assert\Expression("!this.getEstPayant() || this.getTarif()", message="complement.tarif.notblank")
     */
    private $tarif;

    /** @Gedmo\Versioned
     * @ORM\Column(type="boolean", nullable=false, options={"default":0}) */
    private $estEncadre;

    /**
     * @ORM\ManyToMany(targetEntity="Utilisateur", inversedBy="formatsActivite", fetch="LAZY")
     * @Assert\Expression("!this.getEstEncadre() || !this.getEncadrants().isEmpty()", message="complement.encadrant.notblank")
     */
    private $encadrants;

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
    /** @Gedmo\Versioned
     * @ORM\Column(type="boolean", nullable=false) */
    private $promouvoir = false;
    //endregion

    /**
     * Constructor.
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

    //endregion

    //region Méthodes

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
        return ['libelle', 'description', 'type', 'estEncadre', 'profilsUtilisateurs', 'niveauxSportifs', 'encadrants', 'lieu', 'dateDebutEffective', 'dateFinEffective'];
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
        if ($image) {
            $this->updatedAt = new \DateTime('now');
        }

        return $this;
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
        }

        return $this->getArticleMontantDefaut($utilisateur);
    }

    public function getArticleLibelle()
    {
        return $this->getLibelle();
    }

    public function getArticleDescription()
    {
        return Fctn::strTruncate($this->getDescription(), 97);
    }

    public function getArticleDateDebut()
    {
        return $this->getDateDebutEffective();
    }

    public function getArticleDateFin()
    {
        return $this->getDateFinEffective();
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

    public function updateTarifLibelle()
    {
        if (null != $this->getTarif()) {
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
                $this->listeLieux .= ', ';
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
                $this->listeAutorisations .= ', ';
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
                $this->listeNiveauxSportifs .= ', ';
            }
            $this->listeNiveauxSportifs .= $niveauSportif->getLibelle();
        }

        return $this;
    }

    public function updateListeProfils()
    {
        $this->listeProfils = '';
        foreach ($this->getProfilsUtilisateurs() as $formatProfil) {
            if (!empty($this->listeProfils)) {
                $this->listeProfils .= ', ';
            }
            $this->listeProfils .= $formatProfil->getProfilUtilisateur()->getLibelle();
        }

        return $this;
    }

    public function updateListeEncadrants()
    {
        $this->listeEncadrants = '';
        foreach ($this->getEncadrants() as $encadrant) {
            if (!empty($this->listeEncadrants)) {
                $this->listeEncadrants .= ', ';
            }
            $this->listeEncadrants .= $encadrant->getPrenom().' '.$encadrant->getNom();
        }

        return $this;
    }

    public function getInscriptionsValidee()
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('statut', 'valide'))
        ;

        return $this->getInscriptions()->matching($criteria);
    }

    public function getAllInscriptions()
    {
        $criteria = Criteria::create()
            ->orWhere(Criteria::expr()->eq('statut', 'valide'))
            ->orWhere(Criteria::expr()->eq('statut', 'attentepaiement'))
            ->orWhere(Criteria::expr()->eq('statut', 'attentevalidationencadrant'))
            ->orWhere(Criteria::expr()->eq('statut', 'attenteajoutpanier'))
            ->orWhere(Criteria::expr()->eq('statut', 'attentevalidationgestionnaire'))
        ;

        return $this->getInscriptions()->matching($criteria);
    }

    public function getAutorisations($options = null)
    {
        if (empty($options)) {
            return $this->autorisations;
        }

        return $this->autorisations->filter(function ($item) use ($options) {
            return
                !(
                    isset($options['comportement'])
                    && !in_array($item->getComportement()->getCodeComportement(), $options['comportement'])
                )
                && !(
                    isset($options['utilisateur'])
                    && $options['utilisateur']->hasAutorisation($item)
                );
        });
    }

    public function getCapaciteTousProfil()
    {
        $capaciteTotale = 0;

        foreach ($this->getProfilsUtilisateurs() as $formatProfil) {
            $capaciteTotale += (is_integer(intval($formatProfil->getCapaciteProfil())) ? intval($formatProfil->getCapaciteProfil()) : 0);
        }

        return $capaciteTotale;
    }

    public function getMaxCapaciteProfil()
    {
        $capaciteMax = 0;

        foreach ($this->getProfilsUtilisateurs() as $formatProfil) {
            if ((is_integer(intval($formatProfil->getCapaciteProfil())) ? intval($formatProfil->getCapaciteProfil()) : 0) > 0) {
                $capaciteMax = intval($formatProfil->getCapaciteProfil());
            }
        }

        return $capaciteMax;
    }

    public function getCapaciteProfil($profilUtilisateur)
    {
        $criteria = Criteria::create()->andWhere(Criteria::expr()->eq('profilUtilisateur', $profilUtilisateur));
        $result = $this->getProfilsUtilisateurs()->matching($criteria);

        return !$result->isEmpty() ? $result->first()->getCapaciteProfil() : false;
    }

    // end region

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
     * @return FormatActivite
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
     * @param string $description
     *
     * @return FormatActivite
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set lienHtml.
     *
     * @param null|string $lienHtml
     *
     * @return FormatActivite
     * @codeCoverageIgnore
     */
    public function setLienHtml($lienHtml = null)
    {
        $this->lienHtml = $lienHtml;

        return $this;
    }

    /**
     * Get lienHtml.
     *
     * @return null|string
     * @codeCoverageIgnore
     */
    public function getLienHtml()
    {
        return $this->lienHtml;
    }

    /**
     * Set lienPdf.
     *
     * @param null|string $lienPdf
     *
     * @return FormatActivite
     * @codeCoverageIgnore
     */
    public function setLienPdf($lienPdf = null)
    {
        $this->lienPdf = $lienPdf;

        return $this;
    }

    /**
     * Get lienPdf.
     *
     * @return null|string
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @return FormatActivite
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
     * Set estPayant.
     *
     * @param bool $estPayant
     *
     * @return FormatActivite
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     */
    public function getStatut()
    {
        return $this->statut;
    }

    /**
     * Set activite.
     *
     * @return FormatActivite
     * @codeCoverageIgnore
     */
    public function setActivite(Activite $activite = null)
    {
        $this->activite = $activite;

        return $this;
    }

    /**
     * Get activite.
     *
     * @return null|Activite
     * @codeCoverageIgnore
     */
    public function getActivite()
    {
        return $this->activite;
    }

    /**
     * Add inscription.
     *
     * @return FormatActivite
     * @codeCoverageIgnore
     */
    public function addInscription(Inscription $inscription)
    {
        $this->inscriptions[] = $inscription;

        return $this;
    }

    /**
     * Remove inscription.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeInscription(Inscription $inscription)
    {
        return $this->inscriptions->removeElement($inscription);
    }

    /**
     * Get inscriptions.
     *
     * @return \Doctrine\Common\Collections\Collection
     * @codeCoverageIgnore
     */
    public function getInscriptions()
    {
        return $this->inscriptions;
    }

    /**
     * Add lieu.
     *
     * @return FormatActivite
     * @codeCoverageIgnore
     */
    public function addLieu(Lieu $lieu)
    {
        $this->lieu[] = $lieu;

        return $this;
    }

    /**
     * Remove lieu.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeLieu(Lieu $lieu)
    {
        return $this->lieu->removeElement($lieu);
    }

    /**
     * Get lieu.
     *
     * @return \Doctrine\Common\Collections\Collection
     * @codeCoverageIgnore
     */
    public function getLieu()
    {
        return $this->lieu;
    }

    /**
     * Add autorisation.
     *
     * @return FormatActivite
     * @codeCoverageIgnore
     */
    public function addAutorisation(TypeAutorisation $autorisation)
    {
        $this->autorisations[] = $autorisation;

        return $this;
    }

    /**
     * Remove autorisation.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeAutorisation(TypeAutorisation $autorisation)
    {
        return $this->autorisations->removeElement($autorisation);
    }

    /**
     * Add niveauxSportif.
     *
     * @return FormatActivite
     * @codeCoverageIgnore
     */
    public function addNiveauxSportif(NiveauSportif $niveauxSportif)
    {
        $this->niveauxSportifs[] = $niveauxSportif;

        return $this;
    }

    /**
     * Remove niveauxSportif.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeNiveauxSportif(NiveauSportif $niveauxSportif)
    {
        return $this->niveauxSportifs->removeElement($niveauxSportif);
    }

    /**
     * Get niveauxSportifs.
     *
     * @return \Doctrine\Common\Collections\Collection
     * @codeCoverageIgnore
     */
    public function getNiveauxSportifs()
    {
        return $this->niveauxSportifs;
    }

    /**
     * Add profilsUtilisateur.
     *
     * @param ProfilUtilisateur $profilsUtilisateur
     *
     * @return FormatActivite
     * @codeCoverageIgnore
     */
    public function addProfilsUtilisateur(FormatActiviteProfilUtilisateur $profilsUtilisateur)
    {
        $this->profilsUtilisateurs[] = $profilsUtilisateur;

        return $this;
    }

    /**
     * Remove profilsUtilisateur.
     *
     * @param ProfilUtilisateur $profilsUtilisateur
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeProfilsUtilisateur(FormatActiviteProfilUtilisateur $profilsUtilisateur)
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

    /**
     * Set tarif.
     *
     * @return FormatActivite
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
     * Add encadrant.
     *
     * @return FormatActivite
     * @codeCoverageIgnore
     */
    public function addEncadrant(Utilisateur $encadrant)
    {
        $this->encadrants[] = $encadrant;

        return $this;
    }

    /**
     * Remove encadrant.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeEncadrant(Utilisateur $encadrant)
    {
        return $this->encadrants->removeElement($encadrant);
    }

    /**
     * Get encadrants.
     *
     * @return \Doctrine\Common\Collections\Collection
     * @codeCoverageIgnore
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
     * Set listeLieux.
     *
     * @param string $listeLieux
     *
     * @return FormatActivite
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * Set listeEncadrants.
     *
     * @param string $listeEncadrants
     *
     * @return FormatActivite
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     */
    public function getListeEncadrants()
    {
        return $this->listeEncadrants;
    }

    /**
     * Set promouvoir.
     *
     * @param bool $promouvoir
     *
     * @return FormatSimple
     * @codeCoverageIgnore
     */
    public function setPromouvoir($promouvoir)
    {
        $this->promouvoir = $promouvoir;

        return $this;
    }

    /**
     * Get promouvoir.
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function getPromouvoir()
    {
        return $this->promouvoir;
    }

    /**
     * Fonction qui permet de savoir si un Etablissment est associé à un des lieux du format.
     *
     * @param [type] $idEtablissement
     * @codeCoverageIgnore
     */
    public function hasEtablissement($idEtablissement): bool
    {
        foreach ($this->lieu as $l) {
            if ($l->getEtablissement() && $l->getEtablissement()->getId() == $idEtablissement) {
                return true;
            }
        }

        return false;
    }
}