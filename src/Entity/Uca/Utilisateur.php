<?php

/*
 * Classe - Utilisateur:
 *
 * Les utilsateurs de l'application.
*/

namespace App\Entity\Uca;

use App\Repository\CommandeDetailRepository;
use App\Repository\CommandeRepository;
use App\Repository\EntityRepository;
use App\Validator\Constraints as UcaAssert;
use DateTime;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UtilisateurRepository")
 * @Vich\Uploadable
 * @UniqueEntity("username", message="utilisateur.username.existant")
 * @UniqueEntity("email", message="utilisateur.mail.existant")
 */
class Utilisateur implements \App\Entity\Uca\Interfaces\JsonSerializable, UserInterface, PasswordAuthenticatedUserInterface
{
    use \App\Entity\Uca\Traits\JsonSerializable;

    // region Propriétés
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
     * @ORM\Column(type="string")
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
     * @ORM\Column(type="string")
     */
    protected $email;

    /**
     * @Assert\Length(min = 2, max = 4096, minMessage = "utilisateur.password.tropPetit")
     * @Assert\Expression("this.getId() !== null || this.getPlainPassword() !== null", message="utilisateur.password.notnull")
     */
    protected $plainPassword;

    /**
     * @ORM\Column(type="string")
     */
    protected $password;

    /** @ORM\ManyToMany(targetEntity="Groupe", inversedBy="utilisateurs") */
    protected $groups;

    /** @ORM\OneToMany(targetEntity="Inscription", mappedBy="utilisateur") */
    protected $inscriptions;

    /** @ORM\ManyToMany(targetEntity="TypeAutorisation", cascade={"persist"}, fetch="EAGER") */
    protected $autorisations;

    /** @ORM\ManyToOne(targetEntity="ProfilUtilisateur", inversedBy="utilisateur", cascade={"persist"}, fetch="LAZY")
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

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $lastLogin;

    /**
     * Random string sent to the user email address in order to verify it.
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $confirmationToken;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $passwordRequestedAt;

    /**
     * @ORM\Column(name="roles", type="json", nullable=false)
     */
    protected $roles = [];

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

    /** @ORM\Column(type="boolean", nullable=true) */
    private $enabled;

    // endregion

    // region Méthodes

    /**
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        $this->enabled = false;
        $this->roles = [];
        $this->inscriptions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->autorisations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->reservations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->commandes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->groups = new \Doctrine\Common\Collections\ArrayCollection();
        $this->credit = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString()
    {
        return ucfirst($this->getPrenom()).' '.ucfirst($this->getNom());
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
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
        return (int) sizeof(
            $this->getInscriptionsByCriteria([
                ['creneau', 'neq', null],
                ['statut', 'notIn', ['annule', 'desinscrit', 'ancienneinscription', 'desinscriptionadministrative']],
            ])->filter(function ($insc) { return $insc->getFormatActivite() && 'FormatAvecCreneau' == $insc->getFormatActivite()->getFormat(); })
        );
    }

    public function nbCreneauMaximumAtteint()
    {
        return $this->getNbInscriptionCreneau() >= $this->getProfil()->getNbMaxInscriptions();
    }

    /**
     * Fonction qui permet de savoir si l'inscription à une ressource est comprise dans la semaine dans la semaine de la réservabilité.
     */
    public function isValidInscriptionRessource(Inscription $inscription, DateTime $dateDebutEvent): bool
    {
        $dayEvent = $dateDebutEvent->format('N');
        $modify = [
            '1' => [0, 6],
            '2' => [1, 5],
            '3' => [2, 4],
            '4' => [3, 3],
            '5' => [4, 2],
            '6' => [5, 1],
            '7' => [6, 0],
        ];
        $firstDay = (clone $dateDebutEvent)->modify('-'.$modify[$dayEvent][0].' day'); // lundi de la semaine de la réservabilité
        $lastDay = (clone $dateDebutEvent)->modify('+'.$modify[$dayEvent][1].' day'); // dimanche de la semaine de la réservabilité

        $dateInscription = $inscription->getReservabilite()->getEvenement()->getDateDebut();

        return $firstDay <= $dateInscription && $dateInscription <= $lastDay;
    }

    /**
     * Fonction qui permet de récupérer le nombre d'inscription à des ressources pour la semaine de la réservabilité.
     */
    public function getNbInscriptionRessource(Reservabilite $reservabilite): int
    {
        $dateDebutEvent = $reservabilite->getEvenement()->getDateDebut();

        return (int) sizeof(
            $this->getInscriptionsByCriteria([
                ['reservabilite', 'neq', null],
                ['statut', 'notIn', ['annule', 'desinscrit', 'ancienneinscription', 'desinscriptionadministrative']],
            ])->filter(function ($insc) use ($dateDebutEvent) { return $this->isValidInscriptionRessource($insc, $dateDebutEvent); })
        );
    }

    /**
     * Fonction qui permet de savoir si l'utilisateur a atteint son quota de ressource pour la semaine de la réservabilité.
     */
    public function nbRessourceMaximumAtteint(Reservabilite $reservabilite): bool
    {
        return $this->getNbInscriptionRessource($reservabilite) >= $this->getProfil()->getNbMaxInscriptionsRessource();
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

    // endregion

    /**
     * Set description.
     *
     * @param null|string $description
     *
     * @return Utilisateur
     *
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
     * Set matricule.
     *
     * @param null|string $matricule
     *
     * @return Utilisateur
     * @codeCoverageIgnore
     */
    public function setMatricule($matricule = null)
    {
        $this->matricule = $matricule;

        return $this;
    }

    /**
     * Get matricule.
     *
     * @return null|string
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     */
    public function setNumeroNfc($numeroNfc = null)
    {
        $this->numeroNfc = $numeroNfc;

        return $this;
    }

    /**
     * Get numeroNfc.
     *
     * @return null|string
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     */
    public function setPrenom($prenom = null)
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * Get prenom.
     *
     * @return null|string
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     */
    public function setNom($nom = null)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom.
     *
     * @return null|string
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     */
    public function setSexe($sexe = null)
    {
        $this->sexe = $sexe;

        return $this;
    }

    /**
     * Get sexe.
     *
     * @return null|string
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     */
    public function setAdresse($adresse = null)
    {
        $this->adresse = $adresse;

        return $this;
    }

    /**
     * Get adresse.
     *
     * @return null|string
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     */
    public function setCodePostal($codePostal = null)
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    /**
     * Get codePostal.
     *
     * @return null|string
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     */
    public function setVille($ville = null)
    {
        $this->ville = $ville;

        return $this;
    }

    /**
     * Get ville.
     *
     * @return null|string
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     */
    public function setDateNaissance($dateNaissance = null)
    {
        $this->dateNaissance = $dateNaissance;

        return $this;
    }

    /**
     * Get dateNaissance.
     *
     * @return null|\DateTime
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     */
    public function setTelephone($telephone = null)
    {
        $this->telephone = $telephone;

        return $this;
    }

    /**
     * Get telephone.
     *
     * @return null|string
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     */
    public function setDocument($document = null)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * Get document.
     *
     * @return null|string
     * @codeCoverageIgnore
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
     * Set cgvAcceptees.
     *
     * @param bool $cgvAcceptees
     *
     * @return Utilisateur
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     */
    public function getCgvAcceptees()
    {
        return $this->cgvAcceptees;
    }

    /**
     * Add inscription.
     *
     * @return Utilisateur
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
     * Get autorisations.
     *
     * @return \Doctrine\Common\Collections\Collection
     * @codeCoverageIgnore
     */
    public function getAutorisations()
    {
        return $this->autorisations;
    }

    /**
     * Set profil.
     *
     * @return Utilisateur
     * @codeCoverageIgnore
     */
    public function setProfil(ProfilUtilisateur $profil = null)
    {
        $this->profil = $profil;

        return $this;
    }

    /**
     * Get profil.
     *
     * @return null|ProfilUtilisateur
     * @codeCoverageIgnore
     */
    public function getProfil()
    {
        return $this->profil;
    }

    /**
     * Add commande.
     *
     * @return Utilisateur
     * @codeCoverageIgnore
     */
    public function addCommande(Commande $commande)
    {
        $this->commandes[] = $commande;

        return $this;
    }

    /**
     * Remove commande.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeCommande(Commande $commande)
    {
        return $this->commandes->removeElement($commande);
    }

    /**
     * Get commandes.
     *
     * @return \Doctrine\Common\Collections\Collection
     * @codeCoverageIgnore
     */
    public function getCommandes()
    {
        return $this->commandes;
    }

    /**
     * Add formatsActivite.
     *
     * @return Utilisateur
     * @codeCoverageIgnore
     */
    public function addFormatsActivite(FormatActivite $formatsActivite)
    {
        $this->formatsActivite[] = $formatsActivite;

        return $this;
    }

    /**
     * Remove formatsActivite.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeFormatsActivite(FormatActivite $formatsActivite)
    {
        return $this->formatsActivite->removeElement($formatsActivite);
    }

    /**
     * Get formatsActivite.
     *
     * @return \Doctrine\Common\Collections\Collection
     * @codeCoverageIgnore
     */
    public function getFormatsActivite()
    {
        return $this->formatsActivite;
    }

    /**
     * Add creneaux.
     *
     * @return Utilisateur
     * @codeCoverageIgnore
     */
    public function addCreneaux(Creneau $creneaux)
    {
        $this->creneaux[] = $creneaux;

        return $this;
    }

    /**
     * Remove creneaux.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeCreneaux(Creneau $creneaux)
    {
        return $this->creneaux->removeElement($creneaux);
    }

    /**
     * Get creneaux.
     *
     * @return \Doctrine\Common\Collections\Collection
     * @codeCoverageIgnore
     */
    public function getCreneaux()
    {
        return $this->creneaux;
    }

    /**
     * Add inscriptionsAValider.
     *
     * @return Utilisateur
     * @codeCoverageIgnore
     */
    public function addInscriptionsAValider(Inscription $inscriptionsAValider)
    {
        $this->inscriptionsAValider[] = $inscriptionsAValider;

        return $this;
    }

    /**
     * Remove inscriptionsAValider.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeInscriptionsAValider(Inscription $inscriptionsAValider)
    {
        return $this->inscriptionsAValider->removeElement($inscriptionsAValider);
    }

    /**
     * Get inscriptionsAValider.
     *
     * @return \Doctrine\Common\Collections\Collection
     * @codeCoverageIgnore
     */
    public function getInscriptionsAValider()
    {
        return $this->inscriptionsAValider;
    }

    /**
     * Set statut.
     *
     * @return Utilisateur
     * @codeCoverageIgnore
     */
    public function setStatut(StatutUtilisateur $statut = null)
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * Get statut.
     *
     * @return null|StatutUtilisateur
     * @codeCoverageIgnore
     */
    public function getStatut()
    {
        return $this->statut;
    }

    /**
     * Add appel.
     *
     * @return Utilisateur
     * @codeCoverageIgnore
     */
    public function addAppel(Appel $appel)
    {
        $this->appels[] = $appel;

        return $this;
    }

    /**
     * Remove appel.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeAppel(Appel $appel)
    {
        return $this->appels->removeElement($appel);
    }

    /**
     * Get appels.
     *
     * @return \Doctrine\Common\Collections\Collection
     * @codeCoverageIgnore
     */
    public function getAppels()
    {
        return $this->appels;
    }

    /**
     * Add credit.
     *
     * @return Utilisateur
     * @codeCoverageIgnore
     */
    public function addCredit(UtilisateurCreditHistorique $credit)
    {
        $this->credit[] = $credit;

        return $this;
    }

    /**
     * Remove credit.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeCredit(UtilisateurCreditHistorique $credit)
    {
        return $this->credit->removeElement($credit);
    }

    /**
     * Get credit.
     *
     * @return \Doctrine\Common\Collections\Collection
     * @codeCoverageIgnore
     */
    public function getCredit()
    {
        return $this->credit;
    }

    public function addRole($role)
    {
        $role = strtoupper($role);
        if ('ROLE_USER' === $role) {
            return $this;
        }

        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     *
     * @codeCoverageIgnore
     */
    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    /**
     * Get Id.
     *
     * @return int
     * @codeCoverageIgnore
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     *
     * @codeCoverageIgnore
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Get email.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get password.
     *
     * @return null|string The encoded password if any
     * @codeCoverageIgnore
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Get plainPassword.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * Get lastLogin.
     *
     * @return \Datetime
     * @codeCoverageIgnore
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * Get confirmationToken.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getConfirmationToken()
    {
        return $this->confirmationToken;
    }

    /**
     * Get roles.
     *
     * @return array The user roles
     */
    public function getRoles()
    {
        $roles = $this->roles;

        foreach ($this->getGroups() as $group) {
            $roles = array_merge($roles, $group->getRoles());
        }

        // we need to make sure to have at least one role
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function hasRole($role)
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    /**
     * @codeCoverageIgnore
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * @codeCoverageIgnore
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * @codeCoverageIgnore
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * @codeCoverageIgnore
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    public function removeRole($role)
    {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    /**
     * set username.
     *
     * @codeCoverageIgnore
     *
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * set email.
     *
     * @codeCoverageIgnore
     *
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * set enabled.
     *
     * @codeCoverageIgnore
     *
     * @param mixed $boolean
     */
    public function setEnabled($boolean)
    {
        $this->enabled = (bool) $boolean;

        return $this;
    }

    /**
     * set password.
     *
     * @codeCoverageIgnore
     *
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * set plainPassword.
     *
     * @codeCoverageIgnore
     *
     * @param mixed $password
     */
    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;

        return $this;
    }

    /**
     * set lastlogin.
     *
     * @codeCoverageIgnore
     */
    public function setLastLogin(DateTime $time = null)
    {
        $this->lastLogin = $time;

        return $this;
    }

    /**
     * set confirmationToken.
     *
     * @codeCoverageIgnore
     *
     * @param mixed $confirmationToken
     */
    public function setConfirmationToken($confirmationToken)
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    /**
     * set passwordRequestedAt.
     *
     * @codeCoverageIgnore
     */
    public function setPasswordRequestedAt(DateTime $date = null)
    {
        $this->passwordRequestedAt = $date;

        return $this;
    }

    /**
     * Gets the timestamp that the user requested a password reset.
     *
     * @codeCoverageIgnore
     *
     * @return null|\DateTime
     */
    public function getPasswordRequestedAt()
    {
        return $this->passwordRequestedAt;
    }

    public function isPasswordRequestNonExpired($ttl)
    {
        return $this->getPasswordRequestedAt() instanceof \DateTime
               && $this->getPasswordRequestedAt()->getTimestamp() + $ttl > time();
    }

    public function setRoles(array $roles)
    {
        $this->roles = [];

        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }

    /**
     * Get groups.
     *
     * @codeCoverageIgnore
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    public function getGroupNames()
    {
        $names = [];
        foreach ($this->getGroups() as $group) {
            $names[] = $group->getName();
        }

        return $names;
    }

    public function hasGroup($name)
    {
        return in_array($name, $this->getGroupNames());
    }

    public function addGroup(Groupe $group)
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
        }

        return $this;
    }

    public function removeGroup(Groupe $group)
    {
        if ($this->groups->contains($group)) {
            $this->groups->removeElement($group);
        }

        return $this;
    }

    /**
     * Get salt.
     *
     * @return null|string The salt
     *
     * @codeCoverageIgnore
     */
    public function getSalt()
    {
        return null;
    }
}
