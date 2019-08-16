<?php

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\Entity */
class FormatAchatCarte extends FormatActivite
{
    #region Propriétés

    /** @ORM\ManyToOne(targetEntity="TypeAutorisation", inversedBy="formatsAchatCarte") 
     * @Assert\Expression("this.getCarte()", message="formatactivite.achatcarte.carte.notnull") */
    private $carte;

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
}
