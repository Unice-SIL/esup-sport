<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\Fichier;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @internal
 * @coversNothing
 */
class FichierTest extends TestCase
{
    /**
     * @var Fichier
     */
    private $fichier;

    /**
     * Fonction qui s'exécute avant chaque test.
     */
    protected function setUp(): void
    {
        $this->fichier = new Fichier();
    }

    /**
     * @covers \App\Entity\Uca\Fichier::setImageFile
     */
    public function testSetImageFile(): void
    {
        $file = new File(dirname(__DIR__, 2).'/fixtures/test.pdf');
        $this->fichier->setImageFile($file);

        $this->assertEquals($file, $this->fichier->getImageFile());
    }
}