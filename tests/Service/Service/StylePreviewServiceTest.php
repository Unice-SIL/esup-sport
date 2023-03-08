<?php


namespace App\Tests\Service\Service;

use App\Entity\Uca\Activite;
use App\Entity\Uca\ClasseActivite;
use App\Entity\Uca\CommandeDetail;
use App\Entity\Uca\ComportementAutorisation;
use App\Entity\Uca\Creneau;
use App\Entity\Uca\DhtmlxEvenement;
use App\Entity\Uca\DhtmlxSerie;
use App\Entity\Uca\Etablissement;
use App\Entity\Uca\FormatAvecCreneau;
use App\Entity\Uca\FormatSimple;
use App\Entity\Uca\Lieu;
use App\Entity\Uca\NiveauSportif;
use App\Entity\Uca\ProfilUtilisateur;
use App\Entity\Uca\Tarif;
use App\Entity\Uca\TypeActivite;
use App\Entity\Uca\TypeAutorisation;
use App\Entity\Uca\Utilisateur;
use App\Service\Service\StylePreviewService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class StylePreviewServiceTest extends KernelTestCase
{
    private $service;

    private $user;

    protected function setUp(): void
    {
        $this->service = self::getContainer()->get(StylePreviewService::class);
        $this->user = (new Utilisateur())->setProfil((new ProfilUtilisateur())->setLibelle('Test StylePreviewService'));
    }

    /**
     * @covers App\Service\Service\StylePreviewService::setUtilisateur
     */
    public function testSetUtilisateur(): void
    {
        $this->assertInstanceOf(StylePreviewService::class, $this->service->setUtilisateur($this->user));
    }

    /**
     * @covers App\Service\Service\StylePreviewService::__construct
     * @covers App\Service\Service\StylePreviewService::getArticles
     */
    public function testGetArticlesWithoutUser(): void
    {
        $articles = $this->service->getArticles();
        $this->assertIsArray($articles);
        $this->assertCount(0, $articles);
    }

    /**
     * @covers App\Service\Service\StylePreviewService::__construct
     * @covers App\Service\Service\StylePreviewService::getArticles
     * @covers App\Service\Service\StylePreviewService::setUtilisateur
     */
    public function testGetArticlesWithUser(): void
    {
        $this->service->setUtilisateur($this->user);
        $articles = $this->service->getArticles();
        $this->assertIsArray($articles);
        $this->assertNotCount(0, $articles);
        foreach ($articles as $article) {
            $this->assertInstanceOf(CommandeDetail::class, $article);
        }
    }

    /**
     * @covers App\Service\Service\StylePreviewService::__construct
     * @covers App\Service\Service\StylePreviewService::getEncadrant
     */
    public function testGetEncadrant(): void
    {
        $this->assertInstanceOf(Utilisateur::class, $this->service->getEncadrant());
    }
    
    /**
     * @covers App\Service\Service\StylePreviewService::__construct
     * @covers App\Service\Service\StylePreviewService::getNiveauxSportifs
     */
    public function testGetNiveauxSportifs(): void
    {
        $ns = $this->service->getNiveauxSportifs();
        $this->assertIsArray($ns);
        foreach ($ns as $niveau) {
            $this->assertInstanceOf(NiveauSportif::class, $niveau);
        }
    }

    /**
     * @covers App\Service\Service\StylePreviewService::__construct
     * @covers App\Service\Service\StylePreviewService::getEtablissements
     */
    public function testGetEtablissements(): void
    {
        $etablissements = $this->service->getEtablissements();
        $this->assertIsArray($etablissements);
        foreach ($etablissements as $etablissement) {
            $this->assertInstanceOf(Etablissement::class, $etablissement);
        }
    }

    /**
     * @covers App\Service\Service\StylePreviewService::__construct
     * @covers App\Service\Service\StylePreviewService::getTarif
     */
    public function testGetTarifWithoutUser(): void
    {
        $tarif = $this->service->getTarif();
        $this->assertInstanceOf(Tarif::class, $tarif);
        $profils = [];
        foreach ($tarif->getMontants() as $montant) {
            $profils[] = $montant->getProfil();
        }
        $this->assertNotContains($this->user->getProfil(), $profils);
    }

    /**
     * @covers App\Service\Service\StylePreviewService::__construct
     * @covers App\Service\Service\StylePreviewService::getTarif
     * @covers App\Service\Service\StylePreviewService::setUtilisateur
     * @covers App\Service\Service\StylePreviewService::setProfilUtilisateur
     */
    public function testGetTarifWithUser(): void
    {
        $this->service->setUtilisateur($this->user);
        $tarif = $this->service->getTarif();
        $this->assertInstanceOf(Tarif::class, $tarif);
        $profils = [];
        foreach ($tarif->getMontants() as $montant) {
            $profils[] = $montant->getProfil();
        }
        $this->assertContains($this->user->getProfil(), $profils);
        
    }

    /**
     * @covers App\Service\Service\StylePreviewService::__construct
     * @covers App\Service\Service\StylePreviewService::getSeries
     */
    public function testGetSeriesWithoutUser(): void
    {
        $series = $this->service->getSeries();
        $this->assertIsArray($series);
        $this->assertCount(7, $series);
        $profils = [];
        foreach ($series as $serie) {
            foreach ($serie->getCreneau()->getProfilsUtilisateurs() as $creneauProfil) {
                $profils[] = $creneauProfil->getProfilUtilisateur();
            }
        }
        $this->assertNotContains($this->user->getProfil(), $profils);
    }

    /**
     * @covers App\Service\Service\StylePreviewService::__construct
     * @covers App\Service\Service\StylePreviewService::getSeries
     * @covers App\Service\Service\StylePreviewService::setUtilisateur
     * @covers App\Service\Service\StylePreviewService::setProfilUtilisateur
     */
    public function testGetSeriesWithUser(): void
    {
        $this->service->setUtilisateur($this->user);
        $series = $this->service->getSeries();
        $this->assertIsArray($series);
        $this->assertCount(7, $series);
        $profils = [];
        foreach ($series as $serie) {
            foreach ($serie->getCreneau()->getProfilsUtilisateurs() as $creneauProfil) {
                $profils[] = $creneauProfil->getProfilUtilisateur();
            }
        }
        $this->assertContains($this->user->getProfil(), $profils);
    }

    /**
     * @covers App\Service\Service\StylePreviewService::createSerie
     */
    public function testCreateSerie(): void
    {
        $dateDeb = new \DateTime();
        $dateFin = (new \DateTime())->add(new \DateInterval('P1M'));
        $serie = $this->service->createSerie($dateDeb, $dateFin);
        $this->assertInstanceOf(DhtmlxSerie::class, $serie);
        $this->assertInstanceOf(Creneau::class, $serie->getCreneau());
        $this->assertNotNull($serie->getCreneau()->getSerie());
        $this->assertNotCount(0, $serie->getCreneau()->getNiveauxSportifs());
        $this->assertEquals($dateDeb, $serie->getDateDebut());
        $this->assertEquals($dateFin, $serie->getDateFin());
    }

    /**
     * @covers App\Service\Service\StylePreviewService::getEvent
     */
    public function testGetEventNull(): void
    {
        $id = 1;
        $event = $this->service->getEvent($id);
        $this->assertNull($event);
    }

    /**
     * @covers App\Service\Service\StylePreviewService::getEvent
     */
    public function testGetEventNotNull(): void
    {
        $id = (new \DateTime())->modify('first monday of this month')->setTime(12, 0)->getTimestamp();
        $event = $this->service->getEvent($id);
        $this->assertNotNull($event);
        $this->assertEquals($id, $event->getId());
    }

    /**
     * @covers App\Service\Service\StylePreviewService::getEvents
     */
    public function testGetEvents(): void
    {
        $dateDeb = (new \DateTime())->sub(new \DateInterval('P1D'));
        $dateFin = (new \DateTime())->add(new \DateInterval('P2M'));
        $item = $this->service->getFormatAvecCreneau();
        $events = $this->service->getEvents($dateDeb, $dateFin, $item);
        $this->assertIsArray($events);
        $this->assertNotCount(0, $events);
    }

    public function dataProviderCreateEvent(): array
    {
        return [
            [false, false],
            [false, true],
            [true, false],
            [true, true],
        ];
    }

    /**
     * @dataProvider dataProviderCreateEvent
     *
     * @covers App\Service\Service\StylePreviewService::createEvent
     */
    public function testCreateEvent($ff, $bonus): void
    {
        $serie = new DhtmlxSerie();
        $dateDeb = (new \DateTime())->modify('first monday of this month')->setTime(12, 0);
        $dateFin = (clone $dateDeb)->setTime(14, 0);
        $event = $this->service->createEvent($serie, $dateDeb, $dateFin, $ff, $bonus);
        $this->assertInstanceOf(DhtmlxEvenement::class, $event);
        $this->assertEquals($dateDeb->getTimestamp(), $event->getId());
        $this->assertEquals($dateDeb, $event->getDateDebut());
        $this->assertEquals($dateFin, $event->getDateFin());
        $this->assertEquals($serie, $event->getSerie());
        $this->assertEquals($ff, $event->getForteFrequence());
        $this->assertEquals($bonus, $event->getEligibleBonus());
    }

    /**
     * @covers App\Service\Service\StylePreviewService::__construct
     * @covers App\Service\Service\StylePreviewService::getComportementAutorisation
     */
    public function testGetComportementAutorisation(): void
    {
        $this->assertInstanceOf(ComportementAutorisation::class, $this->service->getComportementAutorisation());
    }

    /**
     * @covers App\Service\Service\StylePreviewService::__construct
     * @covers App\Service\Service\StylePreviewService::getTypeAutorisation
     */
    public function testGetTypeAutorisation(): void
    {
        $ta = $this->service->getTypeAutorisation();
        $this->assertInstanceOf(TypeAutorisation::class, $ta);
        $this->assertEquals($this->service->getComportementAutorisation(), $ta->getComportement());
    }

    /**
     * @covers App\Service\Service\StylePreviewService::__construct
     * @covers App\Service\Service\StylePreviewService::getLieu
     */
    public function testGetLieu(): void
    {
        $this->assertInstanceOf(Lieu::class, $this->service->getLieu());
    }

    /**
     * @covers App\Service\Service\StylePreviewService::__construct
     * @covers App\Service\Service\StylePreviewService::getTypeActivite
     */
    public function testGetTypeActivite(): void
    {
        $this->assertInstanceOf(TypeActivite::class, $this->service->getTypeActivite());
    }

    /**
     * @covers App\Service\Service\StylePreviewService::__construct
     * @covers App\Service\Service\StylePreviewService::getClasseActivite
     */
    public function testGetClasseActivite(): void
    {
        $ca = $this->service->getClasseActivite();
        $this->assertInstanceOf(ClasseActivite::class, $ca);
        $this->assertEquals($this->service->getTypeActivite(), $ca->getTypeActivite());
    }

    /**
     * @covers App\Service\Service\StylePreviewService::__construct
     * @covers App\Service\Service\StylePreviewService::getActivite
     */
    public function testGetActivite(): void
    {
        $activite = $this->service->getActivite();
        $this->assertInstanceOf(Activite::class, $activite);
        $this->assertEquals($this->service->getClasseActivite(), $activite->getClasseActivite());
    }

    /**
     * @covers App\Service\Service\StylePreviewService::__construct
     * @covers App\Service\Service\StylePreviewService::getFormatSimple
     */
    public function testGetFormatSimpleWithoutUser(): void
    {
        $format = $this->service->getFormatSimple();
        $this->assertInstanceOf(FormatSimple::class, $format);
        $this->assertEquals($this->service->getActivite(), $format->getActivite());
        $this->assertContains($this->service->getEncadrant(), $format->getEncadrants());
        $this->assertEquals($this->service->getTarif(), $format->getTarif());
        $formatProfils = [];
        foreach ($format->getProfilsUtilisateurs() as $fp) {
            $formatProfils[] = $fp->getProfilUtilisateur();
        }
        
        $this->assertNotContains($this->user->getProfil(), $formatProfils);
    }

    /**
     * @covers App\Service\Service\StylePreviewService::__construct
     * @covers App\Service\Service\StylePreviewService::getFormatavecCreneau
     */
    public function testGetFormatAvecCreneauWithoutUser(): void
    {
        $format = $this->service->getFormatAvecCreneau();
        $this->assertInstanceOf(FormatAvecCreneau::class, $format);
        $this->assertEquals($this->service->getActivite(), $format->getActivite());
        $this->assertContains($this->service->getEncadrant(), $format->getEncadrants());
        $this->assertEquals($this->service->getTarif(), $format->getTarif());
        $formatProfils = [];
        foreach ($format->getProfilsUtilisateurs() as $fp) {
            $formatProfils[] = $fp->getProfilUtilisateur();
        }
        
        $this->assertNotContains($this->user->getProfil(), $formatProfils);
    }

    /**
     * @covers App\Service\Service\StylePreviewService::__construct
     * @covers App\Service\Service\StylePreviewService::getFormatSimple
     * @covers App\Service\Service\StylePreviewService::setUtilisateur
     * @covers App\Service\Service\StylePreviewService::setProfilUtilisateur
     */
    public function testGetFormatSimpleWithUser(): void
    {
        $this->service->setUtilisateur($this->user);
        $format = $this->service->getFormatSimple();
        $this->assertInstanceOf(FormatSimple::class, $format);
        $this->assertEquals($this->service->getActivite(), $format->getActivite());
        $this->assertContains($this->service->getEncadrant(), $format->getEncadrants());
        $this->assertEquals($this->service->getTarif(), $format->getTarif());
        $formatProfils = [];
        foreach ($format->getProfilsUtilisateurs() as $fp) {
            $formatProfils[] = $fp->getProfilUtilisateur();
        }
        
        $this->assertContains($this->user->getProfil(), $formatProfils);
    }

    /**
     * @covers App\Service\Service\StylePreviewService::__construct
     * @covers App\Service\Service\StylePreviewService::getFormatavecCreneau
     * @covers App\Service\Service\StylePreviewService::setUtilisateur
     * @covers App\Service\Service\StylePreviewService::setProfilUtilisateur
     */
    public function testGetFormatAvecCreneauWithUser(): void
    {
        $this->service->setUtilisateur($this->user);
        $format = $this->service->getFormatAvecCreneau();
        $this->assertInstanceOf(FormatAvecCreneau::class, $format);
        $this->assertEquals($this->service->getActivite(), $format->getActivite());
        $this->assertContains($this->service->getEncadrant(), $format->getEncadrants());
        $this->assertEquals($this->service->getTarif(), $format->getTarif());
        $formatProfils = [];
        foreach ($format->getProfilsUtilisateurs() as $fp) {
            $formatProfils[] = $fp->getProfilUtilisateur();
        }
        
        $this->assertContains($this->user->getProfil(), $formatProfils);
    }
}
