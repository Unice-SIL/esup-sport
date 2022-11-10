<?php

/*
 * Classe - ProfilUtilisateur:
 *
 * Permet de saisir les profils utilisateurs
 * Les profils donnent accès aux activités, aux nombres de places, aux prix,..
 * C"est une donnée essentielle pour la logique de contrôle du site.
*/

namespace App\Entity\Uca;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProfilUtilisateurRepository")
 * @Gedmo\Loggable
 * @UniqueEntity(fields="libelle", message="profilutilisateur.uniqueentity")
 */
class ProfilUtilisateur implements \App\Entity\Uca\Interfaces\JsonSerializable
{
    use \App\Entity\Uca\Traits\JsonSerializable;

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

    /** @ORM\OneToMany(targetEntity="MontantTarifProfilUtilisateur", mappedBy="profil", cascade={"persist", "remove"}, fetch="LAZY") */
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

    /** @ORM\OneToMany(targetEntity="CreneauProfilUtilisateur", mappedBy="profilUtilisateur", cascade={"persist", "remove"}, fetch="LAZY", orphanRemoval=true) */
    protected $creneaux;

    /** @ORM\OneToMany(targetEntity="ReservabiliteProfilUtilisateur", mappedBy="profilUtilisateur", cascade={"persist", "remove"}, fetch="LAZY", orphanRemoval=true) */
    protected $reservabilites;

    /** @ORM\OneToMany(targetEntity="Utilisateur", mappedBy="profil", fetch="LAZY") */
    protected $utilisateur;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\NotBlank(message="profilutilisateur.preinscription.notblank")
     */
    protected $preinscription;

    /**
     * @ORM\OneToMany(targetEntity="ProfilUtilisateur", mappedBy="parent", fetch="LAZY")
     */
    protected $enfants;

    /**
     * @ORM\ManyToOne(targetEntity="ProfilUtilisateur", inversedBy="enfants", fetch="LAZY")
     */
    protected $parent;

    /** @ORM\Column(type="integer", options={"default":0})
     * @Gedmo\Versioned
     * @Assert\GreaterThanOrEqual(value = 0)
     * @Assert\Regex(pattern="/^\d+$/", message="message.typeinvalide.entier")
     * @Assert\NotBlank(message="profilutilisateur.nbMaxinscriptions.notblank")
     */
    protected $nbMaxInscriptionsRessource;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    // endregion

    /**
     * Constructor.
     *
     * @codeCoverageIgnore
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
     * Set nbMaxInscriptions.
     *
     * @param int $nbMaxInscriptions
     *
     * @return ProfilUtilisateur
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     */
    public function getPreinscription()
    {
        return $this->preinscription;
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
     * Add montant.
     *
     * @return ProfilUtilisateur
     * @codeCoverageIgnore
     */
    public function addMontant(MontantTarifProfilUtilisateur $montant)
    {
        $this->montants[] = $montant;

        return $this;
    }

    /**
     * Remove montant.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeMontant(MontantTarifProfilUtilisateur $montant)
    {
        return $this->montants->removeElement($montant);
    }

    /**
     * Get montants.
     *
     * @return \Doctrine\Common\Collections\Collection
     * @codeCoverageIgnore
     */
    public function getMontants()
    {
        return $this->montants;
    }

    /**
     * Add creneaux.
     *
     * @return ProfilUtilisateur
     * @codeCoverageIgnore
     */
    public function addCreneaux(CreneauProfilUtilisateur $creneaux)
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
    public function removeCreneaux(CreneauProfilUtilisateur $creneaux)
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
     * Add utilisateur.
     *
     * @return ProfilUtilisateur
     * @codeCoverageIgnore
     */
    public function addUtilisateur(Utilisateur $utilisateur)
    {
        $this->utilisateur[] = $utilisateur;

        return $this;
    }

    /**
     * Remove utilisateur.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeUtilisateur(Utilisateur $utilisateur)
    {
        return $this->utilisateur->removeElement($utilisateur);
    }

    /**
     * Get utilisateur.
     *
     * @return \Doctrine\Common\Collections\Collection
     * @codeCoverageIgnore
     */
    public function getUtilisateur()
    {
        return $this->utilisateur;
    }

    /**
     * Add formatActivite.
     *
     * @return ProfilUtilisateur
     * @codeCoverageIgnore
     */
    public function addFormatActivite(FormatActiviteProfilUtilisateur $formatActivite)
    {
        $this->formatsActivite[] = $formatActivite;

        return $this;
    }

    /**
     * Remove formatActivite.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeFormatActivite(FormatActiviteProfilUtilisateur $formatActivite)
    {
        return $this->formatsActivite->removeElement($formatActivite);
    }

    /**
     * Get formatActivites.
     *
     * @return \Doctrine\Common\Collections\Collection
     * @codeCoverageIgnore
     */
    public function getFormatsActivite()
    {
        return $this->formatsActivite;
    }

    /**
     * Add ressource.
     *
     * @param \App\Entity\Uca\RessourceProfilUtilisateur $ressource
     *
     * @return ProfilUtilisateur
     * @codeCoverageIgnore
     */
    public function addRessource(RessourceProfilUtilisateur $ressource)
    {
        $this->ressources[] = $ressource;

        return $this;
    }

    /**
     * Remove ressource.
     *
     * @param \App\Entity\Uca\RessourceProfilUtilisateur $ressource
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeRessource(RessourceProfilUtilisateur $ressource)
    {
        return $this->ressources->removeElement($ressource);
    }

    /**
     * Get ressources.
     *
     * @return \Doctrine\Common\Collections\Collection
     * @codeCoverageIgnore
     */
    public function getRessources()
    {
        return $this->ressources;
    }

    /**
     * Add reservabilites.
     *
     * @param \App\Entity\Uca\ReservabiliteProfilUtilisateur $reservabilites
     *
     * @return ProfilUtilisateur
     * @codeCoverageIgnore
     */
    public function addReservabilite(ReservabiliteProfilUtilisateur $reservabilites)
    {
        $this->reservabilites[] = $reservabilites;

        return $this;
    }

    /**
     * Remove reservabilites.
     *
     * @param \App\Entity\Uca\ReservabiliteProfilUtilisateur $reservabilites
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeReservabilite(ReservabiliteProfilUtilisateur $reservabilites)
    {
        return $this->reservabilites->removeElement($reservabilites);
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
     * Add enfant.
     *
     * @param \App\Entity\Uca\ProfilUtilisateur $enfant
     *
     * @return ProfilUtilisateur
     * @codeCoverageIgnore
     */
    public function addEnfant(ProfilUtilisateur $enfant)
    {
        $this->enfants[] = $enfant;

        return $this;
    }

    /**
     * Remove utilisateur.
     *
     * @param \App\Entity\Uca\ProfilUtilisateur $utilisateur
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeEnfant(ProfilUtilisateur $enfant)
    {
        return $this->enfants->removeElement($enfant);
    }

    /**
     * Get enfants.
     *
     * @return \Doctrine\Common\Collections\Collection
     * @codeCoverageIgnore
     */
    public function getEnfants()
    {
        return $this->enfants;
    }

    /**
     * Set parent.
     *
     * @param null|\App\Entity\Uca\ProfilUtilisateur $parent
     *
     * @return Utilisateur
     * @codeCoverageIgnore
     */
    public function setParent(ProfilUtilisateur $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent.
     *
     * @return null|\App\Entity\Uca\ProfilUtilisateur
     * @codeCoverageIgnore
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set nbMaxInscriptionsRessource.
     *
     * @param int $nbMaxInscriptionsRessource
     *
     * @return ProfilUtilisateur
     * @codeCoverageIgnore
     */
    public function setNbMaxInscriptionsRessource($nbMaxInscriptionsRessource)
    {
        $this->nbMaxInscriptionsRessource = $nbMaxInscriptionsRessource;

        return $this;
    }

    /**
     * Get nbMaxInscriptionsRessource.
     *
     * @return int
     * @codeCoverageIgnore
     */
    public function getNbMaxInscriptionsRessource()
    {
        return $this->nbMaxInscriptionsRessource;
    }
}