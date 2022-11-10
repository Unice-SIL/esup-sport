<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\Actualite;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @internal
 * @coversNothing
 */
class ActualiteTest extends TestCase
{
    /**
     * @var Actualite
     */
    private $actualite;

    /**
     * Fonction qui s'exécute avant chaque test.
     */
    protected function setUp(): void
    {
        $this->actualite = (new Actualite())
            ->setTitre('Actualité')
            ->setTexte('Texte actualité')
        ;
    }

    /**
     * @covers \App\Entity\Uca\Actualite::setImageFile
     */
    public function testSetImageFile(): void
    {
        $file = new File(__DIR__.'../../../fixtures/test.pdf');
        $this->actualite->setImageFile($file);

        $this->assertEquals($file, $this->actualite->getImageFile());
    }
}