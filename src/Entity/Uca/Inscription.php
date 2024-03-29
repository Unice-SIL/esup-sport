<?php

/*
 * Classe - Inscription:
 *
 * Entité gérant la partie des inscriptions
 * Un utilisateur s'incrit sur un format auquel peut être associé certains élements (un créneua, une ressources, ou un type d'autorisation)
 * La facturation sera elle gérée dans les commandes.
*/

namespace App\Entity\Uca;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\InscriptionRepository")
 * @Gedmo\Loggable
 * @ORM\EntityListeners({"App\Service\Listener\Entity\InscriptionListener"})
 */
class Inscription implements \App\Entity\Uca\Interfaces\JsonSerializable
{
    use \App\Entity\Uca\Traits\JsonSerializable;

    public const STATUT_INVALIDE = ['desinscrit', 'annule', 'ancienneinscription', 'desinscriptionadministrative', 'annulationgestionnaire', 'annulationutilisateur', 'annulationpartenaire'];

    // region Propriétés
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="FormatActivite", inversedBy="inscriptions", cascade={"persist"})
     * @ORM\JoinColumn(name="format_activite_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $formatActivite;

    /**
     * @ORM\ManyToOne(targetEntity="Creneau", inversedBy="inscriptions")
     * @ORM\JoinColumn(name="creneau_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $creneau;

    /**
     * @ORM\ManyToOne(targetEntity="Reservabilite", inversedBy="inscriptions")
     * @ORM\JoinColumn(name="reservabilite_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $reservabilite;

    /** @ORM\ManyToOne(targetEntity="Utilisateur", inversedBy="inscriptions", cascade={"persist"}) */
    private $utilisateur;

    /** @ORM\Column(type="datetime") */
    private $date;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $dateValidation;

    /** @ORM\Column(type="string") */
    private $statut;
    // valeurs : initialise, attentevalidationencadrant, attentevalidationgestionnaire, attenteajoutpanier, attentepaiement, attentepartenaire, valide, annule, desinscrit, ancienneinscription, desinscriptionadministrative

    /** @ORM\Column(type="string", nullable=true) */
    private $motifAnnulation;
    // valeurs : refusencadrant, refusgestionnaire, annulationutilisateur, timeout

    /** @ORM\Column(type="string", nullable=true) */
    private $commentaireAnnulation;

    /** @ORM\OneToMany(targetEntity="Autorisation", mappedBy="inscription", cascade={"persist"}, orphanRemoval=true) */
    private $autorisations;

    /** @ORM\ManyToMany(targetEntity="Utilisateur", inversedBy="inscriptionsAValider") */
    private $encadrants;

    /** @ORM\OneToMany(targetEntity="CommandeDetail", mappedBy="inscription", cascade={"persist"}, orphanRemoval=true) */
    private $commandeDetails;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $dateDesinscription;

    /** @ORM\ManyToOne(targetEntity="Utilisateur") */
    private $utilisateurDesinscription;

    /** @ORM\Column(type="string", nullable=true) */
    private $prenomDesinscription;

    /** @ORM\Column(type="string", nullable=true) */
    private $nomDesinscription;

    /** @ORM\Column(type="string", nullable=true) */
    private $libelle;

    /** @ORM\Column(type="text", nullable=true) */
    private $description;

    /** @ORM\Column(type="string", nullable=true) */
    private $prenomInscrit;

    /** @ORM\Column(type="string", nullable=true) */
    private $nomInscrit;

    /** @ORM\Column(type="text", nullable=true) */
    private $listeEmailPartenaires;

    /** @ORM\Column(type="integer", nullable=true) */
    private $estPartenaire;
    // ce champ va nous permettre de ne pas prendre en compte les inscriptions partenaires dans le limites

    // endregion

    // region Méthodes
    public function __construct(Interfaces\Article $item, $user, $options)
    {
        $this->commandeDetails = new \Doctrine\Common\Collections\ArrayCollection();
        $this->autorisations = new \Doctrine\Common\Collections\ArrayCollection();

        if (!isset($options['format'])) {
            $options['format'] = null;
        }
        if (!isset($options['typeInscription'])) {
            $options['typeInscription'] = 'principale';
        }
        $this->setUtilisateur($user);
        $this->setNomInscrit($user->getNom());
        $this->setPrenomInscrit($user->getPrenom());
        $this->setItem($item, $options['format']);
        $this->setLibelle($item->getArticleLibelle());
        $this->setDescription($item->getArticleDescription());
        $this->setDate(new \DateTime());
        if ('principale' == $options['typeInscription']) {
            foreach ($this->getItem()->getEncadrants()->getIterator() as $encadrant) {
                $this->addEncadrant($encadrant);
                $encadrant->addInscriptionsAValider($this);
            }
            $this->initAutorisations();
            $this->updateStatut();
        } elseif ('format' == $options['typeInscription']) {
            $this->setStatut('attentepaiement');
        }
        $user->addInscription($this);
    }

    public function jsonSerializeProperties()
    {
        return [];
    }

    public function getAutorisationTypes()
    {
        return $this->getItem()->getArticleAutorisations();
    }

    public static function getItemColumn($item)
    {
        if (is_a($item, Creneau::class)) {
            return 'creneau';
        }
        if (is_a($item, Reservabilite::class)) {
            return 'reservabilite';
        }
        if (is_a($item, FormatActivite::class)) {
            return 'formatActivite';
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
    }

    public function getItemId()
    {
        return $this->getItem()->getId();
    }

    public function getItemType()
    {
        if (!empty($this->creneau)) {
            return 'Creneau';
        }
        if (!empty($this->reservabilite)) {
            return 'Reservabilite';
        }
        if (!empty($this->formatActivite)) {
            return 'FormatActivite';
        }
    }

    public function getAutorisationsByComportement($arrayComportement, $statut = 'all')
    {
        return $this->autorisations->filter(function ($autorisation) use ($arrayComportement, $statut) {
            if (!in_array($autorisation->getCodeComportement(), $arrayComportement)) {
                return false;
            }
            if ('all' == $statut) {
                return true;
            }

            return $autorisation->getStatut() == $statut;
        });
    }

    public function hasCodeComportementByStatut($arrayComportement, $statut = 'all')
    {
        return !$this->getAutorisationsByComportement($arrayComportement, $statut)->isEmpty();
    }

    public function setStatut($statut, $options = [])
    {
        $oldStatut = $this->statut;
        $this->statut = $statut;
        if (isset($options['motifAnnulation'])) {
            $this->motifAnnulation = $options['motifAnnulation'];
        }
        if (isset($options['commentaireAnnulation'])) {
            $this->commentaireAnnulation = $options['commentaireAnnulation'];
        }
        if (in_array($statut, ['annule', 'valide'])) {
            $this->removeAllAutorisations();
        }
        if (in_array($statut, ['valide'])) {
            $this->removeAllCommandeDetails();
        }

        // Mise a jour du nombre d'inscrits
        if (in_array($statut, ['valide', 'initialise', 'attentevalidationencadrant', 'attentevalidationgestionnaire', 'attenteajoutpanier', 'attentepaiement', 'attentepartenaire']) && in_array($oldStatut, ['annule', 'desinscrit', 'ancienneinscription', 'desinscriptionadministrative'])) {
            $this->updateNbInscrits(true);
        } elseif (in_array($statut, ['annule', 'desinscrit', 'ancienneinscription', 'desinscriptionadministrative']) && in_array($oldStatut, ['valide', 'initialise', 'attentevalidationencadrant', 'attentevalidationgestionnaire', 'attenteajoutpanier', 'attentepaiement', 'attentepartenaire']) && 'basculesemestre' != $this->motifAnnulation) {
            $this->updateNbInscrits(false);
        }

        return $this;
    }

    public function seDesinscrire(Utilisateur $utilisateur, $avoir = false)
    {
        if (!$avoir) {
            $date = new \DateTime();
            $this->setStatut('desinscrit');
            $this->setDateDesinscription($date);
            $this->setUtilisateurDesinscription($utilisateur);
            $this->setNomDesinscription($utilisateur->getNom());
            $this->setPrenomDesinscription($utilisateur->getPrenom());
        }
        if ($creneau = $this->getCreneau()) {
            $format = $creneau->getFormatActivite();
            foreach ($utilisateur->getInscriptionsByCriteria([
                ['statut', 'notIn', ['annule', 'desinscrit', 'ancienneinscription', 'desinscriptionadministrative']],
                ['id', 'neq', $this->getId()],
            ]) as $inscription) {
                if ($inscription->getCreneau() && $format === $inscription->getCreneau()->getFormatActivite()) {
                    $autreCreneau = true;
                }
                if ($format === $inscription->getFormatActivite()) {
                    $inscriptionFormat = $inscription;
                }
            }
        }
        if (!isset($autreCreneau) && !$avoir && isset($inscriptionFormat)) {
            $date = new \DateTime();
            $inscriptionFormat->setStatut('desinscrit');
            $inscriptionFormat->setDateDesinscription($date);
            $inscriptionFormat->setUtilisateurDesinscription($utilisateur);
            $inscriptionFormat->setNomDesinscription($utilisateur->getNom());
            $inscriptionFormat->setPrenomDesinscription($utilisateur->getPrenom());
        } elseif ($avoir && !isset($autreCreneau) && isset($inscriptionFormat)) {
            $inscriptionFormat->setStatut('ancienneinsciption');
        }
    }

    public function updateStatut()
    {
        foreach ($this->autorisations->getIterator() as $autorisation) {
            $autorisation->updateStatut();
        }
        if ('annule' == $this->statut || 'valide' == $this->statut) {
        } elseif ($this->hasCodeComportementByStatut([/* 'case', */ 'justificatif'], 'invalide')) {
            $this->statut = 'initialise';
        } elseif ($this->hasCodeComportementByStatut(['validationencadrant'], 'invalide')) {
            $this->statut = 'attentevalidationencadrant';
        } elseif ($this->hasCodeComportementByStatut(['validationgestionnaire'], 'invalide')) {
            $this->statut = 'attentevalidationgestionnaire';
        } elseif ($this->hasCodeComportementByStatut(['validationencadrant', 'validationgestionnaire'], 'valide')) {
            $this->statut = 'attenteajoutpanier';
            $this->setDateValidation(new \DateTime());
        } else {
            $this->statut = 'attentepaiement';
        }
    }

    public function initAutorisations()
    {
        $this->autorisations = new ArrayCollection();
        foreach ($this->getAutorisationTypes()->getIterator() as $typeAutorisation) {
            $autorisation = new Autorisation($this, $typeAutorisation);
            if ('valide' != $autorisation->getStatut()) {
                $this->addAutorisation($autorisation);
            }
        }
    }

    public function removeAllAutorisations()
    {
        foreach ($this->autorisations->getIterator() as $autorisation) {
            $this->autorisations->removeElement($autorisation);
            // not needed for persistence, just keeping both sides in sync
            $autorisation->setInscription(null);
            // $autorisation->delete
        }
    }

    public function removeAllCommandeDetails()
    {
        foreach ($this->commandeDetails->getIterator() as $cd) {
            $c = $cd->getCommande();
            if (!in_array($c->getStatut(), ['termine', 'avoir'])) {
                $this->commandeDetails->removeElement($cd);
                $cd->setInscription(null);
                $cd->setMontant(0)->setTva(0);
                $c->updateMontantTotal();
            }
        }
    }

    public function estAnnulable(CommandeDetail $cmdDetailAnnule)
    {
        if ('valide' == $this->statut) {
            return false;
        }
        foreach ($this->getCommandeDetails() as $commandeDetail) {
            if ($commandeDetail->getId() != $cmdDetailAnnule->getId()) {
                if (in_array($commandeDetail->getCommande()->getStatut(), ['panier', 'apayer', 'termine'])) {
                    return false;
                }
            }
        }

        return true;
    }

    // endregion

    /**
     * Get id.
     *
     * @return int
     *
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
     * @return Inscription
     *
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
     *
     * @codeCoverageIgnore
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set dateValidation.
     *
     * @param null|\DateTime $dateValidation
     *
     * @return Inscription
     *
     * @codeCoverageIgnore
     */
    public function setDateValidation($dateValidation = null)
    {
        $this->dateValidation = $dateValidation;

        return $this;
    }

    /**
     * Get dateValidation.
     *
     * @return null|\DateTime
     *
     * @codeCoverageIgnore
     */
    public function getDateValidation()
    {
        return $this->dateValidation;
    }

    /**
     * Get statut.
     *
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getStatut()
    {
        return $this->statut;
    }

    /**
     * Set motifAnnulation.
     *
     * @param null|string $motifAnnulation
     *
     * @return Inscription
     *
     * @codeCoverageIgnore
     */
    public function setMotifAnnulation($motifAnnulation = null)
    {
        $this->motifAnnulation = $motifAnnulation;

        return $this;
    }

    /**
     * Get motifAnnulation.
     *
     * @return null|string
     *
     * @codeCoverageIgnore
     */
    public function getMotifAnnulation()
    {
        return $this->motifAnnulation;
    }

    /**
     * Set commentaireAnnulation.
     *
     * @param null|string $commentaireAnnulation
     *
     * @return Inscription
     *
     * @codeCoverageIgnore
     */
    public function setCommentaireAnnulation($commentaireAnnulation = null)
    {
        $this->commentaireAnnulation = $commentaireAnnulation;

        return $this;
    }

    /**
     * Get commentaireAnnulation.
     *
     * @return null|string
     *
     * @codeCoverageIgnore
     */
    public function getCommentaireAnnulation()
    {
        return $this->commentaireAnnulation;
    }

    /**
     * Set dateDesinscription.
     *
     * @param null|\DateTime $dateDesinscription
     *
     * @return Inscription
     *
     * @codeCoverageIgnore
     */
    public function setDateDesinscription($dateDesinscription = null)
    {
        $this->dateDesinscription = $dateDesinscription;

        return $this;
    }

    /**
     * Get dateDesinscription.
     *
     * @return null|\DateTime
     *
     * @codeCoverageIgnore
     */
    public function getDateDesinscription()
    {
        return $this->dateDesinscription;
    }

    /**
     * Set formatActivite.
     *
     * @return Inscription
     *
     * @codeCoverageIgnore
     */
    public function setFormatActivite(FormatActivite $formatActivite = null)
    {
        $this->formatActivite = $formatActivite;

        return $this;
    }

    /**
     * Get formatActivite.
     *
     * @return null|FormatActivite
     *
     * @codeCoverageIgnore
     */
    public function getFormatActivite()
    {
        return $this->formatActivite;
    }

    /**
     * Set creneau.
     *
     * @return Inscription
     *
     * @codeCoverageIgnore
     */
    public function setCreneau(Creneau $creneau = null)
    {
        $this->creneau = $creneau;

        return $this;
    }

    /**
     * Get creneau.
     *
     * @return null|Creneau
     *
     * @codeCoverageIgnore
     */
    public function getCreneau()
    {
        return $this->creneau;
    }

    /**
     * Set reservabilite.
     *
     * @return Inscription
     *
     * @codeCoverageIgnore
     */
    public function setReservabilite(Reservabilite $reservabilite = null)
    {
        $this->reservabilite = $reservabilite;

        return $this;
    }

    /**
     * Get reservabilite.
     *
     * @return null|Reservabilite
     *
     * @codeCoverageIgnore
     */
    public function getReservabilite()
    {
        return $this->reservabilite;
    }

    /**
     * Set utilisateur.
     *
     * @return Inscription
     *
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
     *
     * @codeCoverageIgnore
     */
    public function getUtilisateur()
    {
        return $this->utilisateur;
    }

    /**
     * Add autorisation.
     *
     * @return Inscription
     *
     * @codeCoverageIgnore
     */
    public function addAutorisation(Autorisation $autorisation)
    {
        $this->autorisations[] = $autorisation;

        return $this;
    }

    /**
     * Remove autorisation.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     *
     * @codeCoverageIgnore
     */
    public function removeAutorisation(Autorisation $autorisation)
    {
        return $this->autorisations->removeElement($autorisation);
    }

    /**
     * Get autorisations.
     *
     * @return \Doctrine\Common\Collections\Collection
     *
     * @codeCoverageIgnore
     */
    public function getAutorisations()
    {
        return $this->autorisations;
    }

    /**
     * Add encadrant.
     *
     * @return Inscription
     *
     * @codeCoverageIgnore
     */
    public function addEncadrant(Utilisateur $encadrant)
    {
        $this->encadrants[] = $encadrant;

        return $this;
    }

    /**
     * Remove encadrant.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     *
     * @codeCoverageIgnore
     */
    public function removeEncadrant(Utilisateur $encadrant)
    {
        return $this->encadrants->removeElement($encadrant);
    }

    /**
     * Get encadrants.
     *
     * @return \Doctrine\Common\Collections\Collection
     *
     * @codeCoverageIgnore
     */
    public function getEncadrants()
    {
        return $this->encadrants;
    }

    /**
     * Add commandeDetail.
     *
     * @return Inscription
     *
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
     *
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
     *
     * @codeCoverageIgnore
     */
    public function getCommandeDetails()
    {
        return $this->commandeDetails;
    }

    /**
     * Set prenomDesinscription.
     *
     * @param null|string $prenomDesinscription
     *
     * @return Inscription
     *
     * @codeCoverageIgnore
     */
    public function setPrenomDesinscription($prenomDesinscription = null)
    {
        $this->prenomDesinscription = $prenomDesinscription;

        return $this;
    }

    /**
     * Get prenomDesinscription.
     *
     * @return null|string
     *
     * @codeCoverageIgnore
     */
    public function getPrenomDesinscription()
    {
        return $this->prenomDesinscription;
    }

    /**
     * Set nomDesinscription.
     *
     * @param null|string $nomDesinscription
     *
     * @return Inscription
     *
     * @codeCoverageIgnore
     */
    public function setNomDesinscription($nomDesinscription = null)
    {
        $this->nomDesinscription = $nomDesinscription;

        return $this;
    }

    /**
     * Get nomDesinscription.
     *
     * @return null|string
     *
     * @codeCoverageIgnore
     */
    public function getNomDesinscription()
    {
        return $this->nomDesinscription;
    }

    /**
     * Set utilisateurDesinscription.
     *
     * @return Inscription
     *
     * @codeCoverageIgnore
     */
    public function setUtilisateurDesinscription(Utilisateur $utilisateurDesinscription = null)
    {
        $this->utilisateurDesinscription = $utilisateurDesinscription;

        return $this;
    }

    /**
     * Get utilisateurDesinscription.
     *
     * @return null|Utilisateur
     *
     * @codeCoverageIgnore
     */
    public function getUtilisateurDesinscription()
    {
        return $this->utilisateurDesinscription;
    }

    /**
     * Set libelle.
     *
     * @param null|string $libelle
     *
     * @return Inscription
     *
     * @codeCoverageIgnore
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
     *
     * @codeCoverageIgnore
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
     * @return Inscription
     *
     * @codeCoverageIgnore
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
     *
     * @codeCoverageIgnore
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set prenomInscrit.
     *
     * @param null|string $prenomInscrit
     *
     * @return Inscription
     *
     * @codeCoverageIgnore
     */
    public function setPrenomInscrit($prenomInscrit = null)
    {
        $this->prenomInscrit = $prenomInscrit;

        return $this;
    }

    /**
     * Get prenomInscrit.
     *
     * @return null|string
     *
     * @codeCoverageIgnore
     */
    public function getPrenomInscrit()
    {
        return $this->prenomInscrit;
    }

    /**
     * Set nomInscrit.
     *
     * @param null|string $nomInscrit
     *
     * @return Inscription
     *
     * @codeCoverageIgnore
     */
    public function setNomInscrit($nomInscrit = null)
    {
        $this->nomInscrit = $nomInscrit;

        return $this;
    }

    /**
     * Get nomInscrit.
     *
     * @return null|string
     *
     * @codeCoverageIgnore
     */
    public function getNomInscrit()
    {
        return $this->nomInscrit;
    }

    /**
     * Fonction qui permet de mettre à jour le champs nbInscrits dans les tables de quota par profil.
     *
     * @param Inscription $inscription
     *
     * @codeCoverageIgnore
     */
    public function updateNbInscrits(bool $add = true): void
    {
        if (!$this->estPartenaire) {
            if ($this->getReservabilite()) {
                $item = $this->getReservabilite();
            } elseif ($this->getCreneau()) {
                $item = $this->getCreneau();
            } elseif ($this->getFormatActivite()) {
                $item = $this->getFormatActivite();
            } else {
                $item = null;
            }

            if (null !== $item && null !== $this->getUtilisateur()->getProfil()) {
                $profilUtilisateur = $this->getUtilisateur()->getProfil()->getParent() ?? $this->getUtilisateur()->getProfil();
                $criteria = Criteria::create()->andWhere(Criteria::expr()->eq('profilUtilisateur', $profilUtilisateur));
                $itemProfils = $item->getProfilsUtilisateurs()->matching($criteria);
                if (sizeof($itemProfils) > 0) {
                    $itemProfil = $itemProfils->first();
                    if ($add) {
                        $itemProfil->setNbInscrits($itemProfil->getNbInscrits() + 1);
                    } else {
                        $itemProfil->setNbInscrits($itemProfil->getNbInscrits() - 1);
                    }
                }
            }
        }
    }

    /**
     * Get listeEmailPartenaires.
     *
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getListeEmailPartenaires(): ?string
    {
        return $this->listeEmailPartenaires;
    }

    /**
     * Set listeEmailPartenaires.
     *
     * @param string $listeEmailPartenaires
     *
     * @codeCoverageIgnore
     */
    public function setListeEmailPartenaires(?string $listeEmailPartenaires): Inscription
    {
        $this->listeEmailPartenaires = $listeEmailPartenaires;

        return $this;
    }

    /**
     * Get estPartenaire.
     *
     * @return int
     *
     * @codeCoverageIgnore
     */
    public function getEstPartenaire(): ?int
    {
        return $this->estPartenaire;
    }

    /**
     * Set estPartenaire.
     *
     * @codeCoverageIgnore
     */
    public function setEstPartenaire(int $estPartenaire): Inscription
    {
        $this->estPartenaire = $estPartenaire;

        return $this;
    }

    public function getFirstCommande(): ?Commande
    {
        if ($this->commandeDetails && $this->commandeDetails->first() && $this->commandeDetails->first()->getCommande()) {
            return $this->commandeDetails->first()->getCommande();
        }

        return null;
    }

    private function setItem($item, $format)
    {
        if (is_a($item, Creneau::class)) {
            $this->setCreneau($item);
            $this->setFormatActivite($item->getFormatActivite());
        } elseif (is_a($item, Reservabilite::class)) {
            $this->setReservabilite($item);
            $this->setFormatActivite($format);
        } elseif (is_a($item, FormatActivite::class)) {
            $this->setFormatActivite($item);
        }
    }
}
