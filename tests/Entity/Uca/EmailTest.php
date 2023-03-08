<?php

namespace App\Tests\Unit\Entity\Uca;

use App\Entity\Uca\Email;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
    private $email;

    protected function setUp(): void
    {
        $this->email = new Email();
    }

    public function testGetId()
    {
        $this->assertNull($this->email->getId());
    }

    public function testSetAndGetCorps()
    {
        $this->email->setCorps('Corps de l\'email');
        $this->assertEquals('Corps de l\'email', $this->email->getCorps());
    }

    public function testSetAndGetSubject()
    {
        $this->email->setSubject('Sujet de l\'email');
        $this->assertEquals('Sujet de l\'email', $this->email->getSubject());
    }

    public function testSetAndGetNom()
    {
        $this->email->setNom('Nom de l\'email');
        $this->assertEquals('Nom de l\'email', $this->email->getNom());
    }
}