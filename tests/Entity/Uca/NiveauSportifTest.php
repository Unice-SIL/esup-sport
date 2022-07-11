<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\NiveauSportif;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class NiveauSportifTest extends TestCase
{
    /**
     * @var NiveauSportif
     */
    private $niveauSportif;

    /**
     * Fonction qui s'exécute avant chaque test.
     */
    protected function setUp(): void
    {
        $this->niveauSportif = (new NiveauSportif())
            ->setLibelle('Activité')
        ;
    }

    /**
     * @covers \App\Entity\Uca\NiveauSportif::jsonSerializeProperties
     */
    public function testjsonSerializeProperties(): void
    {
        $arrayProperties = $this->niveauSportif->jsonSerializeProperties();

        $this->assertIsArray($arrayProperties);
        $this->assertContains('libelle', $arrayProperties);
    }
}