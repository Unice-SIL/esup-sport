<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\ShnuRubrique;
use DateTime;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @internal
 * @coversNothing
 */
class ShnuRubriqueTest extends TestCase
{
    /**
     * @covers \App\Entity\Uca\ShnuRubrique::setImageFile
     */
    public function testSetImageFile(): void
    {
        $file = new File(dirname(__DIR__, 2).'/fixtures/test.pdf');
        $rubrique = (new ShnuRubrique())->setImageFile($file);

        $this->assertInstanceOf(File::class, $rubrique->getImageFile());
        $this->assertEquals($file, $rubrique->getImageFile());
        $this->assertInstanceOf(DateTime::class, $rubrique->getUpdatedAt());
    }
}
