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
        $this->formatAchatCarte->setLibelle('libelle');
        $this->formatAchatCarte->setDescription('Description');

        $this->carte =
            (new TypeAutorisation())
                ->setLibelle('carte')
            ;

        $this->formatAchatCarte->setCarte($this->carte);
    }

    /**
     * @covers \App\Entity\Uca\FormatAchatCarte::getArticleAutorisations
     */
    public function testGetArticleAutorisations(): void
    {
        $autorisations = $this->formatAchatCarte->getArticleAutorisations();
        $this->assertTrue(1 == sizeof($autorisations) && $autorisations->first() == $this->carte);
    }

    /**
     * @covers \App\Entity\Uca\FormatAchatCarte::getArticleLibelle
     */
    public function testGetArticleLibelle(): void
    {
        $this->assertIsString($this->formatAchatCarte->getArticleLibelle());
        $this->assertTrue('libelle' === $this->formatAchatCarte->getArticleLibelle());
    }

    /**
     * @covers \App\Entity\Uca\FormatAchatCarte::getArticleDescription
     */
    public function testGetArticleDescription(): void
    {
        $this->assertIsString($this->formatAchatCarte->getArticleDescription());
        $this->assertEquals('Description', $this->formatAchatCarte->getArticleDescription());
    }

    /**
     * @covers \App\Entity\Uca\FormatAchatCarte::updateCarteLibelle
     */
    public function testUpdateCarteLibelle(): void
    {
        $this->assertEquals('carte', $this->formatAchatCarte->updateCarteLibelle()->getCarteLibelle());

        $this->formatAchatCarte->setCarte(null);

        $this->assertEquals('', $this->formatAchatCarte->updateCarteLibelle()->getCarteLibelle());
    }
}
