<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\Materiel;
use App\Entity\Uca\ProfilUtilisateur;
use App\Entity\Uca\RessourceProfilUtilisateur;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class RessourceProfilUtilisateurTest extends TestCase
{
    /**
     * @var RessourceProfilUtilisateur
     */
    private $ressourceProfilUtilisateur;

    /**
     * Fonction qui s'exécute avant chaque test.
     */
    protected function setUp(): void
    {
        $this->ressourceProfilUtilisateur = new RessourceProfilUtilisateur(
            new Materiel(),
            (new ProfilUtilisateur())->setLibelle('Profil utilisateur'),
            10
        );
    }

    /**
     * @covers \App\Entity\Uca\RessourceProfilUtilisateur::__construct
     */
    public function testConstruct(): void
    {
        $this->assertInstanceOf(RessourceProfilUtilisateur::class, $this->ressourceProfilUtilisateur);
    }

    /**
     * @covers \App\Entity\Uca\RessourceProfilUtilisateur::jsonSerializeProperties
     */
    public function testJsonSerializeProperties(): void
    {
        $arrayProperties = $this->ressourceProfilUtilisateur->jsonSerializeProperties();

        $this->assertIsArray($arrayProperties);
        $this->assertContains('ressource', $arrayProperties);
        $this->assertContains('profilUtilisateur', $arrayProperties);
        $this->assertContains('capaciteProfil', $arrayProperties);
        $this->assertContains('Profil utilisateur', $arrayProperties);
    }
}