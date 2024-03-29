<?php

/*
 * Classe - Texte:
 *
 * Permet de modifier le contenu afficher sur le site à certains endroits.
*/

namespace App\Entity\Uca;

use App\Annotations\CKEditor;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="texte")
 * @ORM\Entity(repositoryClass="App\Repository\TexteRepository")
 * @Gedmo\Loggable
 */
class Texte
{
    //region Propriétés

    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $emplacement;

    /**
     * @Gedmo\Translatable
     * @Gedmo\Versioned
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="texte.titre.notblank")
     */
    private $titre;

    /**
     * @Gedmo\Translatable
     * @Gedmo\Versioned
     * @ORM\Column(type="text", nullable=true)
     * @CKEditor
     * @Assert\Expression("this.getMobile() !== 1 || this.getTexte() !== null", message="texte.texte.notblank")
     */
    private $texte;

    /**
     * @ORM\Column(type="integer")
     * @Gedmo\Versioned
     */
    private $mobile;
    /* valeurs : 0=>Textes identiques sur Desktop et Mobile
                 1=>Textes différents sur Desktop et Mobile
                 2=>Texte à afficher seulement sur Desktop*/

    /**
     * @Gedmo\Translatable
     * @Gedmo\Versioned
     * @ORM\Column(type="text", nullable=true)
     * @CKEditor
     * @Assert\Expression("this.getMobile() !== 1 || this.getTexteMobile() !== null", message="texte.textemobile.notblank")
     */
    private $texteMobile;

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
     * Set emplacement.
     *
     * @param string $emplacement
     *
     * @return Texte
     * @codeCoverageIgnore
     */
    public function setEmplacement($emplacement)
    {
        $this->emplacement = $emplacement;

        return $this;
    }

    /**
     * Get emplacement.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getEmplacement()
    {
        return $this->emplacement;
    }

    /**
     * Set titre.
     *
     * @param string $titre
     *
     * @return Texte
     * @codeCoverageIgnore
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;

        return $this;
    }

    /**
     * Get titre.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * Set texte.
     *
     * @param string $texte
     *
     * @return Texte
     * @codeCoverageIgnore
     */
    public function setTexte($texte)
    {
        $this->texte = $texte;

        return $this;
    }

    /**
     * Get texte.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getTexte()
    {
        return $this->texte;
    }

    /**
     * Set mobile.
     *
     * @param int $mobile
     *
     * @return Texte
     * @codeCoverageIgnore
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * Get mobile.
     *
     * @return int
     * @codeCoverageIgnore
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * Set texteMobile.
     *
     * @param null|string $texteMobile
     *
     * @return Texte
     * @codeCoverageIgnore
     */
    public function setTexteMobile($texteMobile = null)
    {
        $this->texteMobile = $texteMobile;

        return $this;
    }

    /**
     * Get texteMobile.
     *
     * @return null|string
     * @codeCoverageIgnore
     */
    public function getTexteMobile()
    {
        return $this->texteMobile;
    }
}