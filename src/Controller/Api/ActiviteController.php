<?php

/*
 * Classe - ActiviteController:
 *
 * Traitement des activités de amnière générale
 * Contrôleur technique permettant l'organisation des données
*/

namespace App\Controller\Api;

use App\Repository\DhtmlxEvenementRepository;
use App\Repository\UtilisateurRepository;
use App\Service\Common\MailService;
use App\Service\Service\CalendrierService;
use App\Service\Service\StylePreviewService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ActiviteController extends AbstractController
{
    /**
     * @Route("/Api/Activite/GetCreneaux", methods={"POST"}, name="api_activite_creneau", options={"expose"=true})
     */
    public function DataAction(Request $request, CalendrierService $calendrierService)
    {
        return $calendrierService->createPlanning($request->get('data'));
    }

    /**
     * @Route("/Api/Activite/GetModalDetailCreneau/{id}/{typeFormat}/{idFormat}", methods={"GET"}, name="api_detail_creneau", options={"expose"=true})
     */
    public function DetailCreneau(Request $request, string $id, string $typeFormat, string $idFormat, CalendrierService $calendrierService, StylePreviewService $previewService, DhtmlxEvenementRepository $eventRepo)
    {
        if ($idFormat === '0') { // si on est preview du  style alors on utilise le service de preview stle
            $previewService->setUtilisateur($this->getUser());
            $dhtmlxEvenement = $previewService->getEvent($id);
        } else {
            $dhtmlxEvenement = $eventRepo->find($id);
        }
        return $calendrierService->getModalDetailCreneau($dhtmlxEvenement, $typeFormat, $idFormat);
    }

    /**
     * @Route("/Api/Mail/Encadrant", methods={"POST"}, name="api_mailencadrant", options={"expose"=true})
     */
    public function sendMailAction(Request $request, MailService $mailer, UtilisateurRepository $userRepo, DhtmlxEvenementRepository $eventRepo)
    {
        $retour = '';
        if ($this->getUser()) {
            $encadrant = $userRepo->find($request->get('encadrant'));
            $event = $eventRepo->find($request->get('event'));

            $mailer->sendMailWithTemplate(
                null,
                $encadrant->getEmail(),
                'ContactEncadrantEmail',
                ['message' => $request->get('message'), 'contact_from' => $this->getUser()->getEmail(), 'event_date' => date_format($event->getDateDebut(), 'd/m/Y'), 'event_start_hour' => date_format($event->getDateDebut(), 'H:i'), 'event_end_hour' => date_format($event->getDateFin(), 'H:i'), 'format_activite' => $event->getFormatActiviteLibelle()],
                null
            );

            return new JsonResponse(['response' => 'success']);
        }

        return new JsonResponse(['response' => 'success']);
    }
}
