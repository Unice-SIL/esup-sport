<?php

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

/**
 * @ORM\Entity
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

    /** @ORM\ManyToOne(targetEntity="Utilisateur", inversedBy="inscriptions", cascade={"persist"}) */
    private $utilisateur;

    /** @ORM\Column(type="datetime") */
    private $date;

    /** @ORM\Column(type="string") */
    private $statut;
    #endregion

    #region Méthodes

    public function __construct($item, $user, $statut)
    {
        $this->setUtilisateur($user);
        $this->setItem($item);
        $this->setDate(new \DateTime());
        $this->setStatut($statut);
        $user->addInscription($this);
    }

    public function jsonSerializeProperties()
    {
        return [];
    }

    public function setItem($item)
    {
        if (is_a($item, FormatActivite::class)) {
            $this->setFormatActivite($item);
        } else if (is_a($item, Creneau::class)) {
            $this->setCreneau($item);
        }
    }

    public function getItem()
    {
        if (!empty($this->formatActivite)) {
            return $this->formatActivite;
        } elseif (!empty($this->creneau)) {
            return $this->creneau;
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
     * Set statut.
     *
     * @param string $statut
     *
     * @return Inscription
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
     */
    public function getStatut()
    {
        return $this->statut;
    }

    /**
     * Set activite.
     *
     * @param \UcaBundle\Entity\Activite|null $activite
     *
     * @return Inscription
     */
    public function setActivite(\UcaBundle\Entity\Activite $activite = null)
    {
        $this->activite = $activite;

        return $this;
    }

    /**
     * Get activite.
     *
     * @return \UcaBundle\Entity\Activite|null
     */
    public function getActivite()
    {
        return $this->activite;
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
}
