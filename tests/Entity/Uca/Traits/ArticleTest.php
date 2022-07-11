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

        $tarif = (new Tarif())
        ;

        $tarif->addMontant(new MontantTarifProfilUtilisateur($tarif, $this->profil, 10));
        $tarif->setPourcentageTva(20);

        $this->format->setTarif($tarif);

        $this->utilisateur->setProfil($this->profil);

        $this->formatActiviteProfil = new FormatActiviteProfilUtilisateur($this->format, $this->profil, 1);

        $this->formatActiviteProfil->setNbInscrits(2);

        $this->formatReserv = (new FormatAvecReservation())
            ->addProfilsUtilisateur($this->formatActiviteProfil)
        ;

        $this->format->addProfilsUtilisateur($this->formatActiviteProfil);

        $this->inscription = new Inscription($this->format, $this->utilisateur, []);

        $this->format->getCarte()->setTarif($tarif);
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
        $this->assertEquals($this->format->getArticleTva($this->utilisateur), 1.6666666666667);
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
     * @covers \App\Entity\Uca\Traits\Article::getInscriptionInformations
     */
    public function testGetInscriptionInformations(): void
    {
        $this->assertEquals($this->format->getInscriptionInformations($this->utilisateur), [
            'montant' => [
                'article' => -1,
                'total' => -1,
            ],
            'statut' => 'cgvnonacceptees',
        ]);
        $this->assertEquals($this->format->getInscriptionInformations(null), [
            'montant' => [
                'article' => -1,
                'total' => -1,
            ],
            'statut' => 'nonconnecte',
        ]);

        $reservabilite = new Reservabilite();

        $reservabilite->getInscriptionInformations($this->utilisateur);
        $reservabilite->getInscriptionInformations(null);
        $reservabilite->getInscriptionInformations(null, $this->format);

        $this->utilisateur->setCgvAcceptees(true);
        $this->assertEquals($this->format->getInscriptionInformations($this->utilisateur), [
            'montant' => [
                'total' => 10,
                'article' => 0,
                'autorisations' => 10,
                'format' => 0,
            ],
            'statut' => 'preinscrit',
        ]);

        $this->utilisateur->getInscriptions()->first()->setStatut('valide');
        $this->assertEquals($this->format->getInscriptionInformations($this->utilisateur), [
            'montant' => [
                'total' => 10,
                'article' => 0,
                'autorisations' => 10,
                'format' => 0,
            ],
            'statut' => 'inscrit',
        ]);

        $this->utilisateur->removeInscription($this->inscription);
        $this->assertEquals($this->format->getInscriptionInformations($this->utilisateur), [
            'montant' => [
                'total' => 10,
                'article' => 0,
                'autorisations' => 10,
                'format' => 0,
            ],
            'statut' => 'complet',
        ]);

        $this->format->setCapacite(100);

        $creneau = (new Creneau())
            ->setCapacite(10)
        ;

        $creneauProfil = new CreneauProfilUtilisateur($creneau, $this->profil, 1);
        $creneau->addProfilsUtilisateur($creneauProfil);

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

        $creneau->setSerie($serie->addEvenement($evenement));

        $creneau->setFormatActivite(
            (new FormatAvecCreneau())
                ->setLibelle('FormatAvecCreneau')
        );

        $this->assertEquals($creneau->getInscriptionInformations($this->utilisateur, $creneau, 0), [
            'montant' => [
                'total' => -1,
                'article' => -1,
                'autorisations' => 0,
                'format' => -1,
            ],
            'statut' => 'nbcreneaumaxatteint',
        ]);

        $ressource =
            (new Lieu())
                ->setLibelle('lieu')
                ->setTarif(new Tarif())
            ;

        $reservabilite->setCapacite(10);
        $reservabilite->setEvenement($evenement);
        $reservabilite->setRessource($ressource);
        $reservabilite->setFormatActivite(
            $this->format
        );

        $reservabiliteProfil = new ReservabiliteProfilUtilisateur($reservabilite, $this->profil, 1);
        $reservabilite->addProfilsUtilisateur($reservabiliteProfil);

        $this->inscription->setReservabilite($reservabilite);

        $this->assertEquals($reservabilite->getInscriptionInformations($this->utilisateur, $reservabilite), [
            'montant' => [
                'total' => -1,
                'article' => -1,
                'autorisations' => 0,
                'format' => -1,
            ],
            'statut' => 'nbressourcemaxatteint',
        ]);

        $reservabilite->setEvenement($evenement);

        $this->assertEquals($creneau->getInscriptionInformations($this->utilisateur, $creneau), [
            'montant' => [
                'total' => -1,
                'article' => -1,
                'autorisations' => 0,
                'format' => -1,
            ],
            'statut' => 'inscriptionsterminees',
        ]);

        $this->format
            ->setCapacite(100)
            ->setDateDebutInscription(new \DateTime('tomorrow'))
            ->setDateFinInscription(new \DateTime('tomorrow'))
        ;

        $this->formatActiviteProfil->setNbInscrits(0);
        $this->assertEquals($this->format->getInscriptionInformations($this->utilisateur, $this->format), [
            'montant' => [
                'total' => 10,
                'article' => 0,
                'autorisations' => 10,
                'format' => 0,
            ],
            'statut' => 'inscriptionsavenir',
        ]);
        $this->utilisateur->getProfil()->setNbMaxInscriptionsRessource(10);
        $this->utilisateur->removeInscription($this->inscription);
        $this->format->setDateDebutInscription(new \Datetime());
        // $this->assertEquals($reservabilite->getInscriptionInformations($this->utilisateur, null, null, $evenement), [
        //     'montant' => [
        //         'total' => -1,
        //         'article' => -1,
        //         'autorisations' => 10,
        //         'format' => 0,
        //     ],
        //     'statut' => 'inscriptionsterminees',
        // ]);
        // $this->assertEquals($reservabilite->getInscriptionInformations($this->utilisateur), [
        //     'montant' => [
        //         'total' => -1,
        //         'article' => -1,
        //         'autorisations' => 10,
        //         'format' => 0,
        //     ],
        //     'statut' => 'montantincorrect',
        // ]);
        $reservabilite->getRessource()->setTarif($this->format->getTarif());
        // $this->assertEquals($reservabilite->getInscriptionInformations($this->utilisateur), [
        //     'montant' => [
        //         'total' => 20,
        //         'article' => 10,
        //         'autorisations' => 10,
        //         'format' => 0,
        //     ],
        //     'statut' => 'disponible',
        // ]);
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
