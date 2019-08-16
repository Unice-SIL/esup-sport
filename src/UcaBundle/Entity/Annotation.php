<?php

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

/**
 * @ORM\Entity(repositoryClass="UcaBundle\Repository\AnnotationRepository")
 * @ORM\Table(name="ext_annotation")
 */
class Annotation
{
    #region Propriétés
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /** @ORM\Column(type="string") */
    private $entity;

    /** @ORM\Column(type="string") */
    private $field;

    /** @ORM\Column(type="string") */
    private $annotation;
    #endregion

    #region Méthodes
    public function __construct($array)
    {
        $this->entity = $array['entity'];
        $this->field = $array['field'];
        $this->annotation = $array['annotation'];
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
     * Set entity.
     *
     * @param string $entity
     *
     * @return Annotation
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Get entity.
     *
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set field.
     *
     * @param string $field
     *
     * @return Annotation
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * Get field.
     *
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Set annotation.
     *
     * @param string $annotation
     *
     * @return Annotation
     */
    public function setAnnotation($annotation)
    {
        $this->annotation = $annotation;

        return $this;
    }

    /**
     * Get annotation.
     *
     * @return string
     */
    public function getAnnotation()
    {
        return $this->annotation;
    }
}
