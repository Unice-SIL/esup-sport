<?php

namespace UcaBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use UcaBundle\Entity\Utilisateur;

/**
 * @Annotation
 */
class UsernameConstraintValidator extends ConstraintValidator
{
    public function validate($SaisieUtilisateur, Constraint $constrainte)
    {
        if (!$constrainte instanceof UsernameConstraint) {
            throw new UnexpectedTypeException($constrainte, UsernameConstraint::class);
        }

        if (null === $SaisieUtilisateur || '' === $SaisieUtilisateur) {
            return;
        }

        if (!preg_match('/^((?!@).)*$/', $SaisieUtilisateur, $matches)) {
            $user = $this->context->getObject();
            if ($user instanceof Utilisateur) {
                if (!$user->getShibboleth()) {
                    $this->context->buildViolation($constrainte->message)
                        ->addViolation()
                    ;
                }
            }
        }
    }
}
