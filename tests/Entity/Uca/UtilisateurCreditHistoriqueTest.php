<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\Utilisateur;
use App\Entity\Uca\UtilisateurCreditHistorique;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class UtilisateurCreditHistoriqueTest extends TestCase
{
    /**
     * @var UtilisateurCreditHistorique
     */
    private $credit;

    protected function setUp(): void
    {
        $this->credit = new UtilisateurCreditHistorique(
            new Utilisateur(),
            10,
            null,
            'credit',
            'Ajout manuel'
        );
    }

    /**
     * @covers \App\Entity\Uca\UtilisateurCreditHistorique::__construct
     */
    public function testConstruct(): void
    {
        $this->assertInstanceOf(UtilisateurCreditHistorique::class, $this->credit);
    }

    /**
     * @covers \App\Entity\Uca\UtilisateurCreditHistorique::getMontant
     */
    public function testGetMontantCredit(): void
    {
        $this->assertEquals(10, $this->credit->getMontant());
    }

    /**
     * @covers \App\Entity\Uca\UtilisateurCreditHistorique::getMontant
     */
    public function testGetMontantAutre(): void
    {
        $this->credit->setTypeOperation('debit');

        $this->assertEquals(-10, $this->credit->getMontant());
    }
}