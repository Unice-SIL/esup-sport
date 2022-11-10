<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\Etablissement;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @internal
 * @coversNothing
 */
class EtablissementTest extends TestCase
{
    /**
     * @var Etablissement
     */
    private $etablissement;

    /**
     * Fonction qui s'exécute avant chaque test.
     */
    protected function setUp(): void
    {
        $this->etablissement = (new Etablissement())
            ->setLibelle('Activité')
        ;
    }

    /**
     * @covers \App\Entity\Uca\Etablissement::setImageFile
     */
    public function testSetImageFile(): void
    {
        $file = new File(__DIR__.'../../../fixtures/test.pdf');
        $this->etablissement->setImageFile($file);

        $this->assertEquals($file, $this->etablissement->getImageFile());
    }
}