<?php

/*
 * Classe - ComportementAUtorisation:
 *
 * Un Type d'Autorisation va avoir un comportement (Achat de carte, cotisation)
 * Cette enité permet d'organiser ces comportements
 * Ces informations ne sont pas éditables dans l'outil.
*/

namespace App\Entity\Uca;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ComportementAutorisationRepository")
 */
class ComportementAutorisation implements \App\Entity\Uca\Interfaces\JsonSerializable
{
    use \App\Entity\Uca\Traits\JsonSerializable;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="string")
     */
    protected $libelle;

    /** @ORM\Column(type="string", nullable=false) */
    protected $codeComportement;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="string", nullable=true) */
    protected $descriptionComportement;

    //region Propriétés
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    //endregion

    //region Méthodes

    public function jsonSerializeProperties()
    {
        return ['libelle', 'codeComportement'];
    }

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
     * @return ComportementAutorisation
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
     * Set codeComportement.
     *
     * @param string $codeComportement
     *
     * @return ComportementAutorisation
     * @codeCoverageIgnore
     */
    public function setCodeComportement($codeComportement)
    {
        $this->codeComportement = $codeComportement;

        return $this;
    }

    /**
     * Get codeComportement.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getCodeComportement()
    {
        return $this->codeComportement;
    }

    /**
     * Set descriptionComportement.
     *
     * @param null|string $descriptionComportement
     *
     * @return ComportementAutorisation
     * @codeCoverageIgnore
     */
    public function setDescriptionComportement($descriptionComportement = null)
    {
        $this->descriptionComportement = $descriptionComportement;

        return $this;
    }

    /**
     * Get descriptionComportement.
     *
     * @return null|string
     * @codeCoverageIgnore
     */
    public function getDescriptionComportement()
    {
        return $this->descriptionComportement;
    }
}