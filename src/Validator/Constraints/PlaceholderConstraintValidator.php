<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Contracts\Translation\TranslatorInterface;

class PlaceholderConstraintValidator extends ConstraintValidator
{
    private $translator;

    /**
     * @codeCoverageIgnore
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof PlaceholderConstraint) {
            throw new UnexpectedTypeException($constraint, PlaceholderConstraint::class);
        }

        if (!preg_match_all('/\[\[[^\[\]]*\]\]/', $value, $placeholders)) {
            return;
        }

        $placeholders = array_map(function ($val) {
            return str_replace(['[[', ']]'], '', $val);
        }, $placeholders);

        foreach ($placeholders as $placeholder) {
            foreach ($placeholder as $value) {
                if (!in_array($value, $constraint->placeholders)) {
                    $this->context->buildViolation($this->translator->trans($constraint->message))->setParameter('%placeholder%', $value)->addViolation();
                }
            }
        }
    }
}
