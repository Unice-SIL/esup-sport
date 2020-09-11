<?php

/*
 * Classe - FormatAchatCarte:
 *
 * Héritée du foramt d'activité, il s'agit du format permettant d'acheter les cartes.
*/

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use UcaBundle\Service\Common\Fn;

/**
 * @ORM\Entity(repositoryClass="UcaBundle\Repository\FormatAchatCarteRepository")
 * @Gedmo\Loggable
 * @ORM\EntityListeners({"UcaBundle\Service\Listener\Entity\FormatAchatCarteListener"})
 */
class FormatAchatCarte extends FormatActivite implements \UcaBundle\Entity\Interfaces\Article
{
    //region Propriétés

    /**
     * @ORM\ManyToOne(targetEntity="TypeAutorisation", inversedBy="formatsAchatCarte")
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
        return Fn::strTruncate($this->getDescription(), 97);
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
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set carte.
     *
     * @param null|\UcaBundle\Entity\TypeAutorisation $carte
     *
     * @return FormatAchatCarte
     */
    public function setCarte(TypeAutorisation $carte = null)
    {
        $this->carte = $carte;

        return $this;
    }

    /**
     * Get carte.
     *
     * @return \UcaBundle\Entity\TypeAutorisation|null
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
     */
    public function getCarteLibelle()
    {
        return $this->carteLibelle;
    }
}
