<?php

namespace UcaBundle\Controller\UcaWeb;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use UcaBundle\Entity\Inscription;
use UcaBundle\Form\InscriptionType;

/**
 * @Route("UcaWeb")
 */
class InscriptionController extends Controller
{

    private function convertResultToJsonTextResponse($form, $result)
    {
        $response = new JsonResponse($result);
        if ($form->isSubmitted()) {
            $response->headers->set('Content-Type', 'text/plain');
        }
        return $response;
    }
    /**
     * @Route("/Inscription", name="UcaWeb_Inscription", options={"expose"=true}, methods={"POST"})
     */
    public function inscriptionAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('uca.timeout')->nettoyageCommandeEtInscription();
        $inscriptionService = $this->get('uca.inscription');
        $id = $request->get("id");
        $type = $request->get("type");
        $idFormat = $request->get("idFormat");

        $item = $em->getRepository($type)->find($id);
        $format = null;
        if (!empty($idFormat))
            $format = $em->getRepository('UcaBundle:FormatAvecReservation')->find($idFormat);

            
        $inscriptionInformations = $item->getInscriptionInformations($this->getUser(), $format);
        if ($inscriptionInformations['statut'] != 'disponible') {
            $result['itemId'] = $item->getId();
            $result['statut'] = '-1';
            $result['html'] = $this->get('twig')->render(
                '@Uca/UcaWeb/Inscription/Modal.Error.html.twig',
                ["title" => "modal.error", "message" => "modal.error." . $inscriptionInformations['statut']]
            );
            return new JsonResponse($result);
        }

        $inscription = new Inscription($item, $this->getUser(), ['format' => $format]);
        $form = $this->get('form.factory')->create(InscriptionType::class, $inscription);
        $form->handleRequest($request);
        $inscription->updateStatut();

        $inscriptionService->setInscription($inscription);

        if ($inscription->getStatut() == 'initialise') {
            $result = $inscriptionService->getFormulaire($form);
            return $this->convertResultToJsonTextResponse($form, $result);
        } elseif (in_array($inscription->getStatut(), ['attentevalidationencadrant', 'attentevalidationgestionnaire'])) {
            $em->persist($inscription);
            $result = $inscriptionService->getMessagePreInscription();
            $em->flush();
            $inscriptionService->envoyerMailInscriptionNecessitantValidation();
            return $this->convertResultToJsonTextResponse($form, $result);
        } elseif ($inscription->getStatut() == 'attentepaiement') {
            $em->persist($inscription);
            $articles = $inscriptionService->ajoutPanier();
            $result = $inscriptionService->getComfirmationPanier($articles);
            $em->flush();
            return $this->convertResultToJsonTextResponse($form, $result);
        } else {
            dump($inscription);
            die;
        }
    }
}
