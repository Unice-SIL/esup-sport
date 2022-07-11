<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\Activite;
use App\Entity\Uca\Annotation;
use ArgumentCountError;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class AnnotationTest extends TestCase
{
    /**
     * @covers \App\Entity\Uca\Annotation::__construct
     */
    public function testConstructor(): void
    {
        $annotation = new Annotation([
            'entity' => Activite::class,
            'field' => 'libelle',
            'annotation' => 'annotation',
        ]);

        $this->assertInstanceOf(Annotation::class, $annotation);
    }

    /**
     * @covers \App\Entity\Uca\Annotation::__construct
     */
    public function testConstructorTooFewArguments(): void
    {
        $this->expectException(ArgumentCountError::class);
        $annotation = new Annotation();
    }
}