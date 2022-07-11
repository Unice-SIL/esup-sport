<?php

/*
 * Interface - JsonSerializable:
 *
 * Permet d'implementer la serialisation en JSON dans les entités.
*/

namespace App\Entity\Uca\Interfaces;

interface JsonSerializable extends \JsonSerializable
{
    public function jsonSerializeProperties();
}
