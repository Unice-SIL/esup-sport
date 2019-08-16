<?php

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class Lieu extends Ressource
{
    #region Propriétés
    /** @ORM\Column(type="string", nullable=true) */
    private $nomenclatureRus;

    /** @ORM\Column(type="decimal", nullable=true) */
    private $superficie;

    /** @ORM\Column(type="integer", nullable=true) */
    private $capacite;

    /** @ORM\Column(type="string", nullable=true) */
    private $latitude;

    /** @ORM\Column(type="string", nullable=true) */
    private $longitude;
    

    /** 
    * @ORM\ManyToMany(targetEntity="FormatActivite", mappedBy="lieu") 
    */
     private $formatsActivite;
    
    /** @ORM\Column(type="string", nullable=true) 
     * @Assert\NotBlank(message="lieu.adresse.notblank") 
    */
    private $adresse;

    /** @ORM\Column(type="string", nullable=true ,length=5) 
     * @Assert\NotBlank(message="lieu.codepostal.notblank") 
    */
    private $codePostal;

    /** @ORM\Column(type="string", nullable=true) 
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
}
