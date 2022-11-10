<?php

namespace App\Validator\Constraints;

use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Constraint;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Annotation
 */
class OldPasswordConstraintValidator extends \Symfony\Component\Validator\ConstraintValidator
{
    private $passwordEncoder;
    private $user;
    private $translator;

    public function __construct(UserPasswordHasherInterface $passwordEncoder, Security $security, TranslatorInterface $translator) {
        $this->passwordEncoder = $passwordEncoder;
        $this->user = $security->getToken()->getUser();
        $this->translator = $translator;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof OldPasswordConstraint) {
            throw new UnexpectedTypeException($constraint, OldPasswordConstraint::class);
        }

        if (null === $this->user) {
            throw new \Exception('You must be logged in to perfomr this action');
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if (!$this->passwordEncoder->isPasswordValid($this->user, $value)) {
            $this->context->buildViolation($this->translator->trans($constraint->messageErreur))->addViolation();
        }
    }
}
