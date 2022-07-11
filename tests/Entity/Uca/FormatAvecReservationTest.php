<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\FormatAvecReservation;
use App\Entity\Uca\Lieu;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class FormatAvecReservationTest extends TestCase
{
    /**
     * @var FormatAvecReservation
     */
    private $formatAvecReservation;

    /**
     * Fonction qui s'exécute avant chaque test.
     */
    protected function setUp(): void
    {
        $this->formatAvecReservation = new FormatAvecReservation();
    }

    /**
     * @covers \App\Entity\Uca\FormatAvecReservation::updateListeRessources
     */
    public function testUpdateListeRessources(): void
    {
        $ressource1 =
            (new Lieu())
                ->setLibelle('lieu1')
            ;

        $ressource2 =
            (new Lieu())
                ->setLibelle('lieu2')
            ;
        $this->assertEquals('', $this->formatAvecReservation->getListeRessources());

        $this->formatAvecReservation->addRessource($ressource1);
        $this->formatAvecReservation->updateListeRessources();
        $this->formatAvecReservation->addRessource($ressource2);
        $this->formatAvecReservation->updateListeRessources();

        $this->assertEquals('lieu1, lieu2', $this->formatAvecReservation->getListeRessources());
    }
}
