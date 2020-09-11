<?php

/*
 * Interface - JsonSerializable:
 *
 * Permet d'implementer la serialisation en JSON dans les entités.
*/

namespace UcaBundle\Entity\Interfaces;

interface JsonSerializable extends \JsonSerializable
{
    public function jsonSerializeProperties();
}
