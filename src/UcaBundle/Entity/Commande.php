<?php

namespace UcaBundle\Entity;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use UcaBundle\Service\Common\Parametrage;

/**
 * @ORM\Entity(repositoryClass="UcaBundle\Repository\CommandeRepository")
 * @ORM\EntityListeners({"UcaBundle\Service\Listener\Entity\CommandeListener"})
 */
class Commande
{
    //region Propriétés
    /** @ORM\Column(type="boolean") */
    protected $cgvAcceptees = false;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** @ORM\Column(type="integer", nullable=true) */
    private $numeroCommande;

    /** @ORM\Column(type="integer", nullable=true) */
    private $numeroRecu;

    /** @ORM\ManyToOne(targetEntity="Utilisateur", inversedBy="commandes") */
    private $utilisateur;

    /** @ORM\OneToMany(targetEntity="CommandeDetail", mappedBy="commande", cascade={"persist"}, orphanRemoval=true) */
    private $commandeDetails;

    /** @ORM\OneToMany(targetEntity="CommandeDetail", mappedBy="avoir", cascade={"persist"}, orphanRemoval=true) */
    private $avoirCommandeDetails;

    /** @ORM\Column(type="decimal", options={"default"=0}) */
    private $creditUtilise = 0.0;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $datePanier;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $dateCommande;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $datePaiement;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $dateAnnulation;

    /** @ORM\Column(type="string", nullable=true) */
    private $hmac;

    /** @ORM\Column(type="string", nullable=true) */
    private $statut;
    // valeurs : panier, apayer, termine, annule, factureAnnulee, avoir

    /** @ORM\Column(type="string", nullable=true) */
    private $matricule;

    /** @ORM\Column(type="string", nullable=true) */
    private $prenom;

    /** @ORM\Column(type="string", nullable=true) */
    private $nom;

    /** @ORM\Column(type="string", nullable=true) */
    private $email;

    /** @ORM\Column(type="decimal", precision=10, scale=2) */
    private $montantTotal;

    /** @ORM\Column(type="decimal", precision=10, scale=2) */
    private $tva;

    /** @ORM\Column(type="string", nullable=true) */
    private $typePaiement;
    // valeurs : PAYBOX, BDS, NA => NA: Non applicable / Paiment d'un panier gratuir

    /** @ORM\Column(type="string", nullable=true) */
    private $moyenPaiement;
    // valeurs : cheque, cb, espece

    /** @ORM\ManyToOne(targetEntity="Utilisateur") */
    private $utilisateurEncaisseur;

    /** @ORM\Column(type="string", nullable=true) */
    private $prenomEncaisseur;

    /** @ORM\Column(type="string", nullable=true) */
    private $nomEncaisseur;

    /** @ORM\Column(type="string", nullable=true) */
    private $numeroCheque;
    //endregion

    //region Méthodes
    public function __construct($utilisateur)
    {
        $this->commandeDetails = new \Doctrine\Common\Collections\ArrayCollection();
        $this->avoircCommandeDetails = new \Doctrine\Common\Collections\ArrayCollection();
        $this->utilisateur = $utilisateur;
        $this->changeStatut('panier');
    }

    public function updateMontantTotal()
    {
        $this->montantTotal = 0;
        foreach ($this->commandeDetails->getIterator() as $cd) {
            $this->montantTotal += $cd->getMontant();
            $this->tva += $cd->getTva();
        }
    }

    public function sauvegardeInformations()
    {
        $this->matricule = $this->utilisateur->getMatricule();
        $this->prenom = $this->utilisateur->getPrenom();
        $this->nom = $this->utilisateur->getNom();
        foreach ($this->commandeDetails->getIterator() as $cd) {
            $cd->sauvegardeInformations();
        }
    }

    public function changeStatut($statut, $options = [])
    {
        $this->statut = $statut;
        if (isset($options['typePaiement'])) {
            $this->typePaiement = $options['typePaiement'];
        }
        if (isset($options['moyenPaiement'])) {
            $this->moyenPaiement = $options['moyenPaiement'];
        }
        $this->updateMontantTotal();

        if ('panier' == $statut) {
            $this->datePanier = new \DateTime();
            $this->sauvegardeInformations();
        } elseif ('apayer' == $statut) {
            $this->dateCommande = new \DateTime();
            $this->sauvegardeInformations();
        } elseif ('termine' == $statut) {
            if (empty($this->dateCommande)) {
                $this->dateCommande = new \DateTime();
            }
            if (0 == $this->montantTotal) {
                $this->sauvegardeInformations();
            }
            $this->datePaiement = new \DateTime();
            $this->traitementPostPaiement();
        } elseif ('annule' == $statut) {
            $this->dateAnnulation = new \DateTime();
            $this->traitementPostAnnulation($options);
        } elseif ('avoir' == $statut) {
            $this->dateAnnulation = new \DateTime();
            $this->traitementPostGenerationAvoir();
        } elseif ('factureAnnulee' == $statut) {
        }
    }

    public function traitementPostGenerationAvoir()
    {
        foreach ($this->avoirCommandeDetails->getIterator() as $cmdDetails) {
            $cmdDetails->traitementPostGenerationAvoir();
        }
    }

    public function traitementPostPaiement()
    {
        foreach ($this->commandeDetails->getIterator() as $cd) {
            $cd->traitementPostPaiement();
        }
    }

    public function traitementPostAnnulation($options)
    {
        foreach ($this->commandeDetails->getIterator() as $cd) {
            $cd->traitementPostAnnulation($options);
        }
    }

    public function setHmac($hmac)
    {
        $this->hmac = $hmac;
        foreach ($this->commandeDetails->getIterator() as $cd) {
            $cd->setHmac($hmac);
        }
    }

    public function getHmac()
    {
        return 'PAYBOX' == $this->typePaiement && 'CB' == $this->moyenPaiement ? $this->hmac : null;
    }

    public function getTimeout()
    {
        if ('panier' == $this->getStatut()) {
            $dateValeur = $this->getDatePanier();
            $dateLimite = Parametrage::getDateDebutPanierLimite();
        } elseif ('apayer' == $this->getStatut() && 'PAYBOX' == $this->getTypePaiement()) {
            $dateValeur = $this->getDateCommande();
            $dateLimite = Parametrage::getDateDebutCbLimite();
        } elseif ('apayer' == $this->getStatut() && 'BDS' == $this->getTypePaiement()) {
            $dateValeur = $this->getDateCommande();
            $dateLimite = Parametrage::getDateDebutBdsLimite();
        } else {
            return null;
        }
        if ($dateValeur < $dateLimite) {
            return null;
        }

        return $dateValeur->diff($dateLimite);
    }

    public function hasAvoir()
    {
        return !$this->getAvoirCommandeDetails()->isEmpty();
    }

    public function eligibleAvoir()
    {
        foreach ($this->getCommandeDetails() as $cmdDetails) {
            if ($cmdDetails->eligibleAvoir()) {
                return $this;
            }
        }

        return false;
    }

    public function getCommmandeDetailsByAvoir($refAvoir)
    {
        $criteria = Criteria::create()->andWhere(Criteria::expr()->eq('referenceAvoir', $refAvoir));

        return $this->getCommandeDetails()->matching($criteria);
    }

    public function getTotalAvoir($refAvoir)
    {
        $totalAvoir = 0;
        foreach ($this->getCommmandeDetailsByAvoir($refAvoir)->getIterator() as $cmdDetails) {
            $totalAvoir += $cmdDetails->getMontant();
        }

        return $totalAvoir;
    }

    public function getTvaAvoir($refAvoir)
    {
        $totalTva = 0;
        foreach ($this->getCommmandeDetailsByAvoir($refAvoir)->getIterator() as $cmdDetails) {
            $totalTva += $cmdDetails->getTva();
        }

        return $totalTva;
    }

    //endregion

    /**
     * Set cgvAcceptees.
     *
     * @param bool $cgvAcceptees
     *
     * @return Commande
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
     */
    public function getCgvAcceptees()
    {
        return $this->cgvAcceptees;
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
     * Set numeroCommande.
     *
     * @param int|null $numeroCommande
     *
     * @return Commande
     */
    public function setNumeroCommande($numeroCommande = null)
    {
        $this->numeroCommande = $numeroCommande;

        return $this;
    }

    /**
     * Get numeroCommande.
     *
     * @return int|null
     */
    public function getNumeroCommande()
    {
        return $this->numeroCommande;
    }

    /**
     * Set numeroRecu.
     *
     * @param int|null $numeroRecu
     *
     * @return Commande
     */
    public function setNumeroRecu($numeroRecu = null)
    {
        $this->numeroRecu = $numeroRecu;

        return $this;
    }

    /**
     * Get numeroRecu.
     *
     * @return int|null
     */
    public function getNumeroRecu()
    {
        return $this->numeroRecu;
    }

    /**
     * Set creditUtilise.
     *
     * @param string $creditUtilise
     *
     * @return Commande
     */
    public function setCreditUtilise($creditUtilise)
    {
        $this->creditUtilise = $creditUtilise;

        return $this;
    }

    /**
     * Get creditUtilise.
     *
     * @return string
     */
    public function getCreditUtilise()
    {
        return $this->creditUtilise;
    }

    /**
     * Set datePanier.
     *
     * @param \DateTime|null $datePanier
     *
     * @return Commande
     */
    public function setDatePanier($datePanier = null)
    {
        $this->datePanier = $datePanier;

        return $this;
    }

    /**
     * Get datePanier.
     *
     * @return \DateTime|null
     */
    public function getDatePanier()
    {
        return $this->datePanier;
    }

    /**
     * Set dateCommande.
     *
     * @param \DateTime|null $dateCommande
     *
     * @return Commande
     */
    public function setDateCommande($dateCommande = null)
    {
        $this->dateCommande = $dateCommande;

        return $this;
    }

    /**
     * Get dateCommande.
     *
     * @return \DateTime|null
     */
    public function getDateCommande()
    {
        return $this->dateCommande;
    }

    /**
     * Set datePaiement.
     *
     * @param \DateTime|null $datePaiement
     *
     * @return Commande
     */
    public function setDatePaiement($datePaiement = null)
    {
        $this->datePaiement = $datePaiement;

        return $this;
    }

    /**
     * Get datePaiement.
     *
     * @return \DateTime|null
     */
    public function getDatePaiement()
    {
        return $this->datePaiement;
    }

    /**
     * Set dateAnnulation.
     *
     * @param \DateTime|null $dateAnnulation
     *
     * @return Commande
     */
    public function setDateAnnulation($dateAnnulation = null)
    {
        $this->dateAnnulation = $dateAnnulation;

        return $this;
    }

    /**
     * Get dateAnnulation.
     *
     * @return \DateTime|null
     */
    public function getDateAnnulation()
    {
        return $this->dateAnnulation;
    }

    /**
     * Set statut.
     *
     * @param string|null $statut
     *
     * @return Commande
     */
    public function setStatut($statut = null)
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * Get statut.
     *
     * @return string|null
     */
    public function getStatut()
    {
        return $this->statut;
    }

    /**
     * Set matricule.
     *
     * @param string|null $matricule
     *
     * @return Commande
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
     * Set prenom.
     *
     * @param string|null $prenom
     *
     * @return Commande
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
     * @return Commande
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
     * Set email.
     *
     * @param string|null $email
     *
     * @return Commande
     */
    public function setEmail($email = null)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set montantTotal.
     *
     * @param string $montantTotal
     *
     * @return Commande
     */
    public function setMontantTotal($montantTotal)
    {
        $this->montantTotal = $montantTotal;

        return $this;
    }

    /**
     * Get montantTotal.
     *
     * @return string
     */
    public function getMontantTotal()
    {
        return $this->montantTotal;
    }

    /**
     * Set tva.
     *
     * @param string $tva
     *
     * @return Commande
     */
    public function setTva($tva)
    {
        $this->tva = $tva;

        return $this;
    }

    /**
     * Get tva.
     *
     * @return string
     */
    public function getTva()
    {
        return $this->tva;
    }

    /**
     * Set typePaiement.
     *
     * @param string|null $typePaiement
     *
     * @return Commande
     */
    public function setTypePaiement($typePaiement = null)
    {
        $this->typePaiement = $typePaiement;

        return $this;
    }

    /**
     * Get typePaiement.
     *
     * @return string|null
     */
    public function getTypePaiement()
    {
        return $this->typePaiement;
    }

    /**
     * Set moyenPaiement.
     *
     * @param string|null $moyenPaiement
     *
     * @return Commande
     */
    public function setMoyenPaiement($moyenPaiement = null)
    {
        $this->moyenPaiement = $moyenPaiement;

        return $this;
    }

    /**
     * Get moyenPaiement.
     *
     * @return string|null
     */
    public function getMoyenPaiement()
    {
        return $this->moyenPaiement;
    }

    /**
     * Set prenomEncaisseur.
     *
     * @param string|null $prenomEncaisseur
     *
     * @return Commande
     */
    public function setPrenomEncaisseur($prenomEncaisseur = null)
    {
        $this->prenomEncaisseur = $prenomEncaisseur;

        return $this;
    }

    /**
     * Get prenomEncaisseur.
     *
     * @return string|null
     */
    public function getPrenomEncaisseur()
    {
        return $this->prenomEncaisseur;
    }

    /**
     * Set nomEncaisseur.
     *
     * @param string|null $nomEncaisseur
     *
     * @return Commande
     */
    public function setNomEncaisseur($nomEncaisseur = null)
    {
        $this->nomEncaisseur = $nomEncaisseur;

        return $this;
    }

    /**
     * Get nomEncaisseur.
     *
     * @return string|null
     */
    public function getNomEncaisseur()
    {
        return $this->nomEncaisseur;
    }

    /**
     * Set numeroCheque.
     *
     * @param string|null $numeroCheque
     *
     * @return Commande
     */
    public function setNumeroCheque($numeroCheque = null)
    {
        $this->numeroCheque = $numeroCheque;

        return $this;
    }

    /**
     * Get numeroCheque.
     *
     * @return string|null
     */
    public function getNumeroCheque()
    {
        return $this->numeroCheque;
    }

    /**
     * Set utilisateur.
     *
     * @param \UcaBundle\Entity\Utilisateur|null $utilisateur
     *
     * @return Commande
     */
    public function setUtilisateur(\UcaBundle\Entity\Utilisateur $utilisateur = null)
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
     * Add commandeDetail.
     *
     * @param \UcaBundle\Entity\CommandeDetail $commandeDetail
     *
     * @return Commande
     */
    public function addCommandeDetail(\UcaBundle\Entity\CommandeDetail $commandeDetail)
    {
        $this->commandeDetails[] = $commandeDetail;

        return $this;
    }

    /**
     * Remove commandeDetail.
     *
     * @param \UcaBundle\Entity\CommandeDetail $commandeDetail
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeCommandeDetail(\UcaBundle\Entity\CommandeDetail $commandeDetail)
    {
        return $this->commandeDetails->removeElement($commandeDetail);
    }

    /**
     * Get commandeDetails.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCommandeDetails()
    {
        return $this->commandeDetails;
    }

    /**
     * Add avoirCommandeDetail.
     *
     * @param \UcaBundle\Entity\CommandeDetail $avoirCommandeDetail
     *
     * @return Commande
     */
    public function addAvoirCommandeDetail(\UcaBundle\Entity\CommandeDetail $avoirCommandeDetail)
    {
        $this->avoirCommandeDetails[] = $avoirCommandeDetail;

        return $this;
    }

    /**
     * Remove avoirCommandeDetail.
     *
     * @param \UcaBundle\Entity\CommandeDetail $avoirCommandeDetail
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeAvoirCommandeDetail(\UcaBundle\Entity\CommandeDetail $avoirCommandeDetail)
    {
        return $this->avoirCommandeDetails->removeElement($avoirCommandeDetail);
    }

    /**
     * Get avoirCommandeDetails.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAvoirCommandeDetails()
    {
        return $this->avoirCommandeDetails;
    }

    /**
     * Set utilisateurEncaisseur.
     *
     * @param \UcaBundle\Entity\Utilisateur|null $utilisateurEncaisseur
     *
     * @return Commande
     */
    public function setUtilisateurEncaisseur(\UcaBundle\Entity\Utilisateur $utilisateurEncaisseur = null)
    {
        $this->utilisateurEncaisseur = $utilisateurEncaisseur;

        return $this;
    }

    /**
     * Get utilisateurEncaisseur.
     *
     * @return \UcaBundle\Entity\Utilisateur|null
     */
    public function getUtilisateurEncaisseur()
    {
        return $this->utilisateurEncaisseur;
    }
}
