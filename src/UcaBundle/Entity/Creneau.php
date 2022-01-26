<?php

/*
 * Classe - Creneau:
 *
 * Représente un Créneau dans un format avec Créneau
 * Le créneau peut ou non être répétif ou unique.
 * Un utilisateur s'inscrit à un créneau.
*/

namespace UcaBundle\Entity;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use UcaBundle\Repository\EntityRepository;
use UcaBundle\Service\Common\Fn;

/**
 * @ORM\Entity(repositoryClass="UcaBundle\Repository\CreneauRepository")
 * @Gedmo\Loggable
 * @ORM\EntityListeners({"UcaBundle\Service\Listener\Entity\CreneauListener"})
 */
class Creneau implements \UcaBundle\Entity\Interfaces\JsonSerializable, \UcaBundle\Entity\Interfaces\Article
{
    use \UcaBundle\Entity\Traits\JsonSerializable;
    use \UcaBundle\Entity\Traits\Article;

    //region Propriétés
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Gedmo\Versioned
     * @ORM\ManyToOne(targetEntity="Lieu")
     */
    private $lieu;

    /** @ORM\ManyToOne(targetEntity="FormatAvecCreneau", inversedBy="creneaux") */
    private $formatActivite;

    /** @ORM\OneToMany(targetEntity="Inscription", mappedBy="creneau") */
    private $inscriptions;

    /**
     * @ORM\OneToOne(targetEntity="DhtmlxSerie", mappedBy="creneau")
     */
    private $serie;

    //endregion

    //region Propriétés communes FormatActivite

    /** @ORM\OneToMany(targetEntity="CreneauProfilUtilisateur", mappedBy="creneau", cascade={"persist", "remove"}, fetch="LAZY")
     * @Assert\NotBlank(message="complement.profilsutilisateurs.notblank") */
    private $profilsUtilisateurs;

    /** @ORM\ManyToMany(targetEntity="NiveauSportif", inversedBy="creneaux", fetch="EAGER")
     * @Assert\NotBlank(message="complement.niveauSportif.notblank") */
    private $niveauxSportifs;

    /**
     * @Gedmo\Versioned
     * @ORM\ManyToOne(targetEntity="Tarif", inversedBy="creneaux", fetch="EAGER")
     * @Assert\Expression("!this.getEstPayant() || this.getTarif()", message="complement.tarif.notblank")
     */
    private $tarif;

    /**
     * @ORM\ManyToMany(targetEntity="Utilisateur", inversedBy="creneaux")
     * @Assert\Expression("!this.getEstEncadre() || this.getEncadrants()", message="complement.encadrant.notblank") */
    private $encadrants;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="complement.capacite.notblank")
     */
    private $capacite;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="text")
     */
    private $listeEncadrants;

    //endregion

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->inscriptions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->profilsUtilisateurs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->encadrants = new \Doctrine\Common\Collections\ArrayCollection();
    }

    //endregion

    //region Méthodes

    public function jsonSerializeProperties()
    {
        return ['capacite', 'tarif', 'profilsUtilisateurs', 'encadrants', 'niveauxSportifs', 'lieu', 'formatActivite'];
    }

    public function getSerieEvenements()
    {
        return $this->getSerie()->getEvenements()->matching(EntityRepository::criteriaBy([['dependanceSerie', 'eq', true]]));
    }

    public function getArticleLibelle()
    {
        if (false !== $this->getSerieEvenements()->first()) {
            $dateDebut = $this->getSerieEvenements()->first()->getDateDebut();
            $dateFin = $this->getSerieEvenements()->first()->getDateFin();

            return $this->getFormatActivite()->getLibelle()
                .' ['.Fn::intlDateFormat($dateDebut, 'cccc')
                .' '.$dateDebut->format('H:i')
                .' - '.$dateFin->format('H:i').']';
        }

        return $this->getFormatActivite()->getLibelle()
            .' []';
    }

    public function getArticleDescription()
    {
        if ($this->getSerieEvenements()->first()) {
            return Fn::strTruncate($this->getSerieEvenements()->first()->getDescription(), 97);
        }

        return '';
    }

    public function getArticleDateDebut()
    {
        return $this->getFormatActivite()->getArticleDateDebut();
    }

    public function getArticleDateFin()
    {
        return $this->getFormatActivite()->getArticleDateFin();
    }

    public function getArticleAutorisations()
    {
        return $this->formatActivite->getAutorisations();
    }

    public function getDateDebutInscription()
    {
        return $this->formatActivite->getDateDebutInscription();
    }

    public function getDateFinInscription()
    {
        return $this->formatActivite->getDateFinInscription();
    }

    public function getArticleMontant($utilisateur)
    {
        return $this->getArticleMontantDefaut($utilisateur);
    }

    public function getInscriptionsValidee()
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('statut', 'valide'))
        ;

        return $this->getInscriptions()->matching($criteria);
    }

    public function getAllInscriptions()
    {
        $criteria = Criteria::create()
            ->orWhere(Criteria::expr()->eq('statut', 'valide'))
            ->orWhere(Criteria::expr()->eq('statut', 'attentepaiement'))
            ->orWhere(Criteria::expr()->eq('statut', 'attentevalidationencadrant'))
            ->orWhere(Criteria::expr()->eq('statut', 'attenteajoutpanier'))
            ->orWhere(Criteria::expr()->eq('statut', 'attentevalidationgestionnaire'))
        ;

        return $this->getInscriptions()->matching($criteria);
    }

    public function getCapaciteTousProfil()
    {
        $capaciteTotale = 0;

        foreach ($this->getProfilsUtilisateurs() as $creneauProfil) {
            $capaciteTotale += (is_integer($creneauProfil->getCapaciteProfil()) ? $creneauProfil->getCapaciteProfil() : 0);
        }

        return $capaciteTotale;
    }

    public function getCapaciteProfil($profilUtilisateur)
    {
        $criteria = Criteria::create()->andWhere(Criteria::expr()->eq('profilUtilisateur', $profilUtilisateur));
        $result = $this->getProfilsUtilisateurs()->matching($criteria);
        if (!$result->isEmpty()) {
            return $result->first()->getCapaciteProfil();
        }

        return false;
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

    /**
     * Set lieu.
     *
     * @param null|\UcaBundle\Entity\Lieu $lieu
     *
     * @return Creneau
     */
    public function setLieu(Lieu $lieu = null)
    {
        $this->lieu = $lieu;

        return $this;
    }

    /**
     * Get lieu.
     *
     * @return null|\UcaBundle\Entity\Lieu
     */
    public function getLieu()
    {
        return $this->lieu;
    }

    /**
     * Set formatActivite.
     *
     * @param null|\UcaBundle\Entity\FormatAvecCreneau $formatActivite
     *
     * @return Creneau
     */
    public function setFormatActivite(FormatAvecCreneau $formatActivite = null)
    {
        $this->formatActivite = $formatActivite;

        return $this;
    }

    /**
     * Get formatActivite.
     *
     * @return null|\UcaBundle\Entity\FormatAvecCreneau
     */
    public function getFormatActivite()
    {
        return $this->formatActivite;
    }

    /**
     * Add inscription.
     *
     * @param \UcaBundle\Entity\Inscription $inscription
     *
     * @return Creneau
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
     * Set serie.
     *
     * @param null|\UcaBundle\Entity\DhtmlxSerie $serie
     *
     * @return Creneau
     */
    public function setSerie(DhtmlxSerie $serie = null)
    {
        $this->serie = $serie;

        return $this;
    }

    /**
     * Get serie.
     *
     * @return null|\UcaBundle\Entity\DhtmlxSerie
     */
    public function getSerie()
    {
        return $this->serie;
    }

    /**
     * Add profilsUtilisateur.
     *
     * @param \UcaBundle\Entity\ProfilUtilisateur $profilsUtilisateur
     *
     * @return Creneau
     */
    public function addProfilsUtilisateur(CreneauProfilUtilisateur $profilsUtilisateur)
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
    public function removeProfilsUtilisateur(CreneauProfilUtilisateur $profilsUtilisateur)
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
     * Add niveauxSportifs.
     *
     * @param \UcaBundle\Entity\NiveauSportif $niveauxSportifs
     *
     * @return Creneau
     */
    public function addNiveauSportif(NiveauSportif $niveauxSportifs)
    {
        $this->niveauxSportifs[] = $niveauxSportifs;

        return $this;
    }

    /**
     * Remove niveauxSportifs.
     *
     * @param \UcaBundle\Entity\NiveauSportif $niveauxSportifs
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeNiveauSportif(NiveauSportif $niveauxSportifs)
    {
        return $this->niveauxSportifs->removeElement($niveauxSportifs);
    }

    /**
     * Get niveauxSportifs.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNiveauxSportifs()
    {
        return $this->niveauxSportifs;
    }

    /**
     * Set tarif.
     *
     * @param null|\UcaBundle\Entity\Tarif $tarif
     *
     * @return Creneau
     */
    public function setTarif(Tarif $tarif = null)
    {
        $this->tarif = $tarif;

        return $this;
    }

    /**
     * Get tarif.
     *
     * @return null|\UcaBundle\Entity\Tarif
     */
    public function getTarif()
    {
        return $this->tarif;
    }

    /**
     * Add encadrant.
     *
     * @param \UcaBundle\Entity\Utilisateur $encadrant
     *
     * @return Creneau
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
     * Add niveauxSportif.
     *
     * @param \UcaBundle\Entity\NiveauSportif $niveauxSportif
     *
     * @return Creneau
     */
    public function addNiveauxSportif(NiveauSportif $niveauxSportif)
    {
        $this->niveauxSportifs[] = $niveauxSportif;

        return $this;
    }

    /**
     * Remove niveauxSportif.
     *
     * @param \UcaBundle\Entity\NiveauSportif $niveauxSportif
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeNiveauxSportif(NiveauSportif $niveauxSportif)
    {
        return $this->niveauxSportifs->removeElement($niveauxSportif);
    }

    public function updateListeEncadrants()
    {
        $this->listeEncadrants = '';
        foreach ($this->getEncadrants() as $encadrant) {
            if (!empty($this->listeEncadrants)) {
                $this->listeEncadrants .= ', ';
            }
            $this->listeEncadrants .= $encadrant->getPrenom().' '.$encadrant->getNom();
        }

        return $this;
    }

    /**
     * Set listeEncadrants.
     *
     * @param string $listeEncadrants
     *
     * @return FormatActivite
     */
    public function setListeEncadrants($listeEncadrants)
    {
        $this->listeEncadrants = $listeEncadrants;

        return $this;
    }

    /**
     * Get listeEncadrants.
     *
     * @return string
     */
    public function getListeEncadrants()
    {
        return $this->listeEncadrants;
    }
}
