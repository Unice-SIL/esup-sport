<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\ClasseActivite;
use App\Entity\Uca\TypeActivite;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @internal
 * @coversNothing
 */
class ClasseActiviteTest extends TestCase
{
    /**
     * @var ClasseActivite
     */
    private $classeActivite;

    /**
     * Fonction qui s'exécute avant chaque test.
     */
    protected function setUp(): void
    {
        $this->classeActivite = (new ClasseActivite())
            ->setLibelle('Classe activité')
            ->setTypeActivite(
                (new TypeActivite())
                    ->setLibelle('Type activité')
            )
        ;
    }

    /**
     * @covers \App\Entity\Uca\ClasseActivite::__toString
     */
    public function testToString(): void
    {
        $libelle = $this->classeActivite->__toString();

        $this->assertIsString($libelle);
        $this->assertEquals('Classe activité', $libelle);
    }

    /**
     * @covers \App\Entity\Uca\ClasseActivite::jsonSerializeProperties
     */
    public function testjsonSerializeProperties(): void
    {
        $arrayProperties = $this->classeActivite->jsonSerializeProperties();

        $this->assertIsArray($arrayProperties);
        $this->assertContains('id', $arrayProperties);
        $this->assertContains('libelle', $arrayProperties);
    }

    /**
     * @covers \App\Entity\Uca\ClasseActivite::updateTypeActiviteLibelle
     */
    public function testUpdateTypeActiviteLibelle(): void
    {
        $this->classeActivite->setTypeActiviteLibelle('Type activité modifié');
        $this->classeActivite->updateTypeActiviteLibelle();

        $this->assertEquals('Type activité', $this->classeActivite->getTypeActiviteLibelle());
    }

    /**
     * @covers \App\Entity\Uca\ClasseActivite::setImageFile
     */
    public function testSetImageFile(): void
    {
        $file = new File(__DIR__.'../../../fixtures/test.pdf');
        $this->classeActivite->setImageFile($file);

        $this->assertEquals($file, $this->classeActivite->getImageFile());
    }
}