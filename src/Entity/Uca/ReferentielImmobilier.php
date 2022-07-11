<?php

/*
 * Classe - ReferentielImmobilier:
 *
 * Classe dédié à la gestion de l'import du référentiel.
*/

namespace App\Entity\Uca;

use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ReferentielImmobilierRepository")
 * @Vich\Uploadable
 */
class ReferentielImmobilier
{
    //region Propriétés
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
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
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

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $capacite;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $visiteVirtuelle;

    //endregion

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
     * @return ReferentielImmobilier
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
     * Set description.
     *
     * @param null|string $description
     *
     * @return ReferentielImmobilier
     * @codeCoverageIgnore
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return null|string
     * @codeCoverageIgnore
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set codeRus.
     *
     * @param null|string $codeRus
     *
     * @return ReferentielImmobilier
     * @codeCoverageIgnore
     */
    public function setCodeRus($codeRus = null)
    {
        $this->codeRus = $codeRus;

        return $this;
    }

    /**
     * Get codeRus.
     *
     * @return null|string
     * @codeCoverageIgnore
     */
    public function getCodeRus()
    {
        return $this->codeRus;
    }

    /**
     * Set nomCampus.
     *
     * @param null|string $nomCampus
     *
     * @return ReferentielImmobilier
     * @codeCoverageIgnore
     */
    public function setNomCampus($nomCampus = null)
    {
        $this->nomCampus = $nomCampus;

        return $this;
    }

    /**
     * Get nomCampus.
     *
     * @return null|string
     * @codeCoverageIgnore
     */
    public function getNomCampus()
    {
        return $this->nomCampus;
    }

    /**
     * Set superficie.
     *
     * @param null|string $superficie
     *
     * @return ReferentielImmobilier
     * @codeCoverageIgnore
     */
    public function setSuperficie($superficie = null)
    {
        $this->superficie = $superficie;

        return $this;
    }

    /**
     * Get superficie.
     *
     * @return null|string
     * @codeCoverageIgnore
     */
    public function getSuperficie()
    {
        return $this->superficie;
    }

    /**
     * Set latitude.
     *
     * @param null|string $latitude
     *
     * @return ReferentielImmobilier
     * @codeCoverageIgnore
     */
    public function setLatitude($latitude = null)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude.
     *
     * @return null|string
     * @codeCoverageIgnore
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude.
     *
     * @param null|string $longitude
     *
     * @return ReferentielImmobilier
     * @codeCoverageIgnore
     */
    public function setLongitude($longitude = null)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude.
     *
     * @return null|string
     * @codeCoverageIgnore
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set capacite.
     *
     * @param null|int $capacite
     *
     * @return ReferentielImmobilier
     * @codeCoverageIgnore
     */
    public function setCapacite($capacite = null)
    {
        $this->capacite = $capacite;

        return $this;
    }

    /**
     * Get capacite.
     *
     * @return null|int
     * @codeCoverageIgnore
     */
    public function getCapacite()
    {
        return $this->capacite;
    }

    /**
     * Set visiteVirtuelle.
     *
     * @param null|mixed $visiteVirtuelle
     *
     * @return ReferentielImmobilier
     * @codeCoverageIgnore
     */
    public function setVisiteVirtuelle($visiteVirtuelle = null)
    {
        $this->visiteVirtuelle = $visiteVirtuelle;

        return $this;
    }

    /**
     * Get visiteVirtuelle.
     *
     * @return null|string
     * @codeCoverageIgnore
     */
    public function getVisiteVirtuelle()
    {
        return $this->visiteVirtuelle;
    }
}