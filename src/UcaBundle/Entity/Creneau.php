<?php

namespace UcaBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @Gedmo\Loggable
 */
class Creneau implements \UcaBundle\Entity\Interfaces\JsonSerializable, \UcaBundle\Entity\Interfaces\Article, \UcaBundle\Entity\Interfaces\Tarifable
{
    use \UcaBundle\Entity\Traits\JsonSerializable;

    #region Propriétés
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** 
     * @Gedmo\Versioned
     * @ORM\ManyToOne(targetEntity="Lieu") 
     */
    private $lieu;

    /** @ORM\ManyToOne(targetEntity="FormatAvecCreneau", inversedBy="creneaux") */
    private $formatActivite;

    /** @ORM\OneToMany(targetEntity="Inscription", mappedBy="creneau") */
    private $inscriptions;

    /**
     * @ORM\OneToOne(targetEntity="DhtmlxSerie", mappedBy="creneau")
     */
    private $serie;

    #endregion

    #region Propriétés communes FormatActivite

    /** @ORM\ManyToMany(targetEntity="ProfilUtilisateur", inversedBy="creneaux", fetch="EAGER")
     * @Assert\NotBlank(message="complement.profilsutilisateurs.notblank") */
    private $profilsUtilisateurs;


    /** @ORM\ManyToMany(targetEntity="NiveauSportif", inversedBy="creneaux", fetch="EAGER")
     * @Assert\NotBlank(message="complement.niveauSportif.notblank") */
    private $niveauxSportifs;


    /** 
     * @Gedmo\Versioned
     * @ORM\ManyToOne(targetEntity="Tarif", inversedBy="creneaux", fetch="EAGER") 
     * @Assert\Expression("!this.getEstPayant() || this.getTarif()", message="complement.tarif.notblank")
     */
    private $tarif;

    /** 
     * @ORM\ManyToMany(targetEntity="Utilisateur", inversedBy="creneaux") 
     * @Assert\Expression("!this.getEstEncadre() || this.getEncadrants()", message="complement.encadrant.notblank") */
    private $encadrants;

    /** 
     * @Gedmo\Versioned
     * @ORM\Column(type="integer") 
     * @Assert\NotBlank(message="complement.capacite.notblank") 
     */
    private $capacite;

    #endregion

    #region Méthodes

    public function jsonSerializeProperties()
    {
        return ['capacite', 'tarif', 'profilsUtilisateurs', 'encadrants', 'niveauxSportifs'];
    }

    public function getArticleLibelle()
    {
        return $this->getFormatActivite()->getLibelle();
    }

    public function getArticleTarif()
    {
        return $this->tarif;
    }

    public function getArticleDescription()
    {
        return $this->getSerie()->getEvenements()->first()->getDescription();
    }

    public function isFull()
    {
        return  count($this->inscriptions) < $this->capacite;
    }

    public function hasProfil($profil)
    {
        if ($this->profilsUtilisateurs->contains($profil)) {
            return true;
        }

        return false;
    }

    public function dateInscriptionValid()
    {
        $now = new \DateTime("now");
        return $now > $this->formatActivite->getDateDebutInscription() && $now < $this->formatActivite->getDateFinInscription();
    }

    public function getMontant($user)
    {
        if (!empty($this->tarif)) {
            return $this->getTarif()->getUserMontant($user->getProfil()->getId())->getMontant();
        } else {
            return 0;
        }
    }
    
    public function getAutorisations()
    {
        return $this->formatActivite->getAutorisations();
    }

    #endregion


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->inscriptions = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set capacite.
     *
     * @param int $capacite
     *
     * @return Creneau
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
     * Set lieu.
     *
     * @param \UcaBundle\Entity\Lieu|null $lieu
     *
     * @return Creneau
     */
    public function setLieu(\UcaBundle\Entity\Lieu $lieu = null)
    {
        $this->lieu = $lieu;

        return $this;
    }

    /**
     * Get lieu.
     *
     * @return \UcaBundle\Entity\Lieu|null
     */
    public function getLieu()
    {
        return $this->lieu;
    }

    /**
     * Set formatActivite.
     *
     * @param \UcaBundle\Entity\FormatAvecCreneau|null $formatActivite
     *
     * @return Creneau
     */
    public function setFormatActivite(\UcaBundle\Entity\FormatAvecCreneau $formatActivite = null)
    {
        $this->formatActivite = $formatActivite;

        return $this;
    }

    /**
     * Get formatActivite.
     *
     * @return \UcaBundle\Entity\FormatAvecCreneau|null
     */
    public function getFormatActivite()
    {
        return $this->formatActivite;
    }

    /**
     * Add inscription.
     *
     * @param \UcaBundle\Entity\Inscription $inscription
     *
     * @return Creneau
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
     * Set serie.
     *
     * @param \UcaBundle\Entity\DhtmlxSerie|null $serie
     *
     * @return Creneau
     */
    public function setSerie(\UcaBundle\Entity\DhtmlxSerie $serie = null)
    {
        $this->serie = $serie;

        return $this;
    }

    /**
     * Get serie.
     *
     * @return \UcaBundle\Entity\DhtmlxSerie|null
     */
    public function getSerie()
    {
        return $this->serie;
    }

    /**
     * Add profilsUtilisateur.
     *
     * @param \UcaBundle\Entity\ProfilUtilisateur $profilsUtilisateur
     *
     * @return Creneau
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
     * Add niveauxSportifs.
     *
     * @param \UcaBundle\Entity\NiveauSportif $niveauxSportifs
     *
     * @return Creneau
     */
    public function addNiveauSportif(\UcaBundle\Entity\NiveauSportif $niveauxSportifs)
    {
        $this->niveauxSportifs[] = $niveauxSportifs;

        return $this;
    }


    /**
     * Remove niveauxSportifs.
     *
     * @param \UcaBundle\Entity\NiveauSportif $niveauxSportifs
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeNiveauSportif(\UcaBundle\Entity\NiveauSportif $niveauxSportifs)
    {
        return $this->niveauxSportifs->removeElement($niveauxSportifs);
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
     * Set tarif.
     *
     * @param \UcaBundle\Entity\Tarif|null $tarif
     *
     * @return Creneau
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
     * @return Creneau
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
