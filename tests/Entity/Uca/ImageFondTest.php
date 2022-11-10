<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\ImageFond;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @internal
 * @coversNothing
 */
class ImageFondTest extends TestCase
{
    /**
     * @var ImageFond
     */
    private $imageFond;

    /**
     * Fonction qui s'exécute avant chaque test.
     */
    protected function setUp(): void
    {
        $this->imageFond = new ImageFond();
    }

    /**
     * @covers \App\Entity\Uca\ImageFond::setImageFile
     */
    public function testSetImageFile(): void
    {
        $file = new File(__DIR__.'../../../fixtures/test.pdf');
        $this->imageFond->setImageFile($file);

        $this->assertEquals($file, $this->imageFond->getImageFile());
    }
}