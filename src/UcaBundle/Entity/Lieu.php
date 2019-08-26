<?php

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @Gedmo\Loggable
 */
class Lieu extends Ressource
{
    #region Propriétés
    /** @Gedmo\Versioned
     * @ORM\Column(type="string", nullable=true) */
    private $nomenclatureRus;

    /** @Gedmo\Versioned
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     * @Assert\Type(type="double", message="message.typeinvalide.double")
     */
    private $superficie;

    /** @Gedmo\Versioned
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Regex(pattern="/^\d+$/", message="message.typeinvalide.entier")
     * @Assert\GreaterThanOrEqual(value = 0)
     */
    private $capacite;

    /** @Gedmo\Versioned
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Type(type="double", message="message.typeinvalide.double")
     */
    private $latitude;

    /** @Gedmo\Versioned
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Type(type="double", message="message.typeinvalide.double")
     */
    private $longitude;
    

    /** 
    * @ORM\ManyToMany(targetEntity="FormatActivite", mappedBy="lieu") 
    */
     private $formatsActivite;
    
    /** @Gedmo\Versioned
     * @ORM\Column(type="string", nullable=true) 
     * @Assert\NotBlank(message="lieu.adresse.notblank") 
    */
    private $adresse;

    /** @Gedmo\Versioned
     * @ORM\Column(type="string", nullable=true ,length=5) 
     * @Assert\NotBlank(message="lieu.codepostal.notblank")
     * @Assert\Regex(pattern="/^[0-9]{5}$/", message="lieu.codepostal.invalide")
     */
    private $codePostal;

    /**  @Gedmo\Versioned
     * @ORM\Column(type="string", nullable=true) 
     * @Assert\NotBlank(message="lieu.ville.notblank") 
    */
    private $ville;

    #region Propriétés

    
    
    #endregion

    #region Méthodes
    #endregion

    

    /**
     * Set nomenclatureRus.
     *
     * @param string|null $nomenclatureRus
     *
     * @return Lieu
     */
    public function setNomenclatureRus($nomenclatureRus = null)
    {
        $this->nomenclatureRus = $nomenclatureRus;

        return $this;
    }

    /**
     * Get nomenclatureRus.
     *
     * @return string|null
     */
    public function getNomenclatureRus()
    {
        return $this->nomenclatureRus;
    }

    /**
     * Set superficie.
     *
     * @param string|null $superficie
     *
     * @return Lieu
     */
    public function setSuperficie($superficie = null)
    {
        $this->superficie = $superficie;

        return $this;
    }

    /**
     * Get superficie.
     *
     * @return string|null
     */
    public function getSuperficie()
    {
        return $this->superficie;
    }

    /**
     * Set capacite.
     *
     * @param int|null $capacite
     *
     * @return Lieu
     */
    public function setCapacite($capacite = null)
    {
        $this->capacite = $capacite;

        return $this;
    }

    /**
     * Get capacite.
     *
     * @return int|null
     */
    public function getCapacite()
    {
        return $this->capacite;
    }

    /**
     * Set latitude.
     *
     * @param string|null $latitude
     *
     * @return Lieu
     */
    public function setLatitude($latitude = null)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude.
     *
     * @return string|null
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude.
     *
     * @param string|null $longitude
     *
     * @return Lieu
     */
    public function setLongitude($longitude = null)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude.
     *
     * @return string|null
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Add formatsActivite.
     *
     * @param \UcaBundle\Entity\FormatActivite $formatsActivite
     *
     * @return Lieu
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
     * Set adresse.
     *
     * @param string|null $adresse
     *
     * @return Lieu
     */
    public function setAdresse($adresse = null)
    {
        $this->adresse = $adresse;

        return $this;
    }

    /**
     * Get adresse.
     *
     * @return string|null
     */
    public function getAdresse()
    {
        return $this->adresse;
    }

    /**
     * Set codePostal.
     *
     * @param string|null $codePostal
     *
     * @return Lieu
     */
    public function setCodePostal($codePostal = null)
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    /**
     * Get codePostal.
     *
     * @return string|null
     */
    public function getCodePostal()
    {
        return $this->codePostal;
    }

    /**
     * Set ville.
     *
     * @param string|null $ville
     *
     * @return Lieu
     */
    public function setVille($ville = null)
    {
        $this->ville = $ville;

        return $this;
    }

    /**
     * Get ville.
     *
     * @return string|null
     */
    public function getVille()
    {
        return $this->ville;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->formatsActivite = new \Doctrine\Common\Collections\ArrayCollection();
    }

}
