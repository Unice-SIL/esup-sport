<?php

/*
 * Classe - Lieu:
 *
 * Un lieu est une ressource (hérité).
 * Il correspondents aux salles (réservables ou non).
*/

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
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
     * @param null|string $superficie
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
     * Set capaciteAccueil.
     *
     * @param null|int $capaciteAccueil
     *
     * @return Lieu
     */
    public function setCapaciteAccueil($capaciteAccueil = null)
    {
        $this->capaciteAccueil = $capaciteAccueil;

        return $this;
    }

    /**
     * Get capaciteAccueil.
     *
     * @return int|null
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
     * @param null|string $longitude
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
     * Set adresse.
     *
     * @param null|string $adresse
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
     * @param null|string $codePostal
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
     * @param null|string $ville
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
     * Add formatsActivite.
     *
     * @param \UcaBundle\Entity\FormatActivite $formatsActivite
     *
     * @return Lieu
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
     * Set accesPMR.
     *
     * @param null|bool $accesPMR
     *
     * @return Lieu
     */
    public function setAccesPMR($accesPMR = null)
    {
        $this->accesPMR = $accesPMR;

        return $this;
    }

    /**
     * Get accesPMR.
     *
     * @return bool|null
     */
    public function getAccesPMR()
    {
        return $this->accesPMR;
    }

    /**
     * Add imagesSupplementaire.
     *
     * @param \UcaBundle\Entity\ImageSupplementaire $imagesSupplementaire
     *
     * @return Lieu
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
     * @param \UcaBundle\Entity\ImageSupplementaire $imagesSupplementaire
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeImagesSupplementaires(ImageSupplementaire $imagesSupplementaire)
    {
        return $this->imagesSupplementaires->removeElement($imagesSupplementaire);
    }

    /**
     * Get imagesSupplementaires.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getImagesSupplementaires()
    {
        return $this->imagesSupplementaires;
    }

    /**
     * Remove imagesSupplementaire.
     *
     * @param \UcaBundle\Entity\ImageSupplementaire $imagesSupplementaire
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
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
     */
    public function setVisiteVirtuelle($visiteVirtuelle = null)
    {
        $this->visiteVirtuelle = $visiteVirtuelle;

        return $this;
    }

    /**
     * Get visiteVirtuelle.
     *
     * @return string|null
     */
    public function getVisiteVirtuelle()
    {
        return $this->visiteVirtuelle;
    }
}
