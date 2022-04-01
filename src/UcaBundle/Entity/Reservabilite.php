<?php

/*
 * Classe - Réservabiltié:.
 *
 * Reservalité des ressoruces.
*/

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use UcaBundle\Service\Common\Fn;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="UcaBundle\Repository\ReservabiliteRepository")
 * @Gedmo\Loggable
 */
class Reservabilite implements \UcaBundle\Entity\Interfaces\JsonSerializable, \UcaBundle\Entity\Interfaces\Article
{
    use \UcaBundle\Entity\Traits\JsonSerializable;
    use \UcaBundle\Entity\Traits\Article;

    /** @ORM\OneToMany(targetEntity="Inscription", mappedBy="reservabilite") */
    protected $inscriptions;

    //region Propriétés
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** @ORM\ManyToOne(targetEntity="Ressource", inversedBy="reservabilites") */
    private $ressource;

    /** @ORM\OneToOne(targetEntity="DhtmlxSerie", mappedBy="reservabilite", cascade={"remove"}) */
    private $serie;

    /** @ORM\OneToOne(targetEntity="DhtmlxEvenement", mappedBy="reservabilite", cascade={"remove"}) */
    private $evenement;

    /** @ORM\OneToMany(targetEntity="ReservabiliteProfilUtilisateur", mappedBy="reservabilite", cascade={"persist", "remove"})
     * @Assert\NotBlank(message="complement.profilsutilisateurs.notblank") */
    private $profilsUtilisateurs;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="complement.capacite.notblank")
     */
    private $capacite;

    private $formatActivite;

    //endregion

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->inscriptions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->profilsUtilisateurs = new \Doctrine\Common\Collections\ArrayCollection();
    }

    //endregion

    //region Méthodes

    public function jsonSerializeProperties()
    {
        return ['capacite', 'profilsUtilisateurs', 'ressource'];
    }

    public function getTarif()
    {
        return $this->getRessource()->getTarif();
    }

    public function getArticleLibelle()
    {
        return $this->getRessource()->getLibelle()
            .' ['.$this->getArticleDateDebut()->format('d/m/Y H:i')
            .' - '.$this->getArticleDateFin()->format('d/m/Y H:i').']';
    }

    public function getArticleDescription()
    {
        return Fn::strTruncate($this->getEvenement() !== null ? $this->getEvenement()->getDescription() : $this->getSerie()->getEvenements()->first()->getDescription(), 97);
    }

    public function getArticleDateDebut()
    {
        return $this->getEvenement() !== null ? $this->getEvenement()->getDateDebut(): $this->getSerie()->getDateDebut();
    }

    public function getArticleDateFin()
    {
        return $this->getEvenement() !== null ? $this->getEvenement()->getDateFin(): $this->getSerie()->getDateFin();
    }

    public function getAutorisations()
    {
        return $this->getFormatActivite()->getAutorisations();
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

    public function dateReservationPasse(DhtmlxEvenement $event)
    {
        return new \DateTime() > $event->getDateDebut();
    }

    public function getArticleMontant($utilisateur)
    {
        return $this->getArticleMontantDefaut($utilisateur);
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
     * @param null|\UcaBundle\Entity\Ressource $ressource
     *
     * @return Reservabilite
     */
    public function setRessource(Ressource $ressource = null)
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
     * Set serie.
     *
     * @param null|\UcaBundle\Entity\DhtmlxSerie $serie
     *
     * @return Reservabilite
     */
    public function setSerie(DhtmlxSerie $serie = null)
    {
        $this->serie = $serie;

        return $this;
    }

    /**
     * Get serie.
     *
     * @return \UcaBundle\Entity\DhtmlxSerie|null
     */
    public function getSerie()
    {
        return $this->serie;
    }

    /**
     * Add inscription.
     *
     * @param \UcaBundle\Entity\Inscription $inscription
     *
     * @return Reservabilite
     */
    public function addInscription(Inscription $inscription)
    {
        $this->inscriptions[] = $inscription;

        return $this;
    }

    /**
     * Remove inscription.
     *
     * @param \UcaBundle\Entity\Inscription $inscription
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeInscription(Inscription $inscription)
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

    /**
     * Add profilsUtilisateur.
     *
     * @param \UcaBundle\Entity\ProfilUtilisateur $profilsUtilisateur
     *
     * @return Creneau
     */
    public function addProfilsUtilisateur(ReservabiliteProfilUtilisateur $profilsUtilisateur)
    {
        $this->profilsUtilisateurs[] = $profilsUtilisateur;

        return $this;
    }

    /**
     * Remove profilsUtilisateur.
     *
     * @param \UcaBundle\Entity\ProfilUtilisateur $profilsUtilisateur
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeProfilsUtilisateur(ReservabiliteProfilUtilisateur $profilsUtilisateur)
    {
        return $this->profilsUtilisateurs->removeElement($profilsUtilisateur);
    }

    /**
     * Get profilsUtilisateurs.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProfilsUtilisateurs()
    {
        return $this->profilsUtilisateurs;
    }

    /**
     * Set capacite.
     *
     * @param int $capacite
     *
     * @return Creneau
     */
    public function setCapacite($capacite)
    {
        $this->capacite = $capacite;

        return $this;
    }

    /**
     * Get capacite.
     *
     * @return int
     */
    public function getCapacite()
    {
        return $this->capacite;
    }

    public function getCapaciteProfil($profilUtilisateur)
    {
        $criteria = Criteria::create()->andWhere(Criteria::expr()->eq('profilUtilisateur', $profilUtilisateur));
        $result = $this->getProfilsUtilisateurs()->matching($criteria);

        return !$result->isEmpty() ? $result->first()->getCapaciteProfil() : false;
    }

    /**
     * Set evenement.
     *
     * @param null|\UcaBundle\Entity\DhtmlxEvenement $evenement
     *
     * @return Reservabilite
     */
    public function setEvenement(DhtmlxEvenement $evenement = null)
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
}