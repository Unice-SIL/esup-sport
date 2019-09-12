<?php

namespace UcaBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

/**
 * @ORM\Entity(repositoryClass="UcaBundle\Repository\InscriptionRepository")
 * @Gedmo\Loggable
 */
class Inscription implements \UcaBundle\Entity\Interfaces\JsonSerializable
{

    use \UcaBundle\Entity\Traits\JsonSerializable;

    #region Propriétés
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
    /* valeurs : initialise, attentevalidationencadrant, attentevalidationgestionnaire, attenteajoutpanier, attentepaiement, valide, annule, desinscrit */

    /** @ORM\Column(type="string", nullable=true) */
    private $motifAnnulation;
    /* valeurs : refusencadrant, refusgestionnaire, annulationutilisateur, timeout */

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
    #endregion

    #region Méthodes

    public function __construct(\UcaBundle\Entity\Interfaces\Article $item, $user, $options)
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
        if ($options['typeInscription'] == 'principale') {
            foreach ($this->getItem()->getEncadrants()->getIterator() as $encadrant) {
                $this->addEncadrant($encadrant);
                $encadrant->addInscriptionsAValider($this);
            }
            $this->initAutorisations();
            $this->updateStatut();
        } elseif ($options['typeInscription'] == 'format') {
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
        } elseif (is_a($item, Reservabilite::class)) {
            return 'reservabilite';
        } elseif (is_a($item, FormatActivite::class)) {
            return 'formatActivite';
        }
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

    public function getItem()
    {
        if (!empty($this->creneau)) {
            return $this->creneau;
        } elseif (!empty($this->reservabilite)) {
            $this->reservabilite->setFormatActivite($this->formatActivite);
            return $this->reservabilite;
        } elseif (!empty($this->formatActivite)) {
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
        } elseif (!empty($this->reservabilite)) {
            return 'UcaBundle:Reservabilite';
        } elseif (!empty($this->formatActivite)) {
            return 'UcaBundle:FormatActivite';
        }
    }
    public function getAutorisationsByComportement($arrayComportement, $statut = 'all')
    {
        return $this->autorisations->filter(function ($autorisation) use ($arrayComportement, $statut) {
            if (!in_array($autorisation->getCodeComportement(), $arrayComportement))
                return false;
            if ($statut == 'all') return true;
            return $autorisation->getStatut() == $statut;
        });
    }

    public function hasCodeComportementByStatut($arrayComportement, $statut = 'all')
    {
        return !$this->getAutorisationsByComportement($arrayComportement, $statut)->isEmpty();
    }

    public function setStatut($statut, $options = [])
    {
        $this->statut = $statut;
        if (isset($options['motifAnnulation']))
            $this->motifAnnulation = $options['motifAnnulation'];
        if (isset($options['commentaireAnnulation']))
            $this->commentaireAnnulation = $options['commentaireAnnulation'];
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
        if ($this->statut == 'annule' || $this->statut == 'valide') {
            //
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
            if ($autorisation->getStatut() != 'valide')
                $this->addAutorisation($autorisation);
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
            if ($c->getStatut() != 'termine') {
                $this->commandeDetails->removeElement($cd);
                $cd->setInscription(null);
                $cd->setMontant(0)->setTva(0);
                $c->updateMontantTotal();
            }
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
     * @param \DateTime|null $dateValidation
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
     * @param string|null $motifAnnulation
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
     * @param string|null $commentaireAnnulation
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
     * @param \UcaBundle\Entity\FormatActivite|null $formatActivite
     *
     * @return Inscription
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
     * @return Inscription
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
     * @return Inscription
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
     * Set utilisateur.
     *
     * @param \UcaBundle\Entity\Utilisateur|null $utilisateur
     *
     * @return Inscription
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
     * Add autorisation.
     *
     * @param \UcaBundle\Entity\Autorisation $autorisation
     *
     * @return Inscription
     */
    public function addAutorisation(\UcaBundle\Entity\Autorisation $autorisation)
    {
        $this->autorisations[] = $autorisation;

        return $this;
    }

    /**
     * Remove autorisation.
     *
     * @param \UcaBundle\Entity\Autorisation $autorisation
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeAutorisation(\UcaBundle\Entity\Autorisation $autorisation)
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
    public function addEncadrant(\UcaBundle\Entity\Utilisateur $encadrant)
    {
        $this->encadrants[] = $encadrant;

        return $this;
    }

    /**
     * Remove encadrant.
     *
     * @param \UcaBundle\Entity\Utilisateur $encadrant
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeEncadrant(\UcaBundle\Entity\Utilisateur $encadrant)
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
     * Set dateDesinscription.
     *
     * @param \DateTime|null $dateDesinscription
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
}
