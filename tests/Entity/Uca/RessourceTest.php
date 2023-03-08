<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\Etablissement;
use App\Entity\Uca\Materiel;
use App\Entity\Uca\ProfilUtilisateur;
use App\Entity\Uca\Ressource;
use App\Entity\Uca\RessourceProfilUtilisateur;
use App\Entity\Uca\Tarif;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @internal
 * @coversNothing
 */
class RessourceTest extends KernelTestCase
{
    /**
     * @covers \App\Entity\Uca\Ressource::__construct
     */
    public function testConstruct(): void
    {
        $ressource = new Materiel();

        $this->assertInstanceOf(Ressource::class, $ressource);

        $this->assertInstanceOf(ArrayCollection::class, $ressource->getFormatResa());
        $this->assertEmpty($ressource->getFormatResa());

        $this->assertInstanceOf(ArrayCollection::class, $ressource->getReservabilites());
        $this->assertEmpty($ressource->getReservabilites());

        $this->assertInstanceOf(ArrayCollection::class, $ressource->getProfilsUtilisateurs());
        $this->assertEmpty($ressource->getProfilsUtilisateurs());
    }

    /**
     * @covers \App\Entity\Uca\Ressource::formatIsValid
     */
    public function testFormatIsValid(): void
    {
        $isValidLieu = Ressource::formatIsValid('Lieu');
        $isValidMateriel = Ressource::formatIsValid('Materiel');
        $isValidAutre = Ressource::formatIsValid('Autre');

        $this->assertIsBool($isValidLieu);
        $this->assertTrue($isValidLieu);

        $this->assertIsBool($isValidMateriel);
        $this->assertTrue($isValidMateriel);

        $this->assertIsBool($isValidAutre);
        $this->assertFalse($isValidAutre);
    }

    /**
     * @covers \App\Entity\Uca\Ressource::jsonSerializeProperties
     */
    public function testJsonSerializeProperties(): void
    {
        $ressource = new Materiel();

        $arrayProperties = $ressource->jsonSerializeProperties();

        $this->assertIsArray($arrayProperties);
        $this->assertEquals(4, sizeof($arrayProperties));
        $this->assertContains('libelle', $arrayProperties);
        $this->assertContains('description', $arrayProperties);
        $this->assertContains('etablissementLibelle', $arrayProperties);
        $this->assertContains('profilsUtilisateurs', $arrayProperties);
    }

    /**
     * @covers \App\Entity\Uca\Ressource::getImageFile
     * @covers \App\Entity\Uca\Ressource::setImageFile
     */
    public function testSetImageFile(): void
    {
        $file = new File(dirname(__DIR__, 2).'/fixtures/test.pdf');
        $ressource = (new Materiel())->setImageFile($file);

        $this->assertInstanceOf(File::class, $ressource->getImageFile());
        $this->assertEquals($file, $ressource->getImageFile());
        $this->assertInstanceOf(DateTime::class, $ressource->getUpdatedAt());
    }

    /**
     * @covers \App\Entity\Uca\Ressource::updateTarifLibelle
     */
    public function testUpdateTarifLibelle(): void
    {
        $ressource = new Materiel();
        $ressource->updateTarifLibelle();

        $this->assertIsString($ressource->getTarifLibelle());
        $this->assertEquals('', $ressource->getTarifLibelle());

        $ressource->setTarif((new Tarif())->setLibelle('Tarif'));
        $ressource->updateTarifLibelle();

        $this->assertIsString($ressource->getTarifLibelle());
        $this->assertEquals('Tarif', $ressource->getTarifLibelle());
    }

    /**
     * @covers \App\Entity\Uca\Ressource::updateEtablissementLibelle
     */
    public function testUpdateEtablissementLibelle(): void
    {
        $ressource = new Materiel();
        $ressource->updateEtablissementLibelle();

        $this->assertIsString($ressource->getEtablissementLibelle());
        $this->assertEquals('', $ressource->getEtablissementLibelle());

        $ressource->setEtablissement((new Etablissement())->setLibelle('Etablissement'));
        $ressource->updateEtablissementLibelle();

        $this->assertIsString($ressource->getEtablissementLibelle());
        $this->assertEquals('Etablissement', $ressource->getEtablissementLibelle());
    }

    /**
     * @covers \App\Entity\Uca\Ressource::updateListeProfils
     */
    public function testUpdateListeProfils(): void
    {
        $ressource = new Materiel();
        $ressource->updateListeProfils();

        $this->assertIsString($ressource->getListeProfils());
        $this->assertEquals('', $ressource->getListeProfils());

        $ressource->addProfilsUtilisateur(new RessourceProfilUtilisateur(new Materiel(), (new ProfilUtilisateur())->setLibelle('Premier profil'), 10));
        $ressource->addProfilsUtilisateur(new RessourceProfilUtilisateur(new Materiel(), (new ProfilUtilisateur())->setLibelle('Deuxième profil'), 10));
        $ressource->updateListeProfils();

        $this->assertIsString($ressource->getListeProfils());
        $this->assertEquals('Premier profil, Deuxième profil', $ressource->getListeProfils());
    }

    /**
     * @covers \App\Entity\Uca\Ressource::hasProfil
     */
    public function testHasProfil(): void
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $premierProfil = (new ProfilUtilisateur())->setLibelle('Premier profil')->setNbMaxInscriptions(10)->setPreinscription(false)->setNbMaxInscriptionsRessource(0);
        $deuxiemeProfil = (new ProfilUtilisateur())->setLibelle('Deuxième profil')->setNbMaxInscriptions(10)->setPreinscription(false)->setNbMaxInscriptionsRessource(0);

        $em->persist($premierProfil);
        $em->persist($deuxiemeProfil);
        $em->flush();

        $ressource = (new Materiel())->addProfilsUtilisateur(new RessourceProfilUtilisateur(new Materiel(), $premierProfil, 10));

        $this->assertIsBool($ressource->hasProfil($premierProfil));
        $this->assertTrue($ressource->hasProfil($premierProfil));

        $this->assertIsBool($ressource->hasProfil($deuxiemeProfil));
        $this->assertFalse($ressource->hasProfil($deuxiemeProfil));

        $em->remove($premierProfil);
        $em->remove($deuxiemeProfil);
        $em->flush();
    }
}