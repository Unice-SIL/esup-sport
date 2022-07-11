<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\FormatActiviteProfilUtilisateur;
use App\Entity\Uca\FormatAvecCreneau;
use App\Entity\Uca\ProfilUtilisateur;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class FormatActiviteProfilUtilisateurTest extends TestCase
{
    /**
     * @var FormatActiviteProfilUtilisateur
     */
    private $formatActiviteProfilUtilisateur;

    /**
     * Fonction qui s'exécute avant chaque test.
     */
    protected function setUp(): void
    {
        $formatActivite = new FormatAvecCreneau();
        $profilUtilisateur = (new ProfilUtilisateur())->setLibelle('Profil utilisateur');

        $this->formatActiviteProfilUtilisateur = (new FormatActiviteProfilUtilisateur($formatActivite, $profilUtilisateur, 10));
    }

    /**
     * @covers \App\Entity\Uca\FormatActiviteProfilUtilisateur::jsonSerializeProperties
     */
    public function testJsonSerializeProperties(): void
    {
        $arrayProperties = $this->formatActiviteProfilUtilisateur->jsonSerializeProperties();

        $this->assertIsArray($arrayProperties);
        $this->assertContains('formatActivite', $arrayProperties);
        $this->assertContains('profilUtilisateur', $arrayProperties);
        $this->assertContains('capaciteProfil', $arrayProperties);
        $this->assertContains('Profil utilisateur', $arrayProperties);
    }
}