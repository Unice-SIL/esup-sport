<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UsernameConstraint extends Constraint
{
    public $message = 'utilisateur.username.email';

    public function validatedBy()
    {
        return \get_class($this).'Validator';
    }
}
