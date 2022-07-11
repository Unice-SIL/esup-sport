<?php
/*
 * Classe - Annotation:
 *
 * Entité technique pour la gestion des entités.
*/

namespace App\Entity\Uca;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AnnotationRepository")
 * @ORM\Table(name="ext_annotation")
 */
class Annotation
{
    //region Propriétés
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
    //endregion

    //region Méthodes
    public function __construct($array)
    {
        $this->entity = $array['entity'];
        $this->field = $array['field'];
        $this->annotation = $array['annotation'];
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
     * Set entity.
     *
     * @param string $entity
     *
     * @return Annotation
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     */
    public function getAnnotation()
    {
        return $this->annotation;
    }
}