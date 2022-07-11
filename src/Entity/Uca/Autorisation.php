<?php

/*
 * Classe - Autorisation:
 *
 * Elle permet de gérer les autorisations d'accès aux différentes activités
 * Une autorisation existe entre une inscription et un type d'autorisation
 * Une autorisation exite entre un utilisateur et un type d'autorisation
 * Le type d'autorisation par exemple : cotisation sportive, carte d'accès, certificat médiécal,...
*/

namespace App\Entity\Uca;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AutorisationRepository")
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

    /**
     * @codeCoverageIgnore
     */
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     */
    public function setJustificatif($justificatif = null)
    {
        $this->justificatif = $justificatif;

        return $this;
    }

    /**
     * Get justificatif.
     *
     * @return null|string
     * @codeCoverageIgnore
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
     * Set montant.
     *
     * @param string $montant
     *
     * @return Autorisation
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @return string
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set inscription.
     *
     * @return Autorisation
     * @codeCoverageIgnore
     */
    public function setInscription(Inscription $inscription = null)
    {
        $this->inscription = $inscription;

        return $this;
    }

    /**
     * Get inscription.
     *
     * @return null|Inscription
     * @codeCoverageIgnore
     */
    public function getInscription()
    {
        return $this->inscription;
    }

    /**
     * Set typeAutorisation.
     *
     * @return Autorisation
     * @codeCoverageIgnore
     */
    public function setTypeAutorisation(TypeAutorisation $typeAutorisation = null)
    {
        $this->typeAutorisation = $typeAutorisation;

        return $this;
    }

    /**
     * Get typeAutorisation.
     *
     * @return null|TypeAutorisation
     * @codeCoverageIgnore
     */
    public function getTypeAutorisation()
    {
        return $this->typeAutorisation;
    }

    /**
     * Set utilisateur.
     *
     * @return Autorisation
     * @codeCoverageIgnore
     */
    public function setUtilisateur(Utilisateur $utilisateur = null)
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    /**
     * Get utilisateur.
     *
     * @return null|Utilisateur
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     */
    public function setValideParGestionnaire($valideParGestionnaire = null)
    {
        $this->valideParGestionnaire = $valideParGestionnaire;

        return $this;
    }

    /**
     * Get valideParGestionnaire.
     *
     * @return null|bool
     * @codeCoverageIgnore
     */
    public function getValideParGestionnaire()
    {
        return $this->valideParGestionnaire;
    }
}