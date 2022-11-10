<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\TypeActivite;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class TypeActiviteTest extends TestCase
{
    /**
     * @var TypeActivite
     */
    private $typeActivite;

    protected function setUp(): void
    {
        $this->typeActivite = (new TypeActivite())->setLibelle('Type activité');
    }

    /**
     * @covers \App\Entity\Uca\TypeActivite::__toString
     */
    public function testToString(): void
    {
        $libelle = $this->typeActivite->__toString();

        $this->assertIsString($libelle);
        $this->assertEquals('Type activité', $libelle);
    }
}