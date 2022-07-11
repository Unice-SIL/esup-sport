<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\Activite;
use App\Entity\Uca\ClasseActivite;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @internal
 * @coversNothing
 */
class ActiviteTest extends TestCase
{
    /**
     * @var Activite
     */
    private $activite;

    /**
     * Fonction qui s'exécute avant chaque test.
     */
    protected function setUp(): void
    {
        $this->activite = (new Activite())
            ->setLibelle('Activité')
            ->setDescription('Description activité')
            ->setClasseActivite(
                (new ClasseActivite())->setLibelle('Classe activité')
            )
        ;
    }

    /**
     * @covers \App\Entity\Uca\Activite::jsonSerializeProperties
     */
    public function testjsonSerializeProperties(): void
    {
        $arrayProperties = $this->activite->jsonSerializeProperties();

        $this->assertIsArray($arrayProperties);
        $this->assertContains('id', $arrayProperties);
        $this->assertContains('libelle', $arrayProperties);
        $this->assertContains('description', $arrayProperties);
        $this->assertContains('image', $arrayProperties);
        $this->assertContains('classeActivite', $arrayProperties);
    }

    /**
     * @covers \App\Entity\Uca\Activite::getClasseActiviteLibelle
     */
    public function testGetClasseActiviteLibelle(): void
    {
        $this->assertEquals('Classe activité', $this->activite->getClasseActiviteLibelle());
    }

    /**
     * @covers \App\Entity\Uca\Activite::updateClasseActiviteLibelle
     */
    public function testUpdateClasseActiviteLibelle(): void
    {
        $this->activite->setClasseActiviteLibelle('Classe activité modifiée');
        $this->activite->updateClasseActiviteLibelle();

        $this->assertEquals('Classe activité', $this->activite->getClasseActiviteLibelle());
    }

    /**
     * @covers \App\Entity\Uca\Activite::setImageFile
     */
    public function testSetImageFile(): void
    {
        $file = new File(__DIR__.'../../../fixtures/test.pdf');
        $this->activite->setImageFile($file);

        $this->assertEquals($file, $this->activite->getImageFile());
    }
}