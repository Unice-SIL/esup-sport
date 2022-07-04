<?php

/*
 * Classe - TypeAutorisation:
 *
 * Un type d'autorisation (cotisation, carte,..)
 * Il s'agit de l'autorisation 'physique'.
*/

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use UcaBundle\Annotations\CKEditor;
use UcaBundle\Service\Common\Fn;

/**
 * @ORM\Entity
 * @Gedmo\Loggable
 * @UniqueEntity(fields={"libelle"}, message="typeautorisation.uniqueentity")
 * @ORM\EntityListeners({"UcaBundle\Service\Listener\Entity\TypeAutorisationListener"})
 */
class TypeAutorisation implements \UcaBundle\Entity\Interfaces\JsonSerializable, \UcaBundle\Entity\Interfaces\Article
{
    use \UcaBundle\Entity\Traits\JsonSerializable;
    use \UcaBundle\Entity\Traits\Article;

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
    //region Propriétés
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
     * @ORM\Column(type="text")
     */
    private $tarifLibelle;
    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="text")
     */
    private $comportementLibelle;
    //endregion

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->formatsActivite = new \Doctrine\Common\Collections\ArrayCollection();
        $this->formatsAchatCarte = new \Doctrine\Common\Collections\ArrayCollection();
    }

    //endregion

    //region Méthodes

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

        return Fn::strTruncate($description, 97);
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
     */
    public function setInformationsComplementaires($informationsComplementaires = null)
    {
        $this->informationsComplementaires = $informationsComplementaires;

        return $this;
    }

    /**
     * Get informationsComplementaires.
     *
     * @return string|null
     */
    public function getInformationsComplementaires()
    {
        return $this->informationsComplementaires;
    }

    /**
     * Set tarif.
     *
     * @param null|\UcaBundle\Entity\Tarif $tarif
     *
     * @return TypeAutorisation
     */
    public function setTarif(Tarif $tarif = null)
    {
        $this->tarif = $tarif;

        return $this;
    }

    /**
     * Get tarif.
     *
     * @return \UcaBundle\Entity\Tarif|null
     */
    public function getTarif()
    {
        return $this->tarif;
    }

    /**
     * Set comportement.
     *
     * @param null|\UcaBundle\Entity\ComportementAutorisation $comportement
     *
     * @return TypeAutorisation
     */
    public function setComportement(ComportementAutorisation $comportement = null)
    {
        $this->comportement = $comportement;

        return $this;
    }

    /**
     * Get comportement.
     *
     * @return \UcaBundle\Entity\ComportementAutorisation|null
     */
    public function getComportement()
    {
        return $this->comportement;
    }

    /**
     * Add formatsActivite.
     *
     * @param \UcaBundle\Entity\FormatActivite $formatsActivite
     *
     * @return TypeAutorisation
     */
    public function addFormatsActivite(FormatActivite $formatsActivite)
    {
        $this->formatsActivite[] = $formatsActivite;

        return $this;
    }

    /**
     * Remove formatsActivite.
     *
     * @param \UcaBundle\Entity\FormatActivite $formatsActivite
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeFormatsActivite(FormatActivite $formatsActivite)
    {
        return $this->formatsActivite->removeElement($formatsActivite);
    }

    /**
     * Get formatsActivite.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFormatsActivite()
    {
        return $this->formatsActivite;
    }

    /**
     * Add formatsAchatCarte.
     *
     * @param \UcaBundle\Entity\FormatAchatCarte $formatsAchatCarte
     *
     * @return TypeAutorisation
     */
    public function addFormatsAchatCarte(FormatAchatCarte $formatsAchatCarte)
    {
        $this->formatsAchatCarte[] = $formatsAchatCarte;

        return $this;
    }

    /**
     * Remove formatsAchatCarte.
     *
     * @param \UcaBundle\Entity\FormatAchatCarte $formatsAchatCarte
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeFormatsAchatCarte(FormatAchatCarte $formatsAchatCarte)
    {
        return $this->formatsAchatCarte->removeElement($formatsAchatCarte);
    }

    /**
     * Get formatsAchatCarte.
     *
     * @return \Doctrine\Common\Collections\Collection
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
     */
    public function getComportementLibelle()
    {
        return $this->comportementLibelle;
    }
}
