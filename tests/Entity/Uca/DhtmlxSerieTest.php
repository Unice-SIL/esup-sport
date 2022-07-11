<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\Activite;
use App\Entity\Uca\Creneau;
use App\Entity\Uca\DhtmlxSerie;
use App\Entity\Uca\Etablissement;
use App\Entity\Uca\FormatAvecCreneau;
use App\Entity\Uca\Lieu;
use App\Entity\Uca\Materiel;
use App\Entity\Uca\Reservabilite;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class DhtmlxSerieTest extends TestCase
{
    /**
     * @var DhtmlxSerie
     */
    private $serie;

    protected function setUp(): void
    {
        $this->serie = new DhtmlxSerie();
    }

    /**
     * @covers \App\Entity\Uca\DhtmlxSerie::jsonSerializeProperties
     */
    public function testJsonSerializeProperties(): void
    {
        $arrayProperties = $this->serie->jsonSerializeProperties();

        $this->assertIsArray($arrayProperties);

        $this->assertContains('dateDebut', $arrayProperties);
        $this->assertContains('dateFin', $arrayProperties);
        $this->assertContains('evenements', $arrayProperties);
        $this->assertContains('recurrence', $arrayProperties);
        $this->assertContains('dateFinSerie', $arrayProperties);
        $this->assertContains('creneau', $arrayProperties);
        $this->assertContains('oldId', $arrayProperties);
        $this->assertContains('action', $arrayProperties);
        $this->assertContains('reservabilite', $arrayProperties);
    }

    /**
     * @covers \App\Entity\Uca\DhtmlxSerie::getEtablissementLibelle
     */
    public function testGetEtablissementLibelleCreneau(): void
    {
        $this->serie->setCreneau(
            (new Creneau())
                ->setLieu(
                    (new Lieu())
                        ->setLibelle('Lieu')
                )
        );

        $this->assertEquals('Lieu', $this->serie->getEtablissementLibelle());
    }

    /**
     * @covers \App\Entity\Uca\DhtmlxSerie::getEtablissementLibelle
     */
    public function testGetEtablissementLibelleReservabilite(): void
    {
        $this->serie->setReservabilite(
            (new Reservabilite())
                ->setRessource(
                    (new Materiel())
                        ->setEtablissement(
                            (new Etablissement())
                                ->setLibelle('Etablissement')
                        )
                )
        );

        $this->serie->getReservabilite()->getRessource()->updateEtablissementLibelle();

        $this->assertEquals('Etablissement', $this->serie->getEtablissementLibelle());
    }

    /**
     * @covers \App\Entity\Uca\DhtmlxSerie::getEtablissementLibelle
     */
    public function testGetEtablissementLibelle(): void
    {
        $this->assertEquals('', $this->serie->getEtablissementLibelle());
    }

    /**
     * @covers \App\Entity\Uca\DhtmlxSerie::getFormatActiviteLibelle
     */
    public function testFormatActiviteLibelleCreneau(): void
    {
        $this->serie->setCreneau(
            (new Creneau())
                ->setFormatActivite(
                    (new FormatAvecCreneau())
                        ->setLibelle('Format simple')
                )
        );

        $this->assertEquals('Format simple', $this->serie->getFormatActiviteLibelle());
    }

    /**
     * @covers \App\Entity\Uca\DhtmlxSerie::getFormatActiviteLibelle
     */
    public function testFormatActiviteLibelleReservabilite(): void
    {
        $this->serie->setReservabilite(
            (new Reservabilite())
                ->setRessource(
                    (new Materiel())
                        ->setLibelle('Matériel')
                )
        );

        $this->assertEquals('Matériel', $this->serie->getFormatActiviteLibelle());
    }

    /**
     * @covers \App\Entity\Uca\DhtmlxSerie::getFormatActiviteLibelle
     */
    public function testFormatActiviteLibelle(): void
    {
        $this->assertEquals('', $this->serie->getFormatActiviteLibelle());
    }

    /**
     * @covers \App\Entity\Uca\DhtmlxSerie::getActiviteLibelle
     */
    public function testActiviteLibelleCreneau(): void
    {
        $this->serie->setCreneau(
            (new Creneau())
                ->setFormatActivite(
                    (new FormatAvecCreneau())
                        ->setActivite(
                            (new Activite())
                                ->setLibelle('Activité')
                        )
                )
        );

        $this->assertEquals('Activité', $this->serie->getActiviteLibelle());
    }

    /**
     * @covers \App\Entity\Uca\DhtmlxSerie::getActiviteLibelle
     */
    public function testActiviteLibelleReservabilite(): void
    {
        $this->serie->setReservabilite(
            (new Reservabilite())
                ->setRessource(
                    (new Materiel())
                        ->setLibelle('Matériel')
                )
        );

        $this->assertEquals('Matériel', $this->serie->getActiviteLibelle());
    }

    /**
     * @covers \App\Entity\Uca\DhtmlxSerie::getActiviteLibelle
     */
    public function testActiviteLibelle(): void
    {
        $this->assertEquals('', $this->serie->getActiviteLibelle());
    }
}