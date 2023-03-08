<?php

namespace App\Tests\Entity\Uca\Traits;

use App\Entity\Uca\ComportementAutorisation;
use App\Entity\Uca\Creneau;
use App\Entity\Uca\CreneauProfilUtilisateur;
use App\Entity\Uca\DhtmlxEvenement;
use App\Entity\Uca\DhtmlxSerie;
use App\Entity\Uca\FormatAchatCarte;
use App\Entity\Uca\FormatActiviteProfilUtilisateur;
use App\Entity\Uca\FormatAvecCreneau;
use App\Entity\Uca\FormatAvecReservation;
use App\Entity\Uca\Inscription;
use App\Entity\Uca\Lieu;
use App\Entity\Uca\MontantTarifProfilUtilisateur;
use App\Entity\Uca\ProfilUtilisateur;
use App\Entity\Uca\Reservabilite;
use App\Entity\Uca\ReservabiliteProfilUtilisateur;
use App\Entity\Uca\Tarif;
use App\Entity\Uca\Traits\Article;
use App\Entity\Uca\TypeAutorisation;
use App\Entity\Uca\Utilisateur;
use App\Service\Common\Previsualisation;
use DateInterval;
use DateTime;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ArticleTest extends TestCase
{
    /**
     * @var Article
     */
    private $article;

    /**
     * Fonction qui s'exécute avant chaque test.
     */
    protected function setUp(): void
    {
        $comportementAutorisationFormat = (new ComportementAutorisation())
            ->setdescriptionComportement('desc')
            ->setLibelle('libelle comportement')
            ->setCodeComportement('carte')
        ;

        $this->typeAutorisationFormat = (new TypeAutorisation())
            ->setComportement(
                $comportementAutorisationFormat
            )
            ->setLibelle('')
        ;

        $this->format =
        (new FormatAchatCarte())
            ->setCarte(
                $this->typeAutorisationFormat
            )
        ;

        $this->utilisateur = new Utilisateur();
        $this->profil = (new ProfilUtilisateur())
            ->addUtilisateur($this->utilisateur)
        ;

        $this->tarif = (new Tarif());

        $this->tarif->addMontant(new MontantTarifProfilUtilisateur($this->tarif, $this->profil, 10));
        $this->tarif->setPourcentageTva(20);

        $this->format->setTarif($this->tarif);

        $this->utilisateur->setProfil($this->profil);

        $this->formatActiviteProfil = new FormatActiviteProfilUtilisateur($this->format, $this->profil, 10);

        $this->formatActiviteProfil->setNbInscrits(2);

        $this->formatReserv = (new FormatAvecReservation())
            ->addProfilsUtilisateur($this->formatActiviteProfil)
        ;

        $this->format->addProfilsUtilisateur($this->formatActiviteProfil);

        $this->inscription = new Inscription($this->format, $this->utilisateur, []);

        $this->format->getCarte()->setTarif($this->tarif);
    }

    /**
     * @covers \App\Entity\Uca\Traits\Article::getArticleType
     */
    public function testGetArticleType(): void
    {
        $this->assertEquals($this->format->getArticleType(), 'FormatAchatCarte');
    }

    /**
     * @covers \App\Entity\Uca\Traits\Article::getArticleMontantDefaut
     */
    public function testGetArticleMontantDefaut(): void
    {
        $this->assertEquals($this->format->getArticleMontantDefaut($this->utilisateur), 10);
    }

    /**
     * @covers \App\Entity\Uca\Traits\Article::getArticleTva
     */
    public function testGetArticleTva(): void
    {
        $this->assertEquals($this->format->getArticleTva($this->utilisateur), 2);
    }

    /**
     * @covers \App\Entity\Uca\Traits\Article::inscriptionsTerminees
     */
    public function testInscriptionsTerminees(): void
    {
        $this->format->setDateFinInscription(new \DateTime());
        $this->assertTrue($this->format->inscriptionsTerminees());

        $this->format->setDateFinInscription(new \DateTime('tomorrow'));
        $this->assertFalse($this->format->inscriptionsTerminees());
    }

    /**
     * @covers \App\Entity\Uca\Traits\Article::inscriptionsAVenir
     */
    public function testInscriptionsAVenir(): void
    {
        $this->format->setDateDebutInscription(new \DateTime());
        $this->assertFalse($this->format->inscriptionsAVenir());

        $this->format->setDateDebutInscription(new \DateTime('tomorrow'));
        $this->assertTrue($this->format->inscriptionsAVenir());
    }

    /**
     * @covers \App\Entity\Uca\Traits\Article::isNotFull
     */
    public function testIsNotFull(): void
    {
        $this->assertFalse($this->format->isNotFull($this->utilisateur, $this->format));

        $this->formatActiviteProfil->setNbInscrits(0);

        $this->assertFalse($this->format->isNotFull($this->utilisateur, $this->formatReserv));
        
        
        $formatFull = (new FormatAchatCarte())
            ->setCarte(
                $this->typeAutorisationFormat
            )
        ;
        $formatActiviteProfilFull = new FormatActiviteProfilUtilisateur($formatFull, $this->profil, 10);
        $formatActiviteProfilFull->setNbInscrits(10);
        $formatFull->addProfilsUtilisateur($formatActiviteProfilFull);
        $this->assertFalse($formatFull->isNotFull($this->utilisateur, $formatFull));
    }

    /**
     * @covers \App\Entity\Uca\Traits\Article::isFull
     */
    public function testIsFull(): void
    {
        $this->assertTrue($this->format->isFull($this->utilisateur, $this->format));
    }

    /**
     * @covers \App\Entity\Uca\Traits\Article::getArticleAutorisations
     */
    public function testGetArticleAutorisations(): void
    {
        $reservabilite = new Reservabilite();
        $ressource =
            (new Lieu())
                ->setLibelle('lieu1')
                ->setTarif(new Tarif())
        ;

        $reservabilite->setRessource($ressource);

        $typeAutorisation = (new TypeAutorisation())
            ->setLibelle('')
        ;

        $formatActivite =
            (new FormatAvecCreneau())
                ->setLibelle('FormatAvecCreneau')
                ->addAutorisation($typeAutorisation)
                ->setCapacite(10)
        ;

        $reservabilite->setFormatActivite($formatActivite);

        $this->assertEquals($reservabilite->getArticleAutorisations()->first(), $typeAutorisation);
    }

    /**
     * @covers App\Entity\Uca\Traits\Article::getInscriptionInformations
     */
    public function testGetInscriptionInformationsCGVNonAcceptees(): void
    {
        $result = $this->format->getInscriptionInformations($this->utilisateur);
        $this->assertEquals($result, [
            'montant' => [
                'article' => -1,
                'total' => -1,
            ],
            'statut' => 'cgvnonacceptees',
        ]);
    }

    /**
     * @covers App\Entity\Uca\Traits\Article::getInscriptionInformations
     */
    public function testGetInscriptionInformationsNonConnecte(): void
    {
        $result = $this->format->getInscriptionInformations(null);
        $this->assertEquals($result, [
            'montant' => [
                'article' => -1,
                'total' => -1,
            ],
            'statut' => 'nonconnecte',
        ]);
    }

    /**
     * @covers App\Entity\Uca\Traits\Article::getInscriptionInformations
     */
    public function testGetInscriptionInformationsInscritFormatCarteAvecAutorisation(): void
    {
        $this->utilisateur->setCgvAcceptees(true);
        $this->utilisateur->addAutorisation($this->format->getCarte());
        $result = $this->format->getInscriptionInformations($this->utilisateur);
        $this->assertEquals([
            'montant' => [
                'total' => 0,
                'article' => 0,
                'autorisations' => 0,
                'format' => 0,
            ],
            'statut' => 'inscrit',
        ], $result);
    }

    /**
     * @covers App\Entity\Uca\Traits\Article::getInscriptionInformations
     */
    public function testGetInscriptionInformationsProfilInvalide(): void
    {
        $this->utilisateur->setCgvAcceptees(true);
        $this->formatActiviteProfil->setCapaciteProfil(0);
        $result = $this->format->getInscriptionInformations($this->utilisateur);
        $this->assertEquals([
            'montant' => [
                'total' => -1,
                'article' => -1,
            ],
            'statut' => 'profilinvalide',
        ], $result);
    }

    /**
     * @covers App\Entity\Uca\Traits\Article::getInscriptionInformations
     */
    public function testGetInscriptionInformationsInscritFormatReserv(): void
    {
        $this->utilisateur->setCgvAcceptees(true);
        $this->formatReserv
            ->setCapacite(10)
            ->setDateDebutInscription((new DateTime())->sub(new DateInterval('P1D')))
            ->setDateFinInscription((new DateTime())->add(new DateInterval('P1D')))
        ;
        $inscription = new Inscription($this->formatReserv, $this->utilisateur, []);
        $this->utilisateur->removeInscription($this->inscription);
        $this->utilisateur->addInscription($inscription);
        $this->utilisateur->getInscriptions()->first()->setStatut('valide');
        $result = $this->formatReserv->getInscriptionInformations($this->utilisateur);
        $this->assertEquals([
            'montant' => [
                'total' => 0,
                'article' => 0,
                'autorisations' => 0,
                'format' => 0,
            ],
            'statut' => 'inscrit',
        ], $result);
    }

    /**
     * @covers App\Entity\Uca\Traits\Article::getInscriptionInformations
     */
    public function testGetInscriptionInformationsPreinscritFormatReserv(): void
    {
        $this->utilisateur->setCgvAcceptees(true);
        $this->formatReserv
            ->setCapacite(10)
            ->setDateDebutInscription((new DateTime())->sub(new DateInterval('P1D')))
            ->setDateFinInscription((new DateTime())->add(new DateInterval('P1D')))
        ;
        $inscription = new Inscription($this->formatReserv, $this->utilisateur, []);
        $result = $this->formatReserv->getInscriptionInformations($this->utilisateur);
        $this->assertEquals([
            'montant' => [
                'total' => 0,
                'article' => 0,
                'autorisations' => 0,
                'format' => 0,
            ],
            'statut' => 'preinscrit',
        ], $result);
    }

    /**
     * @covers App\Entity\Uca\Traits\Article::getInscriptionInformations
     */
    public function testGetInscriptionInformationsComplet(): void
    {
        $this->utilisateur->setCgvAcceptees(true);
        $this->formatReserv->setCapacite(1);
        $result = $this->formatReserv->getInscriptionInformations($this->utilisateur);
        $this->assertEquals([
            'montant' => [
                'total' => 0,
                'article' => 0,
                'autorisations' => 0,
                'format' => 0,
            ],
            'statut' => 'complet',
        ], $result);
    }

    /**
     * @covers App\Entity\Uca\Traits\Article::getInscriptionInformations
     */
    public function testGetInscriptionInformationsPrevisualisation(): void
    {
        Previsualisation::$IS_ACTIVE = true;
        $this->utilisateur->setCgvAcceptees(true);
        $this->formatReserv->setCapacite(1);
        $result = $this->formatReserv->getInscriptionInformations($this->utilisateur);
        $this->assertEquals([
            'montant' => [
                'total' => 0,
                'article' => 0,
                'autorisations' => 0,
                'format' => 0,
            ],
            'statut' => 'previsualisation',
        ], $result);
        Previsualisation::$IS_ACTIVE = false;
    }

    /**
     * @covers App\Entity\Uca\Traits\Article::getInscriptionInformations
     */
    public function testGetInscriptionInformationsMaxCreneau(): void
    {
        $this->utilisateur->setCgvAcceptees(true);

        $formatCreneau = (new FormatAvecCreneau())
            ->setLibelle('FormatAvecCreneau')
        ;

        $creneau = (new Creneau())
            ->setCapacite(10)
        ;

        $creneauProfil = new CreneauProfilUtilisateur($creneau, $this->profil, 1);
        $creneau->addProfilsUtilisateur($creneauProfil);

        $creneau->setFormatActivite($formatCreneau);

        $dateDeb = (new \Datetime())->sub(new DateInterval('P1D'));
        $dateFin = (new \Datetime())->add(new DateInterval('P1D'));
        $serie = new DhtmlxSerie();
        $evenement =
            (new DhtmlxEvenement())
                ->setSerie($serie)
                ->setDependanceSerie(true)
                ->setDescription('evenement Test')
                ->setDateDebut($dateDeb)
                ->setDateFin($dateFin)
        ;

        $creneau->setSerie($serie->addEvenement($evenement));

        $result = $creneau->getInscriptionInformations($this->utilisateur, $creneau, 0);
        $this->assertEquals([
            'montant' => [
                'total' => -1,
                'article' => -1,
                'autorisations' => 0,
                'format' => -1,
            ],
            'statut' => 'nbcreneaumaxatteint',
        ], $result);
    }

    /**
     * @covers App\Entity\Uca\Traits\Article::getInscriptionInformations
     */
    public function testGetInscriptionInformationsCreneauForteFrequence(): void
    {
        $this->utilisateur->setCgvAcceptees(true);

        $dateDeb = (new \Datetime())->sub(new DateInterval('P1D'));
        $dateFin = (new \Datetime())->add(new DateInterval('P1D'));

        $formatCreneau = (new FormatAvecCreneau())
            ->setLibelle('FormatAvecCreneau')
            ->setDateDebutInscription($dateDeb)
            ->setDateFinInscription($dateFin)
            ->setTarif($this->tarif)
        ;

        $creneauFF = (new Creneau())
            ->setCapacite(10)
            ->setTarif($this->tarif)
        ;

        $creneau = (new Creneau())
            ->setCapacite(10)
            ->setTarif($this->tarif)
        ;

        $creneauProfil = new CreneauProfilUtilisateur($creneau, $this->profil, 10);
        $creneau->addProfilsUtilisateur($creneauProfil);
        $creneauFFProfil = new CreneauProfilUtilisateur($creneauFF, $this->profil, 10);
        $creneauFF->addProfilsUtilisateur($creneauFFProfil);

        $creneau->setFormatActivite($formatCreneau);
        $creneauFF->setFormatActivite($formatCreneau);

        $formatCreneau->addCreneaux($creneau);
        $formatCreneau->addCreneaux($creneauFF);

        $serieFF = new DhtmlxSerie();
        $evenementForteFrequence =
            (new DhtmlxEvenement())
                ->setSerie($serieFF)
                ->setDependanceSerie(true)
                ->setDescription('evenement Test')
                ->setForteFrequence(true)
                ->setDateDebut($dateDeb)
                ->setDateFin($dateFin)
        ;

        $creneauFF->setSerie($serieFF->addEvenement($evenementForteFrequence));
        $inscription = new Inscription($creneauFF, $this->utilisateur, []);

        $serie = new DhtmlxSerie();
        $evenement =
            (new DhtmlxEvenement())
                ->setSerie($serie)
                ->setDependanceSerie(true)
                ->setDescription('evenement Test')
                ->setForteFrequence(true)
                ->setDateDebut($dateDeb)
                ->setDateFin($dateFin)
        ;

        $creneau->setSerie($serie->addEvenement($evenement));


        $result = $creneau->getInscriptionInformations($this->utilisateur);
        $this->assertEquals([
            'montant' => [
                'total' => 10,
                'article' => 10,
                'autorisations' => 0,
                'format' => 0,
            ],
            'statut' => 'fortefrequence',
        ], $result);
    }

    /**
     * @covers App\Entity\Uca\Traits\Article::getInscriptionInformations
     */
    public function testGetInscriptionInformationsCreneauForteFrequenceDisponible(): void
    {
        $this->utilisateur->setCgvAcceptees(true);

        $dateDeb = (new \Datetime())->sub(new DateInterval('P1D'));
        $dateFin = (new \Datetime())->add(new DateInterval('P1D'));

        $formatCreneau = (new FormatAvecCreneau())
            ->setLibelle('FormatAvecCreneau')
            ->setDateDebutInscription($dateDeb)
            ->setDateFinInscription($dateFin)
            ->setTarif($this->tarif)
        ;

        $creneau = (new Creneau())
            ->setCapacite(10)
            ->setTarif($this->tarif)
        ;

        $creneauProfil = new CreneauProfilUtilisateur($creneau, $this->profil, 10);
        $creneau->addProfilsUtilisateur($creneauProfil);

        $creneau->setFormatActivite($formatCreneau);

        $formatCreneau->addCreneaux($creneau);

        $serie = new DhtmlxSerie();
        $evenement =
            (new DhtmlxEvenement())
                ->setSerie($serie)
                ->setDependanceSerie(true)
                ->setDescription('evenement Test')
                ->setForteFrequence(true)
                ->setDateDebut($dateDeb)
                ->setDateFin($dateFin)
        ;

        $creneau->setSerie($serie->addEvenement($evenement));


        $result = $creneau->getInscriptionInformations($this->utilisateur);
        $this->assertEquals([
            'montant' => [
                'total' => 10,
                'article' => 10,
                'autorisations' => 0,
                'format' => 0,
            ],
            'statut' => 'disponible',
        ], $result);
    }

    /**
     * @covers App\Entity\Uca\Traits\Article::getInscriptionInformations
     */
    public function testGetInscriptionInformationsMaxRessource(): void
    {
        $this->utilisateur->setCgvAcceptees(true);

        $dateDeb = (new \Datetime())->sub(new DateInterval('P1D'));
        $dateFin = (new \Datetime())->add(new DateInterval('P1D'));

        $formatAvecReservation = new FormatAvecReservation();
        $formatAvecReservation
            ->setLibelle("Format avec Reservation")
            ->setCapacite(10)
            ->setDateDebutInscription($dateDeb)
            ->setDateFinInscription($dateFin)
            ->addEncadrant($this->utilisateur)
        ;

        $serie = new DhtmlxSerie();
        $evenement =
            (new DhtmlxEvenement())
                ->setSerie($serie)
                ->setDependanceSerie(true)
                ->setDescription('evenement Test')
                ->setDateDebut($dateDeb)
                ->setDateFin($dateFin)
        ;
        $serie->addEvenement($evenement);

        $ressource =
            (new Lieu())
                ->setLibelle('lieu')
                ->setTarif(new Tarif())
        ;

        $reservabilite = new Reservabilite();
        $reservabilite
            ->setFormatActivite($formatAvecReservation)
            ->setRessource($ressource)
            ->setCapacite(10)
            ->setEvenement($evenement)
        ;

        $ressource->addReservabilite($reservabilite);

        $reservabiliteProfil = new ReservabiliteProfilUtilisateur($reservabilite, $this->profil, 1);
        $reservabilite->addProfilsUtilisateur($reservabiliteProfil);

        $this->assertEquals($reservabilite->getInscriptionInformations($this->utilisateur, $reservabilite), [
            'montant' => [
                'total' => -1,
                'article' => -1,
                'autorisations' => 0,
                'format' => -1,
            ],
            'statut' => 'nbressourcemaxatteint',
        ]);
    }

    /**
     * @covers App\Entity\Uca\Traits\Article::getInscriptionInformations
     */
    public function testGetInscriptionInformationsInscriptionTermineesResa(): void
    {
        $this->utilisateur->setCgvAcceptees(true);
        $this->profil->setNbMaxInscriptionsRessource(100);

        $dateDeb = (new \Datetime())->sub(new DateInterval('P1D'));
        $dateFin = (new \Datetime())->add(new DateInterval('P1D'));

        $formatAvecReservation = new FormatAvecReservation();
        $formatAvecReservation
            ->setLibelle("Format avec Reservation")
            ->setCapacite(10)
            ->setDateDebutInscription($dateDeb)
            ->setDateFinInscription($dateFin)
            ->addEncadrant($this->utilisateur)
        ;

        $serie = new DhtmlxSerie();
        $evenement =
            (new DhtmlxEvenement())
                ->setSerie($serie)
                ->setDependanceSerie(true)
                ->setDescription('evenement Test')
                ->setDateDebut($dateDeb)
                ->setDateFin($dateDeb)
        ;
        $serie->addEvenement($evenement);

        $ressource =
            (new Lieu())
                ->setLibelle('lieu')
                ->setTarif(new Tarif())
        ;

        $reservabilite = new Reservabilite();
        $reservabilite
            ->setFormatActivite($formatAvecReservation)
            ->setRessource($ressource)
            ->setCapacite(100)
            ->setEvenement($evenement)
        ;

        $ressource->addReservabilite($reservabilite);

        $reservabiliteProfil = new ReservabiliteProfilUtilisateur($reservabilite, $this->profil, 100);
        $reservabilite->addProfilsUtilisateur($reservabiliteProfil);

        $this->assertEquals($reservabilite->getInscriptionInformations($this->utilisateur, $reservabilite, 100, $evenement), [
            'montant' => [
                'total' => -1,
                'article' => -1,
                'autorisations' => 0,
                'format' => -1,
            ],
            'statut' => 'inscriptionsterminees',
        ]);
    }

    /**
     * @covers App\Entity\Uca\Traits\Article::getInscriptionInformations
     */
    public function testGetInscriptionInformationsMontantIncorrect(): void
    {
        $this->utilisateur->setCgvAcceptees(true);
        $this->profil->setNbMaxInscriptionsRessource(100);

        $dateDeb = (new \Datetime())->sub(new DateInterval('P1D'));
        $dateFin = (new \Datetime())->add(new DateInterval('P1D'));

        $formatAvecReservation = new FormatAvecReservation();
        $formatAvecReservation
            ->setLibelle("Format avec Reservation")
            ->setCapacite(10)
            ->setDateDebutInscription($dateDeb)
            ->setDateFinInscription($dateFin)
            ->addEncadrant($this->utilisateur)
        ;

        $serie = new DhtmlxSerie();
        $evenement =
            (new DhtmlxEvenement())
                ->setSerie($serie)
                ->setDependanceSerie(true)
                ->setDescription('evenement Test')
                ->setDateDebut($dateFin)
                ->setDateFin($dateFin)
        ;
        $serie->addEvenement($evenement);

        $ressource =
            (new Lieu())
                ->setLibelle('lieu')
                ->setTarif(new Tarif())
        ;

        $reservabilite = new Reservabilite();
        $reservabilite
            ->setFormatActivite($formatAvecReservation)
            ->setRessource($ressource)
            ->setCapacite(100)
            ->setEvenement($evenement)
        ;

        $ressource->addReservabilite($reservabilite);

        $reservabiliteProfil = new ReservabiliteProfilUtilisateur($reservabilite, $this->profil, 100);
        $reservabilite->addProfilsUtilisateur($reservabiliteProfil);

        $this->assertEquals($reservabilite->getInscriptionInformations($this->utilisateur, null, 100, $evenement), [
            'montant' => [
                'total' => -1,
                'article' => -1,
                'autorisations' => 0,
                'format' => 0,
            ],
            'statut' => 'montantincorrect',
        ]);
    }

    /**
     * @covers \App\Entity\Uca\Traits\Article::getInscriptionInformations
     */
    public function testGetInscriptionInformationsInscriptionTermineesFormat(): void
    {
        $this->utilisateur->setCgvAcceptees(true);

        $date = (new \Datetime())->sub(new DateInterval('P1D'));

        $this->format
            ->setDateDebutInscription($date)
            ->setDateFinInscription($date)
            ->setCapacite(10)
        ;

        $result = $this->format->getInscriptionInformations($this->utilisateur);
        $this->assertEquals([
            'montant' => [
                'total' => 10,
                'article' => 0,
                'autorisations' => 10,
                'format' => 0,
            ],
            'statut' => 'inscriptionsterminees',
        ], $result);
    }

    /**
     * @covers \App\Entity\Uca\Traits\Article::getInscriptionInformations
     */
    public function testGetInscriptionInformationsInscriptionAVenirFormat(): void
    {
        $this->utilisateur->setCgvAcceptees(true);

        $date = (new \Datetime())->add(new DateInterval('P1D'));

        $this->format
            ->setDateDebutInscription($date)
            ->setDateFinInscription($date)
            ->setCapacite(10)
        ;

        $result = $this->format->getInscriptionInformations($this->utilisateur);
        $this->assertEquals([
            'montant' => [
                'total' => 10,
                'article' => 0,
                'autorisations' => 10,
                'format' => 0,
            ],
            'statut' => 'inscriptionsavenir',
        ], $result);
    }

    /**
     * @covers \App\Entity\Uca\Traits\Article::getInscriptionInformations
     */
    public function testGetInscriptionInformationsInscriptionDisponible(): void
    {
        $this->utilisateur->setCgvAcceptees(true);

        $dateDeb = (new \Datetime())->sub(new DateInterval('P1D'));
        $dateFin = (new \Datetime())->add(new DateInterval('P1D'));

        $this->format
            ->setDateDebutInscription($dateDeb)
            ->setDateFinInscription($dateFin)
            ->setCapacite(10)
        ;

        $this->utilisateur->removeInscription($this->inscription);
        $result = $this->format->getInscriptionInformations($this->utilisateur);
        $this->assertEquals([
            'montant' => [
                'total' => 10,
                'article' => 0,
                'autorisations' => 10,
                'format' => 0,
            ],
            'statut' => 'disponible',
        ], $result);
    }

    /**
     * @covers \App\Entity\Uca\Traits\Article::autoriseProfil
     */
    public function testAutoriseProfil(): void
    {
        $this->assertFalse($this->format->autoriseProfil(new ProfilUtilisateur()));
        $this->assertTrue($this->format->autoriseProfil($this->profil));
    }

    /**
     * @covers \App\Entity\Uca\Traits\Article::getArticleArrayMontant
     */
    public function testGetArticleArrayMontant(): void
    {
        $this->assertEquals($this->format->getArticleArrayMontant($this->utilisateur, $this->format), [
            'total' => 10,
            'article' => 0,
            'autorisations' => 10,
            'format' => 0,
        ]);
        $this->assertEquals($this->format->getArticleArrayMontant($this->utilisateur), [
            'total' => 10,
            'article' => 0,
            'autorisations' => 10,
            'format' => 0,
        ]);
        $reservabilite = new Reservabilite();
        $ressource =
            (new Lieu())
                ->setLibelle('lieu1')
                ->setTarif(new Tarif())
        ;

        $reservabilite->setRessource($ressource);
        $this->assertEquals($reservabilite->getArticleArrayMontant($this->utilisateur, $this->format), [
            'total' => -1,
            'article' => -1,
            'autorisations' => 10,
            'format' => 0,
        ]);
    }

    /**
     * @covers \App\Entity\Uca\Traits\Article::getMontantAutorisations
     */
    public function testGetMontantAutorisations(): void
    {
        $this->assertEquals($this->format->getMontantAutorisations($this->utilisateur), 10);
        $this->format->getCarte()->setTarif(new Tarif());
        $this->assertEquals($this->format->getMontantAutorisations($this->utilisateur), -1);
    }
}
