<?php

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use UcaBundle\Entity\Creneau;
use UcaBundle\Entity\FormatActivite;
use UcaBundle\Entity\TypeAutorisation;

/**
 * @ORM\Entity
 */
class Article implements \UcaBundle\Entity\Interfaces\JsonSerializable
{
    use \UcaBundle\Entity\Traits\JsonSerializable;
    #region Propriétés
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** @ORM\ManyToOne(targetEntity="Panier", inversedBy="articles", cascade={"persist"}) */
    private $panier;

    /** @ORM\ManyToOne(targetEntity="FormatActivite") */
    private $formatActivite;

    /** @ORM\ManyToOne(targetEntity="Creneau") */
    private $creneau;

    /** @ORM\ManyToOne(targetEntity="TypeAutorisation") */
    private $typeAutorisation;

    /** @ORM\Column(type="decimal") */
    private $montant;

    /** @ORM\Column(type="datetime") */
    private $date;

    /** @ORM\Column(type="string") */
    private $statut;
    #endregion

    #region Méthodes

    public function __construct($panier, $type, $item, $user)
    {
        $this->setPanier($panier);
        $this->setItem($item);
        $this->setMontant($item->getMontant($user));
        $this->setDate(new \DateTime());
        $this->setStatut('OK');
        $panier->addArticle($this);
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
        } elseif (is_a($item, TypeAutorisation::class)) {
            $this->setTypeAutorisation($item);
        }
    }

    public function getItem()
    {
        if (!empty($this->formatActivite)) {
            return $this->formatActivite;
        } elseif (!empty($this->creneau)) {
            return $this->creneau;
        } elseif (!empty($this->typeAutorisation)) {
            return $this->typeAutorisation;
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
     * Set montant.
     *
     * @param string $montant
     *
     * @return Article
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
     * Set date.
     *
     * @param \DateTime $date
     *
     * @return Article
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
     * @return Article
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
     * Set panier.
     *
     * @param \UcaBundle\Entity\Panier|null $panier
     *
     * @return Article
     */
    public function setPanier(\UcaBundle\Entity\Panier $panier = null)
    {
        $this->panier = $panier;

        return $this;
    }

    /**
     * Get panier.
     *
     * @return \UcaBundle\Entity\Panier|null
     */
    public function getPanier()
    {
        return $this->panier;
    }

    /**
     * Set formatActivite.
     *
     * @param \UcaBundle\Entity\FormatActivite|null $formatActivite
     *
     * @return Article
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
     * @return Article
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
     * Set typeAutorisation.
     *
     * @param \UcaBundle\Entity\TypeAutorisation|null $typeAutorisation
     *
     * @return Article
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
