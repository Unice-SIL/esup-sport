<?php

namespace UcaBundle\Controller\Api;

use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Entity\Activite;
use UcaBundle\Entity\Creneau;
use UcaBundle\Entity\DhtmlxEvenement;
use UcaBundle\Entity\Etablissement;
use UcaBundle\Entity\FormatAvecCreneau;
use UcaBundle\Entity\FormatAvecReservation;
use UcaBundle\Entity\Utilisateur;

class ActiviteController extends Controller
{
    /**
     * @Route("/Api/Activite/GetCreneaux", methods={"POST"}, name="api_activite_creneau", options={"expose"=true})
     */
    public function DataAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $parametreData = $request->get('data');

        if ('FormatAvecCreneau' == $parametreData['typeFormat']) {
            $item = $em->getRepository(FormatAvecCreneau::class)->findOneById($parametreData['itemId']);
        } elseif ('FormatAvecReservation' == $parametreData['typeFormat']) {
            $item = $em->getRepository(FormatAvecReservation::class)->findOneById($parametreData['itemId']);
        }

        $twigConfig['itemId'] = $item->getId();
        $twigConfig['typeVisualisation'] = $parametreData['typeVisualisation'];
        $currentDate = \DateTime::createFromFormat('d/m/Y', $parametreData['currentDate'])->setTime(0, 0);
        $twigConfig['currentDate'] = $currentDate;
        $twigConfig['typeFormat'] = $parametreData['typeFormat'];
        $twigConfig['idRessource'] = $parametreData['idRessource'];
        $twigConfig['widthWindow'] = $parametreData['widthWindow'];
        $twigConfig['formatActivite'] = $item;

        if ('mois' == $parametreData['typeVisualisation'] && $parametreData['widthWindow'] < 1350 && $parametreData['widthWindow'] >= 580) {
            $parametreData['typeVisualisation'] = 'semaine';
        } elseif (('mois' == $parametreData['typeVisualisation'] || 'semaine' == $parametreData['typeVisualisation']) && $parametreData['widthWindow'] < 580) {
            $parametreData['typeVisualisation'] = 'jour';
        }

        $nbJour = 0;
        if ($parametreData['widthWindow'] > 1350) {
            $nbJour = 7;
        } elseif ($parametreData['widthWindow'] <= 1350 && $parametreData['widthWindow'] > 1150) {
            $nbJour = 6;
        } elseif ($parametreData['widthWindow'] <= 1150 && $parametreData['widthWindow'] > 1000) {
            $nbJour = 5;
        } elseif ($parametreData['widthWindow'] <= 1000 && $parametreData['widthWindow'] > 825) {
            $nbJour = 4;
        } elseif ($parametreData['widthWindow'] <= 825 && $parametreData['widthWindow'] > 650) {
            $nbJour = 3;
        } elseif ($parametreData['widthWindow'] <= 650 && $parametreData['widthWindow'] > 580) {
            $nbJour = 2;
        } elseif ($parametreData['widthWindow'] <= 580) {
            $nbJour = 1;
        }
        $twigConfig['nbJour'] = $nbJour;

        $dates = [];
        $dateDebut = null;
        $datefin = null;
        if ('semaine' == $parametreData['typeVisualisation'] || 'mois' == $parametreData['typeVisualisation']) {
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

            if ('semaine' == $parametreData['typeVisualisation']) {
                $dateDebut = $dates[array_key_first($dates)];
                $datefin = $dates[array_key_last($dates)];
            } elseif ('mois' == $parametreData['typeVisualisation']) {
                $dt = clone $currentDate;
                $dateDebut = $dt->modify('first day of this month');
                $dt = clone $currentDate->modify('last day of this month');
                $datefin = $dt;
            }
        } elseif ('jour' == $parametreData['typeVisualisation']) {
            $dates[] = $currentDate;
            $dateDebut = $currentDate;
            $datefin = $currentDate;
        }
        $datefin = (clone $datefin)->setTime(23, 59);
        $twigConfig['listeJours'] = $dates;

        $listeCampus = [];
        $dataCalendrier = [];

        if ('mois' == $parametreData['typeVisualisation']) {
            $dataCalendrier = array_fill(0, (intval($datefin->format('W')) - intval($dateDebut->format('W')) + 1), array_fill(0, count($twigConfig['listeJours']), null));

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
        }

        if ('FormatAvecCreneau' == $parametreData['typeFormat']) {
            $listeEvenements = $em->getRepository(DhtmlxEvenement::class)->findEvenementChaqueSerieDuFormatBetwennDates($item->getId(), $dateDebut, $datefin);
        } elseif ('FormatAvecReservation' == $parametreData['typeFormat']) {
            $listeEvenements = $em->getRepository(DhtmlxEvenement::class)->findEvenementChaqueSerieDuFormatBetwennDatesByRessource($parametreData['idRessource'], $dateDebut, $datefin);
        }

        foreach ($listeEvenements as $evenement) {
            if ('FormatAvecCreneau' == $parametreData['typeFormat']) {
                $campus = $em->getRepository(Etablissement::class)->findOneById($evenement->getSerie()->getCreneau()->getLieu()->getEtablissement()->getId());
            } elseif ('FormatAvecReservation' == $parametreData['typeFormat']) {
                $campus = 'Ressource';
            }

            $indexColonneCorrespondantDate = null;
            $indexLigneCorrespondantCampus = null;
            $eventDateDebut = (clone $evenement->getDateDebut())->setTime(0, 0);
            if ('semaine' == $parametreData['typeVisualisation'] || 'jour' == $parametreData['typeVisualisation']) {
                if (0 == count($listeCampus) || !in_array($campus, $listeCampus)) {
                    $listeCampus[] = $campus;
                    $dataCalendrier[] = array_fill(0, count($dates), null);
                    $indexLigneCorrespondantCampus = count($listeCampus) - 1;
                } else {
                    $indexLigneCorrespondantCampus = array_search($campus, $listeCampus);
                }
                $indexColonneCorrespondantDate = array_search($eventDateDebut, $dates);
            } elseif ('mois' == $parametreData['typeVisualisation']) {
                $indexColonneCorrespondantDate = $eventDateDebut->format('w') - 1;
                $indexLigneCorrespondantCampus = intval($eventDateDebut->format('W')) - intval($dateDebut->format('W'));
            }

            if (null == $dataCalendrier[$indexLigneCorrespondantCampus][$indexColonneCorrespondantDate]) {
                $dataCalendrier[$indexLigneCorrespondantCampus][$indexColonneCorrespondantDate]['data'] = [];
            }
            $dataCalendrier[$indexLigneCorrespondantCampus][$indexColonneCorrespondantDate]['data'][] = $evenement;
        }

        $twigConfig['listeCampus'] = $listeCampus;
        $twigConfig['dataCalendrier'] = $dataCalendrier;

        $res['content'] = $this->render('@Uca/UcaWeb/Activite/Calendrier/FormatActivite.calendrier.html.twig', $twigConfig)->getContent();

        return new JsonResponse($res);
    }

    /**
     * @Route("/Api/Mail/Encadrant", methods={"POST"}, name="api_mailencadrant", options={"expose"=true})
     */
    public function sendMailAction(Request $request)
    {
        $retour = '';
        if ($this->getUser()) {
            $em = $this->getDoctrine()->getManager();
            $encadrant = $em->getRepository(Utilisateur::class)->find($request->get('encadrant'));
            $event = $em->getRepository(DhtmlxEvenement::class)->find($request->get('event'));

            $objet = $event->getDescription().' : '.date_format($event->getDateDebut(), 'Y/m/d H:i:s').' - '.date_format($event->getDateFin(), 'Y/m/d H:i:s');

            $mailer = $this->container->get('mailService');
            $mailer->sendMailWithTemplate(
                $objet,
                $encadrant->getEmail(),
                '@Uca/Email/Contact/ContactEmail.html.twig',
                ['objet' => $objet, 'message' => $request->get('message'), 'email' => $this->getUser()->getEmail()],
                null
            );

            return new JsonResponse(['response' => 'success']);
        }

        return new JsonResponse(['response' => 'success']);
    }

    /**
     * @Route("/Api/Activite", methods={"POST"}, name="ActiviteApi_RefreshActivities", options={"expose"=true})
     */
    public function refreshActivities(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $idTypeActivite = $request->get('type_activite');
        $idClasseActivite = $request->get('classe_activite');
        $idActivite = $request->get('activite');
        $format = $request->get('format_activite');
        $idEtablissement = $request->get('etablissement');
        $idLieu = $request->get('lieu');
        '' != $request->get('dateDebut') ? $dateDebut = DateTime::createFromFormat('d/m/Y H:i', $request->get('dateDebut')) : $dateDebut = null;
        '' != $request->get('dateFin') ? $dateFin = DateTime::createFromFormat('d/m/Y H:i', $request->get('dateFin')) : $dateFin = null;

        $reponse = [];
        $activitesValides = [];
        $formatActiviteValides = [];
        $detailsActivite = [];
        $acts = [];
        $activites = $em->getRepository(Activite::class)->findByParameters($idTypeActivite, $idClasseActivite, $idActivite, $idEtablissement, $idLieu);

        foreach ($activites as $activite) {
            $formatsActivite = $this->isValid($format, $activite, $dateDebut, $dateFin);
            if (sizeof($formatsActivite) > 0) {
                array_push($activitesValides, $activite);
                $formatActiviteValides[$activite->getId()] = $formatsActivite;
            }
        }

        if (null !== $activitesValides) {
            foreach ($activitesValides as $activite) {
                $acts[] = json_encode($activite);
                $detailsActivite[$activite->getId()] = json_encode($formatActiviteValides[$activite->getId()]);
            }
        }

        if (sizeof($acts) > 0 && sizeof($detailsActivite) > 0) {
            $reponse = json_encode([
                'activite' => $acts,
                'formatActivite' => $detailsActivite,
            ]);
        }

        return new JsonResponse($reponse);
    }

    private function isValid($format, $activite, $dateDebut, $dateFin)
    {
        $formatsValide = [];
        foreach ($activite->getFormatsActivite() as $formatActivite) {
            if (null != $format && '0' !== $format) {
                if ($formatActivite->getFormat() == $format) {
                    if (null != $dateDebut || null != $dateFin) {
                        !$this->checkDate($dateDebut, $dateFin, $formatActivite) ?: array_push($formatsValide, $formatActivite->getLibelle());
                    } else {
                        array_push($formatsValide, $formatActivite->getLibelle());
                    }
                }
            } else {
                if (null != $dateDebut || null != $dateFin) {
                    !$this->checkDate($dateDebut, $dateFin, $formatActivite) ?: array_push($formatsValide, $formatActivite->getLibelle());
                } else {
                    array_push($formatsValide, $formatActivite->getLibelle());
                }
            }
        }

        return $formatsValide;
    }

    private function checkDate($dateDebut, $dateFin, $formatActivite)
    {
        switch ($formatActivite->getFormat()) {
            case 'FormatAvecCreneau':
                foreach ($formatActivite->getCreneaux() as $creneau) {
                    if (null !== $creneau->getSerie()) {
                        if (null != $creneau->getSerie()->getEvenements()) {
                            foreach ($creneau->getSerie()->getEvenements() as $evenement) {
                                if ($this->verifDate($dateDebut, $dateFin, $evenement->getDateDebut(), $evenement->getDateFin())) {
                                    return true;
                                }
                            }
                        }
                    }
                }

                return false;

                break;
            case 'FormatAvecReservation':
                foreach ($formatActivite->getRessource() as $ressource) {
                    foreach ($ressource->getReservabilites() as $reservabilite) {
                        $evenement = $reservabilite->getEvenement();
                        if (null != $evenement) {
                            if ($this->verifDate($dateDebut, $dateFin, $evenement->getDateDebut(), $evenement->getDateFin())) {
                                return true;
                            }
                        }
                    }
                }

                return false;

                break;
            case 'FormatAchatcarte':
                if ($this->verifDate($dateDebut, $dateFin, $formatActivite->getDateDebutEffective(), $formatActivite->getDateFinEffective())) {
                    return true;
                }

                return false;

                break;
            case 'FormatSimple':
                $evenement = $formatActivite->getEvenement();
                if (null != $evenement) {
                    if ($this->verifDate($dateDebut, $dateFin, $formatActivite->getEvenement()->getDateDebut(), $formatActivite->getEvenement()->getDateFin())) {
                        return true;
                    }
                }

                return false;

                break;
            default:
                return false;

                break;
        }
    }

    private function verifDate($dateDebutSearch, $dateFinSearch, $dateDebut, $dateFin)
    {
        if (null != $dateDebut && null != $dateDebut) {
            if ($dateDebutSearch >= $dateDebut && $dateFinSearch <= $dateFin) {
                return true;
            }

            return false;
        }
        if (null != $dateDebut) {
            if ($dateDebutSearch >= $dateDebut) {
                return true;
            }

            return false;
        }
        if (null != $dateFin) {
            if ($dateFinSearch <= $dateFin) {
                return true;
            }

            return false;
        }

        return true;
    }
}
