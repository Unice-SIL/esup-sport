<?php

namespace App\Validator\Constraints;

/**
 * @Annotation
 */
class OldPasswordConstraint extends \Symfony\Component\Validator\Constraint
{
    public $messageErreur = 'utilisateur.change_password.old_password.wrong';

    public function validatedBy()
    {
        return \get_class($this).'Validator';
    }

    public function getTargets()
    {
        return [self::CLASS_CONSTRAINT, self::PROPERTY_CONSTRAINT];
    }
}
