<?php

/*
 * Trait - JsonSerializable:
 *
 * Permet d'implementer la serialization en JSON des champs d'une entité
 * Ce trait est implémenté dans beaucoup d'entité.
*/

namespace App\Entity\Uca\Traits;

trait JsonSerializable
{
    public $serialiseCaller;

    public function getSerialiseId()
    {
        return get_class($this).'#'.$this->getId().'#';
    }

    public function hasSerialiseCaller($object)
    {
        if (null == $this->serialiseCaller) {
            return false;
        }
        if ($this->serialiseCaller->getSerialiseId() == $object->getSerialiseId()) {
            return true;
        }

        return $this->serialiseCaller->hasSerialiseCaller($object);
    }

    public function toArray($serialiseCaller)
    {
        $this->serialiseCaller = $serialiseCaller;
        $res['objectClass'] = get_class($this);
        $allowedProperties = array_flip(array_merge(['id', 'serialiseCaller'], $this->jsonSerializeProperties()));
        $allProperties = array_intersect_key(get_object_vars($this), $allowedProperties);
        foreach ($allProperties as $k => $v) {
            if ('serialiseCaller' == $k) {
                $res[$k] = null == $v ? null : $v->getSerialiseId();
            }
            // for collection
            elseif (is_object($v) && (is_a($v, 'Doctrine\ORM\PersistentCollection') || is_a($v, '\\Doctrine\\Common\\Collections\\ArrayCollection'))) {
                foreach ($v->toArray() as $k1 => $v1) {
                    if (is_a($v1, \JsonSerializable::class) && !$this->hasSerialiseCaller($v1)) {
                        $res[$k][$k1] = $v1->toArray($this);
                    }
                }
            } elseif (is_object($v) && is_a($v, \JsonSerializable::class) && !$this->hasSerialiseCaller($v)) {
                $res[$k] = $v->toArray($this);
            } elseif (is_object($v) && is_a($v, \DateTime::class)) {
                $res[$k] = $v->format('Y-m-d H:i');
            } elseif (!is_object($v)) {
                $res[$k] = $v;
            }
        }

        return $res;
    }

    public function jsonSerialize($serialiseCaller = null)
    {
        return $this->toArray($serialiseCaller);
    }
}
