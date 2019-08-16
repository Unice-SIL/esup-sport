<?php

namespace UcaBundle\Entity\Interfaces;

interface JsonSerializable extends \JsonSerializable
{
    public function jsonSerializeProperties();
}