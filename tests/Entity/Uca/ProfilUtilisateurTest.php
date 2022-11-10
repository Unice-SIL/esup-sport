<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\ProfilUtilisateur;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ProfilUtilisateurTest extends TestCase
{
    /**
     * @var ProfilUtilisateur
     */
    private $profilUtilisateur;

    /**
     * Fonction qui s'exécute avant chaque test.
     */
    protected function setUp(): void
    {
        $this->profilUtilisateur = (new ProfilUtilisateur())
            ->setLibelle('Profil utilisateur')
        ;
    }

    /**
     * @covers \App\Entity\Uca\ProfilUtilisateur::jsonSerializeProperties
     */
    public function testJsonSerializeProperties(): void
    {
        $arrayProperties = $this->profilUtilisateur->jsonSerializeProperties();

        $this->assertIsArray($arrayProperties);
        $this->assertContains('libelle', $arrayProperties);
    }
}