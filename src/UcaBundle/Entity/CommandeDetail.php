<?php

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

/**
 * @ORM\Entity(repositoryClass="UcaBundle\Repository\CommandeDetailRepository")
 */
class CommandeDetail
{
    #region Propriétés
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** @ORM\Column(type="string", nullable=true) */
    private $type;

    /** @ORM\ManyToOne(targetEntity="Commande", inversedBy="commandeDetails") */
    protected $commande;

    /** @ORM\Column(type="string", nullable=true) */
    private $hmac;

    /** @ORM\OneToOne(targetEntity="Inscription") */
    protected $inscription;

    /** @ORM\ManyToMany(targetEntity="CommandeDetail", inversedBy="ligneCommandeLiees", cascade={"persist"}) */
    protected $ligneCommandeReferences;

    /** @ORM\ManyToMany(targetEntity="CommandeDetail", mappedBy="ligneCommandeReferences") */
    protected $ligneCommandeLiees;

    /** @ORM\ManyToOne(targetEntity="FormatActivite") */
    private $formatActivite;

    /** @ORM\ManyToOne(targetEntity="Creneau") */
    private $creneau;

    /** @ORM\ManyToOne(targetEntity="Reservabilite") */
    private $reservabilite;

    /** @ORM\ManyToOne(targetEntity="TypeAutorisation") */
    private $typeAutorisation;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $dateAjoutPanier;

    /** @ORM\Column(type="decimal", precision=10, scale=2) */
    private $montant;

    /** @ORM\Column(type="decimal", precision=10, scale=2) */
    private $tva;

    /** @ORM\Column(type="string", nullable=true) */
    private $jourCreneau;

    /** @ORM\Column(type="string", nullable=true) */
    private $horaireCreneau;

    /** @ORM\Column(type="string", nullable=true) */
    private $libelle;

    /** @ORM\Column(type="string", nullable=true) */
    private $description;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $dateDebut;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $dateFin;
    #endregion

    #region Méthodes

    public function __construct($commande, $type, $data, $article = null)
    {
        $this->type = $type;
        $this->setCommande($commande);
        $this->setDateAjoutPanier(new \DateTime());
        if ($type == 'inscription') {
            $inscription = $data;
            $item = $inscription->getItem();
            $this->setInscription($inscription);
            $this->formatActivite = $inscription->getFormatActivite();
            $this->setItem($item);
        } elseif ($type == 'autorisation') {
            $item = $data;
            $this->setItem($item);
            $this->addLigneCommandeReference($article);
            $article->addLigneCommandeLiee($this);
        }
        $this->setMontant($item->getArticleMontant($commande->getUtilisateur()));
        $this->setTva($item->getArticleTva($commande->getUtilisateur()));
        $commande->addCommandeDetail($this);
        $commande->updateMontantTotal();
    }



    public function jsonSerializeProperties()
    {
        return ['date', 'statut', 'montant', 'formatActivite', 'creneau', 'typeAutorisaton'];
    }

    public function setItem($item)
    {
        if (is_a($item, FormatActivite::class)) {
            $this->setFormatActivite($item);
        } else if (is_a($item, Creneau::class)) {
            $this->setCreneau($item);
        } else if (is_a($item, Reservabilite::class)) {
            $this->setReservabilite($item);
            $this->setFormatActivite($item->getFormatActivite());
        } elseif (is_a($item, TypeAutorisation::class)) {
            $this->setTypeAutorisation($item);
        }
    }

    public function getItem()
    {
        if (!empty($this->reservabilite)) {
            $this->reservabilite->setFormatActivite($this->formatActivite);
            return $this->reservabilite;
        } elseif (!empty($this->formatActivite)) {
            return $this->formatActivite;
        } elseif (!empty($this->creneau)) {
            return $this->creneau;
        } elseif (!empty($this->typeAutorisation)) {
            return $this->typeAutorisation;
        }
    }

    public function sauvegardeInformations()
    {
        $this->libelle = $this->getItem()->getArticleLibelle();
        $this->description = $this->getItem()->getArticleDescription();
        // $this->dateDebut = $this->getItem()->getArticleDateDebut();
        // $this->dateFin = $this->getItem()->getArticleDateFin();
        // $this->montant = $this->getItem()->getArticleMontant();
    }

    public function traitementPostPaiement()
    {
        $hmac = $this->commande->getHmac();
        if (empty($hmac) || $this->hmac == $hmac) {
            if ($this->type == 'inscription') {
                $this->inscription->setStatut('valide');
                $this->inscription->removeAllAutorisations();
            } elseif ($this->type == 'autorisation') {
                $this->commande->getUtilisateur()->addAutorisation($this->getTypeAutorisation());
            }
        } else {
            // $utilisateur = $this->commande->getUtilisateur();
            // $panier = new Commande($utilisateur);
            // $utilisateur->addCommande($panier);
            // $this->commande->removeCommandeDetail($this);
            // $this->commande = $panier;
            // $panier->addCommandeDetail($this);
        }
    }

    public function traitementPostAnnulation($options)
    {
        if ($this->type == 'inscription') {
            $this->inscription->setStatut('annule', $options);
            $this->inscription->removeAllAutorisations();
        } elseif ($this->type == 'autorisation') {
            // NA
        }
    }

    public function remove()
    {
        $this->getCommande()->removeCommandeDetail($this);
        $this->getCommande()->updateMontantTotal();
        $this->setCommande(null);
    }

    public function isRemovable()
    {
        return $this->getType() == 'autorisation' && $this->getLigneCommandeReferences()->isEmpty()
            || $this->getType() == 'inscription';
    }

    public function traitementPostSuppressionPanier($options = [])
    {
        if ($this->getType() == 'autorisation') {
            if (!$this->getLigneCommandeReferences()->isEmpty()) {
                return false;
            } else {
                $this->remove();
                return true;
            }
        } elseif ($this->getType() == 'inscription') {
            if (!$this->getLigneCommandeLiees()->isEmpty()) {
                foreach ($this->getLigneCommandeLiees() as $commandeDetail) {
                    $commandeDetail->removeLigneCommandeReference($this);
                    $this->removeLigneCommandeLiee($commandeDetail);
                }
            }
            $this->getInscription()->setStatut('annule',  $options);
            $this->remove();
            return true;
        }
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
     * Set type.
     *
     * @param string|null $type
     *
     * @return CommandeDetail
     */
    public function setType($type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set hmac.
     *
     * @param string|null $hmac
     *
     * @return CommandeDetail
     */
    public function setHmac($hmac = null)
    {
        $this->hmac = $hmac;

        return $this;
    }

    /**
     * Get hmac.
     *
     * @return string|null
     */
    public function getHmac()
    {
        return $this->hmac;
    }

    /**
     * Set dateAjoutPanier.
     *
     * @param \DateTime|null $dateAjoutPanier
     *
     * @return CommandeDetail
     */
    public function setDateAjoutPanier($dateAjoutPanier = null)
    {
        $this->dateAjoutPanier = $dateAjoutPanier;

        return $this;
    }

    /**
     * Get dateAjoutPanier.
     *
     * @return \DateTime|null
     */
    public function getDateAjoutPanier()
    {
        return $this->dateAjoutPanier;
    }

    /**
     * Set montant.
     *
     * @param string $montant
     *
     * @return CommandeDetail
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
     * Set tva.
     *
     * @param string $tva
     *
     * @return CommandeDetail
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
     * Set jourCreneau.
     *
     * @param string|null $jourCreneau
     *
     * @return CommandeDetail
     */
    public function setJourCreneau($jourCreneau = null)
    {
        $this->jourCreneau = $jourCreneau;

        return $this;
    }

    /**
     * Get jourCreneau.
     *
     * @return string|null
     */
    public function getJourCreneau()
    {
        return $this->jourCreneau;
    }

    /**
     * Set horaireCreneau.
     *
     * @param string|null $horaireCreneau
     *
     * @return CommandeDetail
     */
    public function setHoraireCreneau($horaireCreneau = null)
    {
        $this->horaireCreneau = $horaireCreneau;

        return $this;
    }

    /**
     * Get horaireCreneau.
     *
     * @return string|null
     */
    public function getHoraireCreneau()
    {
        return $this->horaireCreneau;
    }

    /**
     * Set libelle.
     *
     * @param string|null $libelle
     *
     * @return CommandeDetail
     */
    public function setLibelle($libelle = null)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelle.
     *
     * @return string|null
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Set description.
     *
     * @param string|null $description
     *
     * @return CommandeDetail
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set dateDebut.
     *
     * @param \DateTime|null $dateDebut
     *
     * @return CommandeDetail
     */
    public function setDateDebut($dateDebut = null)
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    /**
     * Get dateDebut.
     *
     * @return \DateTime|null
     */
    public function getDateDebut()
    {
        return $this->dateDebut;
    }

    /**
     * Set dateFin.
     *
     * @param \DateTime|null $dateFin
     *
     * @return CommandeDetail
     */
    public function setDateFin($dateFin = null)
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    /**
     * Get dateFin.
     *
     * @return \DateTime|null
     */
    public function getDateFin()
    {
        return $this->dateFin;
    }

    /**
     * Set commande.
     *
     * @param \UcaBundle\Entity\Commande|null $commande
     *
     * @return CommandeDetail
     */
    public function setCommande(\UcaBundle\Entity\Commande $commande = null)
    {
        $this->commande = $commande;

        return $this;
    }

    /**
     * Get commande.
     *
     * @return \UcaBundle\Entity\Commande|null
     */
    public function getCommande()
    {
        return $this->commande;
    }

    /**
     * Set inscription.
     *
     * @param \UcaBundle\Entity\Inscription|null $inscription
     *
     * @return CommandeDetail
     */
    public function setInscription(\UcaBundle\Entity\Inscription $inscription = null)
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
     * Add ligneCommandeReference.
     *
     * @param \UcaBundle\Entity\CommandeDetail $ligneCommandeReference
     *
     * @return CommandeDetail
     */
    public function addLigneCommandeReference(\UcaBundle\Entity\CommandeDetail $ligneCommandeReference)
    {
        $this->ligneCommandeReferences[] = $ligneCommandeReference;

        return $this;
    }

    /**
     * Remove ligneCommandeReference.
     *
     * @param \UcaBundle\Entity\CommandeDetail $ligneCommandeReference
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeLigneCommandeReference(\UcaBundle\Entity\CommandeDetail $ligneCommandeReference)
    {
        return $this->ligneCommandeReferences->removeElement($ligneCommandeReference);
    }

    /**
     * Get ligneCommandeReferences.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLigneCommandeReferences()
    {
        return $this->ligneCommandeReferences;
    }

    /**
     * Add ligneCommandeLiee.
     *
     * @param \UcaBundle\Entity\CommandeDetail $ligneCommandeLiee
     *
     * @return CommandeDetail
     */
    public function addLigneCommandeLiee(\UcaBundle\Entity\CommandeDetail $ligneCommandeLiee)
    {
        $this->ligneCommandeLiees[] = $ligneCommandeLiee;

        return $this;
    }

    /**
     * Remove ligneCommandeLiee.
     *
     * @param \UcaBundle\Entity\CommandeDetail $ligneCommandeLiee
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeLigneCommandeLiee(\UcaBundle\Entity\CommandeDetail $ligneCommandeLiee)
    {
        return $this->ligneCommandeLiees->removeElement($ligneCommandeLiee);
    }

    /**
     * Get ligneCommandeLiees.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLigneCommandeLiees()
    {
        return $this->ligneCommandeLiees;
    }

    /**
     * Set formatActivite.
     *
     * @param \UcaBundle\Entity\FormatActivite|null $formatActivite
     *
     * @return CommandeDetail
     */
    public function setFormatActivite(\UcaBundle\Entity\FormatActivite $formatActivite = null)
    {
        $this->formatActivite = $formatActivite;

        return $this;
    }

    /**
     * Get formatActivite.
     *
     * @return \UcaBundle\Entity\FormatActivite|null
     */
    public function getFormatActivite()
    {
        return $this->formatActivite;
    }

    /**
     * Set creneau.
     *
     * @param \UcaBundle\Entity\Creneau|null $creneau
     *
     * @return CommandeDetail
     */
    public function setCreneau(\UcaBundle\Entity\Creneau $creneau = null)
    {
        $this->creneau = $creneau;

        return $this;
    }

    /**
     * Get creneau.
     *
     * @return \UcaBundle\Entity\Creneau|null
     */
    public function getCreneau()
    {
        return $this->creneau;
    }

    /**
     * Set reservabilite.
     *
     * @param \UcaBundle\Entity\Reservabilite|null $reservabilite
     *
     * @return CommandeDetail
     */
    public function setReservabilite(\UcaBundle\Entity\Reservabilite $reservabilite = null)
    {
        $this->reservabilite = $reservabilite;

        return $this;
    }

    /**
     * Get reservabilite.
     *
     * @return \UcaBundle\Entity\Reservabilite|null
     */
    public function getReservabilite()
    {
        return $this->reservabilite;
    }

    /**
     * Set typeAutorisation.
     *
     * @param \UcaBundle\Entity\TypeAutorisation|null $typeAutorisation
     *
     * @return CommandeDetail
     */
    public function setTypeAutorisation(\UcaBundle\Entity\TypeAutorisation $typeAutorisation = null)
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
}
