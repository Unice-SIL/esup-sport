<?php

namespace App\Validator\Constraints;

/**
 * @Annotation
 */
class IntegerCustomConstraint extends \Symfony\Component\Validator\Constraint
{
    public $messageErreur = 'formatactivite.capaciteprofil.invalide';

    public function validatedBy()
    {
        return \get_class($this).'Validator';
    }

    public function getTargets()
    {
        return [self::CLASS_CONSTRAINT, self::PROPERTY_CONSTRAINT];
    }
}
