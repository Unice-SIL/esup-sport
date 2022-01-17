<?php

/*
 * Classe - ActiviteController:
 *
 * Traitement des activités de amnière générale
 * Contrôleur technique permettant l'organisation des données
*/

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
use UcaBundle\Service\Service\CalendrierService;

class ActiviteController extends Controller
{
    /**
     * @Route("/Api/Activite/GetCreneaux", methods={"POST"}, name="api_activite_creneau", options={"expose"=true})
     */
    public function DataAction(Request $request)
    {
        return $this->get('uca.calendrier')->createPlanning($request->get('data'));
    }

    /**
     * @Route("/Api/Activite/GetModalDetailCreneau/{id}/{typeFormat}/{idFormat}", methods={"GET"}, name="api_detail_creneau", options={"expose"=true})
     */
    public function DetailCreneau(Request $request, DhtmlxEvenement $dhtmlxEvenement, string $typeFormat, string $idFormat)
    {
        return $this->get('uca.calendrier')->getModalDetailCreneau($dhtmlxEvenement, $typeFormat, $idFormat);
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
