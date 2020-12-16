<?php

/*
 * Classe - InscriptionController
 *
 * Vérification des disponibilités
 * Vérification des droits des utilisateur
 * Initilise une commande
*/

namespace UcaBundle\Controller\UcaWeb;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use UcaBundle\Entity\Inscription;
use UcaBundle\Form\InscriptionType;

/** @Route("UcaWeb") */
class InscriptionController extends Controller
{
    /** @Route("/Inscription", name="UcaWeb_Inscription", options={"expose"=true}, methods={"POST"}) */
    public function inscriptionAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->container->get('uca.timeout')->nettoyageCommandeEtInscription();
        $idProfil = $this->getUser() ? $this->getUser()->getProfil()->getId() : false;
        if ($idProfil) {
            $maxCreneau = $idProfil ? $em->getRepository('UcaBundle:ProfilUtilisateur')->findMaxInscription($idProfil) : null;
        }
        $type = $request->get('type');
        $idFormat = $request->get('idFormat');
        $id = $request->get('id');
        $item = $em->getReference($type, $id);
        $format = null;
        if (!empty($idFormat)) {
            $format = $em->getReference('UcaBundle:FormatAvecReservation', $idFormat);
        }

        $inscriptionInformations = $item->getInscriptionInformations($this->getUser(), $format, $maxCreneau);

        if ('disponible' != $inscriptionInformations['statut']) {
            $result['itemId'] = $item->getId();
            $result['statut'] = '-1';
            $result['html'] = $this->get('twig')->render(
                '@Uca/UcaWeb/Inscription/Modal.Error.html.twig',
                ['title' => 'modal.error', 'message' => 'modal.error.'.$inscriptionInformations['statut']]
            );

            return new JsonResponse($result);
        }

        $inscription = new Inscription($item, $this->getUser(), ['format' => $format]);
        $form = $this->get('form.factory')->create(InscriptionType::class, $inscription);
        $form->handleRequest($request);
        $inscription->updateStatut();

        $inscriptionService = $this->container->get('uca.inscription');
        $inscriptionService->setInscription($inscription);

        if ('initialise' == $inscription->getStatut()) {
            if ('confirmation' == $request->get('statut')) {
                $twigConfig['articles'] = $inscriptionService->ajoutPanier(true);

                return new JsonResponse($this->get('twig')->render('@Uca/UcaWeb/Inscription/Modal.ConfirmationAjoutPanier.html.twig', $twigConfig));
            }
            $result = $inscriptionService->getFormulaire($form);

            return $this->convertResultToJsonTextResponse($form, $result);
        }
        if (in_array($inscription->getStatut(), ['attentevalidationencadrant', 'attentevalidationgestionnaire'])) {
            if ('confirmation' == $request->get('statut')) {
                $twigConfig['articles'] = $inscriptionService->ajoutPanier(true);

                return new JsonResponse($this->get('twig')->render('@Uca/UcaWeb/Inscription/Modal.ValidationArticle.html.twig', $twigConfig));
            }
            $em->persist($inscription);
            $result = $inscriptionService->getMessagePreInscription();
            $em->flush();
            $inscriptionService->envoyerMailInscriptionNecessitantValidation();

            return $this->convertResultToJsonTextResponse($form, $result);
        }
        if ('attentepaiement' == $inscription->getStatut()) {
            if ('confirmation' == $request->get('statut')) {
                $twigConfig['articles'] = $inscriptionService->ajoutPanier(true);

                // temps d'éxécution sans cache : 6.1 s
                // temps d'éxécution avec cache : 4.6 s
                // temps d'execution dans le done() du JQuery : 10,5 s
                // Erreur JSON : liée au parsing imposé par JQuery (le JSON.parse n'est plus utile en ES6)

                //dump(new JsonResponse($this->get('twig')->render('@Uca/UcaWeb/Inscription/Modal.ConfirmationAjoutPanier.html.twig', $twigConfig)));
                //die;

                return new JsonResponse($this->get('twig')->render('@Uca/UcaWeb/Inscription/Modal.ConfirmationAjoutPanier.html.twig', $twigConfig));
            }

            $em->persist($inscription);
            $articles = $inscriptionService->ajoutPanier();
            $result = $inscriptionService->getComfirmationPanier($articles);
            $em->flush();

            return $this->convertResultToJsonTextResponse($form, $result);
        }
    }

    private function convertResultToJsonTextResponse($form, $result)
    {
        $response = new JsonResponse($result);
        if ($form->isSubmitted()) {
            $response->headers->set('Content-Type', 'text/plain');
        }

        return $response;
    }
}
