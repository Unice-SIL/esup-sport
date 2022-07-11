<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\StatutUtilisateur;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class StatutUtilisateurTest extends TestCase
{
    /**
     * @var StatutUtilisateur
     */
    private $statutUtilisateur;

    /**
     * Fonction qui s'exécute avant chaque test.
     */
    protected function setUp(): void
    {
        $this->statutUtilisateur = new StatutUtilisateur();
    }

    /**
     * @covers \App\Entity\Uca\StatutUtilisateur::__construct
     */
    public function testConstruct(): void
    {
        $this->assertInstanceOf(StatutUtilisateur::class, $this->statutUtilisateur);
    }

    /**
     * @covers \App\Entity\Uca\StatutUtilisateur::jsonSerializeProperties
     */
    public function testJsonSerializeProperties(): void
    {
        $arrayProperties = $this->statutUtilisateur->jsonSerializeProperties();

        $this->assertIsArray($arrayProperties);
        $this->assertContains('libelle', $arrayProperties);
    }
}