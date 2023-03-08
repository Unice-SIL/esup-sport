<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @codeCoverageIgnore
 */
class PlaceholderConstraint extends Constraint
{
    public array $placeholders = [];
    public string $message = 'email.invalidplaceholder';

    public function validatedBy()
    {
        return self::class.'Validator';
    }
}
