<?php

namespace UcaBundle\Entity;

use FOS\UserBundle\Model\User as FOSUser;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="UcaBundle\Repository\UtilisateurRepository")
 * 
 */
class Utilisateur extends FOSUser implements \UcaBundle\Entity\Interfaces\JsonSerializable
{
    use \UcaBundle\Entity\Traits\JsonSerializable;

    #region Propriétés
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /** @ORM\Column(type="text", nullable=true) */
    private $description;

    /* @Assert\NotNull(message="utilisateur.mail.notnull") */
    protected $email;

    /** @ORM\ManyToMany(targetEntity="UcaBundle\Entity\Groupe", inversedBy="utilisateurs") */
    protected $groups;

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

    /** @ORM\Column(type="string", nullable=true ,length=5) */
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

    /** @ORM\OneToMany(targetEntity="Inscription", mappedBy="utilisateur") */
    protected $inscriptions;

    /** @ORM\OneToMany(targetEntity="Reservation", mappedBy="utilisateur") */
    protected $reservations;

    /** @ORM\ManyToMany(targetEntity="TypeAutorisation", cascade={"persist"}) */
    protected $autorisations;

    /** @ORM\ManyToOne(targetEntity="ProfilUtilisateur", inversedBy="utilisateur", cascade={"persist"}) 
     * @Assert\NotNull(message="utilisateur.UserProfile.notnull") */
    protected $profil;

    /** @ORM\OneToOne(targetEntity="Panier", mappedBy="utilisateur") */
    protected $panier;

    /** @ORM\OneToMany(targetEntity="Commande", mappedBy="utilisateur") */
    protected $commandes;

    /** @ORM\ManyToMany(targetEntity="FormatActivite", mappedBy="encadrants") */
    protected $formatsActivite;

    /** @ORM\ManyToMany(targetEntity="Creneau", mappedBy="encadrants") */
    protected $creneaux;

    /** @ORM\Column(type="boolean", options={"default":"0"}) */
    protected $shibboleth = false;
    #endregion

    #region Méthodes

    public function __construct()
    {
        parent::__construct();
        $this->inscriptions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->autorisations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->reservations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->commandes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public static function getRandomPassword()
    {
        // $password = str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789");
        // $password = substr($password, mt_rand(0, 30), 5);
        $password = base64_encode(random_bytes(10));
        return $password;
    }

    public function jsonSerializeProperties()
    {
        return ['prenom', 'nom'];
    }

    public function getPanier()
    {
        if (empty($this->panier))
            $this->panier = new Panier($this);
        return $this->panier;
    }

    #endregion



    /**
     * Set description.
     *
     * @param string|null $description
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
     * @param string|null $matricule
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
     * @param string|null $numeroNfc
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
     * @param string|null $prenom
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
     * @param string|null $nom
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
     * @param string|null $sexe
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
     * @param string|null $adresse
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
     * @param string|null $codePostal
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
     * @param string|null $ville
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
     * @param \DateTime|null $dateNaissance
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
     * @param string|null $telephone
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
     * Add inscription.
     *
     * @param \UcaBundle\Entity\Inscription $inscription
     *
     * @return Utilisateur
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
     * Add reservation.
     *
     * @param \UcaBundle\Entity\Reservation $reservation
     *
     * @return Utilisateur
     */
    public function addReservation(\UcaBundle\Entity\Reservation $reservation)
    {
        $this->reservations[] = $reservation;

        return $this;
    }

    /**
     * Remove reservation.
     *
     * @param \UcaBundle\Entity\Reservation $reservation
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeReservation(\UcaBundle\Entity\Reservation $reservation)
    {
        return $this->reservations->removeElement($reservation);
    }

    /**
     * Get reservations.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReservations()
    {
        return $this->reservations;
    }

    /**
     * Add autorisation.
     *
     * @param \UcaBundle\Entity\TypeAutorisation $autorisation
     *
     * @return Utilisateur
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
     * Set profil.
     *
     * @param \UcaBundle\Entity\ProfilUtilisateur|null $profil
     *
     * @return Utilisateur
     */
    public function setProfil(\UcaBundle\Entity\ProfilUtilisateur $profil = null)
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
     * Set panier.
     *
     * @param \UcaBundle\Entity\Panier|null $panier
     *
     * @return Utilisateur
     */
    public function setPanier(\UcaBundle\Entity\Panier $panier = null)
    {
        $this->panier = $panier;

        return $this;
    }

    /**
     * Add commande.
     *
     * @param \UcaBundle\Entity\Commande $commande
     *
     * @return Utilisateur
     */
    public function addCommande(\UcaBundle\Entity\Commande $commande)
    {
        $this->commandes[] = $commande;

        return $this;
    }

    /**
     * Remove commande.
     *
     * @param \UcaBundle\Entity\Commande $commande
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeCommande(\UcaBundle\Entity\Commande $commande)
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
    public function addFormatsActivite(\UcaBundle\Entity\FormatActivite $formatsActivite)
    {
        $this->formatsActivite[] = $formatsActivite;

        return $this;
    }

    /**
     * Remove formatsActivite.
     *
     * @param \UcaBundle\Entity\FormatActivite $formatsActivite
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeFormatsActivite(\UcaBundle\Entity\FormatActivite $formatsActivite)
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
    public function addCreneaux(\UcaBundle\Entity\Creneau $creneaux)
    {
        $this->creneaux[] = $creneaux;

        return $this;
    }

    /**
     * Remove creneaux.
     *
     * @param \UcaBundle\Entity\Creneau $creneaux
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeCreneaux(\UcaBundle\Entity\Creneau $creneaux)
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
}
