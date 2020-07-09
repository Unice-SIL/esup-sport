<?php

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="UcaBundle\Repository\AutorisationRepository")
 * @Vich\Uploadable
 */
class Autorisation
{
    //region Propriétés
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** @ORM\ManyToOne(targetEntity="Inscription", inversedBy="autorisations") */
    private $inscription;

    /** @ORM\ManyToOne(targetEntity="TypeAutorisation") */
    private $typeAutorisation;

    /** @ORM\ManyToOne(targetEntity="Utilisateur") */
    private $utilisateur;

    /** @ORM\Column(type="boolean", nullable=true) */
    private $caseACocher = false;

    /** @ORM\Column(type="boolean", nullable=true) */
    private $valideParEncadrant = false;

    /** @ORM\Column(type="boolean", nullable=true) */
    private $valideParGestionnaire = false;

    /** @ORM\Column(type="string", nullable=true) */
    private $justificatif;

    /** @Vich\UploadableField(mapping="justificatif", fileNameProperty="justificatif")
     * @Assert\Expression("this.getJustificatif() !== null || this.getJustificatifFile() !== null", message="formatactivite.image.notnull")
     */
    private $justificatifFile;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $updatedAt;

    /** @ORM\Column(type="decimal", precision=10, scale=2) */
    private $montant;

    /** @ORM\Column(type="string") */
    private $statut; // invalide, soumis, valide

    /** @ORM\Column(type="datetime") */
    private $date;
    //endregion

    //region Méthodes

    public function __construct($inscription, $typeAutorisation)
    {
        $this->inscription = $inscription;
        $this->typeAutorisation = $typeAutorisation;
        $this->utilisateur = $inscription->getUtilisateur();
        $this->setMontant($inscription->getItem()->getArticleMontant($this->getUtilisateur()));
        $this->setDate(new \DateTime());
        $this->updateStatut();
    }

    public function getCodeComportement()
    {
        return $this->getTypeAutorisation()->getComportement()->getCodeComportement();
    }

    public function getInformationsComplementaires()
    {
        return $this->getTypeAutorisation()->getArticleDescription();
    }

    public function setJustificatifFile(File $justificatif = null)
    {
        $this->justificatifFile = $justificatif;
        if ($justificatif) {
            $this->updatedAt = new \DateTime('now');
        }
    }

    public function getJustificatifFile()
    {
        return $this->justificatifFile;
    }

    public function updateStatut()
    {
        $isValide = false;

        if ('case' == $this->getCodeComportement()) {
            $isValide = $this->caseACocher;
        } elseif ('carte' == $this->getCodeComportement()) {
            $isValide = $this->getUtilisateur()->hasAutorisation($this->getTypeAutorisation());
        } elseif ('cotisation' == $this->getCodeComportement()) {
            $isValide = $this->getUtilisateur()->hasAutorisation($this->getTypeAutorisation());
        } elseif ('justificatif' == $this->getCodeComportement()) {
            $isValide = $this->getUtilisateur()->hasAutorisation($this->getTypeAutorisation()) || !empty($this->justificatif) || !empty($this->justificatifFile);
        } elseif ('validationencadrant' == $this->getCodeComportement()) {
            $isValide = $this->valideParEncadrant;
        } elseif ('validationgestionnaire' == $this->getCodeComportement()) {
            $isValide = $this->valideParGestionnaire;
        }

        $this->statut = $isValide ? 'valide' : 'invalide';
    }

    //endregion

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
     * Set caseACocher.
     *
     * @param bool $caseACocher
     *
     * @return Autorisation
     */
    public function setCaseACocher($caseACocher)
    {
        $this->caseACocher = $caseACocher;

        return $this;
    }

    /**
     * Get caseACocher.
     *
     * @return bool
     */
    public function getCaseACocher()
    {
        return $this->caseACocher;
    }

    /**
     * Set valideParEncadrant.
     *
     * @param bool $valideParEncadrant
     *
     * @return Autorisation
     */
    public function setValideParEncadrant($valideParEncadrant)
    {
        $this->valideParEncadrant = $valideParEncadrant;

        return $this;
    }

    /**
     * Get valideParEncadrant.
     *
     * @return bool
     */
    public function getValideParEncadrant()
    {
        return $this->valideParEncadrant;
    }

    /**
     * Set justificatif.
     *
     * @param null|string $justificatif
     *
     * @return Autorisation
     */
    public function setJustificatif($justificatif = null)
    {
        $this->justificatif = $justificatif;

        return $this;
    }

    /**
     * Get justificatif.
     *
     * @return string|null
     */
    public function getJustificatif()
    {
        return $this->justificatif;
    }

    /**
     * Set updatedAt.
     *
     * @param null|\DateTime $updatedAt
     *
     * @return Autorisation
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
     * Set montant.
     *
     * @param string $montant
     *
     * @return Autorisation
     */
    public function setMontant($montant)
    {
        $this->montant = $montant;

        return $this;
    }

    /**
     * Get montant.
     *
     * @return string
     */
    public function getMontant()
    {
        return $this->montant;
    }

    /**
     * Set statut.
     *
     * @param string $statut
     *
     * @return Autorisation
     */
    public function setStatut($statut)
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * Get statut.
     *
     * @return string
     */
    public function getStatut()
    {
        return $this->statut;
    }

    /**
     * Set date.
     *
     * @param \DateTime $date
     *
     * @return Autorisation
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date.
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set inscription.
     *
     * @param null|\UcaBundle\Entity\Inscription $inscription
     *
     * @return Autorisation
     */
    public function setInscription(Inscription $inscription = null)
    {
        $this->inscription = $inscription;

        return $this;
    }

    /**
     * Get inscription.
     *
     * @return \UcaBundle\Entity\Inscription|null
     */
    public function getInscription()
    {
        return $this->inscription;
    }

    /**
     * Set typeAutorisation.
     *
     * @param null|\UcaBundle\Entity\TypeAutorisation $typeAutorisation
     *
     * @return Autorisation
     */
    public function setTypeAutorisation(TypeAutorisation $typeAutorisation = null)
    {
        $this->typeAutorisation = $typeAutorisation;

        return $this;
    }

    /**
     * Get typeAutorisation.
     *
     * @return \UcaBundle\Entity\TypeAutorisation|null
     */
    public function getTypeAutorisation()
    {
        return $this->typeAutorisation;
    }

    /**
     * Set utilisateur.
     *
     * @param null|\UcaBundle\Entity\Utilisateur $utilisateur
     *
     * @return Autorisation
     */
    public function setUtilisateur(Utilisateur $utilisateur = null)
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    /**
     * Get utilisateur.
     *
     * @return \UcaBundle\Entity\Utilisateur|null
     */
    public function getUtilisateur()
    {
        return $this->utilisateur;
    }

    /**
     * Set valideParGestionnaire.
     *
     * @param null|bool $valideParGestionnaire
     *
     * @return Autorisation
     */
    public function setValideParGestionnaire($valideParGestionnaire = null)
    {
        $this->valideParGestionnaire = $valideParGestionnaire;

        return $this;
    }

    /**
     * Get valideParGestionnaire.
     *
     * @return bool|null
     */
    public function getValideParGestionnaire()
    {
        return $this->valideParGestionnaire;
    }
}
