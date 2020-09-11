<?php

/*
 * Classe - Utilisateur:
 *
 * Les utilsateurs de l'application.
*/

namespace UcaBundle\Entity;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as FOSUser;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use UcaBundle\Repository\CommandeDetailRepository;
use UcaBundle\Repository\CommandeRepository;
use UcaBundle\Repository\EntityRepository;
use UcaBundle\Validator\Constraints as UcaAssert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="UcaBundle\Repository\UtilisateurRepository")
 * @Vich\Uploadable
 * @UniqueEntity("username", message="utilisateur.username.existant")
 * @UniqueEntity("email", message="utilisateur.mail.existant")
 */
class Utilisateur extends FOSUser implements \UcaBundle\Entity\Interfaces\JsonSerializable
{
    use \UcaBundle\Entity\Traits\JsonSerializable;

    //region Propriétés
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Assert\NotNull(message="utilisateur.username.notnull")
     * @Assert\Length(min = 2, max = 180, minMessage = "utilisateur.username.tropPetit", maxMessage = "utilisateur.username.tropLong")
     * @UcaAssert\UsernameConstraint
     */
    protected $username;

    /**
     * @Assert\NotNull(message="utilisateur.mail.notnull")
     * @Assert\Length(min = 2, max = 180, minMessage = "utilisateur.mail.tropPetit", maxMessage = "utilisateur.mail.tropLong")
     * @Assert\Email(message = "utilisateur.mail.invalide")
     * @Assert\Expression(
     *  "this.getEmailDomain() not in ['@univ-cotedazur.fr', '@unice.fr','@etu.univ-cotedazur.fr'] || this.getShibboleth()",
     *  message = "utilisateur.mail.shibboleth"
     * )
     */
    protected $email;
    /**
     * @Assert\Length(min = 2, max = 4096, minMessage = "utilisateur.password.tropPetit")
     * @Assert\Expression("this.getId() !== null || this.getPlainPassword() !== null", message="utilisateur.password.notnull")
     */
    protected $plainPassword;

    /** @ORM\ManyToMany(targetEntity="UcaBundle\Entity\Groupe", inversedBy="utilisateurs") */
    protected $groups;

    /** @ORM\OneToMany(targetEntity="Inscription", mappedBy="utilisateur") */
    protected $inscriptions;

    /** @ORM\ManyToMany(targetEntity="TypeAutorisation", cascade={"persist"}, fetch="EAGER") */
    protected $autorisations;

    /** @ORM\ManyToOne(targetEntity="ProfilUtilisateur", inversedBy="utilisateur", cascade={"persist"})
     * @Assert\NotNull(message="utilisateur.UserProfile.notnull") */
    protected $profil;

    /** @ORM\OneToMany(targetEntity="Commande", mappedBy="utilisateur") */
    protected $commandes;

    /** @ORM\ManyToMany(targetEntity="FormatActivite", mappedBy="encadrants") */
    protected $formatsActivite;

    /** @ORM\ManyToMany(targetEntity="Creneau", mappedBy="encadrants") */
    protected $creneaux;

    /** @ORM\Column(type="boolean", options={"default":"0"}) */
    protected $shibboleth = false;

    /** @ORM\ManyToOne(targetEntity="StatutUtilisateur", inversedBy="utilisateur", cascade={"persist"}) */
    protected $statut;

    /** @ORM\Column(type="boolean") */
    protected $cgvAcceptees = false;

    /** @ORM\Column(type="text", nullable=true) */
    private $description;

    /** @ORM\Column(type="string", nullable=true) */
    private $matricule;

    /** @ORM\Column(type="string", nullable=true) */
    private $numeroNfc;

    /** @ORM\Column(type="string", nullable=true)
     * @Assert\NotNull(message="utilisateur.firstname.notnull") */
    private $prenom;

    /** @ORM\Column(type="string", nullable=true)
     * @Assert\NotNull(message="utilisateur.name.notnull") */
    private $nom;

    /** @ORM\Column(type="string", nullable=true,length=1)
     * @Assert\NotNull(message="utilisateur.sexe.notnull") */
    private $sexe;

    /** @ORM\Column(type="string", nullable=true) */
    private $adresse;

    /** @ORM\Column(type="string", nullable=true ,length=5)
     *  @Assert\Regex(pattern="/^[0-9]{5}$/", message="lieu.codepostal.invalide")
     */
    private $codePostal;

    /** @ORM\Column(type="string", nullable=true) */
    private $ville;

    /** @ORM\Column(type="date", nullable=true) */
    private $dateNaissance;

    /** @ORM\Column(type="string", nullable=true)
     *  @Assert\Length(min = 10, max = 10, minMessage = "utilisateur.telephone.invalide", maxMessage = "utilisateur.telephone.invalide")
     *  @Assert\Regex(pattern="/^0[0-9]([-. ]?[0-9]{2}){4}$/", message="utilisateur.telephone.invalide")
     */
    private $telephone;

    /** @ORM\ManyToMany(targetEntity="Inscription", mappedBy="encadrants") */
    private $inscriptionsAValider;

    /** @ORM\Column(type="string", length=255, nullable=true, options={"default": NULL}) */
    private $document;

    /** @Vich\UploadableField(mapping="utilisateur_document", fileNameProperty="document")
     * @Assert\File(
     *     mimeTypes = {"image/png", "image/jpeg", "image/tiff", "application/pdf"},
     *     mimeTypesMessage = "utilisateur.document.format.erreur"
     * )
     */
    private $documentFile;

    /** @ORM\Column(type="datetime",nullable=true) */
    private $updatedAt;

    /** @ORM\OneToMany(targetEntity="Appel", mappedBy="utilisateur") */
    private $appels;

    /** @ORM\OneToMany(targetEntity="UtilisateurCreditHistorique", mappedBy="utilisateur",cascade={"persist","remove"}) */
    private $credit;

    //endregion

    //region Méthodes

    public function __construct()
    {
        parent::__construct();
        $this->inscriptions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->autorisations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->reservations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->commandes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString()
    {
        return ucfirst($this->getPrenom()).' '.ucfirst($this->getNom());
    }

    public static function getRandomPassword()
    {
        // $password = str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789");
        // $password = substr($password, mt_rand(0, 30), 5);
        return base64_encode(random_bytes(10));
    }

    public function jsonSerializeProperties()
    {
        return ['prenom', 'nom'];
    }

    public function getCommandesByCriteria($crits)
    {
        $criterias = EntityRepository::criteriaBy($crits);

        return $this->getCommandes()->matching($criterias);
    }

    public function getCommandesByStatut($statut)
    {
        return $this->getCommandesByCriteria([['statut', 'eq', $statut]]);
    }

    public function getCommandeByAvoir($refAvoir)
    {
        foreach ($this->getCommandes() as $commande) {
            if (!$commande->getCommmandeDetailsByAvoir($refAvoir)->isEmpty()) {
                return $commande->getId();
            }
        }

        return false;
    }

    public function getPanier()
    {
        $panier = $this->getCommandesByStatut('panier')->first();
        if (!$panier) {
            $panier = new Commande($this);
        }

        return $panier;
    }

    public function movePanierToCommande()
    {
        if (!empty($this->panier)) {
            $this->addCommande($this->panier);
            $this->panier = null;
        }
    }

    public function getInscriptionsByCriteria($crits)
    {
        $criterias = EntityRepository::criteriaBy($crits);

        return $this->getInscriptions()->matching($criterias);
    }

    public function hasInscriptionsByCriteria($crits)
    {
        return !$this->getInscriptionsByCriteria($crits)->isEmpty();
    }

    public function hasAutorisation($typeAutorisation)
    {
        // $criteria = Criteria::create()->andWhere(Criteria::expr()->eq('id', $typeAutorisation->getId()));
        // return !$this->autorisations->matching($criteria)->isEmpty();
        return $this->autorisations->contains($typeAutorisation);
    }

    public function getNbInscriptionCreneau()
    {
        return $this->getInscriptionsByCriteria([
            ['creneau', 'neq', null],
            ['statut', 'notIn', ['annule', 'desinscrit', 'ancienneinscription', 'desinscriptionadministrative']],
        ])->count();
    }

    public function nbCreneauMaximumAtteint()
    {
        return $this->getNbInscriptionCreneau() >= $this->getProfil()->getNbMaxInscriptions();
    }

    public function setDocumentFile(File $document = null)
    {
        $this->documentFile = $document;
        if ($document) {
            $this->updatedAt = new \DateTime('now');
        }
    }

    public function getDocumentFile()
    {
        return $this->documentFile;
    }

    public function addAutorisation(TypeAutorisation $autorisation)
    {
        $this->autorisations[] = $autorisation;
        $commandesEnCours = $this->getCommandes()
            ->matching(CommandeRepository::criteriaByStatut(['panier', 'apayer']))
            ->filter(function ($commande) {
                return 'termine' != $commande->getStatut();
            })
        ;

        foreach ($commandesEnCours->getIterator() as $commande) {
            $commande->getCommandeDetails()
                ->matching(CommandeDetailRepository::criteriaByAutorisation($autorisation))
                ->map(function ($cd) {
                    $cd->remove();
                })
            ;
        }

        return $this;
    }

    public function isEncadrantEvenement(DhtmlxEvenement $dhtmlxEvenement)
    {
        if (null != $dhtmlxEvenement->getSerie()) {
            if (null != $dhtmlxEvenement->getSerie()->getCreneau()) {
                return $dhtmlxEvenement->getSerie()->getCreneau()->getEncadrants()->contains($this);
            }
        }
        if ($dhtmlxEvenement->getFormatSimple()) {
            return $dhtmlxEvenement->getFormatSimple()->getEncadrants()->contains($this);
        }

        return false;
    }

    public function getEmailDomain()
    {
        return strstr($this->getEmail(), '@');
    }

    public function getCreditTotal()
    {
        $solde = 0;
        foreach ($this->getCredit() as $ligneCredit) {
            if ('valide' == $ligneCredit->getStatut()) {
                $solde += $ligneCredit->getMontant();
            }
        }

        return $solde;
    }

    //endregion

    /**
     * Set description.
     *
     * @param null|string $description
     *
     * @return Utilisateur
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
     * Set matricule.
     *
     * @param null|string $matricule
     *
     * @return Utilisateur
     */
    public function setMatricule($matricule = null)
    {
        $this->matricule = $matricule;

        return $this;
    }

    /**
     * Get matricule.
     *
     * @return string|null
     */
    public function getMatricule()
    {
        return $this->matricule;
    }

    /**
     * Set numeroNfc.
     *
     * @param null|string $numeroNfc
     *
     * @return Utilisateur
     */
    public function setNumeroNfc($numeroNfc = null)
    {
        $this->numeroNfc = $numeroNfc;

        return $this;
    }

    /**
     * Get numeroNfc.
     *
     * @return string|null
     */
    public function getNumeroNfc()
    {
        return $this->numeroNfc;
    }

    /**
     * Set prenom.
     *
     * @param null|string $prenom
     *
     * @return Utilisateur
     */
    public function setPrenom($prenom = null)
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * Get prenom.
     *
     * @return string|null
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * Set nom.
     *
     * @param null|string $nom
     *
     * @return Utilisateur
     */
    public function setNom($nom = null)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom.
     *
     * @return string|null
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set sexe.
     *
     * @param null|string $sexe
     *
     * @return Utilisateur
     */
    public function setSexe($sexe = null)
    {
        $this->sexe = $sexe;

        return $this;
    }

    /**
     * Get sexe.
     *
     * @return string|null
     */
    public function getSexe()
    {
        return $this->sexe;
    }

    /**
     * Set adresse.
     *
     * @param null|string $adresse
     *
     * @return Utilisateur
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
     * @return Utilisateur
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
     * @return Utilisateur
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

    /**
     * Set dateNaissance.
     *
     * @param null|\DateTime $dateNaissance
     *
     * @return Utilisateur
     */
    public function setDateNaissance($dateNaissance = null)
    {
        $this->dateNaissance = $dateNaissance;

        return $this;
    }

    /**
     * Get dateNaissance.
     *
     * @return \DateTime|null
     */
    public function getDateNaissance()
    {
        return $this->dateNaissance;
    }

    /**
     * Set telephone.
     *
     * @param null|string $telephone
     *
     * @return Utilisateur
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
     * Set shibboleth.
     *
     * @param bool $shibboleth
     *
     * @return Utilisateur
     */
    public function setShibboleth($shibboleth)
    {
        $this->shibboleth = $shibboleth;

        return $this;
    }

    /**
     * Get shibboleth.
     *
     * @return bool
     */
    public function getShibboleth()
    {
        return $this->shibboleth;
    }

    /**
     * Set document.
     *
     * @param null|string $document
     *
     * @return Utilisateur
     */
    public function setDocument($document = null)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * Get document.
     *
     * @return string|null
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Set updatedAt.
     *
     * @param null|\DateTime $updatedAt
     *
     * @return Utilisateur
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
     * Set cgvAcceptees.
     *
     * @param bool $cgvAcceptees
     *
     * @return Utilisateur
     */
    public function setCgvAcceptees($cgvAcceptees)
    {
        $this->cgvAcceptees = $cgvAcceptees;

        return $this;
    }

    /**
     * Get cgvAcceptees.
     *
     * @return bool
     */
    public function getCgvAcceptees()
    {
        return $this->cgvAcceptees;
    }

    /**
     * Add inscription.
     *
     * @param \UcaBundle\Entity\Inscription $inscription
     *
     * @return Utilisateur
     */
    public function addInscription(Inscription $inscription)
    {
        $this->inscriptions[] = $inscription;

        return $this;
    }

    /**
     * Remove inscription.
     *
     * @param \UcaBundle\Entity\Inscription $inscription
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeInscription(Inscription $inscription)
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
     * Remove autorisation.
     *
     * @param \UcaBundle\Entity\TypeAutorisation $autorisation
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeAutorisation(TypeAutorisation $autorisation)
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
     * Set profil.
     *
     * @param null|\UcaBundle\Entity\ProfilUtilisateur $profil
     *
     * @return Utilisateur
     */
    public function setProfil(ProfilUtilisateur $profil = null)
    {
        $this->profil = $profil;

        return $this;
    }

    /**
     * Get profil.
     *
     * @return \UcaBundle\Entity\ProfilUtilisateur|null
     */
    public function getProfil()
    {
        return $this->profil;
    }

    /**
     * Add commande.
     *
     * @param \UcaBundle\Entity\Commande $commande
     *
     * @return Utilisateur
     */
    public function addCommande(Commande $commande)
    {
        $this->commandes[] = $commande;

        return $this;
    }

    /**
     * Remove commande.
     *
     * @param \UcaBundle\Entity\Commande $commande
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeCommande(Commande $commande)
    {
        return $this->commandes->removeElement($commande);
    }

    /**
     * Get commandes.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCommandes()
    {
        return $this->commandes;
    }

    /**
     * Add formatsActivite.
     *
     * @param \UcaBundle\Entity\FormatActivite $formatsActivite
     *
     * @return Utilisateur
     */
    public function addFormatsActivite(FormatActivite $formatsActivite)
    {
        $this->formatsActivite[] = $formatsActivite;

        return $this;
    }

    /**
     * Remove formatsActivite.
     *
     * @param \UcaBundle\Entity\FormatActivite $formatsActivite
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeFormatsActivite(FormatActivite $formatsActivite)
    {
        return $this->formatsActivite->removeElement($formatsActivite);
    }

    /**
     * Get formatsActivite.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFormatsActivite()
    {
        return $this->formatsActivite;
    }

    /**
     * Add creneaux.
     *
     * @param \UcaBundle\Entity\Creneau $creneaux
     *
     * @return Utilisateur
     */
    public function addCreneaux(Creneau $creneaux)
    {
        $this->creneaux[] = $creneaux;

        return $this;
    }

    /**
     * Remove creneaux.
     *
     * @param \UcaBundle\Entity\Creneau $creneaux
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeCreneaux(Creneau $creneaux)
    {
        return $this->creneaux->removeElement($creneaux);
    }

    /**
     * Get creneaux.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCreneaux()
    {
        return $this->creneaux;
    }

    /**
     * Add inscriptionsAValider.
     *
     * @param \UcaBundle\Entity\Inscription $inscriptionsAValider
     *
     * @return Utilisateur
     */
    public function addInscriptionsAValider(Inscription $inscriptionsAValider)
    {
        $this->inscriptionsAValider[] = $inscriptionsAValider;

        return $this;
    }

    /**
     * Remove inscriptionsAValider.
     *
     * @param \UcaBundle\Entity\Inscription $inscriptionsAValider
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeInscriptionsAValider(Inscription $inscriptionsAValider)
    {
        return $this->inscriptionsAValider->removeElement($inscriptionsAValider);
    }

    /**
     * Get inscriptionsAValider.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInscriptionsAValider()
    {
        return $this->inscriptionsAValider;
    }

    /**
     * Set statut.
     *
     * @param null|\UcaBundle\Entity\StatutUtilisateur $statut
     *
     * @return Utilisateur
     */
    public function setStatut(StatutUtilisateur $statut = null)
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * Get statut.
     *
     * @return \UcaBundle\Entity\StatutUtilisateur|null
     */
    public function getStatut()
    {
        return $this->statut;
    }

    /**
     * Add appel.
     *
     * @param \UcaBundle\Entity\Appel $appel
     *
     * @return Utilisateur
     */
    public function addAppel(Appel $appel)
    {
        $this->appels[] = $appel;

        return $this;
    }

    /**
     * Remove appel.
     *
     * @param \UcaBundle\Entity\Appel $appel
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeAppel(Appel $appel)
    {
        return $this->appels->removeElement($appel);
    }

    /**
     * Get appels.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAppels()
    {
        return $this->appels;
    }

    /**
     * Add credit.
     *
     * @param \UcaBundle\Entity\UtilisateurCreditHistorique $credit
     *
     * @return Utilisateur
     */
    public function addCredit(UtilisateurCreditHistorique $credit)
    {
        $this->credit[] = $credit;

        return $this;
    }

    /**
     * Remove credit.
     *
     * @param \UcaBundle\Entity\UtilisateurCreditHistorique $credit
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeCredit(UtilisateurCreditHistorique $credit)
    {
        return $this->credit->removeElement($credit);
    }

    /**
     * Get credit.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCredit()
    {
        return $this->credit;
    }
}
