<?php

/*
 * Classe - EmailingController
 *
 * Gestion des Emailing: selection des utilisateurs et envoi des mailsw
*/

namespace App\Controller\UcaGest\Outils;

use App\Entity\Uca\Lieu;
use App\Form\EmailingType;
use App\Entity\Uca\Inscription;
use App\Entity\Uca\Utilisateur;
use App\Entity\Uca\Etablissement;
use App\Service\Common\MailService;
use App\Form\GestionInscriptionType;
use App\Datatables\EmailingDatatable;
use App\Service\Securite\TimeoutService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sg\DatatablesBundle\Datatable\DatatableFactory;
use Sg\DatatablesBundle\Response\DatatableResponse;
use App\Service\Service\ExtractionInscriptionService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("UcaGest/Emailing")
 * @Security("is_granted('ROLE_GESTION_EMAILING')")
 */
class EmailingController extends AbstractController
{
    /** @Route("/", name="UcaGest_Emailing", methods={"GET","POST"}) */
    public function emailingListeUtilisateurAction(Request $request, TimeoutService $timeoutService, ExtractionInscriptionService $extraction, DatatableFactory $datatableFactory, DatatableResponse $datatableResponse)
    {
        $timeoutService->nettoyageCommandeEtInscription();

        $formMail = $this->createForm(EmailingType::class, null);
        $twigConfig['formMail'] = $formMail->createView();

        $form = $this->createForm(GestionInscriptionType::class, null, $extraction->getOptionsInscription());

        $twigConfig['form'] = $form->createView();

        $datatable = $datatableFactory->create(EmailingDatatable::class);
        $twigConfig['codeListe'] = 'Emailing';
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;
        if ($request->isXmlHttpRequest()) {
            $responseService = $datatableResponse;
            $responseService->setDatatable($datatable);
            $dtQueryBuilder = $responseService->getDatatableQueryBuilder();
            $qb = $dtQueryBuilder->getQb();
            $qb->andWhere('inscription.statut = :statut');
            $qb->setParameter('statut', 'valide');

            return $responseService->getResponse();
        }

        return $this->render('UcaBundle/UcaGest/Outils/Emailing/Voir.html.twig', $twigConfig);
    }

    /** @Route("/{nom}/{prenom}/{idTypeActivite}/{idClasseActivite}/{idActivite}/{idFormatActivite}/{idCreneau}/{idEncadrant}/{idEtablissement}/{idLieu}", name="UcaGest_EmailingListeEmails", methods={"GET"}) */
    public function emailingListeAdressesrAction(Request $request, $nom = null, $prenom = null, $idTypeActivite = null, $idClasseActivite = null, $idActivite = null, $idFormatActivite = null, $idCreneau = null, $idEncadrant = null, $idEtablissement = null, $idLieu = null, EntityManagerInterface $em)
    {
        if (false !== strpos($idCreneau, 'allCreneaux')) {
            $idCreneau = null;
        }

        if (null != $idEncadrant and '0' != $idEncadrant) {
            $encadrant = $em->getRepository(Utilisateur::class)->find($idEncadrant);
        } else {
            $encadrant = null;
        }
        if (null != $idEtablissement and '0' != $idEtablissement) {
            $etablissement = $em->getRepository(Etablissement::class)->find($idEtablissement);
        } else {
            $etablissement = null;
        }
        if (null != $idLieu and '0' != $idLieu) {
            $lieu = $em->getRepository(Lieu::class)->find($idLieu);
        } else {
            $lieu = null;
        }

        $inscriptions = $em->getRepository(Inscription::class)->findInscriptionForDesincription($nom, $prenom, 'valide', $idTypeActivite, $idClasseActivite, $idActivite, $idFormatActivite, $idCreneau, $encadrant, $etablissement, $lieu);

        $tabEmails = [];

        foreach ($inscriptions as $inscription) {
            $usr = $inscription->getUtilisateur();
            $tabEmails[$usr->getPrenom().' '.$usr->getNom()] = $usr->getEmail();
        }

        $response = [];
        $response['emails'] = $tabEmails;
        $response['nbDestinataires'] = count($tabEmails);

        return new JsonResponse($response);
    }

    /** @Route("/Envoyer", name="UcaGest_EmailingEnvoyer", methods={"POST"}, options={"expose"=true}) */
    public function emailingEnvoyerAction(Request $request, MailService $mailer)
    {
        $formMail = $this->createForm(EmailingType::class, null);
        $formMail->handleRequest($request);
        $validation = $formMail->isValid();
        $destinataires = json_decode($request->get('destinataires'), true);
        if ($request->isMethod('POST') && $validation) {
            $message = $formMail->getData()['mail'];
            $objet = $formMail->getData()['objet'];
            $setTo = [];

            foreach ($destinataires as $destinataire) {
                $setTo[] = $destinataire;
            }
            $mailer->sendMailWithTemplate(
                $objet,
                $setTo,
                'UcaBundle/Email/Contact/ContactEmailing.html.twig',
                ['message' => $message]
            );

            return new JsonResponse([
                'success' => true,
            ]);
        }

        return new JsonResponse([
            'success' => false,
            'form' => $this->renderView('UcaBundle/UcaGest/Outils/Emailing/Modal.mail.form.html.twig', [
                'formMail' => $formMail->createView(),
            ]),
        ]);
    }
}
