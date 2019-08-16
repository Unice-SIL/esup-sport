<?php

namespace UcaBundle\Entity\Interfaces;

interface Tarifable
{
    public function getMontant($user);
}