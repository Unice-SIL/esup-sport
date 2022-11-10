<?php

/*
 * Classe - FormatAchatCarte:
 *
 * Héritée du foramt d'activité, il s'agit du format permettant d'acheter les cartes.
*/

namespace App\Entity\Uca;

use App\Service\Common\Fctn;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FormatAchatCarteRepository")
 * @Gedmo\Loggable
 */
class FormatAchatCarte extends FormatActivite implements \App\Entity\Uca\Interfaces\Article
{
    //region Propriétés

    /**
     * @ORM\ManyToOne(targetEntity="TypeAutorisation", inversedBy="formatsAchatCarte", fetch="LAZY")
     * @Assert\Expression("this.getCarte()", message="formatactivite.achatcarte.carte.notnull") */
    private $carte;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="text")
     */
    private $carteLibelle;

    //endregion

    //region Méthodes

    public function getArticleAutorisations()
    {
        $autorisations = clone $this->getAutorisations();
        $autorisations->add($this->carte);

        return $autorisations;
    }

    public function getArticleLibelle()
    {
        return $this->getLibelle();
    }

    public function getArticleDescription()
    {
        return Fctn::strTruncate($this->getDescription(), 97);
    }

    public function updateCarteLibelle()
    {
        if (null != $this->getCarte()) {
            $this->carteLibelle = $this->getCarte()->getLibelle();
        } else {
            $this->carteLibelle = '';
        }

        return $this;
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
     * Set carte.
     *
     * @return FormatAchatCarte
     * @codeCoverageIgnore
     */
    public function setCarte(TypeAutorisation $carte = null)
    {
        $this->carte = $carte;

        return $this;
    }

    /**
     * Get carte.
     *
     * @return null|TypeAutorisation
     * @codeCoverageIgnore
     */
    public function getCarte()
    {
        return $this->carte;
    }

    /**
     * Set carteLibelle.
     *
     * @param string $carteLibelle
     *
     * @return FormatAchatCarte
     * @codeCoverageIgnore
     */
    public function setCarteLibelle($carteLibelle)
    {
        $this->carteLibelle = $carteLibelle;

        return $this;
    }

    /**
     * Get carteLibelle.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getCarteLibelle()
    {
        return $this->carteLibelle;
    }
}