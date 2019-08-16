<?php

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="UcaBundle\Repository\TarifRepository")
 * @ORM\HasLifecycleCallbacks()
 * @Gedmo\Loggable
 * @UniqueEntity(fields="libelle", message="tarif.uniqueentity")
 */
class Tarif implements \UcaBundle\Entity\Interfaces\JsonSerializable
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
     * @Gedmo\Translatable
     * @Gedmo\Versioned
     * @ORM\Column(type="string") 
     * @Assert\NotBlank(message="tarif.libelle.notblank")
     */
    private $libelle;

    /** @ORM\OneToMany(targetEntity="MontantTarifProfilUtilisateur", mappedBy="tarif", cascade={"persist", "remove"}, fetch="EAGER") */
    protected $montants;

    /** @ORM\OneToMany(targetEntity="TypeAutorisation", mappedBy="tarif") */
    private $typesAutorisation;

    /** @ORM\OneToMany(targetEntity="FormatActivite", mappedBy="tarif") */
    private $formatsActivite;

    /** @ORM\OneToMany(targetEntity="Creneau", mappedBy="tarif") */
    private $creneaux;

    /** @ORM\OneToMany(targetEntity="Ressource",mappedBy="tarif") */
    private $ressources;

    /** 
     * @Gedmo\Versioned
     * @ORM\Column(type="text") 
     */
    private $modificationMontants;

    #endregion


    #region Méthodes

    public function __toString()
    {
        return $this->libelle;
    }

    public function jsonSerializeProperties()
    {
        return ['libelle'];
    }

    /** @ORM\PostLoad */
    public function onLoad()
    {
        // On reinitialise ce champ à chaque chargement car il sert uniquement à tracer les modifications.
        $this->modificationMontants = '';
    }

    public function getUserMontant($profilUtilisateurId){
        foreach($this->montants as $key => $montant){
            if($montant->getProfil()->getId() == $profilUtilisateurId){
                return $montant;
            }
        }
        return null;
    }

    #endregion


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->montants = new \Doctrine\Common\Collections\ArrayCollection();
        $this->typesAutorisation = new \Doctrine\Common\Collections\ArrayCollection();
        $this->formatsActivite = new \Doctrine\Common\Collections\ArrayCollection();
        $this->creneaux = new \Doctrine\Common\Collections\ArrayCollection();
        $this->articles = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set libelle.
     *
     * @param string $libelle
     *
     * @return Tarif
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
     * Set modificationMontants.
     *
     * @param string $modificationMontants
     *
     * @return Tarif
     */
    public function setModificationMontants($modificationMontants)
    {
        $this->modificationMontants = $modificationMontants;

        return $this;
    }

    /**
     * Get modificationMontants.
     *
     * @return string
     */
    public function getModificationMontants()
    {
        return $this->modificationMontants;
    }

    /**
     * Add montant.
     *
     * @param \UcaBundle\Entity\MontantTarifProfilUtilisateur $montant
     *
     * @return Tarif
     */
    public function addMontant(\UcaBundle\Entity\MontantTarifProfilUtilisateur $montant)
    {
        $this->montants[] = $montant;

        return $this;
    }

    /**
     * Remove montant.
     *
     * @param \UcaBundle\Entity\MontantTarifProfilUtilisateur $montant
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeMontant(\UcaBundle\Entity\MontantTarifProfilUtilisateur $montant)
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
     * Add typesAutorisation.
     *
     * @param \UcaBundle\Entity\TypeAutorisation $typesAutorisation
     *
     * @return Tarif
     */
    public function addTypesAutorisation(\UcaBundle\Entity\TypeAutorisation $typesAutorisation)
    {
        $this->typesAutorisation[] = $typesAutorisation;

        return $this;
    }

    /**
     * Remove typesAutorisation.
     *
     * @param \UcaBundle\Entity\TypeAutorisation $typesAutorisation
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeTypesAutorisation(\UcaBundle\Entity\TypeAutorisation $typesAutorisation)
    {
        return $this->typesAutorisation->removeElement($typesAutorisation);
    }

    /**
     * Get typesAutorisation.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTypesAutorisation()
    {
        return $this->typesAutorisation;
    }

    /**
     * Add formatsActivite.
     *
     * @param \UcaBundle\Entity\FormatActivite $formatsActivite
     *
     * @return Tarif
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
     * @return Tarif
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

    /**
     * Add article.
     *
     * @param \UcaBundle\Entity\Article $article
     *
     * @return Tarif
     */
    public function addArticle(\UcaBundle\Entity\Article $article)
    {
        $this->articles[] = $article;

        return $this;
    }

    /**
     * Remove article.
     *
     * @param \UcaBundle\Entity\Article $article
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeArticle(\UcaBundle\Entity\Article $article)
    {
        return $this->articles->removeElement($article);
    }

    /**
     * Get articles.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getArticles()
    {
        return $this->articles;
    }

    /**
     * Add ressource.
     *
     * @param \UcaBundle\Entity\Ressource $ressource
     *
     * @return Tarif
     */
    public function addRessource(\UcaBundle\Entity\Ressource $ressource)
    {
        $this->ressources[] = $ressource;

        return $this;
    }

    /**
     * Remove ressource.
     *
     * @param \UcaBundle\Entity\Ressource $ressource
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeRessource(\UcaBundle\Entity\Ressource $ressource)
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
}
