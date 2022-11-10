<?php

/*
 * Classe - TypeAutorisation:
 *
 * Un type d'autorisation (cotisation, carte,..)
 * Il s'agit de l'autorisation 'physique'.
*/

namespace App\Entity\Uca;

use App\Annotations\CKEditor;
use App\Service\Common\Fctn;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use UcaBundle\Annotations\CKEditor;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TypeAutorisationRepository")
 * @Gedmo\Loggable
 * @UniqueEntity(fields={"libelle"}, message="typeautorisation.uniqueentity")
 */
class TypeAutorisation implements \App\Entity\Uca\Interfaces\JsonSerializable, \App\Entity\Uca\Interfaces\Article
{
    use \App\Entity\Uca\Traits\JsonSerializable;
    use \App\Entity\Uca\Traits\Article;

    /**
     * @Gedmo\Translatable
     * @Gedmo\Versioned
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="typeautorisation.libelle.notblank")
     */
    protected $libelle;

    /** @ORM\ManyToOne(targetEntity="Tarif", inversedBy="typesAutorisation") */
    protected $tarif;

    /**
     * @ORM\ManyToOne(targetEntity="ComportementAutorisation", fetch="EAGER")
     * @Assert\NotNull(message="typeautorisation.comportement.notnull")
     */
    protected $comportement;
    // region Propriétés
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Gedmo\Versioned
     * @Gedmo\Translatable
     * @ORM\Column(type="text", nullable=true)
     * @CKEditor
     */
    private $informationsComplementaires;

    /** @ORM\ManyToMany(targetEntity="FormatActivite", mappedBy="autorisations") */
    private $formatsActivite;

    /** @ORM\OneToMany(targetEntity="FormatAchatCarte",mappedBy="carte") */
    private $formatsAchatCarte;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="text", nullable=true)
     */
    private $tarifLibelle;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="text")
     */
    private $comportementLibelle;
    // endregion

    /**
     * Constructor.
     *
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        $this->formatsActivite = new \Doctrine\Common\Collections\ArrayCollection();
        $this->formatsAchatCarte = new \Doctrine\Common\Collections\ArrayCollection();
    }

    // endregion

    // region Méthodes

    public function jsonSerializeProperties()
    {
        return ['libelle', 'tarif', 'comportement', 'informationsComplementaires', 'montant'];
    }

    public function getArticleLibelle()
    {
        return $this->libelle;
    }

    public function getArticleDescription()
    {
        if (!is_null($this->informationsComplementaires)) {
            $description = $this->informationsComplementaires;
        } else {
            $description = $this->getComportement()->getDescriptionComportement();
        }

        return Fctn::strTruncate($description, 97);
    }

    public function getArticleDateDebut()
    {
        return null;
    }

    public function getArticleDateFin()
    {
        return null;
    }

    public function getAutorisations()
    {
        return new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getCapacite()
    {
        return null;
    }

    public function getInscriptions()
    {
        return new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getEncadrants()
    {
        return new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function updateTarifLibelle()
    {
        if (null != $this->getTarif()) {
            $this->tarifLibelle = $this->getTarif()->getLibelle();
        } else {
            $this->tarifLibelle = '';
        }

        return $this;
    }

    public function updateComportementLibelle()
    {
        $this->comportementLibelle = $this->getComportement()->getLibelle();

        return $this;
    }

    public function getArticleMontant($utilisateur)
    {
        if (!in_array($this->comportement->getCodeComportement(), ['carte', 'cotisation'])) {
            return 0;
        }

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
     * Set libelle.
     *
     * @param string $libelle
     *
     * @return TypeAutorisation
     * @codeCoverageIgnore
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelle.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Set informationsComplementaires.
     *
     * @param null|string $informationsComplementaires
     *
     * @return TypeAutorisation
     * @codeCoverageIgnore
     */
    public function setInformationsComplementaires($informationsComplementaires = null)
    {
        $this->informationsComplementaires = $informationsComplementaires;

        return $this;
    }

    /**
     * Get informationsComplementaires.
     *
     * @return null|string
     * @codeCoverageIgnore
     */
    public function getInformationsComplementaires()
    {
        return $this->informationsComplementaires;
    }

    /**
     * Set tarif.
     *
     * @return TypeAutorisation
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
     * Set comportement.
     *
     * @return TypeAutorisation
     * @codeCoverageIgnore
     */
    public function setComportement(ComportementAutorisation $comportement = null)
    {
        $this->comportement = $comportement;

        return $this;
    }

    /**
     * Get comportement.
     *
     * @return null|ComportementAutorisation
     * @codeCoverageIgnore
     */
    public function getComportement()
    {
        return $this->comportement;
    }

    /**
     * Add formatsActivite.
     *
     * @return TypeAutorisation
     * @codeCoverageIgnore
     */
    public function addFormatsActivite(FormatActivite $formatsActivite)
    {
        $this->formatsActivite[] = $formatsActivite;

        return $this;
    }

    /**
     * Remove formatsActivite.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeFormatsActivite(FormatActivite $formatsActivite)
    {
        return $this->formatsActivite->removeElement($formatsActivite);
    }

    /**
     * Get formatsActivite.
     *
     * @return \Doctrine\Common\Collections\Collection
     * @codeCoverageIgnore
     */
    public function getFormatsActivite()
    {
        return $this->formatsActivite;
    }

    /**
     * Add formatsAchatCarte.
     *
     * @return TypeAutorisation
     * @codeCoverageIgnore
     */
    public function addFormatsAchatCarte(FormatAchatCarte $formatsAchatCarte)
    {
        $this->formatsAchatCarte[] = $formatsAchatCarte;

        return $this;
    }

    /**
     * Remove formatsAchatCarte.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeFormatsAchatCarte(FormatAchatCarte $formatsAchatCarte)
    {
        return $this->formatsAchatCarte->removeElement($formatsAchatCarte);
    }

    /**
     * Get formatsAchatCarte.
     *
     * @return \Doctrine\Common\Collections\Collection
     * @codeCoverageIgnore
     */
    public function getFormatsAchatCarte()
    {
        return $this->formatsAchatCarte;
    }

    /**
     * Set tarifLibelle.
     *
     * @param string $tarifLibelle
     *
     * @return TypeAutorisation
     * @codeCoverageIgnore
     */
    public function setTarifLibelle($tarifLibelle)
    {
        $this->tarifLibelle = $tarifLibelle;

        return $this;
    }

    /**
     * Get tarifLibelle.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getTarifLibelle()
    {
        return $this->tarifLibelle;
    }

    /**
     * Set comportementLibelle.
     *
     * @param string $comportementLibelle
     *
     * @return TypeAutorisation
     * @codeCoverageIgnore
     */
    public function setComportementLibelle($comportementLibelle)
    {
        $this->comportementLibelle = $comportementLibelle;

        return $this;
    }

    /**
     * Get comportementLibelle.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getComportementLibelle()
    {
        return $this->comportementLibelle;
    }
}
