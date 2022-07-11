<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\Lieu;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class LieuTest extends TestCase
{
    /**
     * @var Lieu
     */
    private $lieu;

    /**
     * Fonction qui s'exécute avant chaque test.
     */
    protected function setUp(): void
    {
        $this->lieu = new Lieu();
    }

    /**
     * @covers \App\Entity\Uca\Lieu::getCapacite
     */
    public function testGetCapacite(): void
    {
        $capacite = $this->lieu->getCapacite();

        $this->assertIsInt($capacite);
        $this->assertEquals(1, $capacite);
    }
}