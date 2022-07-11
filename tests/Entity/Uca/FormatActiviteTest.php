<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\Activite;
use App\Entity\Uca\ClasseActivite;
use App\Entity\Uca\ComportementAutorisation;
use App\Entity\Uca\FormatAchatCarte;
use App\Entity\Uca\FormatActivite;
use App\Entity\Uca\FormatActiviteProfilUtilisateur;
use App\Entity\Uca\FormatAvecCreneau;
use App\Entity\Uca\FormatAvecReservation;
use App\Entity\Uca\FormatSimple;
use App\Entity\Uca\Inscription;
use App\Entity\Uca\Lieu;
use App\Entity\Uca\MontantTarifProfilUtilisateur;
use App\Entity\Uca\NiveauSportif;
use App\Entity\Uca\ProfilUtilisateur;
use App\Entity\Uca\Tarif;
use App\Entity\Uca\TypeActivite;
use App\Entity\Uca\TypeAutorisation;
use App\Entity\Uca\Utilisateur;
use App\Repository\UtilisateurRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @internal
 * @coversNothing
 */
class FormatActiviteTest extends KernelTestCase
{
    /**
     * @covers \App\Entity\Uca\FormatActivite::__construct
     */
    public function testConstructor(): void
    {
        $formatActivite = new FormatSimple();

        $this->assertInstanceOf(FormatActivite::class, $formatActivite);
        $this->assertInstanceOf(ArrayCollection::class, $formatActivite->getInscriptions());
        $this->assertInstanceOf(ArrayCollection::class, $formatActivite->getLieu());
        $this->assertInstanceOf(ArrayCollection::class, $formatActivite->getAutorisations());
        $this->assertInstanceOf(ArrayCollection::class, $formatActivite->getNiveauxSportifs());
        $this->assertInstanceOf(ArrayCollection::class, $formatActivite->getProfilsUtilisateurs());
        $this->assertInstanceOf(ArrayCollection::class, $formatActivite->getEncadrants());
    }

    /**
     * @covers \App\Entity\Uca\FormatActivite::getClasseActiviteLibelle
     */
    public function testGetClasseActiviteLibelle(): void
    {
        $formatActivite = (new FormatSimple())
            ->setActivite(
                (new Activite())
                    ->setLibelle('Activité')
                    ->setClasseActivite(
                        (new ClasseActivite())->setLibelle('Classe activité')
                    )
            )
        ;

        $this->assertIsString($formatActivite->getClasseActiviteLibelle());
        $this->assertEquals('Classe activité', $formatActivite->getClasseActiviteLibelle());
    }

    /**
     * @covers \App\Entity\Uca\FormatActivite::formatIsValid
     */
    public function testFormatIsValid(): void
    {
        // FormatSimple
        $this->assertIsBool(FormatActivite::formatIsValid('FormatSimple'));
        $this->assertTrue(FormatActivite::formatIsValid('FormatSimple'));

        // FormatAvecCreneau
        $this->assertIsBool(FormatActivite::formatIsValid('FormatAvecCreneau'));
        $this->assertTrue(FormatActivite::formatIsValid('FormatAvecCreneau'));

        // FormatAvecReservation
        $this->assertIsBool(FormatActivite::formatIsValid('FormatAvecReservation'));
        $this->assertTrue(FormatActivite::formatIsValid('FormatAvecReservation'));

        // FormatAchatCarte
        $this->assertIsBool(FormatActivite::formatIsValid('FormatAchatCarte'));
        $this->assertTrue(FormatActivite::formatIsValid('FormatAchatCarte'));

        // FormatActivite
        $this->assertIsBool(FormatActivite::formatIsValid('FormatActivite'));
        $this->assertFalse(FormatActivite::formatIsValid('FormatActivite'));
    }

    /**
     * @covers \App\Entity\Uca\FormatActivite::getActiviteLibelle
     */
    public function testGetActiviteLibelle(): void
    {
        $formatActivite = (new FormatSimple())
            ->setActivite(
                (new Activite())
                    ->setLibelle('Activité')
            )
        ;

        $this->assertIsString($formatActivite->getActiviteLibelle());
        $this->assertEquals('Activité', $formatActivite->getActiviteLibelle());
    }

    /**
     * @covers \App\Entity\Uca\FormatActivite::jsonSerializeProperties
     */
    public function testJsonSerializeProperties(): void
    {
        $formatActivite = new FormatSimple();
        $arrayProperties = $formatActivite->jsonSerializeProperties();

        $this->assertIsArray($arrayProperties);
        $this->assertEquals(10, sizeof($arrayProperties));
        $this->assertContains('libelle', $arrayProperties);
        $this->assertContains('description', $arrayProperties);
        $this->assertContains('type', $arrayProperties);
        $this->assertContains('estEncadre', $arrayProperties);
        $this->assertContains('profilsUtilisateurs', $arrayProperties);
        $this->assertContains('niveauxSportifs', $arrayProperties);
        $this->assertContains('encadrants', $arrayProperties);
        $this->assertContains('lieu', $arrayProperties);
        $this->assertContains('dateDebutEffective', $arrayProperties);
        $this->assertContains('dateFinEffective', $arrayProperties);
    }

    /**
     * @covers \App\Entity\Uca\FormatActivite::getType
     */
    public function testGetType(): void
    {
        $formatActivite = new FormatSimple();

        $this->assertNull($formatActivite->getType());
    }

    /**
     * @covers \App\Entity\Uca\FormatActivite::getFormat
     */
    public function testGetFormat(): void
    {
        $formatActivite = new FormatSimple();

        $this->assertIsString($formatActivite->getFormat());
        $this->assertEquals('FormatSimple', $formatActivite->getFormat());
    }

    /**
     * @covers \App\Entity\Uca\FormatActivite::getImageFile
     * @covers \App\Entity\Uca\FormatActivite::setImageFile
     */
    public function testImageFile(): void
    {
        $file = new File(__DIR__.'../../../fixtures/test.pdf');
        $formatActivite = (new FormatSimple())->setImageFile($file);

        $this->assertInstanceOf(File::class, $formatActivite->getImageFile());
        $this->assertEquals($file, $formatActivite->getImageFile());
    }

    /**
     * @covers \App\Entity\Uca\FormatActivite::getIdA
     * @covers \App\Entity\Uca\FormatActivite::getIdCa
     */
    public function testGetIds(): void
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $typeActivite = (new TypeActivite())->setLibelle('Type activité');
        $em->persist($typeActivite);
        $classeActivite = (new ClasseActivite())->setLibelle('Classe activité')->setTypeActivite($typeActivite)->setImage('image');
        $em->persist($classeActivite);
        $activite = (new Activite())->setLibelle('Activité')->setClasseActivite($classeActivite)->setImage('image')->setDescription('Description');
        $em->persist($activite);
        $em->flush();

        $formatActivite = (new FormatSimple())->setActivite($activite);

        $this->assertIsInt($formatActivite->getIdA());
        $this->assertEquals($activite->getId(), $formatActivite->getIdA());

        $this->assertIsInt($formatActivite->getIdCa());
        $this->assertEquals($classeActivite->getId(), $formatActivite->getIdCa());

        $em->remove($typeActivite);
        $em->remove($classeActivite);
        $em->remove($activite);
        $em->flush();
    }

    /**
     * @covers \App\Entity\Uca\FormatActivite::getArticleMontant
     */
    public function testGetArticleMontantGratuit(): void
    {
        $user = static::getContainer()->get(UtilisateurRepository::class)->findOneByUsername('admin');

        $formatActivite = (new FormatSimple())->setEstPayant(false);

        $this->assertIsNumeric($formatActivite->getArticleMontant($user));
        $this->assertEquals(0, $formatActivite->getArticleMontant($user));
    }

    /**
     * @covers \App\Entity\Uca\FormatActivite::getArticleMontant
     * @covers \App\Entity\Uca\Traits\Article::getArticleMontantDefaut
     */
    public function testGetArticleMontantSansTarif(): void
    {
        $user = static::getContainer()->get(UtilisateurRepository::class)->findOneByUsername('admin');

        $formatActivite = (new FormatSimple())->setEstPayant(true);

        $this->assertIsNumeric($formatActivite->getArticleMontant($user));
        $this->assertEquals(-1, $formatActivite->getArticleMontant($user));
    }

    /**
     * @covers \App\Entity\Uca\FormatActivite::getArticleMontant
     * @covers \App\Entity\Uca\Traits\Article::getArticleMontantDefaut
     */
    public function testGetArticleMontantAvecTarif(): void
    {
        $user = static::getContainer()->get(UtilisateurRepository::class)->findOneByUsername('admin');

        $tarif = new Tarif();
        $montant = new MontantTarifProfilUtilisateur(
            $tarif,
            $user->getProfil(),
            10
        );
        $tarif->addMontant($montant);

        $formatActivite = (new FormatSimple())
            ->setEstPayant(true)
            ->setTarif($tarif)
        ;

        $this->assertIsNumeric($formatActivite->getArticleMontant($user));
        $this->assertEquals(10, $formatActivite->getArticleMontant($user));
    }

    /**
     * @covers \App\Entity\Uca\FormatActivite::getArticleLibelle
     */
    public function testGetArticleLibelle(): void
    {
        $formatActivite = (new FormatAvecReservation())->setLibelle('Format activité');

        $this->assertIsString($formatActivite->getLibelle());
        $this->assertEquals('Format activité', $formatActivite->getArticleLibelle());
    }

    /**
     * @covers \App\Entity\Uca\FormatActivite::getArticleDescription
     */
    public function testGetArticleDescription(): void
    {
        $formatActivite = (new FormatAvecReservation())
            ->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.')
        ;

        $this->assertIsString($formatActivite->getArticleDescription());
        $this->assertEquals(
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labo...',
            $formatActivite->getArticleDescription()
        );
    }

    /**
     * @covers \App\Entity\Uca\FormatActivite::getArticleDateDebut
     */
    public function testGetArticleDateDebut(): void
    {
        $now = new DateTime();
        $formatActivite = (new FormatSimple())->setDateDebutEffective($now);

        $this->assertInstanceOf(DateTime::class, $formatActivite->getArticleDateDebut());
        $this->assertEquals($now, $formatActivite->getArticleDateDebut());
    }

    /**
     * @covers \App\Entity\Uca\FormatActivite::getArticleDateFin
     */
    public function testGetArticleDateFin(): void
    {
        $now = new DateTime();
        $formatActivite = (new FormatSimple())->setDateFinEffective($now);

        $this->assertInstanceOf(DateTime::class, $formatActivite->getArticleDateFin());
        $this->assertEquals($now, $formatActivite->getArticleDateFin());
    }

    /**
     * @covers \App\Entity\Uca\FormatActivite::verifieCoherenceDonnees
     */
    public function testVerifieCoherenceDonnees(): void
    {
        $user = static::getContainer()->get(UtilisateurRepository::class)->findOneByUsername('admin');

        $formatActivite = (new FormatSimple())
            ->setEstPayant(false)
            ->setTarif(new Tarif())
            ->setEstEncadre(false)
            ->addEncadrant($user)
        ;

        $formatActivite->verifieCoherenceDonnees();

        $this->assertNull($formatActivite->getTarif());
        $this->assertEmpty($formatActivite->getEncadrants());
    }

    /**
     * @covers \App\Entity\Uca\FormatActivite::updateTarifLibelle
     */
    public function testUpdateTarifLibelle(): void
    {
        $formatActivite = (new FormatSimple())
            ->setEstPayant(false)
            ->setTarif((new Tarif())->setLibelle('Tarif'))
            ->updateTarifLibelle()
        ;

        $this->assertIsString($formatActivite->getTarifLibelle());
        $this->assertEquals('Tarif', $formatActivite->getTarifLibelle());
    }

    /**
     * @covers \App\Entity\Uca\FormatActivite::updateTarifLibelle
     */
    public function testUpdateTarifLibelleNone(): void
    {
        $formatActivite = (new FormatSimple())->updateTarifLibelle();

        $this->assertIsString($formatActivite->getTarifLibelle());
        $this->assertEquals('', $formatActivite->getTarifLibelle());
    }

    /**
     * @covers \App\Entity\Uca\FormatActivite::updateListeLieux
     */
    public function testUpdateListeLieux(): void
    {
        $formatActivite = (new FormatSimple())
            ->addLieu((new Lieu())->setLibelle('Premier lieu'))
            ->addLieu((new Lieu())->setLibelle('Deuxième lieu'))
            ->updateListeLieux()
        ;

        $this->assertIsString($formatActivite->getListeLieux());
        $this->assertEquals('Premier lieu, Deuxième lieu', $formatActivite->getListeLieux());
    }

    /**
     * @covers \App\Entity\Uca\FormatActivite::updateListeLieux
     */
    public function testUpdateListeLieuxNone(): void
    {
        $formatActivite = (new FormatSimple())->updateListeLieux();

        $this->assertIsString($formatActivite->getListeLieux());
        $this->assertEquals('', $formatActivite->getListeLieux());
    }

    /**
     * @covers \App\Entity\Uca\FormatActivite::updateListeAutorisations
     */
    public function testUpdateListeAutorisations(): void
    {
        $formatActivite = (new FormatSimple())
            ->addAutorisation((new TypeAutorisation())->setLibelle('Premier type'))
            ->addAutorisation((new TypeAutorisation())->setLibelle('Deuxième type'))
            ->updateListeAutorisations()
        ;

        $this->assertIsString($formatActivite->getListeAutorisations());
        $this->assertEquals('Premier type, Deuxième type', $formatActivite->getListeAutorisations());
    }

    /**
     * @covers \App\Entity\Uca\FormatActivite::updateListeAutorisations
     */
    public function testUpdateListeAutorisationsNone(): void
    {
        $formatActivite = (new FormatSimple())->updateListeAutorisations();

        $this->assertIsString($formatActivite->getListeAutorisations());
        $this->assertEquals('', $formatActivite->getListeAutorisations());
    }

    /**
     * @covers \App\Entity\Uca\FormatActivite::updateListeNiveauxSportifs
     */
    public function testUpdateListeNiveauxSportifs(): void
    {
        $formatActivite = (new FormatSimple())
            ->addNiveauxSportif((new NiveauSportif())->setLibelle('Premier niveau'))
            ->addNiveauxSportif((new NiveauSportif())->setLibelle('Deuxième niveau'))
            ->updateListeNiveauxSportifs()
        ;

        $this->assertIsString($formatActivite->getListeNiveauxSportifs());
        $this->assertEquals('Premier niveau, Deuxième niveau', $formatActivite->getListeNiveauxSportifs());
    }

    /**
     * @covers \App\Entity\Uca\FormatActivite::updateListeNiveauxSportifs
     */
    public function testUpdateListeNiveauxSportifsNone(): void
    {
        $formatActivite = (new FormatSimple())->updateListeNiveauxSportifs();

        $this->assertIsString($formatActivite->getListeNiveauxSportifs());
        $this->assertEquals('', $formatActivite->getListeNiveauxSportifs());
    }

    /**
     * @covers \App\Entity\Uca\FormatActivite::updateListeProfils
     */
    public function testUpdateListeProfils(): void
    {
        $premierFormatProfil = (new FormatActiviteProfilUtilisateur(
            new FormatSimple(),
            (new ProfilUtilisateur())->setLibelle('Premier profil'),
            10
        ));

        $deuxiemeFormatProfil = (new FormatActiviteProfilUtilisateur(
            new FormatSimple(),
            (new ProfilUtilisateur())->setLibelle('Deuxième profil'),
            10
        ));

        $formatActivite = (new FormatSimple())
            ->addProfilsUtilisateur($premierFormatProfil)
            ->addProfilsUtilisateur($deuxiemeFormatProfil)
            ->updateListeProfils()
        ;

        $this->assertIsString($formatActivite->getListeProfils());
        $this->assertEquals('Premier profil, Deuxième profil', $formatActivite->getListeProfils());
    }

    /**
     * @covers \App\Entity\Uca\FormatActivite::updateListeProfils
     */
    public function testUpdateListeProfilsNone(): void
    {
        $formatActivite = (new FormatSimple())->updateListeProfils();

        $this->assertIsString($formatActivite->getListeProfils());
        $this->assertEquals('', $formatActivite->getListeProfils());
    }

    /**
     * @covers \App\Entity\Uca\FormatActivite::updateListeEncadrants
     */
    public function testUpdateListeEncadrants(): void
    {
        $formatActivite = (new FormatSimple())
            ->addEncadrant((new Utilisateur())->setNom('Encadrant')->setPrenom('Premier'))
            ->addEncadrant((new Utilisateur())->setNom('Encadrant')->setPrenom('Deuxième'))
            ->updateListeEncadrants()
        ;

        $this->assertIsString($formatActivite->getListeEncadrants());
        $this->assertEquals('Premier Encadrant, Deuxième Encadrant', $formatActivite->getListeEncadrants());
    }

    /**
     * @covers \App\Entity\Uca\FormatActivite::updateListeEncadrants
     */
    public function testUpdateListeEncadrantsNone(): void
    {
        $formatActivite = (new FormatSimple())->updateListeEncadrants();

        $this->assertIsString($formatActivite->getListeEncadrants());
        $this->assertEquals('', $formatActivite->getListeEncadrants());
    }

    /**
     * @covers \App\Entity\Uca\FormatActivite::getInscriptionsValidee
     */
    public function testGetInscriptionsValidee(): void
    {
        $formatActivite = (new FormatSimple())
            ->addInscription($this->createInscription('valide'))
            ->addInscription($this->createInscription('attentepaiement'))
        ;

        $this->assertNotEmpty($formatActivite->getInscriptionsValidee());
        $this->assertEquals(1, sizeof($formatActivite->getInscriptionsValidee()));
    }

    /**
     * @covers \App\Entity\Uca\FormatActivite::getAllInscriptions
     */
    public function testGetAllInscriptions(): void
    {
        $formatActivite = (new FormatSimple())
            ->addInscription($this->createInscription('valide'))
            ->addInscription($this->createInscription('attentepaiement'))
            ->addInscription($this->createInscription('attenteajoutpanier'))
        ;

        $this->assertNotEmpty($formatActivite->getAllInscriptions());
        $this->assertEquals(3, sizeof($formatActivite->getAllInscriptions()));
    }

    /**
     * @covers \App\Entity\Uca\FormatActivite::getAutorisations
     */
    public function testGetAutorisations(): void
    {
        $formatActivite = (new FormatSimple())
            ->addAutorisation((new TypeAutorisation())->setLibelle('Premier type'))
            ->addAutorisation((new TypeAutorisation())->setLibelle('Deuxième type'))
        ;

        $this->assertNotEmpty($formatActivite->getAutorisations());
        $this->assertEquals(2, sizeof($formatActivite->getAutorisations()));
    }

    /**
     * @covers \App\Entity\Uca\FormatActivite::getAutorisations
     */
    public function testGetAutorisationsWithOptions(): void
    {
        $formatActivite = (new FormatSimple())
            ->addAutorisation((new TypeAutorisation())->setLibelle('Premier type')->setComportement((new ComportementAutorisation())->setCodeComportement('123')))
            ->addAutorisation((new TypeAutorisation())->setLibelle('Deuxième type')->setComportement((new ComportementAutorisation())->setCodeComportement('321')))
        ;

        $this->assertNotEmpty($formatActivite->getAutorisations(['comportement' => ['123']]));
        $this->assertEquals(1, sizeof($formatActivite->getAutorisations(['comportement' => ['123']])));
    }

    /**
     * @covers \App\Entity\Uca\FormatActivite::getCapaciteProfil
     * @covers \App\Entity\Uca\FormatActivite::getCapaciteTousProfil
     * @covers \App\Entity\Uca\FormatActivite::getMaxCapaciteProfil
     */
    public function testGetCapaciteTousProfil(): void
    {
        $premierFormatProfil = (new FormatActiviteProfilUtilisateur(
            new FormatSimple(),
            (new ProfilUtilisateur())->setLibelle('Premier profil'),
            10
        ));

        $deuxiemeFormatProfil = (new FormatActiviteProfilUtilisateur(
            new FormatSimple(),
            (new ProfilUtilisateur())->setLibelle('Deuxième profil'),
            10
        ));

        $formatActivite = (new FormatSimple())
            ->addProfilsUtilisateur($premierFormatProfil)
            ->addProfilsUtilisateur($deuxiemeFormatProfil)
            ->updateListeProfils()
        ;

        $this->assertIsInt($formatActivite->getCapaciteTousProfil());
        $this->assertEquals(20, $formatActivite->getCapaciteTousProfil());

        $this->assertIsInt($formatActivite->getMaxCapaciteProfil());
        $this->assertEquals(10, $formatActivite->getMaxCapaciteProfil());

        $this->assertIsBool($formatActivite->getCapaciteProfil($premierFormatProfil));
        $this->assertFalse($formatActivite->getCapaciteProfil($premierFormatProfil));
    }

    /**
     * Fonction qui permet de créer un inscription en spécifiant le status si nécessaire.
     */
    private function createInscription(string $statut = 'valide'): Inscription
    {
        return (new Inscription(
            (new FormatSimple())->setDateDebutEffective(new DateTime()),
            static::getContainer()->get(UtilisateurRepository::class)->findOneByUsername('admin'),
            []
        ))->setStatut($statut);
    }
}