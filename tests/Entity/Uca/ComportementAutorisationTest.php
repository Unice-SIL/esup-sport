<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\ComportementAutorisation;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ComportementAutorisationTest extends TestCase
{
    /**
     * @var ComportementAutorisation
     */
    private $comportementAutorisation;

    /**
     * Fonction qui s'exécute avant chaque test.
     */
    protected function setUp(): void
    {
        $this->comportementAutorisation = (new ComportementAutorisation())
            ->setLibelle('Activité')
            ->setCodeComportement('123456')
        ;
    }

    /**
     * @covers \App\Entity\Uca\ComportementAutorisation::jsonSerializeProperties
     */
    public function testjsonSerializeProperties(): void
    {
        $arrayProperties = $this->comportementAutorisation->jsonSerializeProperties();

        $this->assertIsArray($arrayProperties);
        $this->assertContains('libelle', $arrayProperties);
        $this->assertContains('codeComportement', $arrayProperties);
    }
}