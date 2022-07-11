<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\Materiel;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class MaterielTest extends TestCase
{
    /**
     * @var Materiel
     */
    private $materiel;

    protected function setUp(): void
    {
        $this->materiel = (new Materiel())->setQuantiteDisponible(10);
    }

    /**
     * @covers \App\Entity\Uca\Materiel::getCapacite
     */
    public function testGetCapacite(): void
    {
        $capacite = $this->materiel->getCapacite();

        $this->assertIsInt($capacite);
        $this->assertEquals(10, $capacite);
    }
}