<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\Autorisation;
use App\Entity\Uca\ComportementAutorisation;
use App\Entity\Uca\FormatSimple;
use App\Entity\Uca\Inscription;
use App\Entity\Uca\TypeAutorisation;
use App\Entity\Uca\Utilisateur;
use DateTime;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @internal
 * @coversNothing
 */
class AutorisationTest extends TestCase
{
    /**
     * @var Autorisation
     */
    private $autorisation;

    protected function setUp(): void
    {
        $this->autorisation = new Autorisation(
            new Inscription(
                (new FormatSimple())->setDateDebutEffective(new DateTime())->setDateFinEffective(new DateTime()),
                new Utilisateur(),
                []
            ),
            (new TypeAutorisation())
                ->setComportement((new ComportementAutorisation())->setLibelle('Comportement autorisation')->setCodeComportement('case'))
                ->setInformationsComplementaires('Infos complémentaires')
        );
    }

    /**
     * @covers \App\Entity\Uca\Autorisation::__construct
     */
    public function testConstruct(): void
    {
        $this->assertInstanceOf(Autorisation::class, $this->autorisation);
    }

    /**
     * @covers \App\Entity\Uca\Autorisation::getCodeComportement
     */
    public function testGetCodeComportement(): void
    {
        $this->assertEquals('case', $this->autorisation->getCodeComportement());
    }

    /**
     * @covers \App\Entity\Uca\Autorisation::getInformationsComplementaires
     */
    public function testGetInformationsComplementaires(): void
    {
        $this->assertEquals('Infos complémentaires', $this->autorisation->getInformationsComplementaires());
    }

    /**
     * @covers \App\Entity\Uca\Autorisation::setJustificatifFile
     */
    public function testSetJustificatifFile(): void
    {
        $file = new File(dirname(__DIR__, 2).'/fixtures/test.pdf');
        $this->autorisation->setJustificatifFile($file);

        $this->assertEquals($file, $this->autorisation->getJustificatifFile());
    }

    /**
     * @covers \App\Entity\Uca\Autorisation::updateStatut
     */
    public function testUpdateStatutCase(): void
    {
        $this->autorisation->setCaseACocher(true);
        $this->autorisation->updateStatut();
        $this->assertEquals('valide', $this->autorisation->getStatut());
    }

    /**
     * @covers \App\Entity\Uca\Autorisation::updateStatut
     */
    public function testUpdateStatutCarte(): void
    {
        $this->autorisation->getTypeAutorisation()->getComportement()->setCodeComportement('carte');
        $this->autorisation->updateStatut();
        $this->assertEquals('invalide', $this->autorisation->getStatut());
    }

    /**
     * @covers \App\Entity\Uca\Autorisation::updateStatut
     */
    public function testUpdateStatutCotisation(): void
    {
        $this->autorisation->getTypeAutorisation()->getComportement()->setCodeComportement('cotisation');
        $this->autorisation->updateStatut();
        $this->assertEquals('invalide', $this->autorisation->getStatut());
    }

    /**
     * @covers \App\Entity\Uca\Autorisation::updateStatut
     */
    public function testUpdateStatutJustificatif(): void
    {
        $this->autorisation->getTypeAutorisation()->getComportement()->setCodeComportement('justificatif');
        $this->autorisation->updateStatut();
        $this->assertEquals('invalide', $this->autorisation->getStatut());
    }

    /**
     * @covers \App\Entity\Uca\Autorisation::updateStatut
     */
    public function testUpdateStatutValidationEncadrant(): void
    {
        $this->autorisation->getTypeAutorisation()->getComportement()->setCodeComportement('validationencadrant');
        $this->autorisation->updateStatut();
        $this->assertEquals('invalide', $this->autorisation->getStatut());
    }

    /**
     * @covers \App\Entity\Uca\Autorisation::updateStatut
     */
    public function testUpdateStatutValidationGestionnaire(): void
    {
        $this->autorisation->getTypeAutorisation()->getComportement()->setCodeComportement('validationgestionnaire');
        $this->autorisation->updateStatut();
        $this->assertEquals('invalide', $this->autorisation->getStatut());
    }
}