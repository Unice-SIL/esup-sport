<?php

/*
 * Classe - ProfilUtilisateur:
 *
 * Permet de saisir les profils utilisateurs
 * Les profils donnent accès aux activités, aux nombres de places, aux prix,..
 * C"est une donnée essentielle pour la logique de contrôle du site.
*/

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="UcaBundle\Repository\ProfilUtilisateurRepository")
 * @Gedmo\Loggable
 * @UniqueEntity(fields="libelle", message="profilutilisateur.uniqueentity")
 */
class ProfilUtilisateur implements \UcaBundle\Entity\Interfaces\JsonSerializable
{
    use \UcaBundle\Entity\Traits\JsonSerializable;

    // region Propriétés

    /**
     * @Gedmo\Translatable
     * @Gedmo\Versioned
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="profilutilisateur.libelle.notblank")
     */
    protected $libelle;

    /** @ORM\Column(type="integer")
     * @Gedmo\Versioned
     * @Assert\GreaterThanOrEqual(value = 0)
     * @Assert\Regex(pattern="/^\d+$/", message="message.typeinvalide.entier")
     * @Assert\NotBlank(message="profilutilisateur.nbMaxinscriptions.notblank")
     */
    protected $nbMaxInscriptions;

    /** @ORM\OneToMany(targetEntity="MontantTarifProfilUtilisateur", mappedBy="profil", cascade={"persist", "remove"}) */
    protected $montants;

    /**
     * @ORM\OneToMany(targetEntity="FormatActiviteProfilUtilisateur", mappedBy="profilUtilisateur", cascade={"persist", "remove"}, fetch="LAZY")
     * @Assert\NotBlank(message="complement.profilsutilisateurs.notblank")
     */
    protected $formatsActivite;

    /**
     * @ORM\OneToMany(targetEntity="RessourceProfilUtilisateur", mappedBy="profilUtilisateur", cascade={"persist", "remove"}, fetch="LAZY")
     * @Assert\NotBlank(message="complement.profilsutilisateurs.notblank")
     */
    protected $ressources;

    /** @ORM\OneToMany(targetEntity="CreneauProfilUtilisateur", mappedBy="profilUtilisateur", cascade={"persist", "remove"}, fetch="EAGER", orphanRemoval=true) */
    protected $creneaux;

    /** @ORM\OneToMany(targetEntity="ReservabiliteProfilUtilisateur", mappedBy="profilUtilisateur", cascade={"persist", "remove"}, fetch="EAGER", orphanRemoval=true) */
    protected $reservabilites;

    /** @ORM\OneToMany(targetEntity="Utilisateur", mappedBy="profil") */
    protected $utilisateur;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\NotBlank(message="profilutilisateur.preinscription.notblank")
     */
    protected $preinscription;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    
    /**
     * @ORM\OneToMany(targetEntity="ProfilUtilisateur", mappedBy="parent")
     */
    protected $enfants;

    /**
     * @ORM\ManyToOne(targetEntity="ProfilUtilisateur", inversedBy="enfants")
     */
    protected $parent;

    // endregion

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->montants = new \Doctrine\Common\Collections\ArrayCollection();
        $this->formatsActivite = new \Doctrine\Common\Collections\ArrayCollection();
        $this->ressources = new \Doctrine\Common\Collections\ArrayCollection();
        $this->creneaux = new \Doctrine\Common\Collections\ArrayCollection();
        $this->reservabilites = new \Doctrine\Common\Collections\ArrayCollection();
        $this->utilisateur = new \Doctrine\Common\Collections\ArrayCollection();
        $this->enfants = new \Doctrine\Common\Collections\ArrayCollection();
    }

    // region Méthodes

    public function jsonSerializeProperties()
    {
        return ['libelle'];
    }

    // endregion

    /**
     * Set libelle.
     *
     * @param string $libelle
     *
     * @return ProfilUtilisateur
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
     * Set nbMaxInscriptions.
     *
     * @param int $nbMaxInscriptions
     *
     * @return ProfilUtilisateur
     */
    public function setNbMaxInscriptions($nbMaxInscriptions)
    {
        $this->nbMaxInscriptions = $nbMaxInscriptions;

        return $this;
    }

    /**
     * Get nbMaxInscriptions.
     *
     * @return int
     */
    public function getNbMaxInscriptions()
    {
        return $this->nbMaxInscriptions;
    }

    /**
     * Set preinscription.
     *
     * @param bool $preinscription
     *
     * @return ProfilUtilisateur
     */
    public function setPreinscription($preinscription)
    {
        $this->preinscription = $preinscription;

        return $this;
    }

    /**
     * Get preinscription.
     *
     * @return bool
     */
    public function getPreinscription()
    {
        return $this->preinscription;
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
     * Add montant.
     *
     * @param \UcaBundle\Entity\MontantTarifProfilUtilisateur $montant
     *
     * @return ProfilUtilisateur
     */
    public function addMontant(MontantTarifProfilUtilisateur $montant)
    {
        $this->montants[] = $montant;

        return $this;
    }

    /**
     * Remove montant.
     *
     * @param \UcaBundle\Entity\MontantTarifProfilUtilisateur $montant
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     */
    public function removeMontant(MontantTarifProfilUtilisateur $montant)
    {
        return $this->montants->removeElement($montant);
    }

    /**
     * Get montants.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMontants()
    {
        return $this->montants;
    }

    /**
     * Add creneaux.
     *
     * @param \UcaBundle\Entity\CreneauProfilUtilisateur $creneaux
     *
     * @return ProfilUtilisateur
     */
    public function addCreneaux(CreneauProfilUtilisateur $creneaux)
    {
        $this->creneaux[] = $creneaux;

        return $this;
    }

    /**
     * Remove creneaux.
     *
     * @param \UcaBundle\Entity\CreneauProfilUtilisateur $creneaux
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     */
    public function removeCreneaux(CreneauProfilUtilisateur $creneaux)
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
     * Add utilisateur.
     *
     * @param \UcaBundle\Entity\Utilisateur $utilisateur
     *
     * @return ProfilUtilisateur
     */
    public function addUtilisateur(Utilisateur $utilisateur)
    {
        $this->utilisateur[] = $utilisateur;

        return $this;
    }

    /**
     * Remove utilisateur.
     *
     * @param \UcaBundle\Entity\Utilisateur $utilisateur
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     */
    public function removeUtilisateur(Utilisateur $utilisateur)
    {
        return $this->utilisateur->removeElement($utilisateur);
    }

    /**
     * Get utilisateur.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUtilisateur()
    {
        return $this->utilisateur;
    }

    /**
     * Add formatActivite.
     *
     * @param \UcaBundle\Entity\FormatActiviteProfilUtilisateur $formatActivite
     *
     * @return ProfilUtilisateur
     */
    public function addFormatActivite(FormatActiviteProfilUtilisateur $formatActivite)
    {
        $this->formatsActivite[] = $formatActivite;

        return $this;
    }

    /**
     * Remove formatActivite.
     *
     * @param \UcaBundle\Entity\FormatActiviteProfilUtilisateur $formatActivite
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeFormatActivite(FormatActiviteProfilUtilisateur $formatActivite)
    {
        return $this->formatsActivite->removeElement($formatActivite);
    }

    /**
     * Get formatActivites.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFormatsActivite()
    {
        return $this->formatsActivite;
    }

    /**
     * Add ressource.
     *
     * @param \UcaBundle\Entity\RessourceProfilUtilisateur $ressource
     *
     * @return ProfilUtilisateur
     */
    public function addRessource(RessourceProfilUtilisateur $ressource)
    {
        $this->ressources[] = $ressource;

        return $this;
    }

    /**
     * Remove ressource.
     *
     * @param \UcaBundle\Entity\RessourceProfilUtilisateur $ressource
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeRessource(RessourceProfilUtilisateur $ressource)
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
     * Add reservabilites.
     *
     * @param \UcaBundle\Entity\ReservabiliteProfilUtilisateur $reservabilites
     *
     * @return ProfilUtilisateur
     */
    public function addReservabilite(ReservabiliteProfilUtilisateur $reservabilites)
    {
        $this->reservabilites[] = $reservabilites;

        return $this;
    }

    /**
     * Remove reservabilites.
     *
     * @param \UcaBundle\Entity\ReservabiliteProfilUtilisateur $reservabilites
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeReservabilite(ReservabiliteProfilUtilisateur $reservabilites)
    {
        return $this->reservabilites->removeElement($reservabilites);
    }

    /**
     * Get reservabilites.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReservabilites()
    {
        return $this->reservabilites;
    }

    /**
     * Add enfant.
     *
     * @param \UcaBundle\Entity\ProfilUtilisateur $enfant
     *
     * @return ProfilUtilisateur
     */
    public function addEnfant(ProfilUtilisateur $enfant)
    {
        $this->enfants[] = $enfant;

        return $this;
    }

    /**
     * Remove utilisateur.
     *
     * @param \UcaBundle\Entity\ProfilUtilisateur $utilisateur
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     */
    public function removeEnfant(ProfilUtilisateur $enfant)
    {
        return $this->enfants->removeElement($enfant);
    }

    /**
     * Get enfants.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEnfants()
    {
        return $this->enfants;
    }
    
    /**
     * Set parent.
     *
     * @param null|\UcaBundle\Entity\ProfilUtilisateur $parent
     *
     * @return Utilisateur
     */
    public function setParent(ProfilUtilisateur $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent.
     *
     * @return null|\UcaBundle\Entity\ProfilUtilisateur
     */
    public function getParent()
    {
        return $this->parent;
    }
}
