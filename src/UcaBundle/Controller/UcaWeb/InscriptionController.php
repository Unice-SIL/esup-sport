<?php

namespace UcaBundle\Controller\UcaWeb;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
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
    /**
     * @Route("/Inscription", name="UcaWeb_Inscription", options={"expose"=true})
     * @Method("POST")
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

        $result = $inscriptionService->controlePrevisualisation($item);
        if ($result['statut'] == '-1')
            return new JsonResponse($result);
        $result = $inscriptionService->controleDejaInscrit($item);
        if ($result['statut'] == '-1')
            return new JsonResponse($result);
        $result = $inscriptionService->controleMaxInscriptionCreneau($item, $type);
        if ($result['statut'] == '-1')
            return new JsonResponse($result);
        $result = $inscriptionService->controleMaxCapacite($item, $type);
        if ($result['statut'] == '-1')
            return new JsonResponse($result);

        $format = null;
        if (!empty($idFormat))
            $format = $em->getRepository('UcaBundle:FormatAvecReservation')->find($idFormat);

        $inscription = new Inscription($item, $this->getUser(), $format);
        $form = $this->get('form.factory')->create(InscriptionType::class, $inscription);
        $form->handleRequest($request);
        $inscription->updateStatut();

        $inscriptionService->setInscription($inscription);

        $result = $inscriptionService->controleDateInscription($item, $type);
        if ($result['statut'] == '-1')
            return new JsonResponse($result);

        $result = $inscriptionService->controleMontantItem($this->getUser());
        if ($result['statut'] == '-1')
            return new JsonResponse($result);

        $result = $inscriptionService->controleMontantAutorisations($this->getUser());
        if ($result['statut'] == '-1')
            return new JsonResponse($result);

        if ($inscription->getStatut() == 'initialise') {
            $result = $inscriptionService->getFormulaire($form);
            return new JsonResponse($result);
        } elseif (in_array($inscription->getStatut(), ['attentevalidationencadrant', 'attentevalidationgestionnaire'])) {
            $em->persist($inscription);
            $result = $inscriptionService->getMessagePreInscription();
            $em->flush();

            $inscriptionService->envoyerMailInscriptionNecessitantValidation();
            
            $response = new JsonResponse($result);
            $response->headers->set('Content-Type', 'text/plain');
            return $response;
        } elseif ($inscription->getStatut() == 'attentepaiement') {
            $em->persist($inscription);
            $articles = $inscriptionService->ajoutPanier();
            $result = $inscriptionService->getComfirmationPanier($articles);
            $em->flush();
            return new JsonResponse($result);
        } else {
            dump($inscription);
            die;
        }
    }
}
