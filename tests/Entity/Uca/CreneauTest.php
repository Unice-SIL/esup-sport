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
        $this->creneau = new Creneau();

        $this->creneau->setSerie(new DhtmlxSerie());
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
        $this->creneau->setFormatActivite(
            (new FormatAvecCreneau())
                ->setLibelle('FormatAvecCreneau')
        );

        $libelle = $this->creneau->getArticleLibelle();

        $this->assertIsString($libelle);

        $this->assertEquals('FormatAvecCreneau []', $libelle);
    }

    /**
     * @covers \App\Entity\Uca\Creneau::getArticleDescription
     */
    public function testGetArticleDescriptionSansSite(): void
    {
        $libelle = $this->creneau->getArticleDescription();

        $this->assertIsString($libelle);

        $this->assertEquals('', $libelle);
    }

    /**
     * @covers \App\Entity\Uca\Creneau::getSerieEvenements
     */
    public function testGetSerieEvenementsAvecSite(): void
    {
        $serie = new DhtmlxSerie();
        $evenement =
            (new DhtmlxEvenement())
                ->setSerie($serie)
                ->setDependanceSerie(true)
                ->setDescription('evenement Test')
            ;
        $this->creneau->setSerie($serie->addEvenement($evenement));

        $evenements = $this->creneau->getSerieEvenements();

        $this->assertEquals($evenement, $evenements->first());
    }

    /**
     * @covers \App\Entity\Uca\Creneau::getArticleLibelle
     */
    public function testGetArticleLibelleAvecSite(): void
    {
        $date = new \Datetime();
        $serie = new DhtmlxSerie();
        $evenement =
            (new DhtmlxEvenement())
                ->setSerie($serie)
                ->setDependanceSerie(true)
                ->setDescription('evenement Test')
                ->setDateDebut($date)
                ->setDateFin($date)
            ;

        $this->creneau->setSerie($serie->addEvenement($evenement));

        $this->creneau->setFormatActivite(
            (new FormatAvecCreneau())
                ->setLibelle('FormatAvecCreneau')
        );

        $libelle = $this->creneau->getArticleLibelle();

        $this->assertIsString($libelle);

        $this->assertEquals('FormatAvecCreneau ['.Fctn::intlDateFormat($date, 'cccc').' '.$date->format('H:i').' - '.$date->format('H:i').']', $libelle);
    }

    /**
     * @covers \App\Entity\Uca\Creneau::getArticleDescription
     */
    public function testGetArticleDescriptionAvecSite(): void
    {
        $date = new \Datetime();
        $serie = new DhtmlxSerie();
        $evenement =
            (new DhtmlxEvenement())
                ->setSerie($serie)
                ->setDependanceSerie(true)
                ->setDescription('evenement Test')
                ->setDateDebut($date)
                ->setDateFin($date)
            ;

        $this->creneau->setSerie($serie->addEvenement($evenement));

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

        $dateDebut = $this->creneau->getArticleDateFin();

        $this->assertEquals($date, $dateDebut);
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

        $this->creneau->setFormatActivite(
            (new FormatAvecCreneau())
                ->setLibelle('FormatAvecCreneau')
                ->setDateFinInscription($date)
        );

        $dateFinInscription = $this->creneau->getDateFinInscription();

        $this->assertEquals($date, $dateFinInscription);
    }

    /**
     * @covers \App\Entity\Uca\Creneau::getArticleMontant
     */
    public function testGetArticleMontant(): void
    {
        $tarif = -1;

        $this->creneau->setFormatActivite(
            (new FormatAvecCreneau())
                ->setLibelle('FormatAvecCreneau')
        );

        $articleMontant = $this->creneau->getArticleMontant(new Utilisateur());

        $this->assertEquals($tarif, $articleMontant);
    }

    /**
     * @covers \App\Entity\Uca\Creneau::getInscriptionsValidee
     */
    public function testGetInscriptionsValidee(): void
    {
        $user = new Utilisateur();

        $date = new \Datetime();
        $serie = new DhtmlxSerie();
        $evenement =
            (new DhtmlxEvenement())
                ->setSerie($serie)
                ->setDependanceSerie(true)
                ->setDescription('evenement Test')
                ->setDateDebut($date)
                ->setDateFin($date)
            ;

        $this->creneau->setSerie($serie->addEvenement($evenement));

        $this->creneau->setFormatActivite(
            (new FormatAvecCreneau())
                ->setLibelle('FormatAvecCreneau')
        );

        $inscription =
            (new Inscription($this->creneau, $user, null))
                ->setStatut('valide')
            ;

        $this->creneau->addInscription($inscription);

        $inscriptionValidée = $this->creneau->getInscriptionsValidee($user);

        $this->assertEquals($inscription, $inscriptionValidée->first());
    }

    /**
     * @covers \App\Entity\Uca\Creneau::getAllInscriptions
     */
    public function testGetAllInscriptions(): void
    {
        $user = new Utilisateur();

        $this->creneau->setFormatActivite(
            (new FormatAvecCreneau())
                ->setLibelle('FormatAvecCreneau')
        );

        $inscription = new Inscription($this->creneau, $user, null);

        $this->creneau->addInscription($inscription);

        $inscriptions = $this->creneau->getAllInscriptions($user);

        $this->assertEquals($inscription, $inscriptions->first());
    }

    /**
     * @covers \App\Entity\Uca\Creneau::getCapaciteTousProfil
     */
    public function testGetCapaciteTousProfil(): void
    {
        $profilUtilisateur1 =
            (new ProfilUtilisateur())
                ->setLibelle('Profil utilisateur1')
            ;

        $profilUtilisateur2 =
            (new ProfilUtilisateur())
                ->setLibelle('Profil utilisateur2')
            ;

        $creneauProfilUtilisateur1 = (new CreneauProfilUtilisateur($this->creneau, $profilUtilisateur1, 10));
        $creneauProfilUtilisateur2 = (new CreneauProfilUtilisateur($this->creneau, $profilUtilisateur2, 1));

        $this->creneau->addProfilsUtilisateur($creneauProfilUtilisateur1);
        $this->creneau->addProfilsUtilisateur($creneauProfilUtilisateur2);

        $capaciteTousProfil = $this->creneau->getCapaciteTousProfil();
        $this->assertEquals(11, $capaciteTousProfil);
    }

    /**
     * @covers \App\Entity\Uca\Creneau::getCapaciteProfil
     */
    public function testGetCapaciteProfil(): void
    {
        $profilUtilisateur =
            (new ProfilUtilisateur())
                ->setLibelle('Profil utilisateur')
            ;

        $creneauProfilUtilisateur = (new CreneauProfilUtilisateur($this->creneau, $profilUtilisateur, 3));

        $this->creneau->addProfilsUtilisateur($creneauProfilUtilisateur);

        $capacite1 = $this->creneau->getCapaciteProfil($profilUtilisateur);
        $this->assertEquals(3, $capacite1);

        $capacite2 = $this->creneau->getCapaciteProfil(new profilUtilisateur());
        $this->assertEquals(0, $capacite2);
    }
}
