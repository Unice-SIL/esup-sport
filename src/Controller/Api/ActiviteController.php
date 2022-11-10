<?php

/*
 * Classe - ActiviteController:
 *
 * Traitement des activités de amnière générale
 * Contrôleur technique permettant l'organisation des données
*/

namespace App\Controller\Api;

use App\Entity\Uca\Activite;
use App\Entity\Uca\DhtmlxEvenement;
use App\Repository\DhtmlxEvenementRepository;
use App\Repository\UtilisateurRepository;
use App\Service\Common\MailService;
use App\Service\Service\CalendrierService;
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
    public function DetailCreneau(Request $request, DhtmlxEvenement $dhtmlxEvenement, string $typeFormat, string $idFormat, CalendrierService $calendrierService)
    {
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

            $objet = $event->getFormatActiviteLibelle().' : '.date_format($event->getDateDebut(), 'Y/m/d H:i:s').' - '.date_format($event->getDateFin(), 'Y/m/d H:i:s');

            $mailer->sendMailWithTemplate(
                $objet,
                $encadrant->getEmail(),
                'UcaBundle/Email/Contact/ContactEmail.html.twig',
                ['objet' => $objet, 'message' => $request->get('message'), 'contact_from' => $this->getUser()->getEmail()],
                null
            );

            return new JsonResponse(['response' => 'success']);
        }

        return new JsonResponse(['response' => 'success']);
    }
}
