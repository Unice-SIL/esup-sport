<?php

/*
 * Classe - Commande:
 *
 * Une commande est générer lorsque q'un utilisateur valide son panier
 * Une commande correspond une facture unique (une facture est une commande)
 * Les inscriptions associée sont des détails des cette commnande
 * Il est possible de faire un avoir sur un ou plusieurs détail(s) de la commande.
*/

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

    public function hasFormatAchatCarte()
    {
        $tabCartes = [];
        foreach ($this->getCommandeDetails() as $cmdDetails) {
            if ('autorisation' == $cmdDetails->getType() && 'Achat de Carte' == $cmdDetails->getTypeAutorisation()->getComportementLibelle()) {
                $tabCartes[] = $cmdDetails;
            } elseif ('inscription' == $cmdDetails->getType() && $cmdDetails->getFormatActivite() instanceof FormatAchatCarte) {
                // Cas ou un format == une autorisation
            }
        }

        return (!(empty($tabCartes))) ? $tabCartes : false;
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

    public function getDateAvoir()
    {
        $avoirs = $this->getAvoirCommandeDetails();

        return !empty($avoirs) ? $avoirs->first()->getDateAvoir() : false;
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
     * @param null|int $numeroCommande
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
     * @return null|int
     */
    public function getNumeroCommande()
    {
        return $this->numeroCommande;
    }

    /**
     * Set numeroRecu.
     *
     * @param null|int $numeroRecu
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
     * @return null|int
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
     * @param null|\DateTime $datePanier
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
     * @return null|\DateTime
     */
    public function getDatePanier()
    {
        return $this->datePanier;
    }

    /**
     * Set dateCommande.
     *
     * @param null|\DateTime $dateCommande
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
     * @return null|\DateTime
     */
    public function getDateCommande()
    {
        return $this->dateCommande;
    }

    /**
     * Set datePaiement.
     *
     * @param null|\DateTime $datePaiement
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
     * @return null|\DateTime
     */
    public function getDatePaiement()
    {
        return $this->datePaiement;
    }

    /**
     * Set dateAnnulation.
     *
     * @param null|\DateTime $dateAnnulation
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
     * @return null|\DateTime
     */
    public function getDateAnnulation()
    {
        return $this->dateAnnulation;
    }

    /**
     * Set statut.
     *
     * @param null|string $statut
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
     * @return null|string
     */
    public function getStatut()
    {
        return $this->statut;
    }

    /**
     * Set matricule.
     *
     * @param null|string $matricule
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
     * @return null|string
     */
    public function getMatricule()
    {
        return $this->matricule;
    }

    /**
     * Set prenom.
     *
     * @param null|string $prenom
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
     * @return null|string
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * Set nom.
     *
     * @param null|string $nom
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
     * @return null|string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set email.
     *
     * @param null|string $email
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
     * @return null|string
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
     * @param null|string $typePaiement
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
     * @return null|string
     */
    public function getTypePaiement()
    {
        return $this->typePaiement;
    }

    /**
     * Set moyenPaiement.
     *
     * @param null|string $moyenPaiement
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
     * @return null|string
     */
    public function getMoyenPaiement()
    {
        return $this->moyenPaiement;
    }

    /**
     * Set prenomEncaisseur.
     *
     * @param null|string $prenomEncaisseur
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
     * @return null|string
     */
    public function getPrenomEncaisseur()
    {
        return $this->prenomEncaisseur;
    }

    /**
     * Set nomEncaisseur.
     *
     * @param null|string $nomEncaisseur
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
     * @return null|string
     */
    public function getNomEncaisseur()
    {
        return $this->nomEncaisseur;
    }

    /**
     * Set numeroCheque.
     *
     * @param null|string $numeroCheque
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
     * @return null|string
     */
    public function getNumeroCheque()
    {
        return $this->numeroCheque;
    }

    /**
     * Set utilisateur.
     *
     * @param null|\UcaBundle\Entity\Utilisateur $utilisateur
     *
     * @return Commande
     */
    public function setUtilisateur(Utilisateur $utilisateur = null)
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    /**
     * Get utilisateur.
     *
     * @return null|\UcaBundle\Entity\Utilisateur
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
    public function addCommandeDetail(CommandeDetail $commandeDetail)
    {
        $this->commandeDetails[] = $commandeDetail;

        return $this;
    }

    /**
     * Remove commandeDetail.
     *
     * @param \UcaBundle\Entity\CommandeDetail $commandeDetail
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeCommandeDetail(CommandeDetail $commandeDetail)
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
    public function addAvoirCommandeDetail(CommandeDetail $avoirCommandeDetail)
    {
        $this->avoirCommandeDetails[] = $avoirCommandeDetail;

        return $this;
    }

    /**
     * Remove avoirCommandeDetail.
     *
     * @param \UcaBundle\Entity\CommandeDetail $avoirCommandeDetail
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeAvoirCommandeDetail(CommandeDetail $avoirCommandeDetail)
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
     * @param null|\UcaBundle\Entity\Utilisateur $utilisateurEncaisseur
     *
     * @return Commande
     */
    public function setUtilisateurEncaisseur(Utilisateur $utilisateurEncaisseur = null)
    {
        $this->utilisateurEncaisseur = $utilisateurEncaisseur;

        return $this;
    }

    /**
     * Get utilisateurEncaisseur.
     *
     * @return null|\UcaBundle\Entity\Utilisateur
     */
    public function getUtilisateurEncaisseur()
    {
        return $this->utilisateurEncaisseur;
    }
}
