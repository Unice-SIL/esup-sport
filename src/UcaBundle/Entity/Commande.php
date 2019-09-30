<?php

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use UcaBundle\Service\Common\Parametrage;

/**
 * @ORM\Entity(repositoryClass="UcaBundle\Repository\CommandeRepository")
 * @ORM\EntityListeners({"UcaBundle\Service\Listener\Entity\CommandeListener"})
 */
class Commande
{
    #region Propriétés
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
    /* valeurs : panier, apayer, termine, annule */

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
    /* valeurs : PAYBOX, BDS, NA => NA: Non applicable / Paiment d'un panier gratuir */

    /** @ORM\Column(type="string", nullable=true) */
    private $moyenPaiement;
    /* valeurs : cheque, cb, espece */

    /** @ORM\Column(type="boolean") */
    protected $cgvAcceptees = false;
    #endregion

    #region Méthodes
    public function __construct($utilisateur)
    {
        $this->commandeDetails = new \Doctrine\Common\Collections\ArrayCollection();
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
        if (isset($options['typePaiement']))
            $this->typePaiement = $options['typePaiement'];
        if (isset($options['moyenPaiement']))
            $this->moyenPaiement = $options['moyenPaiement'];
        $this->updateMontantTotal();

        if ($statut == 'panier') {
            $this->datePanier = new \DateTime();
            $this->sauvegardeInformations();
        } elseif ($statut == 'apayer') {
            $this->dateCommande = new \DateTime();
            $this->sauvegardeInformations();
        } elseif ($statut == 'termine') {
            if (empty($this->dateCommande)) {
                $this->dateCommande = new \DateTime();
            }
            $this->datePaiement = new \DateTime();
            $this->traitementPostPaiement();
        } elseif ($statut == 'annule') {
            $this->dateAnnulation = new \DateTime();
            $this->traitementPostAnnulation($options);
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
        return $this->typePaiement == 'PAYBOX' && $this->moyenPaiement == 'CB' ? $this->hmac : null;
    }

    function getTimeout()
    {
        if ($this->getStatut() == 'panier') {
            $dateValeur = $this->getDatePanier();
            $dateLimite = Parametrage::getDateDebutPanierLimite();
        } elseif ($this->getStatut() == 'apayer' && $this->getTypePaiement() == 'PAYBOX') {
            $dateValeur = $this->getDateCommande();
            $dateLimite = Parametrage::getDateDebutCbLimite();
        } elseif ($this->getStatut() == 'apayer' && $this->getTypePaiement() == 'BDS') {
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

    #endregion


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
     * Set cgvAcceptees.
     *
     * @param bool $cgvAcceptees
     *
     * @return Utilisateur
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
}
