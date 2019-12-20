<?php

namespace UcaBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="UcaBundle\Repository\InscriptionRepository")
 * @Gedmo\Loggable
 */
class Inscription implements \UcaBundle\Entity\Interfaces\JsonSerializable
{
    use \UcaBundle\Entity\Traits\JsonSerializable;

    //region Propriétés
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** @ORM\ManyToOne(targetEntity="FormatActivite", inversedBy="inscriptions") */
    private $formatActivite;

    /** @ORM\ManyToOne(targetEntity="Creneau", inversedBy="inscriptions") */
    private $creneau;

    /** @ORM\ManyToOne(targetEntity="Reservabilite", inversedBy="inscriptions") */
    private $reservabilite;

    /** @ORM\ManyToOne(targetEntity="Utilisateur", inversedBy="inscriptions", cascade={"persist"}) */
    private $utilisateur;

    /** @ORM\Column(type="datetime") */
    private $date;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $dateValidation;

    /** @ORM\Column(type="string") */
    private $statut;
    // valeurs : initialise, attentevalidationencadrant, attentevalidationgestionnaire, attenteajoutpanier, attentepaiement, valide, annule, desinscrit, ancienneinscription

    /** @ORM\Column(type="string", nullable=true) */
    private $motifAnnulation;
    // valeurs : refusencadrant, refusgestionnaire, annulationutilisateur, timeout, bascule

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
    //endregion

    //region Méthodes

    public function __construct(Interfaces\Article $item, $user, $options)
    {
        if (!isset($options['format'])) {
            $options['format'] = null;
        }
        if (!isset($options['typeInscription'])) {
            $options['typeInscription'] = 'principale';
        }
        $this->setUtilisateur($user);
        $this->setItem($item, $options['format']);
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
            return 'UcaBundle:Creneau';
        }
        if (!empty($this->reservabilite)) {
            return 'UcaBundle:Reservabilite';
        }
        if (!empty($this->formatActivite)) {
            return 'UcaBundle:FormatActivite';
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
        if ('ancienneinscription' == $statut) {
            if ('attentevalidationencadrant' == $this->getStatut() || 'attentevalidationgestionnaire' == $this->getStatut() || 'attenteajoutpanier' == $this->getStatut() || 'initialise' == $this->getStatut()) {
                $this->statut = 'annule';
                $this->motifAnnulation = 'bascule';
            }
            if ('valide' == $this->getStatut()) {
                $this->statut = 'ancienneinscription';
            }
        } else {
            $this->statut = $statut;
        }
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

        return $this;
    }

    public function updateStatut()
    {
        foreach ($this->autorisations->getIterator() as $autorisation) {
            $autorisation->updateStatut();
        }
        if ('annule' == $this->statut || 'valide' == $this->statut) {
        } elseif ($this->hasCodeComportementByStatut(['case', 'justificatif'], 'invalide')) {
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
            if ('termine' != $c->getStatut()) {
                $this->commandeDetails->removeElement($cd);
                $cd->setInscription(null);
                $cd->setMontant(0)->setTva(0);
                $c->updateMontantTotal();
            }
        }
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
     * Set date.
     *
     * @param \DateTime $date
     *
     * @return Inscription
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
     * Set dateValidation.
     *
     * @param null|\DateTime $dateValidation
     *
     * @return Inscription
     */
    public function setDateValidation($dateValidation = null)
    {
        $this->dateValidation = $dateValidation;

        return $this;
    }

    /**
     * Get dateValidation.
     *
     * @return \DateTime|null
     */
    public function getDateValidation()
    {
        return $this->dateValidation;
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
     * Set motifAnnulation.
     *
     * @param null|string $motifAnnulation
     *
     * @return Inscription
     */
    public function setMotifAnnulation($motifAnnulation = null)
    {
        $this->motifAnnulation = $motifAnnulation;

        return $this;
    }

    /**
     * Get motifAnnulation.
     *
     * @return string|null
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
     */
    public function setCommentaireAnnulation($commentaireAnnulation = null)
    {
        $this->commentaireAnnulation = $commentaireAnnulation;

        return $this;
    }

    /**
     * Get commentaireAnnulation.
     *
     * @return string|null
     */
    public function getCommentaireAnnulation()
    {
        return $this->commentaireAnnulation;
    }

    /**
     * Set formatActivite.
     *
     * @param null|\UcaBundle\Entity\FormatActivite $formatActivite
     *
     * @return Inscription
     */
    public function setFormatActivite(FormatActivite $formatActivite = null)
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
     * @param null|\UcaBundle\Entity\Creneau $creneau
     *
     * @return Inscription
     */
    public function setCreneau(Creneau $creneau = null)
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
     * @param null|\UcaBundle\Entity\Reservabilite $reservabilite
     *
     * @return Inscription
     */
    public function setReservabilite(Reservabilite $reservabilite = null)
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
     * Set utilisateur.
     *
     * @param null|\UcaBundle\Entity\Utilisateur $utilisateur
     *
     * @return Inscription
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
     * Add autorisation.
     *
     * @param \UcaBundle\Entity\Autorisation $autorisation
     *
     * @return Inscription
     */
    public function addAutorisation(Autorisation $autorisation)
    {
        $this->autorisations[] = $autorisation;

        return $this;
    }

    /**
     * Remove autorisation.
     *
     * @param \UcaBundle\Entity\Autorisation $autorisation
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeAutorisation(Autorisation $autorisation)
    {
        return $this->autorisations->removeElement($autorisation);
    }

    /**
     * Get autorisations.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAutorisations()
    {
        return $this->autorisations;
    }

    /**
     * Add encadrant.
     *
     * @param \UcaBundle\Entity\Utilisateur $encadrant
     *
     * @return Inscription
     */
    public function addEncadrant(Utilisateur $encadrant)
    {
        $this->encadrants[] = $encadrant;

        return $this;
    }

    /**
     * Remove encadrant.
     *
     * @param \UcaBundle\Entity\Utilisateur $encadrant
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeEncadrant(Utilisateur $encadrant)
    {
        return $this->encadrants->removeElement($encadrant);
    }

    /**
     * Get encadrants.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEncadrants()
    {
        return $this->encadrants;
    }

    /**
     * Add commandeDetail.
     *
     * @param \UcaBundle\Entity\CommandeDetail $commandeDetail
     *
     * @return Inscription
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
     * Set dateDesinscription.
     *
     * @param null|\DateTime $dateDesinscription
     *
     * @return Inscription
     */
    public function setDateDesinscription($dateDesinscription = null)
    {
        $this->dateDesinscription = $dateDesinscription;

        return $this;
    }

    /**
     * Get dateDesinscription.
     *
     * @return \DateTime|null
     */
    public function getDateDesinscription()
    {
        return $this->dateDesinscription;
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

    /**
     * Set prenomDesinscription.
     *
     * @param string|null $prenomDesinscription
     *
     * @return Inscription
     */
    public function setPrenomDesinscription($prenomDesinscription = null)
    {
        $this->prenomDesinscription = $prenomDesinscription;

        return $this;
    }

    /**
     * Get prenomDesinscription.
     *
     * @return string|null
     */
    public function getPrenomDesinscription()
    {
        return $this->prenomDesinscription;
    }

    /**
     * Set nomDesinscription.
     *
     * @param string|null $nomDesinscription
     *
     * @return Inscription
     */
    public function setNomDesinscription($nomDesinscription = null)
    {
        $this->nomDesinscription = $nomDesinscription;

        return $this;
    }

    /**
     * Get nomDesinscription.
     *
     * @return string|null
     */
    public function getNomDesinscription()
    {
        return $this->nomDesinscription;
    }

    /**
     * Set utilisateurDesinscription.
     *
     * @param \UcaBundle\Entity\Utilisateur|null $utilisateurDesinscription
     *
     * @return Inscription
     */
    public function setUtilisateurDesinscription(\UcaBundle\Entity\Utilisateur $utilisateurDesinscription = null)
    {
        $this->utilisateurDesinscription = $utilisateurDesinscription;

        return $this;
    }

    /**
     * Get utilisateurDesinscription.
     *
     * @return \UcaBundle\Entity\Utilisateur|null
     */
    public function getUtilisateurDesinscription()
    {
        return $this->utilisateurDesinscription;
    }
}
