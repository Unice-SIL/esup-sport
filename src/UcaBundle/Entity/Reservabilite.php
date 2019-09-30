<?php

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use UcaBundle\Service\Common\Fn;
use UcaBundle\Service\Common\Previsualisation;

/**
 * @ORM\Entity(repositoryClass="UcaBundle\Repository\ReservabiliteRepository")
 * @Gedmo\Loggable 
 */
class Reservabilite implements \UcaBundle\Entity\Interfaces\JsonSerializable, \UcaBundle\Entity\Interfaces\Article
{

    use \UcaBundle\Entity\Traits\JsonSerializable;
    use \UcaBundle\Entity\Traits\Article;

    #region Propriétés
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** @ORM\ManyToOne(targetEntity="Ressource", inversedBy="reservabilites") */
    private $ressource;

    /** @ORM\OneToOne(targetEntity="DhtmlxEvenement", mappedBy="reservabilite") */
    private $evenement;

    /** @ORM\OneToMany(targetEntity="Inscription", mappedBy="reservabilite") */
    protected $inscriptions;

    private $formatActivite;

    #endregion

    #region Méthodes

    public function jsonSerializeProperties()
    {
        return [];
    }

    public function getTarif()
    {
        return $this->getRessource()->getTarif();
    }

    public function getArticleLibelle()
    {
        return $this->getRessource()->getLibelle()
            . ' [' . $this->getArticleDateDebut()->format('d/m/Y H:i')
            . ' - ' . $this->getArticleDateFin()->format('d/m/Y H:i') . ']';
    }

    public function getArticleDescription()
    {
        return Fn::strTruncate($this->getFormatActivite()->getLibelle(), 97);
    }

    public function getArticleDateDebut()
    {
        return $this->getEvenement()->getDateDebut();
    }

    public function getArticleDateFin()
    {
        return $this->getEvenement()->getDateFin();
    }

    public function getAutorisations()
    {
        return $this->getFormatActivite()->getAutorisations();
    }

    public function getCapacite()
    {
        return $this->ressource->getCapacite();
    }

    public function getEncadrants()
    {
        return $this->getFormatActivite()->getEncadrants();
    }

    public function getFormatActivite()
    {
        return $this->formatActivite;
    }

    public function setFormatActivite($formatActivite)
    {
        $this->formatActivite = $formatActivite;
    }

    public function dateReservationPasse()
    {
        return new \DateTime() > $this->getEvenement()->getDateDebut();
    }

    public function getArticleMontant($utilisateur)
    {
        return $this->getArticleMontantDefaut($utilisateur);
    }

    #endregion

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->inscriptions = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set ressource.
     *
     * @param \UcaBundle\Entity\Ressource|null $ressource
     *
     * @return Reservabilite
     */
    public function setRessource(\UcaBundle\Entity\Ressource $ressource = null)
    {
        $this->ressource = $ressource;

        return $this;
    }

    /**
     * Get ressource.
     *
     * @return \UcaBundle\Entity\Ressource|null
     */
    public function getRessource()
    {
        return $this->ressource;
    }

    /**
     * Set evenement.
     *
     * @param \UcaBundle\Entity\DhtmlxEvenement|null $evenement
     *
     * @return Reservabilite
     */
    public function setEvenement(\UcaBundle\Entity\DhtmlxEvenement $evenement = null)
    {
        $this->evenement = $evenement;

        return $this;
    }

    /**
     * Get evenement.
     *
     * @return \UcaBundle\Entity\DhtmlxEvenement|null
     */
    public function getEvenement()
    {
        return $this->evenement;
    }

    /**
     * Add inscription.
     *
     * @param \UcaBundle\Entity\Inscription $inscription
     *
     * @return Reservabilite
     */
    public function addInscription(\UcaBundle\Entity\Inscription $inscription)
    {
        $this->inscriptions[] = $inscription;

        return $this;
    }

    /**
     * Remove inscription.
     *
     * @param \UcaBundle\Entity\Inscription $inscription
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeInscription(\UcaBundle\Entity\Inscription $inscription)
    {
        return $this->inscriptions->removeElement($inscription);
    }

    /**
     * Get inscriptions.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInscriptions()
    {
        return $this->inscriptions;
    }
}
