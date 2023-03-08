<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\FormatActiviteNiveauSportif;
use App\Entity\Uca\FormatAvecCreneau;
use App\Entity\Uca\NiveauSportif;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class FormatActiviteNiveauSportifTest extends TestCase
{
    /**
     * @var FormatActiviteNiveauSportif
     */
    private $formatActiviteNiveauSportif;

    /**
     * Fonction qui s'exécute avant chaque test.
     */
    protected function setUp(): void
    {
        $formatActivite = new FormatAvecCreneau();
        $niveauSportif = (new NiveauSportif())->setLibelle('Niveau sportif');

        $this->formatActiviteNiveauSportif = (new FormatActiviteNiveauSportif($formatActivite, $niveauSportif, 'détails du niveau'));
    }

    /**
     * @covers \App\Entity\Uca\FormatActiviteNiveauSportif::jsonSerializeProperties
     */
    public function testJsonSerializeProperties(): void
    {
        $arrayProperties = $this->formatActiviteNiveauSportif->jsonSerializeProperties();

        $this->assertIsArray($arrayProperties);
        $this->assertContains('formatActivite', $arrayProperties);
        $this->assertContains('niveauSportif', $arrayProperties);
        $this->assertContains('detail', $arrayProperties);
        $this->assertContains('Niveau sportif', $arrayProperties);
    }
}
