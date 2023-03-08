<?php

namespace App\Tests\Validator\Constraints;

use App\Validator\Constraints\PlaceholderConstraint;
use App\Validator\Constraints\PlaceholderConstraintValidator;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class PlaceholderConstraintValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator()
    {
        return new PlaceholderConstraintValidator(new Translator('fr'));
    }

    public function dataProviderValid() {
        return [
            [null],
            [''],
            ['[[testValid]]'],
            ['test'],
        ];
    }

    /**
     * @dataProvider dataProviderValid
     * @covers App\Validator\Constraints\PlaceholderConstraintValidator::validate()
     */
    public function testValid($value): void
    {
        $constraint = new PlaceholderConstraint();
        $constraint->message =  'testConstraint';
        $constraint->placeholders = ['testValid'];
        $this->validator->validate($value, $constraint);

        $this->assertNoViolation();
    }
    
    public function dataProviderInvalid() {
        return [
            ['[[]]'],
            ['[[testInvalid]]'],
        ];
    }

    /**
     * @dataProvider dataProviderInvalid
     * @covers App\Validator\Constraints\PlaceholderConstraintValidator::validate()
     */
    public function testInvalid($value): void
    {
        $constraint = new PlaceholderConstraint();
        $constraint->message =  'testConstraint';
        $constraint->placeholders = ['testValid'];
        $this->validator->validate($value, $constraint);

        $this->buildViolation('testConstraint')->setParameter('%placeholder%', str_replace(['[[', ']]'], '', $value))->assertRaised();
    }

    public function testUnexceptedType(): void
    {
        $constraint = new Email();
        $this->expectException(UnexpectedTypeException::class);

        $this->validator->validate(null, $constraint);
    }
}
