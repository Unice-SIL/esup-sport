<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\Creneau;
use App\Entity\Uca\CreneauProfilUtilisateur;
use App\Entity\Uca\DhtmlxEvenement;
use App\Entity\Uca\DhtmlxSerie;
use App\Entity\Uca\FormatAvecCreneau;
use App\Entity\Uca\Inscription;
use App\Entity\Uca\ProfilUtilisateur;
use App\Entity\Uca\TypeAutorisation;
use App\Entity\Uca\Utilisateur;
use App\Service\Common\Fctn;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class CreneauTest extends TestCase
{
    /**
     * @var Creneau
     */
    private $creneau;

    /**
     * Fonction qui s'exécute avant chaque test.
     */
    protected function setUp(): void
    {
        $this->date = new \Datetime();
        $this->user = new Utilisateur();

        $this->serie = new DhtmlxSerie();
        $this->formatActivite = (new FormatAvecCreneau())
            ->setLibelle('FormatAvecCreneau')
            ->setDateDebutEffective($this->date)
            ->setDateFinEffective($this->date)
        ;

        $this->creneau = (new Creneau())
            ->setSerie($this->serie)
            ->setFormatActivite($this->formatActivite)
        ;

        $this->libelle = $this->creneau->getArticleLibelle();
        $this->description = $this->creneau->getArticleDescription();

        $this->inscription =
            (new Inscription($this->creneau, $this->user, null))
                ->setStatut('valide')
            ;

        $this->creneau->addInscription($this->inscription);

        $this->profilUtilisateur1 =
            (new ProfilUtilisateur())
                ->setLibelle('Profil utilisateur1')
            ;

        $this->profilUtilisateur2 =
            (new ProfilUtilisateur())
                ->setLibelle('Profil utilisateur2')
            ;

        $this->profilUtilisateur3 =
            (new ProfilUtilisateur())
                ->setLibelle('Profil utilisateur')
            ;

        $creneauProfilUtilisateur1 = (new CreneauProfilUtilisateur($this->creneau, $this->profilUtilisateur1, 10));
        $creneauProfilUtilisateur2 = (new CreneauProfilUtilisateur($this->creneau, $this->profilUtilisateur2, 1));
        $creneauProfilUtilisateur3 = (new CreneauProfilUtilisateur($this->creneau, $this->profilUtilisateur3, 3));

        $this->creneau->addProfilsUtilisateur($creneauProfilUtilisateur1);
        $this->creneau->addProfilsUtilisateur($creneauProfilUtilisateur2);
        $this->creneau->addProfilsUtilisateur($creneauProfilUtilisateur3);
    }

    /**
     * @covers \App\Entity\Uca\Creneau::jsonSerializeProperties
     */
    public function testJsonSerializeProperties(): void
    {
        $arrayProperties = $this->creneau->jsonSerializeProperties();
        $properties = ['capacite', 'tarif', 'profilsUtilisateurs', 'encadrants', 'niveauxSportifs', 'lieu', 'formatActivite'];

        $this->assertIsArray($arrayProperties);

        foreach ($properties as $property) {
            $this->assertContains($property, $arrayProperties);
        }

        $this->assertTrue($arrayProperties == $properties);
    }

    /**
     * @covers \App\Entity\Uca\Creneau::getSerieEvenements
     */
    public function testGetSerieEvenementsSansSite(): void
    {
        $this->assertFalse($this->creneau->getSerieEvenements()->first());
    }

    /**
     * @covers \App\Entity\Uca\Creneau::getArticleLibelle
     */
    public function testGetArticleLibelleSansSite(): void
    {
        $this->assertIsString($this->libelle);

        $this->assertEquals('FormatAvecCreneau []', $this->libelle);
    }

    /**
     * @covers \App\Entity\Uca\Creneau::getArticleDescription
     */
    public function testGetArticleDescriptionSansSite(): void
    {
        $this->assertIsString($this->description);

        $this->assertEquals('', $this->description);
    }

    /**
     * @covers \App\Entity\Uca\Creneau::getSerieEvenements
     */
    public function testGetSerieEvenementsAvecSite(): void
    {
        $evenement =
            (new DhtmlxEvenement())
                ->setSerie($this->serie)
                ->setDependanceSerie(true)
                ->setDescription('evenement Test')
            ;
        $this->creneau->setSerie($this->serie->addEvenement($evenement));

        $evenements = $this->creneau->getSerieEvenements();

        $this->assertEquals($evenement, $evenements->first());
    }

    /**
     * @covers \App\Entity\Uca\Creneau::getArticleLibelle
     */
    public function testGetArticleLibelleAvecSite(): void
    {
        $date = new \Datetime();
        $evenement =
            (new DhtmlxEvenement())
                ->setSerie($this->serie)
                ->setDependanceSerie(true)
                ->setDescription('evenement Test')
                ->setDateDebut($date)
                ->setDateFin($date)
            ;

        $this->creneau->setSerie($this->serie->addEvenement($evenement));
        $this->libelle = $this->creneau->getArticleLibelle();

        $this->assertIsString($this->libelle);

        $this->assertEquals('FormatAvecCreneau ['.Fctn::intlDateFormat($date, 'cccc').' '.$date->format('H:i').' - '.$date->format('H:i').']', $this->libelle);
    }

    /**
     * @covers \App\Entity\Uca\Creneau::getArticleDescription
     */
    public function testGetArticleDescriptionAvecSite(): void
    {
        $date = new \Datetime();
        $this->serie = new DhtmlxSerie();
        $evenement =
            (new DhtmlxEvenement())
                ->setSerie($this->serie)
                ->setDependanceSerie(true)
                ->setDescription('evenement Test')
                ->setDateDebut($date)
                ->setDateFin($date)
            ;

        $this->creneau->setSerie($this->serie->addEvenement($evenement));

        $description = $this->creneau->getArticleDescription();

        $this->assertIsString($description);

        $this->assertEquals('evenement Test', $description);
    }

    /**
     * @covers \App\Entity\Uca\Creneau::getArticleDateDebut
     */
    public function testGetArticleDateDebut(): void
    {
        $date = new \Datetime();

        $this->creneau->setFormatActivite(
            (new FormatAvecCreneau())
                ->setLibelle('FormatAvecCreneau')
                ->setDateDebutEffective($date)
        );

        $dateDebut = $this->creneau->getArticleDateDebut();

        $this->assertEquals($date, $dateDebut);
    }

    /**
     * @covers \App\Entity\Uca\Creneau::getArticleDateFin
     */
    public function testGetArticleDateFin(): void
    {
        $date = new \Datetime();

        $this->creneau->setFormatActivite(
            (new FormatAvecCreneau())
                ->setLibelle('FormatAvecCreneau')
                ->setDateFinEffective($date)
        );

        $dateFin = $this->creneau->getArticleDateFin();

        $this->assertEquals($date, $dateFin);
    }

    /**
     * @covers \App\Entity\Uca\Creneau::getArticleAutorisations
     */
    public function testGetArticleAutorisations(): void
    {
        $autorisation = new TypeAutorisation();

        $this->creneau->setFormatActivite(
            (new FormatAvecCreneau())
                ->setLibelle('FormatAvecCreneau')
                ->addAutorisation($autorisation)
        );

        $autorisations = $this->creneau->getArticleAutorisations();

        $this->assertEquals($autorisation, $autorisations->first());
    }

    /**
     * @covers \App\Entity\Uca\Creneau::getDateDebutInscription
     */
    public function testGetDateDebutInscription(): void
    {
        $date = new \Datetime();

        $this->creneau->setFormatActivite(
            (new FormatAvecCreneau())
                ->setLibelle('FormatAvecCreneau')
                ->setDateDebutInscription($date)
        );

        $dateDebutInscription = $this->creneau->getDateDebutInscription();

        $this->assertEquals($date, $dateDebutInscription);
    }

    /**
     * @covers \App\Entity\Uca\Creneau::getDateFinInscription
     */
    public function testGetDateFinInscription(): void
    {
        $date = new \Datetime();

        $this->creneau->getFormatActivite()
            ->setDateFinInscription($date)
        ;

        $dateFinInscription = $this->creneau->getDateFinInscription();

        $this->assertEquals($date, $dateFinInscription);
    }

    /**
     * @covers \App\Entity\Uca\Creneau::getArticleMontant
     */
    public function testGetArticleMontant(): void
    {
        $tarif = -1;

        $articleMontant = $this->creneau->getArticleMontant(new Utilisateur());

        $this->assertEquals($tarif, $articleMontant);
    }

    /**
     * @covers \App\Entity\Uca\Creneau::getInscriptionsValidee
     */
    public function testGetInscriptionsValidee(): void
    {
        $date = new \Datetime();
        $evenement =
            (new DhtmlxEvenement())
                ->setSerie($this->serie)
                ->setDependanceSerie(true)
                ->setDescription('evenement Test')
                ->setDateDebut($date)
                ->setDateFin($date)
            ;

        $this->creneau->setSerie($this->serie->addEvenement($evenement));

        $inscriptionValidée = $this->creneau->getInscriptionsValidee($this->user);

        $this->assertEquals($this->inscription, $inscriptionValidée->first());
    }

    /**
     * @covers \App\Entity\Uca\Creneau::getAllInscriptions
     */
    public function testGetAllInscriptions(): void
    {
        $inscriptions = $this->creneau->getAllInscriptions($this->user);

        $this->assertEquals($this->inscription, $inscriptions->first());
    }

    /**
     * @covers \App\Entity\Uca\Creneau::getCapaciteTousProfil
     */
    public function testGetCapaciteTousProfil(): void
    {
        $capaciteTousProfil = $this->creneau->getCapaciteTousProfil();
        $this->assertEquals(14, $capaciteTousProfil);
    }

    /**
     * @covers \App\Entity\Uca\Creneau::getCapaciteProfil
     */
    public function testGetCapaciteProfil(): void
    {
        $capacite1 = $this->creneau->getCapaciteProfil($this->profilUtilisateur3);
        $this->assertEquals(3, $capacite1);

        $capacite2 = $this->creneau->getCapaciteProfil(new profilUtilisateur());
        $this->assertEquals(0, $capacite2);
    }
}
