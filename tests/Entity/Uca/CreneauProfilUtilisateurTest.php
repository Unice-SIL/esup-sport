<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\Creneau;
use App\Entity\Uca\CreneauProfilUtilisateur;
use App\Entity\Uca\ProfilUtilisateur;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class CreneauProfilUtilisateurTest extends TestCase
{
    /**
     * @var CreneauProfilUtilisateur
     */
    private $creneauProfilUtilisateur;

    /**
     * Fonction qui s'exécute avant chaque test.
     */
    protected function setUp(): void
    {
        $creneau = new Creneau();
        $profilUtilisateur = (new ProfilUtilisateur())->setLibelle('Profil utilisateur');

        $this->creneauProfilUtilisateur = (new CreneauProfilUtilisateur($creneau, $profilUtilisateur, 10));
    }

    /**
     * @covers \App\Entity\Uca\CreneauProfilUtilisateur::jsonSerializeProperties
     */
    public function testJsonSerializeProperties(): void
    {
        $arrayProperties = $this->creneauProfilUtilisateur->jsonSerializeProperties();

        $this->assertIsArray($arrayProperties);
        $this->assertContains('creneau', $arrayProperties);
        $this->assertContains('profilUtilisateur', $arrayProperties);
        $this->assertContains('capaciteProfil', $arrayProperties);
    }

    /**
     * @covers \App\Entity\Uca\CreneauProfilUtilisateur::getLibelle
     */
    public function testGetLibelle(): void
    {
        $libelle = $this->creneauProfilUtilisateur->getLibelle();

        $this->assertIsString($libelle);
        $this->assertEquals('Profil utilisateur', $libelle);
    }
}