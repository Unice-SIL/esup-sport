<?php

/*
 * Classe - UtilisateurCreditHistoriqueRepository
 *
 * Elle contient les opérations de crédit/débit du solde des utilisateurs (en fonction des avoirss).
*/

namespace App\Entity\Uca;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UtilisateurCreditHistoriqueRepository")
 * @Gedmo\Loggable
 */
class UtilisateurCreditHistorique
{
    // region properties
    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @ORM\Column(type="datetime") */
    private $date;

    /** @ORM\ManyToOne(targetEntity="Utilisateur", inversedBy="credit")
     * @Assert\NotNull(message="utilisateur.UserProfile.notnull")
     */
    private $utilisateur;

    /** @ORM\Column(type="decimal") */
    private $montant;

    /** @ORM\Column(type="integer", nullable=true) */
    private $avoir;

    /** @ORM\Column(type="string", nullable=false) */
    private $typeOperation;
    // Valeur : credit / debit

    /** @ORM\Column(type="string", nullable=false) */
    private $statut;
    // Valeurs : annule, valide

    /** @ORM\Column(type="string", nullable=false)*/
    private $operation;
    // Valeur : Génération d'avoir, Règlement d'une facture, Report d'avoir, Ajout manuel de crédit

    /** @ORM\Column(type="string", nullable=true) */
    private $commandeAssociee;

    // end region

    // regiion methods
    public function __construct(Utilisateur $utilisateur, $montant, $avoir = null, $typeOpoeration, $operation, $commande = null)
    {
        $dateNow = new \DateTime();
        $this->date = $dateNow;
        $this->utilisateur = $utilisateur;
        $this->montant = $montant;
        $this->avoir = $avoir;
        $this->typeOperation = $typeOpoeration;
        $this->operation = $operation;
        $this->statut = 'valide';
        $this->commandeAssociee = $commande;
    }

    /**
     * Get montant.
     *
     * @return string
     */
    public function getMontant()
    {
        if ('credit' === $this->typeOperation) {
            return $this->montant;
        }

        return -($this->montant);
    }

    // endregion

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
     * Set date.
     *
     * @param \DateTime $date
     *
     * @return UtilisateurCreditHistorique
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
     * Set montant.
     *
     * @param string $montant
     *
     * @return UtilisateurCreditHistorique
     * @codeCoverageIgnore
     */
    public function setMontant($montant)
    {
        $this->montant = $montant;

        return $this;
    }

    /**
     * Set avoir.
     *
     * @param null|int $avoir
     *
     * @return UtilisateurCreditHistorique
     * @codeCoverageIgnore
     */
    public function setAvoir($avoir = null)
    {
        $this->avoir = $avoir;

        return $this;
    }

    /**
     * Get avoir.
     *
     * @return null|int
     * @codeCoverageIgnore
     */
    public function getAvoir()
    {
        return $this->avoir;
    }

    /**
     * Set typeOperation.
     *
     * @param string $typeOperation
     *
     * @return UtilisateurCreditHistorique
     * @codeCoverageIgnore
     */
    public function setTypeOperation($typeOperation)
    {
        $this->typeOperation = $typeOperation;

        return $this;
    }

    /**
     * Get typeOperation.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getTypeOperation()
    {
        return $this->typeOperation;
    }

    /**
     * Set statut.
     *
     * @param string $statut
     *
     * @return UtilisateurCreditHistorique
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
     * Set operation.
     *
     * @param string $operation
     *
     * @return UtilisateurCreditHistorique
     * @codeCoverageIgnore
     */
    public function setOperation($operation)
    {
        $this->operation = $operation;

        return $this;
    }

    /**
     * Get operation.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * Set commandeAssociee.
     *
     * @param null|string $commandeAssociee
     *
     * @return UtilisateurCreditHistorique
     * @codeCoverageIgnore
     */
    public function setCommandeAssociee($commandeAssociee = null)
    {
        $this->commandeAssociee = $commandeAssociee;

        return $this;
    }

    /**
     * Get commandeAssociee.
     *
     * @return null|string
     * @codeCoverageIgnore
     */
    public function getCommandeAssociee()
    {
        return $this->commandeAssociee;
    }

    /**
     * Set utilisateur.
     *
     * @return UtilisateurCreditHistorique
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
}