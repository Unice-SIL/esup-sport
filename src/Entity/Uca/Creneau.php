<?php

/*
 * Classe - Creneau:
 *
 * Représente un Créneau dans un format avec Créneau
 * Le créneau peut ou non être répétif ou unique.
 * Un utilisateur s'inscrit à un créneau.
*/

namespace App\Entity\Uca;

use App\Repository\EntityRepository;
use App\Service\Common\Fctn;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CreneauRepository")
 * @Gedmo\Loggable
 */
class Creneau implements \App\Entity\Uca\Interfaces\JsonSerializable, \App\Entity\Uca\Interfaces\Article
{
    use \App\Entity\Uca\Traits\JsonSerializable;
    use \App\Entity\Uca\Traits\Article;

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
     *
     * @codeCoverageIgnore
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
            $dateDebut = $this->getSerieEvenements()->last()->getDateDebut();
            $dateFin = $this->getSerieEvenements()->last()->getDateFin();

            return $this->getFormatActivite()->getLibelle()
                .' ['.Fctn::intlDateFormat($dateDebut, 'cccc')
                .' '.$dateDebut->format('H:i')
                .' - '.$dateFin->format('H:i').']';
        }

        return $this->getFormatActivite()->getLibelle()
            .' []';
    }

    public function getArticleDescription()
    {
        if ($this->getSerieEvenements()->first()) {
            return Fctn::strTruncate($this->getSerieEvenements()->first()->getDescription(), 97);
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
     * @codeCoverageIgnore
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

    /**
     * Set lieu.
     *
     * @return Creneau
     * @codeCoverageIgnore
     */
    public function setLieu(Lieu $lieu = null)
    {
        $this->lieu = $lieu;

        return $this;
    }

    /**
     * Get lieu.
     *
     * @return null|Lieu
     * @codeCoverageIgnore
     */
    public function getLieu()
    {
        return $this->lieu;
    }

    /**
     * Set formatActivite.
     *
     * @return Creneau
     * @codeCoverageIgnore
     */
    public function setFormatActivite(FormatAvecCreneau $formatActivite = null)
    {
        $this->formatActivite = $formatActivite;

        return $this;
    }

    /**
     * Get formatActivite.
     *
     * @return null|FormatAvecCreneau
     * @codeCoverageIgnore
     */
    public function getFormatActivite()
    {
        return $this->formatActivite;
    }

    /**
     * Add inscription.
     *
     * @return Creneau
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
     * Set serie.
     *
     * @return Creneau
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
     * @return null|DhtmlxSerie
     * @codeCoverageIgnore
     */
    public function getSerie()
    {
        return $this->serie;
    }

    /**
     * Add profilsUtilisateur.
     *
     * @param ProfilUtilisateur $profilsUtilisateur
     *
     * @return Creneau
     * @codeCoverageIgnore
     */
    public function addProfilsUtilisateur(CreneauProfilUtilisateur $profilsUtilisateur)
    {
        $this->profilsUtilisateurs[] = $profilsUtilisateur;

        return $this;
    }

    /**
     * Remove profilsUtilisateur.
     *
     * @param ProfilUtilisateur $profilsUtilisateur
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeProfilsUtilisateur(CreneauProfilUtilisateur $profilsUtilisateur)
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
     * Add niveauxSportifs.
     *
     * @return Creneau
     * @codeCoverageIgnore
     */
    public function addNiveauSportif(NiveauSportif $niveauxSportifs)
    {
        $this->niveauxSportifs[] = $niveauxSportifs;

        return $this;
    }

    /**
     * Remove niveauxSportifs.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeNiveauSportif(NiveauSportif $niveauxSportifs)
    {
        return $this->niveauxSportifs->removeElement($niveauxSportifs);
    }

    /**
     * Get niveauxSportifs.
     *
     * @return \Doctrine\Common\Collections\Collection
     * @codeCoverageIgnore
     */
    public function getNiveauxSportifs()
    {
        return $this->niveauxSportifs;
    }

    /**
     * Set tarif.
     *
     * @return Creneau
     * @codeCoverageIgnore
     */
    public function setTarif(Tarif $tarif = null)
    {
        $this->tarif = $tarif;

        return $this;
    }

    /**
     * Get tarif.
     *
     * @return null|Tarif
     * @codeCoverageIgnore
     */
    public function getTarif()
    {
        return $this->tarif;
    }

    /**
     * Add encadrant.
     *
     * @return Creneau
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
     * @codeCoverageIgnore
     */
    public function getEncadrants()
    {
        return $this->encadrants;
    }

    /**
     * Add niveauxSportif.
     *
     * @return Creneau
     * @codeCoverageIgnore
     */
    public function addNiveauxSportif(NiveauSportif $niveauxSportif)
    {
        $this->niveauxSportifs[] = $niveauxSportif;

        return $this;
    }

    /**
     * Remove niveauxSportif.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeNiveauxSportif(NiveauSportif $niveauxSportif)
    {
        return $this->niveauxSportifs->removeElement($niveauxSportif);
    }

    /**
     * Update listeEncadrants.
     *
     * @return Creneau
     * @codeCoverageIgnore
     */
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     */
    public function getListeEncadrants()
    {
        return $this->listeEncadrants;
    }
}
