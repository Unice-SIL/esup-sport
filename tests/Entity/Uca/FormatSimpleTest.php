<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\DhtmlxEvenement;
use App\Entity\Uca\FormatSimple;
use DateTime;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class FormatSimpleTest extends TestCase
{
    /**
     * @var FormatSimple
     */
    private $formatSimple;

    /**
     * Fonction qui est appelé avant chaque test.
     */
    protected function setUp(): void
    {
        $this->formatSimple = new FormatSimple();
    }

    /**
     * @covers \App\Entity\Uca\FormatSimple::__construct
     */
    public function testConstruct(): void
    {
        $format = new FormatSimple();

        $this->assertInstanceOf(FormatSimple::class, $format);
        $this->assertInstanceOf(DhtmlxEvenement::class, $format->getEvenement());
    }

    /**
     * @covers \App\Entity\Uca\FormatSimple::setLibelle
     */
    public function testSetLibelle(): void
    {
        $this->formatSimple->setLibelle('Libellé');

        $this->assertEquals('Libellé', $this->formatSimple->getEvenement()->getLibelle());
    }

    /**
     * @covers \App\Entity\Uca\FormatSimple::getArticleDescription
     */
    public function testGetArticleDescription(): void
    {
        $this->formatSimple->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.');

        $this->assertEquals(
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labo...',
            $this->formatSimple->getArticleDescription()
        );
    }

    /**
     * @covers \App\Entity\Uca\FormatSimple::setDateDebutEffective
     */
    public function testSetDateDebutEffective(): void
    {
        $now = new DateTime();
        $this->formatSimple->setDateDebutEffective($now);

        $this->assertEquals(
            $now,
            $this->formatSimple->getDateDebutEffective()
        );
    }

    /**
     * @covers \App\Entity\Uca\FormatSimple::setDateFinEffective
     */
    public function testSetDateFinEffective(): void
    {
        $now = new DateTime();
        $this->formatSimple->setDateFinEffective($now);

        $this->assertEquals(
            $now,
            $this->formatSimple->getDateFinEffective()
        );
    }

    /**
     * @covers \App\Entity\Uca\FormatSimple::getArticleLibelle
     */
    public function testGetArticleLibelle(): void
    {
        $this->formatSimple->setLibelle('Libellé');
        $now = new DateTime();
        $this->formatSimple->setDateDebutEffective($now);

        $this->assertEquals(
            'Libellé ['.$now->format('d/m/Y H:i').']',
            $this->formatSimple->getArticleLibelle()
        );
    }
}