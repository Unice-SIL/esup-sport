<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\ProfilUtilisateur;
use App\Entity\Uca\Reservabilite;
use App\Entity\Uca\ReservabiliteProfilUtilisateur;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ReservabiliteProfilUtilisateurTest extends TestCase
{
    /**
     * @var ReservabiliteProfilUtilisateur
     */
    private $reservabiliteProfilUtilisateur;

    /**
     * Fonction qui s'exécute avant chaque test.
     */
    protected function setUp(): void
    {
        $this->reservabiliteProfilUtilisateur = new ReservabiliteProfilUtilisateur(
            new Reservabilite(),
            (new ProfilUtilisateur())->setLibelle('Profil utilisateur'),
            10
        );
    }

    /**
     * @covers \App\Entity\Uca\ReservabiliteProfilUtilisateur::__construct
     */
    public function testConstruct(): void
    {
        $this->assertInstanceOf(ReservabiliteProfilUtilisateur::class, $this->reservabiliteProfilUtilisateur);
    }

    /**
     * @covers \App\Entity\Uca\ReservabiliteProfilUtilisateur::jsonSerializeProperties
     */
    public function testJsonSerializeProperties(): void
    {
        $arrayProperties = $this->reservabiliteProfilUtilisateur->jsonSerializeProperties();

        $this->assertIsArray($arrayProperties);
        $this->assertContains('profilUtilisateur', $arrayProperties);
        $this->assertContains('capaciteProfil', $arrayProperties);
    }

    /**
     * @covers \App\Entity\Uca\ReservabiliteProfilUtilisateur::getLibelle
     */
    public function testGetLibelle(): void
    {
        $this->assertEquals('Profil utilisateur', $this->reservabiliteProfilUtilisateur->getLibelle());
    }
}