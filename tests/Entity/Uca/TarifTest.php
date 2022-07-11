<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\MontantTarifProfilUtilisateur;
use App\Entity\Uca\Tarif;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 * @coversNothing
 */
class TarifTest extends KernelTestCase
{
    /**
     * @var Tarif
     */
    private $tarif;

    protected function setUp(): void
    {
        $this->tarif = (new Tarif())
            ->setLibelle('Tarif')
        ;
    }

    /**
     * @covers \App\Entity\Uca\Tarif::__toString
     */
    public function testToString(): void
    {
        $libelle = $this->tarif->__toString();

        $this->assertIsString($libelle);
        $this->assertEquals('Tarif', $libelle);
    }

    /**
     * @covers \App\Entity\Uca\Tarif::jsonSerializeProperties
     */
    public function testJsonSerializeProperties(): void
    {
        $arrayProperties = $this->tarif->jsonSerializeProperties();

        $this->assertIsArray($arrayProperties);
        $this->assertContains('libelle', $arrayProperties);
    }

    /**
     * @covers \App\Entity\Uca\Tarif::onLoad
     */
    public function testOnLoad(): void
    {
        $this->tarif->setModificationMontants('modificationMontants');
        $this->tarif->onLoad();

        $this->assertEquals('', $this->tarif->getModificationMontants());
    }

    /**
     * @covers \App\Entity\Uca\Tarif::getMontantUtilisateur
     */
    public function testGetMontantUtilisateur(): void
    {
        $user = static::getContainer()->get(UtilisateurRepository::class)->findOneByUsername('admin');

        $montant = new MontantTarifProfilUtilisateur(
            $this->tarif,
            $user->getProfil(),
            10
        );

        $this->tarif->addMontant($montant);

        $montantUtilisateur = $this->tarif->getMontantUtilisateur($user);

        $this->assertIsNumeric($montantUtilisateur);
        $this->assertEquals(10, $montantUtilisateur);
    }

    /**
     * @covers \App\Entity\Uca\Tarif::getMontantUtilisateur
     */
    public function testGetMontantUtilisateurEmpty(): void
    {
        $user = static::getContainer()->get(UtilisateurRepository::class)->findOneByUsername('admin');

        $montantUtilisateur = $this->tarif->getMontantUtilisateur($user);

        $this->assertIsNumeric($montantUtilisateur);
        $this->assertEquals(-1, $montantUtilisateur);
    }

    /**
     * @covers \App\Entity\Uca\Tarif::getTvaUtilisateur
     */
    public function testGetTvaUtilisateur(): void
    {
        $user = static::getContainer()->get(UtilisateurRepository::class)->findOneByUsername('admin');

        $montant = new MontantTarifProfilUtilisateur(
            $this->tarif,
            $user->getProfil(),
            10
        );

        // Pourcentage TVA qui permet d'obtenir 1.0 avec un montant de 10
        $this->tarif->setTva(true)->setPourcentageTVA(11.11111111);
        $this->tarif->addMontant($montant);

        $tvaUtilisateur = $this->tarif->getTvaUtilisateur($user);

        $this->assertIsNumeric($tvaUtilisateur);
        $this->assertEquals(1.0, $tvaUtilisateur);
    }

    /**
     * @covers \App\Entity\Uca\Tarif::getTvaUtilisateur
     */
    public function testGetTvaUtilisateurEmpty(): void
    {
        $user = static::getContainer()->get(UtilisateurRepository::class)->findOneByUsername('admin');

        $tvaUtilisateur = $this->tarif->getTvaUtilisateur($user);

        $this->assertIsNumeric($tvaUtilisateur);
        $this->assertEquals(0, $tvaUtilisateur);
    }
}