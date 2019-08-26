<?php

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\Entity 
 * @Gedmo\Loggable
 * @ORM\EntityListeners({"UcaBundle\Service\Listener\Entity\FormatAchatCarteListener"})
 */
class FormatAchatCarte extends FormatActivite implements \UcaBundle\Entity\Interfaces\Article
{
    #region Propriétés

    /**
     * @ORM\ManyToOne(targetEntity="TypeAutorisation", inversedBy="formatsAchatCarte") 
     * @Assert\Expression("this.getCarte()", message="formatactivite.achatcarte.carte.notnull") */
    private $carte;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="text")
     */
    private $carteLibelle;

    #endregion

    #region Méthodes

    public function getArticleAutorisations()
    {
        $autorisations = $this->getAutorisations();
        $autorisations->add($this->carte);
        return $autorisations;
    }

    public function getArticleLibelle()
    {
        return $this->getLibelle();
    }

    public function getArticleDescription()
    {
        return $this->getDescription();
    }

    public function updateCarteLibelle()
    {
        if($this->getCarte() != null){
            $this->carteLibelle = $this->getCarte()->getLibelle();
        } else{
            $this->carteLibelle = '';
        }

        return $this;
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
     * Set carte.
     *
     * @param \UcaBundle\Entity\TypeAutorisation|null $carte
     *
     * @return FormatAchatCarte
     */
    public function setCarte(\UcaBundle\Entity\TypeAutorisation $carte = null)
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
