<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\DhtmlxEvenement;
use App\Entity\Uca\DhtmlxSerie;
use App\Entity\Uca\FormatAvecCreneau;
use App\Entity\Uca\Lieu;
use App\Entity\Uca\ProfilUtilisateur;
use App\Entity\Uca\Reservabilite;
use App\Entity\Uca\ReservabiliteProfilUtilisateur;
use App\Entity\Uca\Tarif;
use App\Entity\Uca\TypeAutorisation;
use App\Entity\Uca\Utilisateur;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ReservabiliteTest extends TestCase
{
    /**
     * @var Reservabilite
     */
    private $reservabilite;

    /**
     * Fonction qui s'exécute avant chaque test.
     */
    protected function setUp(): void
    {
        $this->reservabilite = new Reservabilite();

        $this->tarif = new Tarif();
        $this->autorisation = new TypeAutorisation();
        $this->date = new \DateTime();
        $this->serie = new DhtmlxSerie();
        $this->encadrant = new Utilisateur();
        $this->evenement1 =
            (new DhtmlxEvenement())
                ->setSerie($this->serie)
                ->setDependanceSerie(true)
                ->setDescription('evenement Test')
                ->setDateDebut($this->date)
                ->setDateFin($this->date)
            ;

        $this->evenement2 =
            (new DhtmlxEvenement())
                ->setSerie($this->serie)
                ->setDependanceSerie(true)
                ->setDescription('evenement Test')
                ->setDateDebut(new \DateTime('tomorrow'))
                ->setDateFin(new \DateTime('tomorrow'))
        ;

        $this->ressource =
            (new Lieu())
                ->setLibelle('lieu')
                ->setTarif(new Tarif())
            ;

        $this->reservabilite->setFormatActivite(
            (new FormatAvecCreneau())
                ->setLibelle('FormatAvecCreneau')
                ->addAutorisation($this->autorisation)
                ->addEncadrant($this->encadrant)
        );

        $this->reservabilite->setEvenement($this->evenement1);
        $this->reservabilite->setRessource($this->ressource);
    }

    /**
     * @covers \App\Entity\Uca\Reservabilite::jsonSerializeProperties
     */
    public function testJsonSerializeProperties(): void
    {
        $arrayProperties = $this->reservabilite->jsonSerializeProperties();
        $properties = ['capacite', 'profilsUtilisateurs', 'ressource'];

        $this->assertIsArray($arrayProperties);

        $this->assertTrue($arrayProperties == $properties);
    }

    /**
     * @covers \App\Entity\Uca\Reservabilite::getTarif
     */
    public function testGetTarif(): void
    {
        $this->reservabilite->setRessource($this->ressource);

        $this->assertEquals($this->reservabilite->getTarif(), $this->tarif);
    }

    /**
     * @covers \App\Entity\Uca\Reservabilite::getArticleLibelle
     */
    public function testGetArticleLibelle(): void
    {
        $this->assertIsString($this->reservabilite->getArticleLibelle());
        $this->assertEquals($this->reservabilite->getArticleLibelle(), 'lieu'.' ['.$this->date->format('d/m/Y H:i').' - '.$this->date->format('d/m/Y H:i').']');
    }

    /**
     * @covers \App\Entity\Uca\Reservabilite::getArticleDescription
     */
    public function testGetArticleDescription(): void
    {
        $this->assertIsString($this->reservabilite->getArticleDescription());
        $this->assertEquals($this->reservabilite->getArticleDescription(), 'evenement Test');
    }

    /**
     * @covers \App\Entity\Uca\Reservabilite::getArticleDateDebut
     */
    public function testGetArticleDateDebut(): void
    {
        $this->assertEquals($this->reservabilite->getArticleDateDebut(), $this->date);
    }

    /**
     * @covers \App\Entity\Uca\Reservabilite::getArticleDateFin
     */
    public function testGetArticleDateFin(): void
    {
        $this->assertEquals($this->reservabilite->getArticleDateFin(), $this->date);
    }

    /**
     * @covers \App\Entity\Uca\Reservabilite::getAutorisations
     */
    public function testGetAutorisations(): void
    {
        $this->assertEquals($this->reservabilite->getAutorisations()->first(), $this->autorisation);
    }

    /**
     * @covers \App\Entity\Uca\Reservabilite::getEncadrants
     */
    public function testGetEncadrants(): void
    {
        $this->assertEquals($this->reservabilite->getEncadrants()->first(), $this->encadrant);
    }

    /**
     * @covers \App\Entity\Uca\Reservabilite::dateReservationPasse
     */
    public function testDateReservationPasse(): void
    {
        $this->assertTrue($this->reservabilite->dateReservationPasse($this->evenement1));
        $this->assertFalse($this->reservabilite->dateReservationPasse($this->evenement2));
    }

    /**
     * @covers \App\Entity\Uca\Reservabilite::getArticleMontant
     */
    public function testGetArticleMontant(): void
    {
        $this->assertEquals(-1, $this->reservabilite->getArticleMontant($this->encadrant));
    }

    /**
     * @covers \App\Entity\Uca\Reservabilite::getCapaciteProfil
     */
    public function testGetCapaciteProfil(): void
    {
        $profilUtilisateur = new Profilutilisateur();
        $this->reservabilite->addProfilsUtilisateur(
            (new ReservabiliteProfilUtilisateur($this->reservabilite, $profilUtilisateur, 14))
                ->setNbInscrits(10)
        );

        $this->assertEquals(14, $this->reservabilite->getCapaciteProfil($profilUtilisateur));
    }
}
