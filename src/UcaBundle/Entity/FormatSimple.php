<?php

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

/**
 * @ORM\Entity(repositoryClass="UcaBundle\Repository\FormatSimpleRepository")
 */
class FormatSimple extends FormatActivite
{
  #region Propriétés
    /** @ORM\Column(type="boolean",nullable=false) */
    private $promouvoir = false;

    protected $type = "simple";
  #endregion

  #region Méthodes
    
    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
  #endregion

    /**
     * Set promouvoir.
     *
     * @param bool $promouvoir
     *
     * @return FormatSimple
     */
    public function setPromouvoir($promouvoir)
    {
        $this->promouvoir = $promouvoir;

        return $this;
    }

    /**
     * Get promouvoir.
     *
     * @return bool
     */
    public function getPromouvoir()
    {
        return $this->promouvoir;
    }
}
