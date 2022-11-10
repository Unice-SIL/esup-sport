<?php

namespace App\Tests;

use App\Entity\Uca\TypeRubrique;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class TypeRubriqueTest extends TestCase
{
    /**
     * @var TypeRubrique
     */
    private $typeRubrique;

    /**
     * Fonction qui s'exécute avant chaque test.
     */
    protected function setUp(): void
    {
        $this->typeRubrique = (new TypeRubrique())
            ->setLibelle('Test type Rubrique')
        ;
    }

    /**
     * @covers \App\Entity\Uca\TypeRubrique::jsonSerializeProperties
     */
    public function testjsonSerializeProperties(): void
    {
        $arrayProperties = $this->typeRubrique->jsonSerializeProperties();

        $this->assertIsArray($arrayProperties);
        $this->assertContains('libelle', $arrayProperties);
    }
}
