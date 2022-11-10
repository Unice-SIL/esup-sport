<?php

/*
 * Classe - Commande:
 *
 * Une commande est générer lorsque q'un utilisateur valide son panier
 * Une commande correspond une facture unique (une facture est une commande)
 * Les inscriptions associée sont des détails des cette commnande
 * Il est possible de faire un avoir sur un ou plusieurs détail(s) de la commande.
*/

namespace App\Entity\Uca;

use App\Service\Common\Parametrage;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommandeRepository")
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

    /** @ORM\Column(type="boolean", nullable=true) */
    private $inscriptionAvecPartenaires;
    // champ qui va nous permettre de filtrer les commandes qui concerne des inscriptions avec partenaires qui ont un timeout différents

    /** @ORM\Column(type="decimal", precision=10, scale=2, nullable=true) */
    private $montantPaybox;

    //endregion

    //region Méthodes
    public function __construct($utilisateur)
    {
        $this->commandeDetails = new \Doctrine\Common\Collections\ArrayCollection();
        $this->avoirCommandeDetails = new \Doctrine\Common\Collections\ArrayCollection();
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

        if (null === $this->tva) {
            $this->tva = 0.00;
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
            $this->traitementPostPaiement(isset($options['em']) ? $options['em'] : null);
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

    public function traitementPostPaiement($em = null)
    {
        foreach ($this->commandeDetails->getIterator() as $cd) {
            $cd->traitementPostPaiement($em);
        }
    }

    public function traitementPostAnnulation($options)
    {
        foreach ($this->commandeDetails->getIterator() as $cd) {
            $cd->traitementPostAnnulation($options);
        }

        // Si on annule une commande et que des crédits ont été utilisés, on annule l'utilisation du crédit
        if ($this->creditUtilise > 0 && $options['em']) {
            $credit = $options['em']->getRepository(UtilisateurCreditHistorique::class)->findOneBy(['commandeAssociee' => $this->id, 'montant' => $this->creditUtilise, 'typeOperation' => 'debit']);
            if ($credit) {
                // @codeCoverageIgnoreStart
                $credit->setStatut('annule');
                // @codeCoverageIgnoreEnd
            }
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     */
    public function getCgvAcceptees()
    {
        return $this->cgvAcceptees;
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
     * Set numeroCommande.
     *
     * @param null|int $numeroCommande
     *
     * @return Commande
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @return string
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     */
    public function getNumeroCheque()
    {
        return $this->numeroCheque;
    }

    /**
     * Set utilisateur.
     *
     * @return Commande
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
     * Add commandeDetail.
     *
     * @return Commande
     * @codeCoverageIgnore
     */
    public function addCommandeDetail(CommandeDetail $commandeDetail)
    {
        $this->commandeDetails[] = $commandeDetail;

        return $this;
    }

    /**
     * Remove commandeDetail.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeCommandeDetail(CommandeDetail $commandeDetail)
    {
        return $this->commandeDetails->removeElement($commandeDetail);
    }

    /**
     * Get commandeDetails.
     *
     * @return \Doctrine\Common\Collections\Collection
     * @codeCoverageIgnore
     */
    public function getCommandeDetails()
    {
        return $this->commandeDetails;
    }

    /**
     * Add avoirCommandeDetail.
     *
     * @return Commande
     * @codeCoverageIgnore
     */
    public function addAvoirCommandeDetail(CommandeDetail $avoirCommandeDetail)
    {
        $this->avoirCommandeDetails[] = $avoirCommandeDetail;

        return $this;
    }

    /**
     * Remove avoirCommandeDetail.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeAvoirCommandeDetail(CommandeDetail $avoirCommandeDetail)
    {
        return $this->avoirCommandeDetails->removeElement($avoirCommandeDetail);
    }

    /**
     * Get avoirCommandeDetails.
     *
     * @return \Doctrine\Common\Collections\Collection
     * @codeCoverageIgnore
     */
    public function getAvoirCommandeDetails()
    {
        return $this->avoirCommandeDetails;
    }

    /**
     * Set utilisateurEncaisseur.
     *
     * @return Commande
     * @codeCoverageIgnore
     */
    public function setUtilisateurEncaisseur(Utilisateur $utilisateurEncaisseur = null)
    {
        $this->utilisateurEncaisseur = $utilisateurEncaisseur;

        return $this;
    }

    /**
     * Get utilisateurEncaisseur.
     *
     * @return null|Utilisateur
     * @codeCoverageIgnore
     */
    public function getUtilisateurEncaisseur()
    {
        return $this->utilisateurEncaisseur;
    }

    /**
     * Get inscriptionAvecPartenaires.
     *
     * @codeCoverageIgnore
     */
    public function getInscriptionAvecPartenaires(): ?bool
    {
        return $this->inscriptionAvecPartenaires;
    }

    /**
     * Set inscriptionAvecPartenaires.
     *
     * @codeCoverageIgnore
     */
    public function setInscriptionAvecPartenaires(bool $inscriptionAvecPartenaires): Commande
    {
        $this->inscriptionAvecPartenaires = $inscriptionAvecPartenaires;

        return $this;
    }

    /**
     * Set montantPaybox.
     *
     * @param string $montantPaybox
     *
     * @return Commande
     * @codeCoverageIgnore
     */
    public function setMontantPaybox($montantPaybox)
    {
        $this->montantPaybox = $montantPaybox;

        return $this;
    }

    /**
     * Get montantPaybox.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getMontantPaybox()
    {
        return $this->montantPaybox;
    }

    public function getMontantAPayer()
    {
        return (float) $this->montantTotal - (float) $this->creditUtilise;
    }
}