<?php

/*
 * Classe - CommandeDetail:
 *
 * Une comamnde contient un ou plusieur détails
 * Tous les détails (payant ou non) figure dans une commande
 * Un détail de commande correspond à une inscription d'un utilisateur à un format d'activté / créneau / réservation.
*/

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="UcaBundle\Repository\CommandeDetailRepository")
 * @ORM\Table(name="commande_detail")
 * @ORM\EntityListeners({"UcaBundle\Service\Listener\Entity\CommandeDetailListener"})
 */
class CommandeDetail
{
    //region Propriété

    /** @ORM\ManyToOne(targetEntity="Commande", inversedBy="commandeDetails") */
    protected $commande;

    /** @ORM\ManyToOne(targetEntity="Commande", inversedBy="avoirCommandeDetails")*/
    protected $avoir;

    /** @ORM\ManyToOne(targetEntity="Inscription", inversedBy="commandeDetails", cascade={"persist"}) */
    protected $inscription;

    /** @ORM\ManyToMany(targetEntity="CommandeDetail", inversedBy="ligneCommandeLiees", cascade={"persist"}) */
    protected $ligneCommandeReferences;

    /** @ORM\ManyToMany(targetEntity="CommandeDetail", mappedBy="ligneCommandeReferences") */
    protected $ligneCommandeLiees;

    /** @ORM\Column(name="referenceAvoir", type="integer", nullable=true) */
    private $referenceAvoir;

    /** @ORM\Column(type="datetime", nullable=true,  options={"default":null}) */
    private $dateAvoir;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** @ORM\Column(type="string", nullable=true) */
    private $type;

    /** @ORM\Column(type="string", nullable=true) */
    private $hmac;

    /**
     * @ORM\ManyToOne(targetEntity="FormatActivite")
     * @ORM\JoinColumn(name="format_activite_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $formatActivite;

    /**
     * @ORM\ManyToOne(targetEntity="Creneau")
     * @ORM\JoinColumn(name="creneau_id", referencedColumnName="id", onDelete="SET NULL")
     */
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

    /** @ORM\Column(type="text", nullable=true) */
    private $description;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $dateDebut;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $dateFin;

    /** @ORM\Column(type="string", nullable=true) */
    private $typeArticle;

    /** @ORM\Column(type="string", nullable=true) */
    private $numeroCarte;

    /** @ORM\ManyToOne(targetEntity="Etablissement", inversedBy="cartesRetirees") */
    private $etablissementRetraitCarte;

    /** @ORM\Column(type="datetime", nullable = true) */
    private $dateCarteFinValidite;

    //endregion

    //region Méthodes

    public function __construct($commande, $type, $data, $article = null)
    {
        $this->type = $type;
        $this->setCommande($commande);
        $this->setDateAjoutPanier(new \DateTime());
        if ('inscription' == $type) {
            $inscription = $data;
            $item = $inscription->getItem();
            $this->setInscription($inscription);
            $this->formatActivite = $inscription->getFormatActivite();
            $this->setItem($item);
        } elseif ('format' == $type) {
            $inscription = $data;
            $item = $inscription->getItem();
            $this->setInscription($inscription);
            $this->setItem($item);
            $this->addLigneCommandeReference($article);
            $article->addLigneCommandeLiee($this);
        } elseif ('autorisation' == $type) {
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

    public function affichageDetailCommande()
    {
        $format = $this->formatActivite;
        if ('format' === $this->type && ('FormatAvecCreneau' === $this->typeArticle || $format instanceof FormatAvecCreneau)) {
            //return $format->getEstPayant() xor 0 == $format->getTarif()->getMontantUtilisateur($this->commande->getUtilisateur())
            return $format->getEstPayant() && ($format->getEstPayant() && 0 != $format->getTarif()->getMontantUtilisateur($this->commande->getUtilisateur()));
        }

        return true;
    }

    public function jsonSerializeProperties()
    {
        return ['date', 'statut', 'montant', 'formatActivite', 'creneau', 'typeAutorisaton'];
    }

    public function setItem($item)
    {
        if (is_a($item, FormatActivite::class)) {
            $this->setFormatActivite($item);
        } elseif (is_a($item, Creneau::class)) {
            $this->setCreneau($item);
        } elseif (is_a($item, Reservabilite::class)) {
            $this->setReservabilite($item);
            $this->setFormatActivite($item->getFormatActivite());
        } elseif (is_a($item, TypeAutorisation::class)) {
            $this->setTypeAutorisation($item);
        }
    }

    public function getItem()
    {
        if (!empty($this->creneau)) {
            return $this->creneau;
        }
        if (!empty($this->reservabilite)) {
            $this->reservabilite->setFormatActivite($this->formatActivite);

            return $this->reservabilite;
        }
        if (!empty($this->formatActivite)) {
            return $this->formatActivite;
        }
        if (!empty($this->typeAutorisation)) {
            return $this->typeAutorisation;
        }
    }

    public function sauvegardeInformations()
    {
        $this->libelle = $this->getItem()->getArticleLibelle();
        $this->description = $this->getItem()->getArticleDescription();
        $this->dateDebut = $this->getItem()->getArticleDateDebut();
        $this->dateFin = $this->getItem()->getArticleDateFin();
        $this->typeArticle = $this->getItem()->getArticleType();
    }

    public function traitementPostPaiement()
    {
        if ('inscription' == $this->type) {
            $this->inscription->setStatut('valide');
        } elseif ('format' == $this->type) {
            $this->inscription->setStatut('valide');
        } elseif ('autorisation' == $this->type) {
            $this->commande->getUtilisateur()->addAutorisation($this->getTypeAutorisation());
        }
    }

    public function traitementPostAnnulation($options)
    {
        if ('inscription' == $this->type) {
            $this->inscription->setStatut('annule', $options);
        } elseif ('format' == $this->type) {
            $this->inscription->setStatut('annule', $options);
        } elseif ('autorisation' == $this->type) {
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
        return 'autorisation' == $this->getType() && $this->getLigneCommandeReferences()->isEmpty()
            || 'format' == $this->getType() && $this->getLigneCommandeReferences()->isEmpty()
            || 'inscription' == $this->getType();
    }

    public function traitementPostSuppressionPanier($options = [])
    {
        if ('autorisation' == $this->getType()) {
            if (!$this->getLigneCommandeReferences()->isEmpty()) {
                return false;
            }
            $this->remove();

            return true;
        }
        if ('format' == $this->getType()) {
            if (!$this->getLigneCommandeReferences()->isEmpty()) {
                return false;
            }
            $this->getInscription()->setStatut('annule', $options);
            $this->remove();

            return true;
        }
        if ('inscription' == $this->getType()) {
            if (!$this->getLigneCommandeLiees()->isEmpty()) {
                foreach ($this->getLigneCommandeLiees() as $commandeDetail) {
                    $commandeDetail->removeLigneCommandeReference($this);
                    $this->removeLigneCommandeLiee($commandeDetail);
                }
            }
            $this->getInscription()->setStatut('annule', $options);
            $this->remove();

            return true;
        }
    }

    public function voir()
    {
        return !('autorisation' === $this->getType());
    }

    public function isFormatCarte()
    {
        return $this->formatActivite instanceof FormatAchatCarte;
    }

    public function appartientAvoir()
    {
        if (in_array($this, $this->commande->getAvoirCommandeDetails()->toArray(), true)) {
            return $this->commande;
        }

        return false;
    }

    public function eligibleAvoir()
    {
        if ($this->montant > 0 && !$this->appartientAvoir()) {
            if ($this->typeAutorisation && 2 !== $this->typeAutorisation->getId()) {
                return $this;
            }

            return $this;
        }

        return false;
    }

    public function traitementPostGenerationAvoir()
    {
        $avoir = $this->getReferenceAvoir();

        if (null !== $avoir) {
            if ('autorisation' == $this->getType()) {
                if ('Achat de Carte' == $this->getTypeAutorisation()->getComportementLibelle()) {
                    foreach ($this->getCommande()->getCommandeDetails() as $cmdDetails) {
                        if ('inscription' == $cmdDetails->getType() && ($format = $cmdDetails->getFormatActivite()) instanceof FormatAchatCarte && $format->getCarte() == $this->getTypeAutorisation()) {
                            $cmdDetails
                                ->getInscription()->setStatut('desinscrit')
                                /*->setAvoir($this->getCommande())
                                ->setReferenceAvoir($avoir)
                                ->setDateAvoir($this->getDateAvoir())*/
                            ;
                        }
                    }
                }
                if (!empty($autorisation = $this->typeAutorisation)) {
                    // $autorisation = $this->typeAutorisation->getTarif();
                    $this->getCommande()->getUtilisateur()->removeAutorisation($autorisation);
                }
                if ($insciption = $this->getInscription()) {
                    $insciption->removeAllAutorisations();
                }
            }
        } elseif ('inscription' == $this->getType()) {
            $this->getInscription()->setStatut('annule', ['motifAnnulation' => "Génération d'avoir"]);
            $this->getInscription()->removeAllAutorisations();
        }

        return $this;
    }

    //endregion

    /**
     * Set referenceAvoir.
     *
     * @param null|int $referenceAvoir
     *
     * @return CommandeDetail
     */
    public function setReferenceAvoir($referenceAvoir = null)
    {
        $this->referenceAvoir = $referenceAvoir;

        return $this;
    }

    /**
     * Get referenceAvoir.
     *
     * @return null|int
     */
    public function getReferenceAvoir()
    {
        return $this->referenceAvoir;
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
     * Set type.
     *
     * @param null|string $type
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
     * @return null|string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set hmac.
     *
     * @param null|string $hmac
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
     * @return null|string
     */
    public function getHmac()
    {
        return $this->hmac;
    }

    /**
     * Set dateAjoutPanier.
     *
     * @param null|\DateTime $dateAjoutPanier
     *
     * @return CommandeDetail
     */
    public function setDateAjoutPanier($dateAjoutPanier = null)
    {
        $this->dateAjoutPanier = $dateAjoutPanier;

        return $this;
    }

    /**
     * Set dateAvoir.
     *
     * @param null|\DateTime $dateAvoir
     *
     * @return CommandeDetail
     */
    public function setDateAvoir($dateAvoir = null)
    {
        $this->dateAvoir = $dateAvoir;

        return $this;
    }

    /**
     * Get dateAjoutPanier.
     *
     * @return null|\DateTime
     */
    public function getDateAjoutPanier()
    {
        return $this->dateAjoutPanier;
    }

    /**
     * Get dateAvoir.
     *
     * @return null|\DateTime
     */
    public function getDateAvoir()
    {
        return $this->dateAvoir;
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
     * @param null|string $jourCreneau
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
     * @return null|string
     */
    public function getJourCreneau()
    {
        return $this->jourCreneau;
    }

    /**
     * Set horaireCreneau.
     *
     * @param null|string $horaireCreneau
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
     * @return null|string
     */
    public function getHoraireCreneau()
    {
        return $this->horaireCreneau;
    }

    /**
     * Set libelle.
     *
     * @param null|string $libelle
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
     * @return null|string
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Set description.
     *
     * @param null|string $description
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
     * @return null|string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set dateDebut.
     *
     * @param null|\DateTime $dateDebut
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
     * @return null|\DateTime
     */
    public function getDateDebut()
    {
        return $this->dateDebut;
    }

    /**
     * Set dateFin.
     *
     * @param null|\DateTime $dateFin
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
     * @return null|\DateTime
     */
    public function getDateFin()
    {
        return $this->dateFin;
    }

    /**
     * Set typeArticle.
     *
     * @param null|string $typeArticle
     *
     * @return CommandeDetail
     */
    public function setTypeArticle($typeArticle = null)
    {
        $this->typeArticle = $typeArticle;

        return $this;
    }

    /**
     * Get typeArticle.
     *
     * @return null|string
     */
    public function getTypeArticle()
    {
        return $this->typeArticle;
    }

    /**
     * Set numeroCarte.
     *
     * @param null|string $numeroCarte
     *
     * @return CommandeDetail
     */
    public function setNumeroCarte($numeroCarte = null)
    {
        $this->numeroCarte = $numeroCarte;

        return $this;
    }

    /**
     * Get numeroCarte.
     *
     * @return null|string
     */
    public function getNumeroCarte()
    {
        return $this->numeroCarte;
    }

    /**
     * Set dateCarteFinValidite.
     *
     * @param null|\DateTime $dateCarteFinValidite
     *
     * @return CommandeDetail
     */
    public function setDateCarteFinValidite($dateCarteFinValidite = null)
    {
        $this->dateCarteFinValidite = $dateCarteFinValidite;

        return $this;
    }

    /**
     * Get dateCarteFinValidite.
     *
     * @return null|\DateTime
     */
    public function getDateCarteFinValidite()
    {
        return $this->dateCarteFinValidite;
    }

    /**
     * Set commande.
     *
     * @param null|\UcaBundle\Entity\Commande $commande
     *
     * @return CommandeDetail
     */
    public function setCommande(Commande $commande = null)
    {
        $this->commande = $commande;

        return $this;
    }

    /**
     * Get commande.
     *
     * @return null|\UcaBundle\Entity\Commande
     */
    public function getCommande()
    {
        return $this->commande;
    }

    /**
     * Set avoir.
     *
     * @param null|\UcaBundle\Entity\Commande $avoir
     *
     * @return CommandeDetail
     */
    public function setAvoir(Commande $avoir = null)
    {
        $this->avoir = $avoir;

        return $this;
    }

    /**
     * Get avoir.
     *
     * @return null|\UcaBundle\Entity\Commande
     */
    public function getAvoir()
    {
        return $this->avoir;
    }

    /**
     * Set inscription.
     *
     * @param null|\UcaBundle\Entity\Inscription $inscription
     *
     * @return CommandeDetail
     */
    public function setInscription(Inscription $inscription = null)
    {
        $this->inscription = $inscription;

        return $this;
    }

    /**
     * Get inscription.
     *
     * @return null|\UcaBundle\Entity\Inscription
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
    public function addLigneCommandeReference(CommandeDetail $ligneCommandeReference)
    {
        $this->ligneCommandeReferences[] = $ligneCommandeReference;

        return $this;
    }

    /**
     * Remove ligneCommandeReference.
     *
     * @param \UcaBundle\Entity\CommandeDetail $ligneCommandeReference
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeLigneCommandeReference(CommandeDetail $ligneCommandeReference)
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
    public function addLigneCommandeLiee(CommandeDetail $ligneCommandeLiee)
    {
        $this->ligneCommandeLiees[] = $ligneCommandeLiee;

        return $this;
    }

    /**
     * Remove ligneCommandeLiee.
     *
     * @param \UcaBundle\Entity\CommandeDetail $ligneCommandeLiee
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeLigneCommandeLiee(CommandeDetail $ligneCommandeLiee)
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
     * @param null|\UcaBundle\Entity\FormatActivite $formatActivite
     *
     * @return CommandeDetail
     */
    public function setFormatActivite(FormatActivite $formatActivite = null)
    {
        $this->formatActivite = $formatActivite;

        return $this;
    }

    /**
     * Get formatActivite.
     *
     * @return null|\UcaBundle\Entity\FormatActivite
     */
    public function getFormatActivite()
    {
        return $this->formatActivite;
    }

    /**
     * Set creneau.
     *
     * @param null|\UcaBundle\Entity\Creneau $creneau
     *
     * @return CommandeDetail
     */
    public function setCreneau(Creneau $creneau = null)
    {
        $this->creneau = $creneau;

        return $this;
    }

    /**
     * Get creneau.
     *
     * @return null|\UcaBundle\Entity\Creneau
     */
    public function getCreneau()
    {
        return $this->creneau;
    }

    /**
     * Set reservabilite.
     *
     * @param null|\UcaBundle\Entity\Reservabilite $reservabilite
     *
     * @return CommandeDetail
     */
    public function setReservabilite(Reservabilite $reservabilite = null)
    {
        $this->reservabilite = $reservabilite;

        return $this;
    }

    /**
     * Get reservabilite.
     *
     * @return null|\UcaBundle\Entity\Reservabilite
     */
    public function getReservabilite()
    {
        return $this->reservabilite;
    }

    /**
     * Set typeAutorisation.
     *
     * @param null|\UcaBundle\Entity\TypeAutorisation $typeAutorisation
     *
     * @return CommandeDetail
     */
    public function setTypeAutorisation(TypeAutorisation $typeAutorisation = null)
    {
        $this->typeAutorisation = $typeAutorisation;

        return $this;
    }

    /**
     * Get typeAutorisation.
     *
     * @return null|\UcaBundle\Entity\TypeAutorisation
     */
    public function getTypeAutorisation()
    {
        return $this->typeAutorisation;
    }

    /**
     * Set etablissementRetraitCarte.
     *
     * @param null|\UcaBundle\Entity\Etablissement $etablissementRetraitCarte
     *
     * @return CommandeDetail
     */
    public function setEtablissementRetraitCarte(Etablissement $etablissementRetraitCarte = null)
    {
        $this->etablissementRetraitCarte = $etablissementRetraitCarte;

        return $this;
    }

    /**
     * Get etablissementRetraitCarte.
     *
     * @return null|\UcaBundle\Entity\Etablissement
     */
    public function getEtablissementRetraitCarte()
    {
        return $this->etablissementRetraitCarte;
    }
}
