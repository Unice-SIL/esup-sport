<?php

namespace UcaBundle\Validator\Constraints;

use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class IntegerCustomConstraintValidator extends \Symfony\Component\Validator\ConstraintValidator
{
    public function validate($saisieUtilisteur, Constraint $constrainte)
    {
        if (!$constrainte instanceof IntegerCustomConstraint) {
            throw new UnexpectedTypeException($constrainte, IntegerCustomConstraint::class);
        }

        if (null === $saisieUtilisteur || '' === $saisieUtilisteur) {
            return;
        }
        foreach ($saisieUtilisteur as $champ) {
            if (!preg_match('/^\d+$/', $champ) || null === $champ || '' === $champ) {
                $this->context->buildViolation($constrainte->messageErreur)->addViolation();
            }
        }
    }
}
