<?php

/*
 * Classe - Lieu:
 *
 * Un lieu est une ressource (hérité).
 * Il correspondents aux salles (réservables ou non).
*/

namespace App\Entity\Uca;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LieuRepository")
 * @Gedmo\Loggable
 */
class Lieu extends Ressource
{
    //region Propriétés

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
    private $capaciteAccueil;

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

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank(message="lieu.ville.notblank")
     */
    private $ville;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $accesPMR;

    /** @ORM\OneToMany(targetEntity="ImageSupplementaire", mappedBy="lieu" , fetch="EXTRA_LAZY", orphanRemoval=true, cascade={"persist","remove"}) */
    private $imagesSupplementaires;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $visiteVirtuelle;

    //endregion
    //region Méthodes

    public function getCapacite()
    {
        return 1;
    }

    //endregion

    /**
     * Set nomenclatureRus.
     *
     * @param null|string $nomenclatureRus
     *
     * @return Lieu
     * @codeCoverageIgnore
     */
    public function setNomenclatureRus($nomenclatureRus = null)
    {
        $this->nomenclatureRus = $nomenclatureRus;

        return $this;
    }

    /**
     * Get nomenclatureRus.
     *
     * @return null|string
     * @codeCoverageIgnore
     */
    public function getNomenclatureRus()
    {
        return $this->nomenclatureRus;
    }

    /**
     * Set superficie.
     *
     * @param null|string $superficie
     *
     * @return Lieu
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
     * Set capaciteAccueil.
     *
     * @param null|int $capaciteAccueil
     *
     * @return Lieu
     * @codeCoverageIgnore
     */
    public function setCapaciteAccueil($capaciteAccueil = null)
    {
        $this->capaciteAccueil = $capaciteAccueil;

        return $this;
    }

    /**
     * Get capaciteAccueil.
     *
     * @return null|int
     * @codeCoverageIgnore
     */
    public function getCapaciteAccueil()
    {
        return $this->capaciteAccueil;
    }

    /**
     * Set latitude.
     *
     * @param null|string $latitude
     *
     * @return Lieu
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
     * @return Lieu
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
     * Set adresse.
     *
     * @param null|string $adresse
     *
     * @return Lieu
     * @codeCoverageIgnore
     */
    public function setAdresse($adresse = null)
    {
        $this->adresse = $adresse;

        return $this;
    }

    /**
     * Get adresse.
     *
     * @return null|string
     * @codeCoverageIgnore
     */
    public function getAdresse()
    {
        return $this->adresse;
    }

    /**
     * Set codePostal.
     *
     * @param null|string $codePostal
     *
     * @return Lieu
     * @codeCoverageIgnore
     */
    public function setCodePostal($codePostal = null)
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    /**
     * Get codePostal.
     *
     * @return null|string
     * @codeCoverageIgnore
     */
    public function getCodePostal()
    {
        return $this->codePostal;
    }

    /**
     * Set ville.
     *
     * @param null|string $ville
     *
     * @return Lieu
     * @codeCoverageIgnore
     */
    public function setVille($ville = null)
    {
        $this->ville = $ville;

        return $this;
    }

    /**
     * Get ville.
     *
     * @return null|string
     * @codeCoverageIgnore
     */
    public function getVille()
    {
        return $this->ville;
    }

    /**
     * Add formatsActivite.
     *
     * @return Lieu
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
     * Set accesPMR.
     *
     * @param null|bool $accesPMR
     *
     * @return Lieu
     * @codeCoverageIgnore
     */
    public function setAccesPMR($accesPMR = null)
    {
        $this->accesPMR = $accesPMR;

        return $this;
    }

    /**
     * Get accesPMR.
     *
     * @return null|bool
     * @codeCoverageIgnore
     */
    public function getAccesPMR()
    {
        return $this->accesPMR;
    }

    /**
     * Add imagesSupplementaire.
     *
     * @return Lieu
     * @codeCoverageIgnore
     */
    public function addImagesSupplementaire(ImageSupplementaire $imagesSupplementaire)
    {
        $this->imagesSupplementaires[] = $imagesSupplementaire;
        if (null == $imagesSupplementaire->getLieu()) {
            $imagesSupplementaire->setLieu($this);
        }

        return $this;
    }

    /**
     * Remove imagesSupplementaire.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeImagesSupplementaires(ImageSupplementaire $imagesSupplementaire)
    {
        return $this->imagesSupplementaires->removeElement($imagesSupplementaire);
    }

    /**
     * Get imagesSupplementaires.
     *
     * @return \Doctrine\Common\Collections\Collection
     * @codeCoverageIgnore
     */
    public function getImagesSupplementaires()
    {
        return $this->imagesSupplementaires;
    }

    /**
     * Remove imagesSupplementaire.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     * @codeCoverageIgnore
     */
    public function removeImagesSupplementaire(ImageSupplementaire $imagesSupplementaire)
    {
        return $this->imagesSupplementaires->removeElement($imagesSupplementaire);
    }

    /**
     * Set visiteVirtuelle.
     *
     * @param null|mixed $visiteVirtuelle
     *
     * @return Lieu
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