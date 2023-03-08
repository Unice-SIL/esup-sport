<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\PeriodeFermeture;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class PeriodeFermetureTest extends TestCase
{
    /**
     * @var PeriodeFermeture
     */
    private $periodeFermeture;

    /**
     * Fonction qui s'exécute avant chaque test.
     */
    protected function setUp(): void
    {
        $this->periodeFermeture = (new PeriodeFermeture())
            ->setDescription('Période Test')
            ->setDateDeb(new \DateTime('2023-01-17'))
            ->setDatefin(new \DateTime('2023-01-24'))
        ;
    }

    /**
     * @covers \App\Entity\Uca\PeriodeFermeture::jsonSerializeProperties
     */
    public function testjsonSerializeProperties(): void
    {
        $arrayProperties = $this->periodeFermeture->jsonSerializeProperties();

        $this->assertIsArray($arrayProperties);
        $this->assertContains('description', $arrayProperties);
        $this->assertContains('dateDeb', $arrayProperties);
        $this->assertContains('dateFin', $arrayProperties);
    }
}
