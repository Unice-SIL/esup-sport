<?php

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @ORM\Entity
 * @Vich\Uploadable 
 */
class ReferentielImmobilier
{
    #region Propriétés
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** 
     * @ORM\Column(type="string") 
     */
    private $libelle;

    /** 
     * @ORM\Column(type="string", nullable=true) 
     */
    private $description;

    /** 
     * @ORM\Column(type="string", nullable=true) 
     */
    private $codeRus;

    /** 
     * @ORM\Column(type="string", nullable=true) 
     */
    private $nomCampus;

    /** 
     * @ORM\Column(type="decimal", nullable=true) 
     */
    private $superficie;

    /** 
     * @ORM\Column(type="string", nullable=true) 
     */
    private $latitude;

    /** 
     * @ORM\Column(type="string", nullable=true) 
     */
    private $longitude;

    #endregion

    

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
     * @return ReferentielImmobilier
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
     * Set description.
     *
     * @param string|null $description
     *
     * @return ReferentielImmobilier
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set codeRus.
     *
     * @param string|null $codeRus
     *
     * @return ReferentielImmobilier
     */
    public function setCodeRus($codeRus = null)
    {
        $this->codeRus = $codeRus;

        return $this;
    }

    /**
     * Get codeRus.
     *
     * @return string|null
     */
    public function getCodeRus()
    {
        return $this->codeRus;
    }

    /**
     * Set nomCampus.
     *
     * @param string|null $nomCampus
     *
     * @return ReferentielImmobilier
     */
    public function setNomCampus($nomCampus = null)
    {
        $this->nomCampus = $nomCampus;

        return $this;
    }

    /**
     * Get nomCampus.
     *
     * @return string|null
     */
    public function getNomCampus()
    {
        return $this->nomCampus;
    }

    /**
     * Set superficie.
     *
     * @param string|null $superficie
     *
     * @return ReferentielImmobilier
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
     * Set latitude.
     *
     * @param string|null $latitude
     *
     * @return ReferentielImmobilier
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
     * @return ReferentielImmobilier
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
}
