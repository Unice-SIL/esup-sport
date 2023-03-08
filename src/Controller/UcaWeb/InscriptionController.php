<?php

/*
 * Classe - InscriptionController
 *
 * Vérification des disponibilités
 * Vérification des droits des utilisateur
 * Initilise une commande
*/

namespace App\Controller\UcaWeb;

use App\Entity\Uca\Creneau;
use App\Entity\Uca\FormatAchatCarte;
use App\Entity\Uca\FormatActivite;
use App\Entity\Uca\FormatAvecCreneau;
use App\Entity\Uca\FormatAvecReservation;
use App\Entity\Uca\FormatSimple;
use App\Entity\Uca\Inscription;
use App\Entity\Uca\Reservabilite;
use App\Form\InscriptionType;
use App\Repository\ProfilUtilisateurRepository;
use App\Service\Securite\TimeoutService;
use App\Service\Service\InscriptionService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/** @Route("UcaWeb") */
class InscriptionController extends AbstractController
{
    public const CLASS_NAMES = [
        'Creneau' => Creneau::class,
        'FormatAchatCarte' => FormatAchatCarte::class,
        'FormatActivite' => FormatActivite::class,
        'FormatAvecCreneau' => FormatAvecCreneau::class,
        'FormatAvecReservation' => FormatAvecReservation::class,
        'FormatSimple' => FormatSimple::class,
        'Reservabilite' => Reservabilite::class,
    ];

    /** @Route("/Inscription", name="UcaWeb_Inscription", options={"expose"=true}, methods={"POST"}) */
    public function inscriptionAction(Request $request, EntityManagerInterface $em, ProfilUtilisateurRepository $profilUtilisateurRepository, TimeoutService $timeoutService, InscriptionService $inscriptionService, TranslatorInterface $translator)
    {
        $timeoutService->nettoyageCommandeEtInscription();
        $idProfil = $this->getUser() ? $this->getUser()->getProfil()->getId() : false;
        if ($idProfil) {
            $maxCreneau = $idProfil ? $profilUtilisateurRepository->findMaxInscription($idProfil) : null;
        }
        $type = $request->get('type');
        $idFormat = $request->get('idFormat');
        $id = $request->get('id');
        $item = $em->getReference(self::CLASS_NAMES[$type], $id);
        $format = null;
        if (!empty($idFormat)) {
            $format = $em->getReference(FormatAvecReservation::class, $idFormat);
        }

        $inscriptionInformations = $item->getInscriptionInformations($this->getUser(), $format, $maxCreneau);

        if ('disponible' != $inscriptionInformations['statut']) {
            $result['itemId'] = $item->getId();
            $result['statut'] = '-1';
            $result['html'] = $this->renderView(
                'UcaBundle/UcaWeb/Inscription/Modal.Error.html.twig',
                ['title' => 'modal.error', 'message' => 'modal.error.'.$inscriptionInformations['statut']]
            );
            
            return new JsonResponse($result);
        }

        $inscription = new Inscription($item, $this->getUser(), ['format' => $format]);
        $form = $this->createForm(InscriptionType::class, $inscription);
        $form->handleRequest($request);
        $inscription->updateStatut();

        $inscriptionService->setInscription($inscription);

        if ('initialise' == $inscription->getStatut()) {
            if ('confirmation' == $request->get('statut')) {
                $twigConfig['articles'] = $inscriptionService->ajoutPanier(true);

                return new JsonResponse($this->renderView('UcaBundle/UcaWeb/Inscription/Modal.ConfirmationAjoutPanier.html.twig', $twigConfig));
            }
            $result = $inscriptionService->getFormulaire($form);

            return $this->convertResultToJsonTextResponse($form, $result);
        }
        if (in_array($inscription->getStatut(), ['attentevalidationencadrant', 'attentevalidationgestionnaire'])) {
            if ('confirmation' == $request->get('statut')) {
                $twigConfig['articles'] = $inscriptionService->ajoutPanier(true);

                return new JsonResponse($this->renderView('UcaBundle/UcaWeb/Inscription/Modal.ValidationArticle.html.twig', $twigConfig));
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
                $twigConfig['item'] = $item;
                $twigConfig['reservabilite'] = $item instanceof Reservabilite;
                $twigConfig['autorisation_case'] = $inscription->getAutorisationsByComportement(['case']);

                return new JsonResponse($this->renderView('UcaBundle/UcaWeb/Inscription/Modal.ConfirmationAjoutPanier.html.twig', $twigConfig));
            }

            $partenaires = $request->get('partenaires');
            if (null != $partenaires && sizeof($partenaires) > 0 && (sizeof($partenaires) !== sizeof(array_unique($partenaires)))) {
                return new JsonResponse(['error' => $translator->trans('ressource.partenaires.email.identique')]);
            }
            if (null != $partenaires && sizeof($partenaires) > 0 && in_array($this->getUser()->getEmail(), $partenaires)) {
                return new JsonResponse(['error' => $translator->trans('ressource.partenaires.email.identique_user')]);
            }

            $em->persist($inscription);
            $articles = $inscriptionService->ajoutPanier();
            $result = $inscriptionService->getConfirmationPanier($articles);
            $em->flush();
            if (null != $request->get('partenaires') && sizeof($request->get('partenaires')) > 0) {
                $inscriptionService->setPartenaires($request->get('partenaires'));
            }

            return $this->convertResultToJsonTextResponse($form, $result);
        }
    }

    /**
     * @Route("/Inscription/Partenaire/{id}", name="UcaWeb_InscriptionAvecPartenaire")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function inscriptionAvecPartenaire(Inscription $inscription, InscriptionService $inscriptionService, EntityManagerInterface $em)
    {
        if ($this->isGranted('inscriptionPartenaire', $inscription)) {
            $inscriptionService->cloneInscription($inscription, $this->getUser());
            $em->flush();
            $this->addFlash('success', 'ressource.partenaires.panier.success');

            return $this->redirectToRoute('UcaWeb_Panier');
        }

        return $this->redirectToRoute('UcaWeb_Accueil');
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
