<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\ComportementAutorisation;
use App\Entity\Uca\ProfilUtilisateur;
use App\Entity\Uca\Tarif;
use App\Entity\Uca\TypeAutorisation;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class TypeAutorisationTest extends TestCase
{
    /**
     * @var TypeAutorisation
     */
    private $typeAutorisation;

    /**
     * Fonction qui s'exécute avant chaque test.
     */
    protected function setUp(): void
    {
        $this->typeAutorisation = new TypeAutorisation();

        $this->typeAutorisation->setLibelle('libelle');
        $this->typeAutorisation->setComportement((new ComportementAutorisation())->setdescriptionComportement('desc')->setLibelle('libelle comportement'));
    }

    /**
     * @covers \App\Entity\Uca\TypeAutorisation::jsonSerializeProperties
     */
    public function testJsonSerializeProperties(): void
    {
        $arrayProperties = $this->typeAutorisation->jsonSerializeProperties();
        $properties = ['libelle', 'tarif', 'comportement', 'informationsComplementaires', 'montant'];

        $this->assertIsArray($arrayProperties);

        foreach ($properties as $property) {
            $this->assertContains($property, $arrayProperties);
        }

        $this->assertTrue($arrayProperties == $properties);
    }

    /**
     * @covers \App\Entity\Uca\TypeAutorisation::getArticleLibelle
     */
    public function testGetArticleLibelle(): void
    {
        $this->assertEquals('libelle', $this->typeAutorisation->getArticleLibelle());
    }

    /**
     * @covers \App\Entity\Uca\TypeAutorisation::getArticleDescription
     */
    public function testGetArticleDescription(): void
    {
        $this->assertEquals('desc', $this->typeAutorisation->getArticleDescription());

        $this->typeAutorisation->setInformationsComplementaires('infos comp');
        $this->assertEquals('infos comp', $this->typeAutorisation->getArticleDescription());
    }

    /**
     * @covers \App\Entity\Uca\TypeAutorisation::getArticleDateDebut
     */
    public function testGetArticleDateDebut(): void
    {
        $this->assertEquals(null, $this->typeAutorisation->getArticleDateDebut());
    }

    /**
     * @covers \App\Entity\Uca\TypeAutorisation::getArticleDateFin
     */
    public function testGetArticleDateFin(): void
    {
        $this->assertEquals(null, $this->typeAutorisation->getArticleDateFin());
    }

    /**
     * @covers \App\Entity\Uca\TypeAutorisation::getCapacite
     */
    public function testGetCapacite(): void
    {
        $this->assertEquals(null, $this->typeAutorisation->getCapacite());
    }

    /**
     * @covers \App\Entity\Uca\TypeAutorisation::getAutorisations
     */
    public function testGetAutorisations(): void
    {
        $this->assertEquals(new \Doctrine\Common\Collections\ArrayCollection(), $this->typeAutorisation->getAutorisations());
    }

    /**
     * @covers \App\Entity\Uca\TypeAutorisation::getEncadrants
     */
    public function testGetEncadrants(): void
    {
        $this->assertEquals(new \Doctrine\Common\Collections\ArrayCollection(), $this->typeAutorisation->getEncadrants());
    }

    /**
     * @covers \App\Entity\Uca\TypeAutorisation::getInscriptions
     */
    public function testGetInscriptions(): void
    {
        $this->assertEquals(new \Doctrine\Common\Collections\ArrayCollection(), $this->typeAutorisation->getInscriptions());
    }

    /**
     * @covers \App\Entity\Uca\TypeAutorisation::updateTarifLibelle
     */
    public function testUpdateTarifLibelle(): void
    {
        $this->assertEquals('', $this->typeAutorisation->updateTarifLibelle()->getTarifLibelle());

        $this->typeAutorisation->setTarif((new Tarif())->setLibelle('tarif'));

        $this->assertEquals('tarif', $this->typeAutorisation->updateTarifLibelle()->getTarifLibelle());
    }

    /**
     * @covers \App\Entity\Uca\TypeAutorisation::updateComportementLibelle
     */
    public function testUpdateComportementLibelle(): void
    {
        $this->assertEquals(null, $this->typeAutorisation->getComportementLibelle());
        $this->typeAutorisation->updateComportementLibelle();
        $this->assertEquals('libelle comportement', $this->typeAutorisation->getComportementLibelle());
    }

    /**
     * @covers \App\Entity\Uca\TypeAutorisation::getArticleMontant
     */
    public function testGetArticleMontant(): void
    {
        $user = new ProfilUtilisateur();
        $this->assertEquals(0, $this->typeAutorisation->getArticleMontant($user));

        $this->typeAutorisation->getComportement()->setCodeComportement('carte');

        $this->assertEquals(-1, $this->typeAutorisation->getArticleMontant($user));
    }
}

// Tasks :
