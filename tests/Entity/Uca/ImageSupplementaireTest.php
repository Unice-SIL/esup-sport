<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\ImageSupplementaire;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @internal
 * @coversNothing
 */
class ImageSupplementaireTest extends TestCase
{
    /**
     * @var ImageSupplementaire
     */
    private $imageSupplementaire;

    /**
     * Fonction qui s'exécute avant chaque test.
     */
    protected function setUp(): void
    {
        $this->imageSupplementaire = new ImageSupplementaire();
    }

    /**
     * @covers \App\Entity\Uca\ImageSupplementaire::setImageFile
     */
    public function testSetImageFile(): void
    {
        $file = new File(__DIR__.'../../../fixtures/test.pdf');
        $this->imageSupplementaire->setImageFile($file);

        $this->assertEquals($file, $this->imageSupplementaire->getImageFile());
    }
}