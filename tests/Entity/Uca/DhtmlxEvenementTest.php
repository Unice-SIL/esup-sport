<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\Activite;
use App\Entity\Uca\Creneau;
use App\Entity\Uca\DhtmlxEvenement;
use App\Entity\Uca\DhtmlxSerie;
use App\Entity\Uca\Etablissement;
use App\Entity\Uca\FormatSimple;
use App\Entity\Uca\Lieu;
use App\Entity\Uca\Materiel;
use App\Entity\Uca\Reservabilite;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class DhtmlxEvenementTest extends TestCase
{
    /**
     * @var DhtmlxEvenement
     */
    private $event;

    protected function setUp(): void
    {
        $this->event = new DhtmlxEvenement();
    }

    /**
     * @covers \App\Entity\Uca\DhtmlxEvenement::jsonSerializeProperties
     */
    public function testJsonSerializeProperties(): void
    {
        $arrayProperties = $this->event->jsonSerializeProperties();

        $this->assertIsArray($arrayProperties);

        $this->assertContains('dateDebut', $arrayProperties);
        $this->assertContains('dateFin', $arrayProperties);
        $this->assertContains('dependanceSerie', $arrayProperties);
        $this->assertContains('formatSimple', $arrayProperties);
        $this->assertContains('description', $arrayProperties);
        $this->assertContains('oldId', $arrayProperties);
        $this->assertContains('action', $arrayProperties);
        $this->assertContains('serie', $arrayProperties);
        $this->assertContains('eligibleBonus', $arrayProperties);
        $this->assertContains('reservabilite', $arrayProperties);
        $this->assertContains('informations', $arrayProperties);
    }

    /**
     * @covers \App\Entity\Uca\DhtmlxEvenement::getEtablissementLibelle
     */
    public function testGetEtablissementLibelleFormatSimple(): void
    {
        $this->event->setFormatSimple((new FormatSimple())->addLieu((new Lieu())->setLibelle('Lieu')));

        $this->assertEquals('Lieu', $this->event->getEtablissementLibelle());
    }

    /**
     * @covers \App\Entity\Uca\DhtmlxEvenement::getEtablissementLibelle
     */
    public function testGetEtablissementLibelleReservabilite(): void
    {
        $this->event->setReservabilite(
            (new Reservabilite())
                ->setRessource(
                    (new Materiel())
                        ->setEtablissement(
                            (new Etablissement())
                                ->setLibelle('Etablissement')
                        )
                )
        );

        $this->event->getReservabilite()->getRessource()->updateEtablissementLibelle();

        $this->assertEquals('Etablissement', $this->event->getEtablissementLibelle());
    }

    /**
     * @covers \App\Entity\Uca\DhtmlxEvenement::getEtablissementLibelle
     */
    public function testGetEtablissementLibelleSerie(): void
    {
        $this->event->setSerie(
            (new DhtmlxSerie())
                ->setCreneau(
                    (new Creneau())
                        ->setLieu(
                            (new Lieu())->setLibelle('Lieu')
                        )
                )
        );

        $this->assertEquals('Lieu', $this->event->getEtablissementLibelle());
    }

    /**
     * @covers \App\Entity\Uca\DhtmlxEvenement::getEtablissementLibelle
     */
    public function testGetEtablissementLibelle(): void
    {
        $this->assertEquals('', $this->event->getEtablissementLibelle());
    }

    /**
     * @covers \App\Entity\Uca\DhtmlxEvenement::getFormatActiviteLibelle
     */
    public function testFormatActiviteLibelleFormatSimple(): void
    {
        $this->event->setFormatSimple((new FormatSimple())->setLibelle('Format simple'));

        $this->assertEquals('Format simple', $this->event->getFormatActiviteLibelle());
    }

    /**
     * @covers \App\Entity\Uca\DhtmlxEvenement::getFormatActiviteLibelle
     */
    public function testFormatActiviteLibelleReservabilite(): void
    {
        $this->event->setReservabilite(
            (new Reservabilite())
                ->setRessource(
                    (new Materiel())
                        ->setLibelle('Matériel')
                )
        );

        $this->assertEquals('Matériel', $this->event->getFormatActiviteLibelle());
    }

    /**
     * @covers \App\Entity\Uca\DhtmlxEvenement::getFormatActiviteLibelle
     */
    public function testFormatActiviteLibelleSerie(): void
    {
        $this->event->setSerie(
            (new DhtmlxSerie())
                ->setReservabilite(
                    (new Reservabilite())
                        ->setRessource(
                            (new Materiel())
                                ->setLibelle('Matériel')
                        )
                )
        );

        $this->assertEquals('Matériel', $this->event->getFormatActiviteLibelle());
    }

    /**
     * @covers \App\Entity\Uca\DhtmlxEvenement::getFormatActiviteLibelle
     */
    public function testFormatActiviteLibelle(): void
    {
        $this->assertEquals('', $this->event->getFormatActiviteLibelle());
    }

    /**
     * @covers \App\Entity\Uca\DhtmlxEvenement::getActiviteLibelle
     */
    public function testActiviteLibelleFormatSimple(): void
    {
        $this->event->setFormatSimple((new FormatSimple())->setActivite((new Activite())->setLibelle('Activité')));

        $this->assertEquals('Activité', $this->event->getActiviteLibelle());
    }

    /**
     * @covers \App\Entity\Uca\DhtmlxEvenement::getActiviteLibelle
     */
    public function testActiviteLibelleReservabilite(): void
    {
        $this->event->setReservabilite(
            (new Reservabilite())
                ->setRessource(
                    (new Materiel())
                        ->setLibelle('Matériel')
                )
        );

        $this->assertEquals('Matériel', $this->event->getActiviteLibelle());
    }

    /**
     * @covers \App\Entity\Uca\DhtmlxEvenement::getActiviteLibelle
     */
    public function testActiviteLibelleSerie(): void
    {
        $this->event->setSerie(
            (new DhtmlxSerie())
                ->setReservabilite(
                    (new Reservabilite())
                        ->setRessource(
                            (new Materiel())
                                ->setLibelle('Matériel')
                        )
                )
        );

        $this->assertEquals('Matériel', $this->event->getActiviteLibelle());
    }

    /**
     * @covers \App\Entity\Uca\DhtmlxEvenement::getActiviteLibelle
     */
    public function testActiviteLibelle(): void
    {
        $this->assertEquals('', $this->event->getActiviteLibelle());
    }
}