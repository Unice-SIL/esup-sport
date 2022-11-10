<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\LogConnexion;
use App\Entity\Uca\Utilisateur;
use DateTime;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class LogConnexionTest extends TestCase
{
    /**
     * @covers \App\Entity\Uca\LogConnexion::__construct
     */
    public function testConstruct(): void
    {
        $logConnexion = new LogConnexion(new Utilisateur());

        $this->assertInstanceOf(LogConnexion::class, $logConnexion);
        $this->assertEquals((new DateTime())->format('d/m/Y'), $logConnexion->getDateConnexion()->format('d/m/Y'));
    }
}