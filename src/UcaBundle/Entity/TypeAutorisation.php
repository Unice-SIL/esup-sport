<?php

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @Gedmo\Loggable
 * @UniqueEntity(fields={"libelle"}, message="typeautorisation.uniqueentity")
 */
class TypeAutorisation implements \UcaBundle\Entity\Interfaces\JsonSerializable, \UcaBundle\Entity\Interfaces\Article, \UcaBundle\Entity\Interfaces\Tarifable
{
    use \UcaBundle\Entity\Traits\JsonSerializable;
    #region Propriétés
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

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

    /** 
     * @Gedmo\Translatable
     * @ORM\Column(type="text", nullable=true)
     */
    private $informationsComplementaires;

    /** @ORM\ManyToMany(targetEntity="FormatActivite", mappedBy="autorisations") */
    private $formatsActivite;

    /** @ORM\OneToMany(targetEntity="FormatAchatCarte",mappedBy="carte") */
    private $formatsAchatCarte;

    #endregion

    #region Méthodes

    public function jsonSerializeProperties()
    {
        return ['libelle', 'tarif', 'comportement', 'informationsComplementaires'];
    }

    public function getArticleLibelle()
    {
        return $this->libelle;
    }

    public function getArticleTarif()
    {
        return $this->tarif;
    }

    public function getArticleDescription()
    {
        return 'type.autorisation.panier.description';
    }

    public function getMontant($user)
    {
        if (!empty($this->tarif)) {
            return $this->getTarif()->getUserMontant($user->getProfil()->getId())->getMontant();
        } else {
            return 0;
        }
    }
    #endregion

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->formatsActivite = new \Doctrine\Common\Collections\ArrayCollection();
        $this->formatsAchatCarte = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @param string|null $informationsComplementaires
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
     * @param \UcaBundle\Entity\Tarif|null $tarif
     *
     * @return TypeAutorisation
     */
    public function setTarif(\UcaBundle\Entity\Tarif $tarif = null)
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
     * @param \UcaBundle\Entity\ComportementAutorisation|null $comportement
     *
     * @return TypeAutorisation
     */
    public function setComportement(\UcaBundle\Entity\ComportementAutorisation $comportement = null)
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
    public function addFormatsActivite(\UcaBundle\Entity\FormatActivite $formatsActivite)
    {
        $this->formatsActivite[] = $formatsActivite;

        return $this;
    }

    /**
     * Remove formatsActivite.
     *
     * @param \UcaBundle\Entity\FormatActivite $formatsActivite
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeFormatsActivite(\UcaBundle\Entity\FormatActivite $formatsActivite)
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
    public function addFormatsAchatCarte(\UcaBundle\Entity\FormatAchatCarte $formatsAchatCarte)
    {
        $this->formatsAchatCarte[] = $formatsAchatCarte;

        return $this;
    }

    /**
     * Remove formatsAchatCarte.
     *
     * @param \UcaBundle\Entity\FormatAchatCarte $formatsAchatCarte
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeFormatsAchatCarte(\UcaBundle\Entity\FormatAchatCarte $formatsAchatCarte)
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
}
