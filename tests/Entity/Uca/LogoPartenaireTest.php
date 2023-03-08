<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\LogoPartenaire;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @internal
 * @coversNothing
 */
class LogoPartenaireTest extends TestCase
{
    /**
     * @var LogoPartenaire
     */
    private $logoPartenaire;

    /**
     * Fonction qui s'exécute avant chaque test.
     */
    protected function setUp(): void
    {
        $this->logoPartenaire = new LogoPartenaire();
    }

    /**
     * @covers \App\Entity\Uca\LogoPartenaire::setImageFile
     */
    public function testSetImageFile(): void
    {
        $file = new File(dirname(__DIR__, 2).'/fixtures/test.pdf');
        $this->logoPartenaire->setImageFile($file);

        $this->assertEquals($file, $this->logoPartenaire->getImageFile());
    }
}