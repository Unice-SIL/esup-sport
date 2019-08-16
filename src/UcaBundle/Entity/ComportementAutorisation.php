<?php

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

/**
 * @ORM\Entity
 * @Gedmo\Loggable
 */
class ComportementAutorisation implements \UcaBundle\Entity\Interfaces\JsonSerializable
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
     */
    protected $libelle;

    /** @ORM\Column(type="string", nullable=false) */
    protected $codeComportement;
    #endregion

    #region Méthodes

    public function jsonSerializeProperties()
    {
        return ['libelle', 'codeComportement'];
    }

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
     * @return ComportementAutorisation
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
     * Set codeComportement.
     *
     * @param string $codeComportement
     *
     * @return ComportementAutorisation
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
     */
    public function getCodeComportement()
    {
        return $this->codeComportement;
    }
}
