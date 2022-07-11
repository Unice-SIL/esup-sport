<?php

/*
 * Classe - Tarif:
 *
 * Permet de fixer un tarifs (une grille) de prix pour un élement (format, creneau, autorisation,...).
 * Un tafif est définit par profil.
*/

namespace App\Entity\Uca;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TarifRepository")
 * @ORM\HasLifecycleCallbacks()
 * @Gedmo\Loggable
 * @UniqueEntity(fields="libelle", message="tarif.uniqueentity")
 */
class Tarif implements \App\Entity\Uca\Interfaces\JsonSerializable
{
    use \App\Entity\Uca\Traits\JsonSerializable;

    /** @ORM\OneToMany(targetEntity="MontantTarifProfilUtilisateur", mappedBy="tarif", cascade={"persist", "remove"}, fetch="LAZY")
     * @Assert\Valid()
     */
    protected $montants;

    /** @ORM\Column(type="decimal", precision=3, scale=1,options={"default":0})
     * @Assert\Expression("this.getPourcentageTVA() < 100 && this.getPourcentageTVA() >= 0", message="tarif.tva.invalid")
     */
    protected $pourcentageTVA;

    //region Propriétés
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

    /** @ORM\OneToMany(targetEntity="TypeAutorisation", mappedBy="tarif", fetch="LAZY") */
    private $typesAutorisation;

    /** @ORM\OneToMany(targetEntity="FormatActivite", mappedBy="tarif", fetch="LAZY") */
    private $formatsActivite;

    /** @ORM\OneToMany(targetEntity="Creneau", mappedBy="tarif", fetch="LAZY") */
    private $creneaux;

    /** @ORM\OneToMany(targetEntity="Ressource",mappedBy="tarif", fetch="LAZY") */
    private $ressources;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="text")
     */
    private $modificationMontants;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="boolean")
     */
    private $tva;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="text", nullable=true)
     */
    private $tvaNonApplicable;

    //endregion

    /**
     * Constructor.
     *
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        $this->montants = new \Doctrine\Common\Collections\ArrayCollection();
        $this->typesAutorisation = new \Doctrine\Common\Collections\ArrayCollection();
        $this->formatsActivite = new \Doctrine\Common\Collections\ArrayCollection();
        $this->creneaux = new \Doctrine\Common\Collections\ArrayCollection();
        $this->ressources = new \Doctrine\Common\Collections\ArrayCollection();
    }

    //endregion

    //region Méthodes

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

    public function getMontantUtilisateur($utilisateur)
    {
        $criteria = Criteria::create()->andWhere(Criteria::expr()->eq('profil', $utilisateur->getProfil()));
        $resultat = $this->montants->matching($criteria);
        if (!$resultat->isEmpty()) {
            return $resultat->first()->getMontant();
        }

        return -1;
    }

    public function getTvaUtilisateur($utilisateur)
    {
        $montant = $this->getMontantUtilisateur($utilisateur);
        $coefTva = $this->getPourcentageTva() / 100;

        return $montant * $coefTva / (1 + $coefTva);
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
     * Set libelle.
     *
     * @param string $libelle
     *
     * @return Tarif
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
     * Set modificationMontants.
     *
     * @param string $modificationMontants
     *
     * @return Tarif
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     */
    public function getModificationMontants()
    {
        return $this->modificationMontants;
    }

    /**
     * Add montant.
     *
     * @return Tarif
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
     * Add typesAutorisation.
     *
     * @return Tarif
     * @codeCoverageIgnore
     */
    public function addTypesAutorisation(TypeAutorisation $typesAutorisation)
    {
        $this->typesAutorisation[] = $typesAutorisation;

        return $this;
    }

    /**
     * Remove typesAutorisation.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeTypesAutorisation(TypeAutorisation $typesAutorisation)
    {
        return $this->typesAutorisation->removeElement($typesAutorisation);
    }

    /**
     * Get typesAutorisation.
     *
     * @return \Doctrine\Common\Collections\Collection
     * @codeCoverageIgnore
     */
    public function getTypesAutorisation()
    {
        return $this->typesAutorisation;
    }

    /**
     * Add formatsActivite.
     *
     * @return Tarif
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
     * @return Tarif
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
     * Add ressource.
     *
     * @return Tarif
     * @codeCoverageIgnore
     */
    public function addRessource(Ressource $ressource)
    {
        $this->ressources[] = $ressource;

        return $this;
    }

    /**
     * Remove ressource.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeRessource(Ressource $ressource)
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
     * Set pourcentageTVA.
     *
     * @param string $pourcentageTVA
     *
     * @return Tarif
     * @codeCoverageIgnore
     */
    public function setPourcentageTVA($pourcentageTVA)
    {
        $this->pourcentageTVA = $pourcentageTVA;

        return $this;
    }

    /**
     * Get pourcentageTVA.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getPourcentageTVA()
    {
        return $this->pourcentageTVA;
    }

    /**
     * Set tva.
     *
     * @param bool $tva
     *
     * @return Tarif
     * @codeCoverageIgnore
     */
    public function setTva($tva)
    {
        $this->tva = $tva;

        return $this;
    }

    /**
     * Get tva.
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function getTva()
    {
        return $this->tva;
    }

    /**
     * Set tvaNonApplicable.
     *
     * @param string $tvaNonApplicable
     *
     * @return Tarif
     * @codeCoverageIgnore
     */
    public function setTvaNonApplicable($tvaNonApplicable)
    {
        $this->tvaNonApplicable = $tvaNonApplicable;

        return $this;
    }

    /**
     * Get tvaNonApplicable.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getTvaNonApplicable()
    {
        return $this->tvaNonApplicable;
    }
}