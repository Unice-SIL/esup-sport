<?php

namespace App\Tests\Service\Service;

use DateTime;
use ReflectionClass;
use App\Entity\Uca\Lieu;
use App\Entity\Uca\Creneau;
use App\Entity\Uca\DhtmlxSerie;
use App\Entity\Uca\Etablissement;
use App\Entity\Uca\Reservabilite;
use App\Entity\Uca\DhtmlxEvenement;
use App\Entity\Uca\TypeAutorisation;
use App\Entity\Uca\FormatAvecCreneau;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Uca\FormatAvecReservation;
use Symfony\Component\DomCrawler\Crawler;
use App\Service\Service\CalendrierService;
use App\Entity\Uca\ComportementAutorisation;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 * @coversNothing
 */
class CalendrierServiceTest extends WebTestCase
{
    /**
     * @var KernelBrowser
     */
    private $client;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var CalendrierService
     */
    private $calendrierService;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();

        $this->DataCheckForassert_days = [
            'MONDAY',
            'TUESDAY',
            'WEDNESDAY',
            'THURSDAY',
            'FRIDAY',
            'SATURDAY',
            'SUNDAY',
        ];
        $this->DataCheckForassert_TypePeriod = [
            'Semaine',
            'Jour',
        ];
        // On récupère les 31 jours des mois testés ici : Janvier/Juillet/Décembre
        // Attention : On prend Janvier en référence mais sinon prendre en référence le mois de la currentDate !!
        $daysDigitCurrentMonth = [];
        if (empty($this->DataCheckForassert_daysDigitCurrentMonth)) {
            $monthStart = strtotime('2022-01-01 00:00:00', time());
            $monthEnd = strtotime('2022-01-31 00:00:00', time());
            $monthStartInt = intval(date('d', $monthStart));
            $monthEndInt = intval(date('d', $monthEnd));
            for ($dayDigit = $monthStartInt; $dayDigit < $monthEndInt; $dayDigit++) {
                array_push($daysDigitCurrentMonth, str_pad(strval($dayDigit), 2, '0', STR_PAD_LEFT));
            }
        }
        $this->DataCheckForassert_daysDigitCurrentMonth = $daysDigitCurrentMonth;

        $this->em = $container->get(EntityManagerInterface::class);
        $this->calendrierService = $container->get(CalendrierService::class);
    }
    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }


    public function createFormatActivite(
        $comportementAutorisationData,
        $formatActiviteData,
        $creneauData,
        $serieData,
        $eventsData,
        $ressourceData = null,
        $reservabiliteData = null,
        $etablissementData = null
    ): array {
        $comportementAutorisation = (new ComportementAutorisation())
            ->setdescriptionComportement($comportementAutorisationData['Description'])
            ->setLibelle($comportementAutorisationData['Libelle'])
            ->setCodeComportement($comportementAutorisationData['CodeComportement']);
        $typeAutorisation = (new TypeAutorisation())
            ->setComportement($comportementAutorisation)
            ->setLibelle('');

        if ($formatActiviteData['Class'] === 'FormatAvecCreneau')
            $formatActivite = new FormatAvecCreneau();

        if ($formatActiviteData['Class'] === 'FormatAvecReservation')
            $formatActivite = new FormatAvecReservation();

        $formatActivite
            ->setLibelle($formatActiviteData['Libelle'])
            ->addAutorisation($typeAutorisation)
            ->setCapacite($formatActiviteData['Capacite'])
            ->setDescription($formatActiviteData['Description'])
            ->setDateDebutPublication($formatActiviteData['DateDebutPublication'])
            ->setDateFinPublication($formatActiviteData['DateFinPublication'])
            ->setDateDebutInscription($formatActiviteData['DateDebutInscription'])
            ->setDateFinInscription($formatActiviteData['DateFinInscription'])
            ->setDateDebutEffective($formatActiviteData['DateDebutEffective'])
            ->setDateFinEffective($formatActiviteData['DateFinEffective'])
            ->setImage($formatActiviteData['Image'])
            ->setEstPayant($formatActiviteData['EstPayant'])
            ->setEstEncadre($formatActiviteData['EstEncadre']);

        if ($formatActivite instanceof FormatAvecCreneau) {
            if (!isset($etablissement) && $etablissementData !== null) {
                $etablissement = (new Etablissement())
                    ->setLibelle($etablissementData['Libelle'])
                    ->setCode($etablissementData['Code'])
                    ->setAdresse($etablissementData['Adresse'])
                    ->setCodePostal($etablissementData['CodePostal'])
                    ->setVille($etablissementData['Ville']);
                $etablissement->setImageFile($etablissementData['ImageFile']);
                $etablissement->setImage($etablissementData['Image']);
            } else {
                $etablissement = null;
            }
            $ressource = (new Lieu())
                ->setLibelle($ressourceData['Libelle'])
                ->setNbPartenaires($ressourceData['NbPartenaires'])
                ->setImageFile($ressourceData['ImageFile'])
                ->setImage($ressourceData['Image'])
                ->setNbPartenairesMax($ressourceData['NbPartenairesMax'])
                ->setEtablissement($etablissement);
            $creneau = (new Creneau())
                ->setFormatActivite($formatActivite)
                ->setLieu($ressource)
                ->setCapacite($creneauData['Capacite']);
        } else {
            $creneau = null;
        }


        $serie = (new DhtmlxSerie())
            ->setCreneau($creneau)
            ->setDateDebut($serieData['DateDebut'])
            ->setDateFin($serieData['DateFin']);
        foreach ($eventsData as $eventData) {
            $events[] = (new DhtmlxEvenement())
                ->setSerie($serie)
                ->setDescription($eventData['Description'])
                ->setDateDebut($eventData['DateDebut'])
                ->setDateFin($eventData['DateFin']);
        }

        if ($formatActivite instanceof FormatAvecReservation) {
            if (!isset($etablissement) && $etablissementData !== null) {
                $etablissement = (new Etablissement())
                    ->setLibelle($etablissementData['Libelle'])
                    ->setCode($etablissementData['Code'])
                    ->setAdresse($etablissementData['Adresse'])
                    ->setCodePostal($etablissementData['CodePostal'])
                    ->setVille($etablissementData['Ville']);
                $etablissement->setImageFile($etablissementData['ImageFile']);
                $etablissement->setImage($etablissementData['Image']);
            } else {
                $etablissement = null;
            }

            $ressource = (new Lieu())
                ->setLibelle($ressourceData['Libelle'])
                ->setNbPartenaires($ressourceData['NbPartenaires'])
                ->setImageFile($ressourceData['ImageFile'])
                ->setImage($ressourceData['Image'])
                ->addFormatResa($formatActivite)
                ->setNbPartenairesMax($ressourceData['NbPartenairesMax'])
                ->setEtablissement();
            $reservabilite = (new Reservabilite())
                ->setRessource($ressource)
                ->setSerie($serie)
                ->setCapacite($reservabiliteData['Capacite']);

            $serie->setReservabilite($reservabilite);
            // $reservabilite->addInscription($inscription);
            // $reservabilite->setEvenement($dhtmlxEvenement);
            // $reservabilite->addProfilsUtilisateur($ReservabiliteProfilUtilisateur);
        } else {
            if (!isset($ressource))
                $ressource = null;
            if (!isset($reservabilite))
                $reservabilite = null;
            if (!isset($etablissement))
                $etablissement = null;
        }
        return [
            'comportementAutorisation' => $comportementAutorisation,
            'typeAutorisation' => $typeAutorisation,
            'formatActivite' => $formatActivite,
            'creneau' => $creneau,
            'serie' => $serie,
            'events' => $events,
            'ressource' => $ressource,
            'reservabilite' => $reservabilite,
            'etablissement' => $etablissement,
        ];
    }

    /**
     * Data provider pour la creation du planning mois.
     */
    public function createMonthPlanningDataProvider()
    {
        $_data = self::initData();
        $comportementAutorisationData = $_data['comportementAutorisationData'];
        $formatActiviteData = $_data['formatActiviteData'];
        $creneauData = $_data['creneauData'];
        $serieData = $_data['serieData'];
        $EventsData = $_data['EventsData'];
        $ressourceData = $_data['ressourceData'];
        $reservabiliteData = $_data['reservabiliteData'];
        $etablissementData = $_data['etablissementData'];
        $format = $_data['format'];
        $format['typeVisualisation'] = 'jour';
        $twigConfig = $_data['twigConfig'];
        $twigConfig['typeVisualisation'] = $format['typeVisualisation'];

        // Cas particulier Mois de Janvier : on ne change que la currentDate 
        $comportementAutorisationData_2 = $comportementAutorisationData;
        $formatActiviteData_2 = $formatActiviteData;
        $creneauData_2 = $creneauData;
        $serieData_2 = $serieData;
        $EventsData_2 = $EventsData;
        $format_2 = $format;
        $format_2['currentDate'] = date_format((new \DateTime('2022-01-15')), 'd/m/Y');
        $twigConfig_2 = $twigConfig;
        $twigConfig_2['currentDate'] = \DateTime::createFromFormat('d/m/Y', $format_2['currentDate'])->setTime(0, 0);

        // Cas particulier Mois de Décembre : on ne change que la currentDate 
        $comportementAutorisationData_3 = $comportementAutorisationData;
        $formatActiviteData_3 = $formatActiviteData;
        $creneauData_3 = $creneauData;
        $serieData_3 = $serieData;
        $EventsData_3 = $EventsData;
        $format_3 = $format;
        $format_3['currentDate'] = date_format((new \DateTime('2022-12-15')), 'd/m/Y');
        $twigConfig_3 = $twigConfig;
        $twigConfig_3['currentDate'] = \DateTime::createFromFormat('d/m/Y', $format_3['currentDate'])->setTime(0, 0);

        return [
            // Cas par défault Mois différent que janvier et décembre
            [$this->createFormatActivite($comportementAutorisationData, $formatActiviteData, $creneauData, $serieData, $EventsData, $ressourceData, $reservabiliteData), $format, $twigConfig],
            // Cas particulier Mois de Janvier : on ne change que la currentDate 
            [$this->createFormatActivite($comportementAutorisationData_2, $formatActiviteData_2, $creneauData_2, $serieData_2, $EventsData_2, $ressourceData, $reservabiliteData), $format_2, $twigConfig_2],
            // Cas particulier Mois de Décembre : on ne change que la currentDate 
            [$this->createFormatActivite($comportementAutorisationData_3, $formatActiviteData_3, $creneauData_3, $serieData_3, $EventsData_3, $ressourceData, $reservabiliteData), $format_3, $twigConfig_3]
        ];
    }

    /**
     * @dataProvider createMonthPlanningDataProvider
     * 
     * @covers \App\Service\Service\CalendrierService::createMonthPlanning
     */
    public function testcreateMonthPlanning(array $objects, array $format, array $twigConfig): void
    {
        $objects = $this->persistObjects($objects);

        if (null !== $objects['ressource']) {
            $format['idRessource'] = $objects['ressource']->getId();
            $twigConfig['idRessource'] = $format['idRessource'];
        }

        $format['itemId'] = $objects['formatActivite']->getId();
        $format['typeVisualisation'] = 'mois';
        $twigConfig['typeVisualisation'] = $format['typeVisualisation'];
        $twigConfig['itemId'] = $format['itemId'];
        $twigConfig['formatActivite'] = $objects['formatActivite'];
        // END INIT objects before unitTest

        $htmlRender = $this->calendrierService->createMonthPlanning($objects['formatActivite'], $format, $twigConfig);
        foreach ($this->DataCheckForassert_daysDigitCurrentMonth as $dayDigit) {
            $this->assertStringContainsString($dayDigit, $htmlRender);
        }
        foreach ($this->DataCheckForassert_days as $days) {
            $this->assertStringContainsString($days, $htmlRender);
        }
        foreach ($this->DataCheckForassert_TypePeriod as $TypePeriod) {
            $this->assertStringContainsString($TypePeriod, $htmlRender);
        }

        foreach ($objects['events'] as $event) {
            $querySelector = '#content_popover_' . $event->getId();

            // Vérification selon le mois courrant si un event doit être retourné dans le modal 
            $currentDate = $format['currentDate'];
            $eventDateDebut = $event->getDateDebut();
            if (date('m', strtotime($currentDate)) === $eventDateDebut->format('m')) {
                $this->assertStringContainsString($querySelector, $htmlRender);
            } else {
                $this->assertStringNotContainsString($querySelector, $htmlRender);
            }
        }

        // START clean BDD after unitTest
        $objects = $this->removeObjects($objects);
    }

    /**
     * Data provider pour la creation du planning week.
     */
    public function createWeekPlanningDataProvider()
    {
        $_data = self::initData();
        $comportementAutorisationData = $_data['comportementAutorisationData'];
        $formatActiviteData = $_data['formatActiviteData'];
        $creneauData = $_data['creneauData'];
        $serieData = $_data['serieData'];
        $EventsData = $_data['EventsData'];
        $ressourceData = $_data['ressourceData'];
        $reservabiliteData = $_data['reservabiliteData'];
        $etablissementData = $_data['etablissementData'];
        $format = $_data['format'];
        $twigConfig = $_data['twigConfig'];

        $format_width_sup_1425 = $format_width_1250_1425 = $format_width_1100_1250 = $format_width_910_1100 = $format_width_750_910 = $format_width_580_750 = $format_width_inf_580 = $format;
        $format_width_sup_1425['widthWindow'] = 1500;
        $format_width_1250_1425['widthWindow'] = 1300;
        $format_width_1100_1250['widthWindow'] = 1280;
        $format_width_910_1100['widthWindow'] = 970;
        $format_width_750_910['widthWindow'] = 800;
        $format_width_580_750['widthWindow'] = 600;
        $format_width_inf_580['widthWindow'] = 480;

        // Cas par défault Jour FormatAvecReservation
        $formatActiviteData_FormatAvecReservation = $formatActiviteData;
        $formatActiviteData_FormatAvecReservation['Class'] = 'FormatAvecReservation';
        $formatActiviteData_FormatAvecReservation['Libelle'] = 'FormatAvecReservation';
        $format_2 = $format;
        $format_2['typeFormat'] = 'FormatAvecReservation';
        // Cas Jour FormatAvecReservation et changement de widthWindow
        $format_width_sup_1425_2 = $format_width_1250_1425_2 = $format_width_1100_1250_2 = $format_width_910_1100_2 = $format_width_750_910_2 = $format_width_580_750_2 = $format_width_inf_580_2 = $format_2;
        $format_width_sup_1425_2['widthWindow'] = 1500;
        $format_width_1250_1425_2['widthWindow'] = 1300;
        $format_width_1100_1250_2['widthWindow'] = 1280;
        $format_width_910_1100_2['widthWindow'] = 970;
        $format_width_750_910_2['widthWindow'] = 800;
        $format_width_580_750_2['widthWindow'] = 600;
        $format_width_inf_580_2['widthWindow'] = 480;

        return [
            // Cas par défault Jour FormatAvecCreneau
            [$this->createFormatActivite($comportementAutorisationData, $formatActiviteData, $creneauData, $serieData, $EventsData, $ressourceData, $reservabiliteData, $etablissementData), $format, $twigConfig],
            // Cas Jour FormatAvecCreneau et changement de widthWindow
            [$this->createFormatActivite($comportementAutorisationData, $formatActiviteData, $creneauData, $serieData, $EventsData, $ressourceData, $reservabiliteData, $etablissementData), $format_width_sup_1425, $twigConfig],
            [$this->createFormatActivite($comportementAutorisationData, $formatActiviteData, $creneauData, $serieData, $EventsData, $ressourceData, $reservabiliteData, $etablissementData), $format_width_1250_1425, $twigConfig],
            [$this->createFormatActivite($comportementAutorisationData, $formatActiviteData, $creneauData, $serieData, $EventsData, $ressourceData, $reservabiliteData, $etablissementData), $format_width_1100_1250, $twigConfig],
            [$this->createFormatActivite($comportementAutorisationData, $formatActiviteData, $creneauData, $serieData, $EventsData, $ressourceData, $reservabiliteData, $etablissementData), $format_width_910_1100, $twigConfig],
            [$this->createFormatActivite($comportementAutorisationData, $formatActiviteData, $creneauData, $serieData, $EventsData, $ressourceData, $reservabiliteData, $etablissementData), $format_width_750_910, $twigConfig],
            [$this->createFormatActivite($comportementAutorisationData, $formatActiviteData, $creneauData, $serieData, $EventsData, $ressourceData, $reservabiliteData, $etablissementData), $format_width_580_750, $twigConfig],
            [$this->createFormatActivite($comportementAutorisationData, $formatActiviteData, $creneauData, $serieData, $EventsData, $ressourceData, $reservabiliteData, $etablissementData), $format_width_inf_580, $twigConfig],
            // Cas par défault Jour FormatAvecReservation
            [$this->createFormatActivite($comportementAutorisationData, $formatActiviteData_FormatAvecReservation, $creneauData, $serieData, $EventsData, $ressourceData, $reservabiliteData, $etablissementData), $format_2, $twigConfig],
            // Cas Jour FormatAvecReservation et changement de widthWindow
            [$this->createFormatActivite($comportementAutorisationData, $formatActiviteData_FormatAvecReservation, $creneauData, $serieData, $EventsData, $ressourceData, $reservabiliteData, $etablissementData), $format_width_sup_1425_2, $twigConfig],
            [$this->createFormatActivite($comportementAutorisationData, $formatActiviteData_FormatAvecReservation, $creneauData, $serieData, $EventsData, $ressourceData, $reservabiliteData, $etablissementData), $format_width_1250_1425_2, $twigConfig],
            [$this->createFormatActivite($comportementAutorisationData, $formatActiviteData_FormatAvecReservation, $creneauData, $serieData, $EventsData, $ressourceData, $reservabiliteData, $etablissementData), $format_width_1100_1250_2, $twigConfig],
            [$this->createFormatActivite($comportementAutorisationData, $formatActiviteData_FormatAvecReservation, $creneauData, $serieData, $EventsData, $ressourceData, $reservabiliteData, $etablissementData), $format_width_910_1100_2, $twigConfig],
            [$this->createFormatActivite($comportementAutorisationData, $formatActiviteData_FormatAvecReservation, $creneauData, $serieData, $EventsData, $ressourceData, $reservabiliteData, $etablissementData), $format_width_750_910_2, $twigConfig],
            [$this->createFormatActivite($comportementAutorisationData, $formatActiviteData_FormatAvecReservation, $creneauData, $serieData, $EventsData, $ressourceData, $reservabiliteData, $etablissementData), $format_width_580_750_2, $twigConfig],
            [$this->createFormatActivite($comportementAutorisationData, $formatActiviteData_FormatAvecReservation, $creneauData, $serieData, $EventsData, $ressourceData, $reservabiliteData, $etablissementData), $format_width_inf_580_2, $twigConfig],
        ];
    }

    /**
     * @dataProvider createWeekPlanningDataProvider
     * 
     * @covers \App\Service\Service\CalendrierService::createWeekPlanning
     */
    public function testcreateWeekPlanning(array $objects, array $format, array $twigConfig): void
    {
        $objects = $this->persistObjects($objects);

        if (null !== $objects['ressource']) {
            $format['idRessource'] = $objects['ressource']->getId();
            $twigConfig['idRessource'] = $format['idRessource'];
        }

        $format['itemId'] = $objects['formatActivite']->getId();
        $twigConfig['itemId'] = $format['itemId'];
        $twigConfig['formatActivite'] = $objects['formatActivite'];
        // END INIT objects before unitTest

        $htmlRender = $this->calendrierService->createWeekPlanning($objects['formatActivite'], $format, $twigConfig);

        $crawler = new Crawler($htmlRender);
        if ($format['widthWindow'] > 1425) {
            $this->assertEquals(7, $crawler->filter('[class*=calendar-entete]')->count());
            for ($i = 0; $i <= 6; $i++) {
                $dateCurrent  = new \DateTime($format['currentDate']);
                $datePlusOne = $dateCurrent->modify('+' . $i . ' day');
                $day_letter = $datePlusOne->format('l');
                $this->assertStringContainsStringIgnoringCase($day_letter, $htmlRender);
            }
        } elseif ($format['widthWindow'] <= 1425 && $format['widthWindow'] > 1250) {
            $this->assertEquals(6, $crawler->filter('[class*=calendar-entete]')->count());
            for ($i = 0; $i <= 5; $i++) {
                $dateCurrent  = new \DateTime($format['currentDate']);
                $datePlusOne = $dateCurrent->modify('+' . $i . ' day');
                $day_letter = $datePlusOne->format('l');
                $this->assertStringContainsStringIgnoringCase($day_letter, $htmlRender);
            }
        } elseif ($format['widthWindow'] <= 1250 && $format['widthWindow'] > 1100) {
            $this->assertEquals(5, $crawler->filter('[class*=calendar-entete]')->count());
            for ($i = 0; $i <= 4; $i++) {
                $dateCurrent  = new \DateTime($format['currentDate']);
                $datePlusOne = $dateCurrent->modify('+' . $i . ' day');
                $day_letter = $datePlusOne->format('l');
                $this->assertStringContainsStringIgnoringCase($day_letter, $htmlRender);
            }
        } elseif ($format['widthWindow'] <= 1100 && $format['widthWindow'] > 910) {
            $this->assertEquals(4, $crawler->filter('[class*=calendar-entete]')->count());
            for ($i = 0; $i <= 3; $i++) {
                $dateCurrent  = new \DateTime($format['currentDate']);
                $datePlusOne = $dateCurrent->modify('+' . $i . ' day');
                $day_letter = $datePlusOne->format('l');
                $this->assertStringContainsStringIgnoringCase($day_letter, $htmlRender);
            }
        } elseif ($format['widthWindow'] <= 910 && $format['widthWindow'] > 750) {
            $this->assertEquals(3, $crawler->filter('[class*=calendar-entete]')->count());
            for ($i = 0; $i <= 2; $i++) {
                $dateCurrent  = new \DateTime($format['currentDate']);
                $datePlusOne = $dateCurrent->modify('+' . $i . ' day');
                $day_letter = $datePlusOne->format('l');
                $this->assertStringContainsStringIgnoringCase($day_letter, $htmlRender);
            }
        } elseif ($format['widthWindow'] <= 750 && $format['widthWindow'] > 580) {
            $this->assertEquals(2, $crawler->filter('[class*=calendar-entete]')->count());
            for ($i = 0; $i <= 1; $i++) {
                $dateCurrent  = new \DateTime($format['currentDate']);
                $datePlusOne = $dateCurrent->modify('+' . $i . ' day');
                $day_letter = $datePlusOne->format('l');
                $this->assertStringContainsStringIgnoringCase($day_letter, $htmlRender);
            }
        } elseif ($format['widthWindow'] <= 580) {
            $this->assertEquals(1, $crawler->filter('[class*=calendar-entete]')->count());
            for ($i = 0; $i <= 0; $i++) {
                $dateCurrent  = new \DateTime($format['currentDate']);
                $datePlusOne = $dateCurrent->modify('+' . $i . ' day');
                $day_letter = $datePlusOne->format('l');
                $this->assertStringContainsStringIgnoringCase($day_letter, $htmlRender);
            }
        }

        // START clean BDD after unitTest
        $objects = $this->removeObjects($objects);
    }

    /**
     * Data provider pour la fonction getEvents
     */
    public function getEventsDataProvider()
    {
        $_data = self::initData();
        $comportementAutorisationData = $_data['comportementAutorisationData'];
        $formatActiviteData = $_data['formatActiviteData'];
        $creneauData = $_data['creneauData'];
        $serieData = $_data['serieData'];
        $EventsData = $_data['EventsData'];
        $ressourceData = $_data['ressourceData'];
        $reservabiliteData = $_data['reservabiliteData'];
        $etablissementData = $_data['etablissementData'];
        $format = $_data['format'];

        $dateDebut = new DateTime('2022-01-01 00:00:00');
        $dateFin = new DateTime('2022-01-31 23:59:59');

        $dateDebut_1 = new DateTime('2022-12-01 00:00:00');
        $dateFin_1 = new DateTime('2022-12-31 23:59:59');

        $dateDebut_2 = new DateTime('2022-06-01 00:00:00');
        $dateFin_2 = new DateTime('2022-06-30 23:59:59');

        $formatActiviteData_1 = $_data['formatActiviteData'];
        $formatActiviteData_1['Class'] = 'FormatAvecReservation';
        $formatActiviteData_1['Libelle'] = 'FormatAvecReservation';
        $format_1 = $format;
        $format_1['typeFormat'] = 'FormatAvecReservation';
        return [
            // Format Avec Creneau
            [$this->createFormatActivite($comportementAutorisationData, $formatActiviteData, $creneauData, $serieData, $EventsData, $ressourceData, $reservabiliteData, $etablissementData), $format, $dateDebut, $dateFin],
            [$this->createFormatActivite($comportementAutorisationData, $formatActiviteData, $creneauData, $serieData, $EventsData, $ressourceData, $reservabiliteData, $etablissementData), $format, $dateDebut_1, $dateFin_1],
            [$this->createFormatActivite($comportementAutorisationData, $formatActiviteData, $creneauData, $serieData, $EventsData, $ressourceData, $reservabiliteData, $etablissementData), $format, $dateDebut_2, $dateFin_2],
            // Format Avec reservation
            [$this->createFormatActivite($comportementAutorisationData, $formatActiviteData_1, $creneauData, $serieData, $EventsData, $ressourceData, $reservabiliteData, $etablissementData), $format_1, $dateDebut, $dateFin],
            [$this->createFormatActivite($comportementAutorisationData, $formatActiviteData_1, $creneauData, $serieData, $EventsData, $ressourceData, $reservabiliteData, $etablissementData), $format_1, $dateDebut_1, $dateFin_1],
            [$this->createFormatActivite($comportementAutorisationData, $formatActiviteData_1, $creneauData, $serieData, $EventsData, $ressourceData, $reservabiliteData, $etablissementData), $format_1, $dateDebut_2, $dateFin_2],

        ];
    }

    /**
     * @dataProvider getEventsDataProvider
     * 
     * @covers \App\Service\Service\CalendrierService::getEvents
     */
    public function testgetEvents($objects, $format, DateTime $dateDebut, DateTime $dateFin): void
    {
        $objects = $this->persistObjects($objects);

        $format['itemId'] = $objects['formatActivite']->getId();
        // END INIT objects before unitTest

        $args_getEvents = [
            $format,
            $dateDebut,
            $dateFin,
            $objects['formatActivite']
        ];
        $_getEvents = self::callMethod($this->calendrierService, 'getEvents', $args_getEvents);
        $this->assertContainsOnlyInstancesOf('App\Entity\Uca\DhtmlxEvenement', $_getEvents);
        foreach ($_getEvents as $event) {
            $eventDateDebut = $event->getDateDebut();
            $eventDateFin = $event->getDateFin();
            $FA_DateFinEffective = $objects['formatActivite']->getDateFinEffective();
            $eventInInterval = $eventDateDebut <= $dateFin  && $eventDateFin >= $dateDebut && $eventDateFin <= $FA_DateFinEffective;
            $this->assertTrue($eventInInterval);
        }

        // START clean BDD after unitTest
        $objects = $this->removeObjects($objects);
    }


    /**
     * Data provider pour la fonction createDayPlanning
     */
    public function createDayPlanningDataProvider()
    {
        $_data = self::initData();
        $comportementAutorisationData = $_data['comportementAutorisationData'];
        $formatActiviteData = $_data['formatActiviteData'];
        $creneauData = $_data['creneauData'];
        $serieData = $_data['serieData'];
        $EventsData = $_data['EventsData'];
        $ressourceData = $_data['ressourceData'];
        $reservabiliteData = $_data['reservabiliteData'];
        $etablissementData = $_data['etablissementData'];
        $format = $_data['format'];
        $format['typeVisualisation'] = 'jour';
        $twigConfig = $_data['twigConfig'];
        $twigConfig['typeVisualisation'] = $format['typeVisualisation'];

        $formatActiviteData_1 = $_data['formatActiviteData'];
        $formatActiviteData_1['Class'] = 'FormatAvecReservation';
        $formatActiviteData_1['Libelle'] = 'FormatAvecReservation';
        $format_1 = $format;
        $format_1['typeFormat'] = 'FormatAvecReservation';
        return [
            // Format Avec Creneau
            [$this->createFormatActivite($comportementAutorisationData, $formatActiviteData, $creneauData, $serieData, $EventsData, $ressourceData, $reservabiliteData, $etablissementData), $format, $twigConfig],
            // Format Avec Reservation
            [$this->createFormatActivite($comportementAutorisationData, $formatActiviteData_1, $creneauData, $serieData, $EventsData, $ressourceData, $reservabiliteData, $etablissementData), $format_1, $twigConfig],

        ];
    }

    /**
     * @dataProvider createDayPlanningDataProvider
     * 
     * @covers \App\Service\Service\CalendrierService::createDayPlanning
     */
    public function testcreateDayPlanning($objects, array $format, array $twigConfig): void
    {
        $objects = $this->persistObjects($objects);

        $format['itemId'] = $objects['formatActivite']->getId();
        if (null !== $objects['ressource']) {
            $format['idRessource'] = $objects['ressource']->getId();
            $twigConfig['idRessource'] = $format['idRessource'];
        }

        $twigConfig['itemId'] = $format['itemId'];
        $twigConfig['formatActivite'] = $objects['formatActivite'];
        // END INIT objects before unitTest

        $htmlRender = $this->calendrierService->createDayPlanning($objects['formatActivite'], $format, $twigConfig);
        $currentDate = new DateTime($format['currentDate']);
        $dateDebut = clone $currentDate->modify('first day of this month');
        $dateFin = clone $currentDate->modify('last day of this month')->setTime(23, 59, 59);

        foreach ($objects['events'] as $event) {
            $eventDateDebut = $event->getDateDebut();
            $eventDateFin = $event->getDateFin();
            $FA_DateFinEffective = $objects['formatActivite']->getDateFinEffective();
            $querySelector = '#content_popover_' . $event->getId();
            if ($format['typeFormat'] == 'FormatAvecCreneau') {

                $eventInInterval = $eventDateDebut <= $dateFin  && $eventDateFin >= $dateDebut && $eventDateFin <= $FA_DateFinEffective;
                if ($eventInInterval) {
                    $this->assertStringContainsStringIgnoringCase($eventDateDebut->format('l d/m'), $htmlRender);
                    $this->assertStringContainsString($querySelector, $htmlRender);
                } else {
                    $this->assertStringNotContainsString($querySelector, $htmlRender);
                }
            } else if ($format['typeFormat'] == 'FormatAvecReservation') {
                $eventInInterval = $eventDateDebut <= $dateFin  && $eventDateFin >= $dateDebut;
                if ($eventInInterval) {
                    $this->assertStringContainsStringIgnoringCase($eventDateDebut->format('l d/m'), $htmlRender);
                    $this->assertStringContainsString($querySelector, $htmlRender);
                } else {
                    $this->assertStringNotContainsString($querySelector, $htmlRender);
                }
            }
        }

        // START clean BDD after unitTest
        $objects = $this->removeObjects($objects);
    }

    /**
     * Data provider pour la fonction getModalDetailCreneau
     */
    public function getModalDetailCreneauDataProvider()
    {
        $_data = self::initData();
        $comportementAutorisationData = $_data['comportementAutorisationData'];
        $formatActiviteData = $_data['formatActiviteData'];
        $creneauData = $_data['creneauData'];
        $serieData = $_data['serieData'];
        $EventsData = $_data['EventsData'];
        $ressourceData = $_data['ressourceData'];
        $reservabiliteData = $_data['reservabiliteData'];
        $etablissementData = $_data['etablissementData'];
        $format = $_data['format'];
        $format['typeVisualisation'] = 'jour';
        $twigConfig = $_data['twigConfig'];
        $twigConfig['typeVisualisation'] = $format['typeVisualisation'];

        $formatActiviteData_1 = $_data['formatActiviteData'];
        $formatActiviteData_1['Class'] = 'FormatAvecReservation';
        $formatActiviteData_1['Libelle'] = 'FormatAvecReservation';
        $format_1 = $format;
        $format_1['typeFormat'] = 'FormatAvecReservation';
        return [
            // Format Avec Creneau
            [$this->createFormatActivite($comportementAutorisationData, $formatActiviteData, $creneauData, $serieData, $EventsData, $ressourceData, $reservabiliteData, $etablissementData), $format, $twigConfig],
            // Format Avec Reservation
            [$this->createFormatActivite($comportementAutorisationData, $formatActiviteData_1, $creneauData, $serieData, $EventsData, $ressourceData, $reservabiliteData, $etablissementData), $format_1, $twigConfig],

        ];
    }

    /**
     * @dataProvider getModalDetailCreneauDataProvider
     * 
     * @covers \App\Service\Service\CalendrierService::getModalDetailCreneau
     */
    public function testgetModalDetailCreneau($objects, array $format, array $twigConfig): void
    {
        $objects = $this->persistObjects($objects);

        $format['itemId'] = $objects['formatActivite']->getId();
        if (null !== $objects['ressource']) {
            $format['idRessource'] = $objects['ressource']->getId();
            $twigConfig['idRessource'] = $format['idRessource'];
        }

        $twigConfig['itemId'] = $format['itemId'];
        $twigConfig['formatActivite'] = $objects['formatActivite'];
        // END INIT objects before unitTest

        foreach ($objects['events'] as $event) {
            $json_response = $this->calendrierService->getModalDetailCreneau(
                $event,
                $format['typeFormat'],
                $objects['formatActivite']->getId()
            );
            $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $json_response);
        }

        // START clean BDD after unitTest
        $objects = $this->removeObjects($objects);
    }

    protected static function initData(): array
    {
        // Cas par défault Jour et au "FormatAvecCreneau"
        $dateDebut = new \DateTime('2022-01-01');
        $dateFin = new \DateTime('2023-01-01');
        $comportementAutorisationData['Description'] = 'desc2';
        $comportementAutorisationData['Libelle'] = 'libelle comportement2';
        $comportementAutorisationData['CodeComportement'] = 'code';

        $formatActiviteData['Class'] = 'FormatAvecCreneau';
        $formatActiviteData['Libelle'] = 'FormatAvecCreneau';
        $formatActiviteData['Capacite'] = 10;
        $formatActiviteData['Description'] = '';
        $formatActiviteData['DateDebutPublication'] = $dateDebut;
        $formatActiviteData['DateFinPublication'] = $dateFin;
        $formatActiviteData['DateDebutInscription'] = $dateDebut;
        $formatActiviteData['DateFinInscription'] = $dateFin;
        $formatActiviteData['DateDebutEffective'] = $dateDebut;
        $formatActiviteData['DateFinEffective'] = $dateFin;
        $formatActiviteData['Image'] = '';
        $formatActiviteData['EstPayant'] = false;
        $formatActiviteData['EstEncadre'] = false;

        $creneauData['Capacite'] = 10;

        $serieData['DateDebut'] = $dateDebut;
        $serieData['DateFin'] = $dateFin;

        $EventsData = [
            'Event_en_cours' => [
                'Description' => 'Event_en_cours',
                'DateDebut' => new \DateTime('2022-07-04'),
                'DateFin' => new \DateTime('2022-12-31'),
            ],
            'Event_hors_currentDate' => [
                'Description' => 'Event_hors_currentDate',
                'DateDebut' => new \DateTime('2022-02-04'),
                'DateFin' => new \DateTime('2022-03-15'),
            ],
            'Event_lundi_11' => [
                'Description' => 'Event_hors_currentDate',
                'DateDebut' => new \DateTime('2022-07-11'),
                'DateFin' => new \DateTime('2022-07-11'),
            ],
            'Event_lundi_7' => [
                'Description' => 'Event_hors_currentDate',
                'DateDebut' => new \DateTime('2022-07-07'),
                'DateFin' => new \DateTime('2022-07-07'),
            ]

        ];

        $ressourceData['Libelle'] = 'lieu';
        $ressourceData['NbPartenaires'] = 1;
        $imageTest = new File(__DIR__ . '../../../fixtures/vtt.jpg');
        $ressourceData['ImageFile'] = $imageTest;
        $ressourceData['Image'] = $imageTest->getRealPath();
        $ressourceData['NbPartenairesMax'] = 1;

        $reservabiliteData['Capacite'] = 33;

        $etablissementData['Libelle'] = 'Etablissement';
        $etablissementData['Code'] = 'code etabli';
        $etablissementData['Adresse'] = 'adresse';
        $etablissementData['CodePostal'] = '45000';
        $etablissementData['Ville'] = 'villll';
        $etablissementData['ImageFile'] = $imageTest;
        $etablissementData['Image'] = $imageTest->getRealPath();

        $format = [
            // Valeur importante selon le cas de test, cette valeur peut être mise à override dans le dataprovider 
            'typeFormat' => 'FormatAvecCreneau',
            // Valeur importante selon le cas de test, cette valeur peut être mise à override dans le dataprovider 
            'typeVisualisation' => 'semaine',
            'widthWindow' => 1000,
            'currentDate' => date_format((new \DateTime('2022-07-07')), 'd/m/Y'),
            // Valeur importante selon le cas de test, cette valeur peut être mise à override dans le dataprovider
            'idRessource' => 1
        ];
        $twigConfig['typeVisualisation'] = $format['typeVisualisation'];
        $twigConfig['currentDate'] = \DateTime::createFromFormat('d/m/Y', $format['currentDate'])->setTime(0, 0);
        $twigConfig['typeFormat'] = $format['typeFormat'];
        $twigConfig['idRessource'] = $format['idRessource'];
        $twigConfig['widthWindow'] = $format['widthWindow'];
        return [
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
            'comportementAutorisationData' => $comportementAutorisationData,
            'formatActiviteData' => $formatActiviteData,
            'creneauData' => $creneauData,
            'serieData' => $serieData,
            'EventsData' => $EventsData,
            'ressourceData' => $ressourceData,
            'reservabiliteData' => $reservabiliteData,
            'etablissementData' => $etablissementData,
            'format' => $format,
            'twigConfig' => $twigConfig,
        ];
    }

    private function persistObjects($objects)
    {
        $this->em->persist($objects['comportementAutorisation']);
        $this->em->persist($objects['typeAutorisation']);
        $this->em->persist($objects['formatActivite']);

        if (null !== $objects['ressource'])
            $this->em->persist($objects['ressource']);
        if (null !== $objects['reservabilite'])
            $this->em->persist($objects['reservabilite']);
        if (null !== $objects['etablissement'])
            $this->em->persist($objects['etablissement']);
        if (null !== $objects['creneau'])
            $this->em->persist($objects['creneau']);

        $this->em->persist($objects['serie']);
        foreach ($objects['events'] as $event) {
            $this->em->persist($event);
        }
        $this->em->flush();
        return $objects;
    }

    private function removeObjects($objects)
    {
        $this->em->remove($objects['comportementAutorisation']);
        $this->em->remove($objects['typeAutorisation']);
        $this->em->remove($objects['formatActivite']);

        if (null !== $objects['ressource'])
            $this->em->remove($objects['ressource']);
        if (null !== $objects['reservabilite'])
            $this->em->remove($objects['reservabilite']);
        if (null !== $objects['etablissement'])
            $this->em->remove($objects['etablissement']);
        if (null !== $objects['creneau'])
            $this->em->remove($objects['creneau']);

        $this->em->remove($objects['serie']);
        foreach ($objects['events'] as $event) {
            $this->em->remove($event);
        }
        $this->em->flush();
        return $objects;
    }

    protected static function callMethod($obj, $name, array $args)
    {
        $class = new ReflectionClass('App\Service\Service\CalendrierService');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method->invokeArgs($obj, $args);
    }
}
