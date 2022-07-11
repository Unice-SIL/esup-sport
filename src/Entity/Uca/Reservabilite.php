<?php

/*
 * Classe - Réservabiltié:.
 *
 * Reservalité des ressoruces.
*/

namespace App\Entity\Uca;

use App\Service\Common\Fctn;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ReservabiliteRepository")
 * @Gedmo\Loggable
 */
class Reservabilite implements \App\Entity\Uca\Interfaces\JsonSerializable, \App\Entity\Uca\Interfaces\Article
{
    use \App\Entity\Uca\Traits\JsonSerializable;
    use \App\Entity\Uca\Traits\Article;

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
     *
     * @codeCoverageIgnore
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
        return Fctn::strTruncate(null !== $this->getEvenement() ? $this->getEvenement()->getDescription() : $this->getSerie()->getEvenements()->first()->getDescription(), 97);
    }

    public function getArticleDateDebut()
    {
        return null !== $this->getEvenement() ? $this->getEvenement()->getDateDebut() : $this->getSerie()->getDateDebut();
    }

    public function getArticleDateFin()
    {
        return null !== $this->getEvenement() ? $this->getEvenement()->getDateFin() : $this->getSerie()->getDateFin();
    }

    public function getAutorisations()
    {
        return $this->getFormatActivite()->getAutorisations();
    }

    public function getEncadrants()
    {
        return $this->getFormatActivite()->getEncadrants();
    }

    /**
     * Get formatActivite.
     *
     * @codeCoverageIgnore
     *
     * @return FormatActivite
     */
    public function getFormatActivite()
    {
        return $this->formatActivite;
    }

    /**
     * Set formatActivite.
     *
     * @codeCoverageIgnore
     *
     * @param $formatActivite
     *
     * @return Reservabilite
     */
    public function setFormatActivite($formatActivite)
    {
        $this->formatActivite = $formatActivite;

        return $this;
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
     * @codeCoverageIgnore
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set ressource.
     *
     * @return Reservabilite
     * @codeCoverageIgnore
     */
    public function setRessource(Ressource $ressource = null)
    {
        $this->ressource = $ressource;

        return $this;
    }

    /**
     * Get ressource.
     *
     * @return null|Ressource
     * @codeCoverageIgnore
     */
    public function getRessource()
    {
        return $this->ressource;
    }

    /**
     * Set serie.
     *
     * @param null|\App\Entity\Uca\DhtmlxSerie $serie
     *
     * @return Reservabilite
     * @codeCoverageIgnore
     */
    public function setSerie(DhtmlxSerie $serie = null)
    {
        $this->serie = $serie;

        return $this;
    }

    /**
     * Get serie.
     *
     * @return null|App\Entity\Uca\DhtmlxSerie
     * @codeCoverageIgnore
     */
    public function getSerie()
    {
        return $this->serie;
    }

    /**
     * Add inscription.
     *
     * @return Reservabilite
     * @codeCoverageIgnore
     */
    public function addInscription(Inscription $inscription)
    {
        $this->inscriptions[] = $inscription;

        return $this;
    }

    /**
     * Remove inscription.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeInscription(Inscription $inscription)
    {
        return $this->inscriptions->removeElement($inscription);
    }

    /**
     * Get inscriptions.
     *
     * @return \Doctrine\Common\Collections\Collection
     * @codeCoverageIgnore
     */
    public function getInscriptions()
    {
        return $this->inscriptions;
    }

    /**
     * Add profilsUtilisateur.
     *
     * @param \App\Entity\Uca\ProfilUtilisateur $profilsUtilisateur
     *
     * @return Creneau
     * @codeCoverageIgnore
     */
    public function addProfilsUtilisateur(ReservabiliteProfilUtilisateur $profilsUtilisateur)
    {
        $this->profilsUtilisateurs[] = $profilsUtilisateur;

        return $this;
    }

    /**
     * Remove profilsUtilisateur.
     *
     * @param \App\Entity\Uca\ProfilUtilisateur $profilsUtilisateur
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeProfilsUtilisateur(ReservabiliteProfilUtilisateur $profilsUtilisateur)
    {
        return $this->profilsUtilisateurs->removeElement($profilsUtilisateur);
    }

    /**
     * Get profilsUtilisateurs.
     *
     * @return \Doctrine\Common\Collections\Collection
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @param null|\App\Entity\Uca\DhtmlxEvenement $evenement
     *
     * @return Reservabilite
     * @codeCoverageIgnore
     */
    public function setEvenement(DhtmlxEvenement $evenement = null)
    {
        $this->evenement = $evenement;

        return $this;
    }

    /**
     * Get evenement.
     *
     * @return null|\App\Entity\Uca\DhtmlxEvenement
     * @codeCoverageIgnore
     */
    public function getEvenement()
    {
        return $this->evenement;
    }
}