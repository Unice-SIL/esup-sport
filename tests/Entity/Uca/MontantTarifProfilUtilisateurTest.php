<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\MontantTarifProfilUtilisateur;
use App\Entity\Uca\ProfilUtilisateur;
use App\Entity\Uca\Tarif;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class MontantTarifProfilUtilisateurTest extends TestCase
{
    /**
     * @var MontantTarifProfilUtilisateur
     */
    private $montant;

    protected function setUp(): void
    {
        $tarif = new Tarif();
        $profilUtilisateur = new ProfilUtilisateur();

        $this->montant = new MontantTarifProfilUtilisateur($tarif, $profilUtilisateur, 10);
    }

    /**
     * @covers \App\Entity\Uca\MontantTarifProfilUtilisateur::__construct
     */
    public function testConstruct(): void
    {
        $this->assertInstanceOf(MontantTarifProfilUtilisateur::class, $this->montant);
    }

    /**
     * @covers \App\Entity\Uca\MontantTarifProfilUtilisateur::setMontant
     */
    public function testSetMontant(): void
    {
        $this->montant->setMontant(100);

        $this->assertEquals(100, $this->montant->getMontant());
    }
}