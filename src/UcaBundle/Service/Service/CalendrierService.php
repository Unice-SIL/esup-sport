<?php

namespace UcaBundle\Service\Service;

use DateInterval;
use DatePeriod;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Translation\TranslatorInterface;
use UcaBundle\Entity\DhtmlxEvenement;
use UcaBundle\Entity\Etablissement;
use UcaBundle\Entity\FormatActivite;
use UcaBundle\Entity\FormatAvecCreneau;
use UcaBundle\Entity\FormatAvecReservation;

class CalendrierService
{
    public const DAYS = [
        1 => 'common.monday',
        2 => 'common.tuesday',
        3 => 'common.wednesday',
        4 => 'common.thursday',
        5 => 'common.friday',
        6 => 'common.saturday',
        7 => 'common.sunday',
    ];
    private $formatAvecCreneauRepository;
    private $formatAvecReservationRepository;
    private $dhtmlxEvenementRepository;
    private $etablissementRepository;
    private $twig;
    private $translator;

    public function __construct(
        EntityManagerInterface $em,
        TwigEngine $twig,
        TranslatorInterface $translator
    ) {
        $this->formatAvecCreneauRepository = $em->getRepository(FormatAvecCreneau::class);
        $this->formatAvecReservationRepository = $em->getRepository(FormatAvecReservation::class);
        $this->dhtmlxEvenementRepository = $em->getRepository(DhtmlxEvenement::class);
        $this->etablissementRepository = $em->getRepository(Etablissement::class);
        $this->twig = $twig;
        $this->translator = $translator;
    }

    /**
     * Fonction qui retourne le planning.
     */
    public function createPlanning(array $parametreData): JsonResponse
    {
        if ('FormatAvecCreneau' == $parametreData['typeFormat']) {
            $item = $this->formatAvecCreneauRepository->findOneById($parametreData['itemId']);
        } elseif ('FormatAvecReservation' == $parametreData['typeFormat']) {
            $item = $this->formatAvecReservationRepository->findOneById($parametreData['itemId']);
        }

        $twigConfig['itemId'] = $item->getId();
        $twigConfig['typeVisualisation'] = $parametreData['typeVisualisation'];
        $twigConfig['currentDate'] = \DateTime::createFromFormat('d/m/Y', $parametreData['currentDate'])->setTime(0, 0);
        $twigConfig['typeFormat'] = $parametreData['typeFormat'];
        $twigConfig['idRessource'] = $parametreData['idRessource'];
        $twigConfig['widthWindow'] = $parametreData['widthWindow'];
        $twigConfig['formatActivite'] = $item;

        if ('mois' == $parametreData['typeVisualisation'] && $parametreData['widthWindow'] < 1350 && $parametreData['widthWindow'] >= 580) {
            $parametreData['typeVisualisation'] = 'semaine';
        } elseif (('mois' == $parametreData['typeVisualisation'] || 'semaine' == $parametreData['typeVisualisation']) && $parametreData['widthWindow'] < 580) {
            $parametreData['typeVisualisation'] = 'jour';
        }

        if ('jour' == $parametreData['typeVisualisation']) {
            $res['content'] = $this->createDayPlanning($item, $parametreData, $twigConfig);
        } elseif ('semaine' == $parametreData['typeVisualisation']) {
            $res['content'] = $this->createWeekPlanning($item, $parametreData, $twigConfig);
        } else {
            $res['content'] = $this->createMonthPlanning($item, $parametreData, $twigConfig);
        }

        return new JsonResponse($res);
    }

    /**
     * Fonction qui permet de créer le planning pour la visulaisation par mois.
     */
    public function createMonthPlanning(FormatActivite $item, array $parametreData, array $twigConfig): string
    {
        $currentDate = clone $twigConfig['currentDate'];
        $twigConfig['nbJour'] = 7;

        $dates = [];
        $dateDebut = null;
        $datefin = null;
        for ($d = 1; $d <= 7; ++$d) {
            $dt = clone $currentDate;
            $dt->setISODate($dt->format('o'), $dt->format('W'), $d);
            $dates[] = $dt;
        }

        $dt = clone $currentDate;
        $dateDebut = $dt->modify('first day of this month');
        $dt = clone $currentDate->modify('last day of this month');
        $datefin = $dt;
        $datefin = (clone $datefin)->setTime(23, 59);
        $twigConfig['listeJours'] = $dates;

        $listeCampus = [];
        $dataCalendrier = [];

        if (01 == $dateDebut->format('m')) {
            $dataCalendrier = array_fill(0, (intval($datefin->format('W')) + 1), array_fill(0, count($twigConfig['listeJours']), null));
        } elseif (12 == $dateDebut->format('m')) {
            $dataCalendrier = array_fill(0, (53 - intval($dateDebut->format('W')) + 1), array_fill(0, count($twigConfig['listeJours']), null));
        } else {
            $dataCalendrier = array_fill(0, (intval($datefin->format('W')) - intval($dateDebut->format('W')) + 1), array_fill(0, count($twigConfig['listeJours']), null));
        }

        $dt = clone $dateDebut;
        $dt = $dt->modify('monday this week');

        foreach ($dataCalendrier as $ndexWeek => $ligne) {
            foreach ($ligne as $indexInWeek => $oneData) {
                $dataCalendrier[$ndexWeek][$indexInWeek]['day'] = $dt->format('d');
                $dataCalendrier[$ndexWeek][$indexInWeek]['actif'] = $dt->format('m') === $currentDate->format('m');
                $dataCalendrier[$ndexWeek][$indexInWeek]['data'] = [];
                $dt = $dt->modify('+1 day');
            }
        }

        $listeEvenements = $this->getEvents($parametreData, $dateDebut, $datefin, $item);

        foreach ($listeEvenements as $evenement) {
            $indexColonneCorrespondantDate = null;
            $indexLigneCorrespondantCampus = null;
            $eventDateDebut = (clone $evenement->getDateDebut())->setTime(0, 0);
            if (0 == $eventDateDebut->format('w')) {
                $indexColonneCorrespondantDate = 6;
            } else {
                $indexColonneCorrespondantDate = $eventDateDebut->format('w') - 1;
            }
            if (53 == $dateDebut->format('W') && 01 == $dateDebut->format('m')) {
                if (53 == $eventDateDebut->format('W')) {
                    $indexLigneCorrespondantCampus = 0;
                } else {
                    $indexLigneCorrespondantCampus = intval($eventDateDebut->format('W'));
                }
            } else {
                $indexLigneCorrespondantCampus = intval($eventDateDebut->format('W')) - intval($dateDebut->format('W'));
                if ($indexLigneCorrespondantCampus < 0) {
                    $indexLigneCorrespondantCampus += 52;
                }
            }

            if (!isset($dataCalendrier[$indexLigneCorrespondantCampus]) || !isset($dataCalendrier[$indexLigneCorrespondantCampus][$indexColonneCorrespondantDate]) || null == $dataCalendrier[$indexLigneCorrespondantCampus][$indexColonneCorrespondantDate]) {
                $dataCalendrier[$indexLigneCorrespondantCampus][$indexColonneCorrespondantDate]['data'] = [];
            } else {
                $dataCalendrier[$indexLigneCorrespondantCampus][$indexColonneCorrespondantDate]['data'][] = $evenement;
            }
        }

        $twigConfig['listeCampus'] = $listeCampus;
        $twigConfig['dataCalendrier'] = $dataCalendrier;

        return $this->twig->render('@Uca/UcaWeb/Activite/Calendrier/FormatActivite.calendrier.html.twig', $twigConfig);
    }

    /**
     * Fonction qui permet de créer le planning pour la visualisation par semaine.
     */
    public function createWeekPlanning(FormatActivite $item, array $parametreData, array $twigConfig): string
    {
        $currentDate = clone $twigConfig['currentDate'];

        $nbJour = 0;
        if ($parametreData['widthWindow'] > 1425) {
            $nbJour = 7;
        } elseif ($parametreData['widthWindow'] <= 1425 && $parametreData['widthWindow'] > 1250) {
            $nbJour = 6;
        } elseif ($parametreData['widthWindow'] <= 1250 && $parametreData['widthWindow'] > 1100) {
            $nbJour = 5;
        } elseif ($parametreData['widthWindow'] <= 1100 && $parametreData['widthWindow'] > 910) {
            $nbJour = 4;
        } elseif ($parametreData['widthWindow'] <= 910 && $parametreData['widthWindow'] > 750) {
            $nbJour = 3;
        } elseif ($parametreData['widthWindow'] <= 750 && $parametreData['widthWindow'] > 580) {
            $nbJour = 2;
        } elseif ($parametreData['widthWindow'] <= 580) {
            $nbJour = 1;
        }
        $twigConfig['nbJour'] = $nbJour;

        $dates = [];
        $dateDebut = null;
        $datefin = null;
        if (7 == $nbJour) {
            for ($d = 1; $d <= 7; ++$d) {
                $dt = clone $currentDate;
                $dt->setISODate($dt->format('o'), $dt->format('W'), $d);
                $dates[] = $dt;
            }
        } else {
            for ($d = 0; $d < $nbJour; ++$d) {
                $dt = clone $currentDate;
                date_add($dt, date_interval_create_from_date_string($d.' days'));
                $dates[] = $dt;
            }
        }
        $dateDebut = $dates[array_key_first($dates)];
        $datefin = $dates[array_key_last($dates)];

        $datefin = (clone $datefin)->setTime(23, 59);
        $twigConfig['listeJours'] = $dates;

        $listeCampus = [];
        $dataCalendrier = [];

        $listeEvenements = $this->getEvents($parametreData, $dateDebut, $datefin, $item);

        foreach ($listeEvenements as $evenement) {
            if ('FormatAvecCreneau' == $parametreData['typeFormat']) {
                if ($evenement->getSerie()->getCreneau()->getLieu()->getEtablissement()) {
                    $campus = $this->etablissementRepository->findOneById($evenement->getSerie()->getCreneau()->getLieu()->getEtablissement()->getId());
                } else {
                    $c = [];
                    $c['libelle'] = $this->translator->trans('etablissement.exterieur');
                    $campus = $c;
                }
            } elseif ('FormatAvecReservation' == $parametreData['typeFormat']) {
                if ($evenement->getSerie() && $evenement->getSerie()->getReservabilite() && $ressource = $evenement->getSerie()->getReservabilite()->getRessource()) {
                    $campus = $ressource->getEtablissement();
                } elseif (null === $evenement->getSerie() && $evenement->getReservabilite() && $ressource = $evenement->getReservabilite()->getRessource()) {
                    $campus = $ressource->getEtablissement();
                } else {
                    $campus = 'Ressource';
                }
            }

            $indexColonneCorrespondantDate = null;
            $indexLigneCorrespondantCampus = null;
            $eventDateDebut = (clone $evenement->getDateDebut())->setTime(0, 0);

            if (0 == count($listeCampus) || !in_array($campus, $listeCampus)) {
                $listeCampus[] = $campus;
                $dataCalendrier[] = array_fill(0, count($dates), null);
                $indexLigneCorrespondantCampus = count($listeCampus) - 1;
            } else {
                $indexLigneCorrespondantCampus = array_search($campus, $listeCampus);
            }
            $indexColonneCorrespondantDate = array_search($eventDateDebut, $dates);

            if (!array_key_exists($indexLigneCorrespondantCampus, $dataCalendrier) || !array_key_exists($indexColonneCorrespondantDate, $dataCalendrier[$indexLigneCorrespondantCampus])) {
                $dataCalendrier[$indexLigneCorrespondantCampus][$indexColonneCorrespondantDate]['data'] = [];
            } else {
                $dataCalendrier[$indexLigneCorrespondantCampus][$indexColonneCorrespondantDate]['data'][] = $evenement;
            }
        }

        $twigConfig['listeCampus'] = $listeCampus;
        $twigConfig['dataCalendrier'] = $dataCalendrier;

        return $this->twig->render('@Uca/UcaWeb/Activite/Calendrier/FormatActivite.calendrier.html.twig', $twigConfig);
    }

    /**
     * Fonction qui permet de créer le planning pour la visualisation par jour.
     */
    public function createDayPlanning(FormatActivite $item, array $parametreData, array $twigConfig): string
    {
        $currentDate = clone $twigConfig['currentDate'];
        $dateDebut = clone $currentDate->modify('first day of this month');
        $dateFin = (clone $currentDate->modify('last day of this month'))->setTime(23, 59, 59);

        if ('FormatAvecCreneau' == $parametreData['typeFormat']) {
            $events = $this->dhtmlxEvenementRepository->findEvenementChaqueSerieDuFormatBetwennDates($item->getId(), $dateDebut, $dateFin);
        } elseif ('FormatAvecReservation' == $parametreData['typeFormat']) {
            $events = array_merge($this->dhtmlxEvenementRepository->findEventByRessourceAndDate($parametreData['idRessource'], $dateDebut, $dateFin), $this->dhtmlxEvenementRepository->findEventOfSerieByRessourceAndDate($parametreData['idRessource'], $dateDebut, $dateFin));
        }

        $listeEtablissements = [];
        $datas = [];
        $keysWeek = [];

        $weekPeriod = new DatePeriod($dateDebut, DateInterval::createFromDateString('1 day'), $dateFin);
        foreach ($weekPeriod as $week) {
            if (!isset($keysWeek[$week->format('W')])) {
                $keysWeek[$week->format('W')] = $this->translator->trans('common.weekfrom', ['%start%' => (clone $week->modify('Monday this week'))->format('d/m'), '%end%' => (clone $week->modify('Sunday this week'))->format('d/m')]);
            }
        }

        foreach ($events as $event) {
            $listeEtablissements[] = $event->getEtablissementLibelle();
            $dateDebutEvent = $event->getDateDebut();
            $keyDay = $this->translator->trans(static::DAYS[$dateDebutEvent->format('N')]).' '.$dateDebutEvent->format('d/m');

            $datas[$keysWeek[$dateDebutEvent->format('W')]][$keyDay][] = $event;
        }

        $twigConfig['etablissements'] = array_unique($listeEtablissements);
        $twigConfig['events'] = $datas;

        return $this->twig->render('@Uca/UcaWeb/Activite/Calendrier/FormatActivite.calendrier.mobile.html.twig', $twigConfig);
    }

    /**
     * Fonction qui permet de créer la modal de détail d'un DhtmlxEvenement.
     */
    public function getModalDetailCreneau(DhtmlxEvenement $event, string $typeFormat, string $idFormat): JsonResponse
    {
        if ('FormatAvecReservation' == $typeFormat) {
            $formatActivite = $this->formatAvecReservationRepository->find($idFormat);
        } else {
            $formatActivite = null;
        }

        return new JsonResponse([
            'html' => $this->twig->render('@Uca/UcaWeb/Activite/Calendrier/Modal.DetailCreneau.html.twig', ['event' => $event, 'typeFormat' => $typeFormat, 'formatActivite' => $formatActivite]),
        ]);
    }

    /**
     * Fonction qui permet de retourner les événements par date et activité.
     */
    private function getEvents(array $parametreData, DateTime $dateDebut, DateTime $dateFin, FormatActivite $item): array
    {
        if ('FormatAvecCreneau' == $parametreData['typeFormat']) {
            return $this->dhtmlxEvenementRepository->findEvenementChaqueSerieDuFormatBetwennDates($item->getId(), $dateDebut, $dateFin);
        }
        if ('FormatAvecReservation' == $parametreData['typeFormat']) {
            return array_merge($this->dhtmlxEvenementRepository->findEventByRessourceAndDate($parametreData['idRessource'], $dateDebut, $dateFin), $this->dhtmlxEvenementRepository->findEventOfSerieByRessourceAndDate($parametreData['idRessource'], $dateDebut, $dateFin));
        }
    }
}
