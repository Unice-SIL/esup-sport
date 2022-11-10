<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\Groupe;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class GroupeTest extends TestCase
{
    /**
     * @var Groupe
     */
    private $groupe;

    /**
     * Fonction qui est appelée avant chaque test.
     */
    protected function setUp(): void
    {
        $this->groupe = new Groupe('Gestionnaire d\'activité', [
            'ROLE_GESTION_ACTIVITE_LECTURE',
            'ROLE_GESTION_ACTIVITE_ECRITURE',
            'ROLE_GESTION_FORMAT_ACTIVITE_LECTURE',
            'ROLE_GESTION_FORMAT_ACTIVITE_ECRITURE',
            'ROLE_GESTION_CLASSE_ACTIVITE_LECTURE',
            'ROLE_GESTION_CLASSE_ACTIVITE_ECRITURE',
            'ROLE_GESTION_TYPE_ACTIVITE_LECTURE',
            'ROLE_GESTION_TYPE_ACTIVITE_ECRITURE',
        ]);
    }

    /**
     * @covers \App\Entity\Uca\Groupe::hasRole
     */
    public function testHasRoleFalse(): void
    {
        $this->assertFalse($this->groupe->hasRole('ROLE_ADMIN'));
    }

    /**
     * @covers \App\Entity\Uca\Groupe::hasRole
     */
    public function testHasRoletrue(): void
    {
        $this->assertTrue($this->groupe->hasRole('ROLE_GESTION_ACTIVITE_LECTURE'));
    }
}