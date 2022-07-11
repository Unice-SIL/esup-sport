<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\FormatAchatCarte;
use App\Entity\Uca\TypeAutorisation;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class FormatAchatCarteTest extends TestCase
{
    /**
     * @var FormatAchatCarte
     */
    private $formatAchatCarte;

    /**
     * Fonction qui s'exécute avant chaque test.
     */
    protected function setUp(): void
    {
        $this->formatAchatCarte = new FormatAchatCarte();
    }

    /**
     * @covers \App\Entity\Uca\FormatAchatCarte::getArticleAutorisations
     */
    public function testGetArticleAutorisations(): void
    {
        $carte = new TypeAutorisation();
        $this->formatAchatCarte->setCarte($carte);

        $autorisations = $this->formatAchatCarte->getArticleAutorisations();
        $this->assertTrue(1 == sizeof($autorisations) && $autorisations->first() == $carte);
    }

    /**
     * @covers \App\Entity\Uca\FormatAchatCarte::getArticleLibelle
     */
    public function testGetArticleLibelle(): void
    {
        $this->formatAchatCarte->setLibelle('libelle');

        $this->assertIsString($this->formatAchatCarte->getArticleLibelle());
        $this->assertTrue('libelle' === $this->formatAchatCarte->getArticleLibelle());
    }

    /**
     * @covers \App\Entity\Uca\FormatAchatCarte::getArticleDescription
     */
    public function testGetArticleDescription(): void
    {
        $this->formatAchatCarte->setDescription('Description');

        $this->assertIsString($this->formatAchatCarte->getArticleDescription());
        $this->assertEquals('Description', $this->formatAchatCarte->getArticleDescription());
    }

    /**
     * @covers \App\Entity\Uca\FormatAchatCarte::updateCarteLibelle
     */
    public function testUpdateCarteLibelle(): void
    {
        /*$this->formatAchatCarte->setDescription('Description');

        $this->assertIsString($this->formatAchatCarte->updateCarteLibelle());
        $this->assertEquals('Description', $this->formatAchatCarte->updateCarteLibelle());*/

        $carte =
            (new TypeAutorisation())
                ->setLibelle('carte')
            ;

        $this->formatAchatCarte->setCarte($carte);

        $this->assertEquals('carte', $this->formatAchatCarte->updateCarteLibelle()->getCarteLibelle());

        $this->formatAchatCarte->setCarte(null);

        $this->assertEquals('', $this->formatAchatCarte->updateCarteLibelle()->getCarteLibelle());
    }
}
