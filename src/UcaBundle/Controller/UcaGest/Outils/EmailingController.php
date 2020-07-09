<?php

namespace UcaBundle\Controller\UcaGest\Outils;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Datatables\EmailingDatatable;
use UcaBundle\Entity;
use UcaBundle\Form\EmailingType;
use UcaBundle\Form\GestionInscriptionType;

/**
 * @Route("UcaGest/Emailing")
 * @Security("is_granted('ROLE_GESTION_EMAILING')")
 */
class EmailingController extends Controller
{
    /** @Route("/", name="UcaGest_Emailing", methods={"GET","POST"}) */
    public function emailingListeUtilisateurAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('uca.timeout')->nettoyageCommandeEtInscription();

        $formMail = $this->get('form.factory')->create(EmailingType::class, null);
        $twigConfig['formMail'] = $formMail->createView();

        $form = $this->get('form.factory')->create(
            GestionInscriptionType::class,
            [
                'typeActivite' => $em->getRepository(Entity\TypeActivite::class)->findAll(),
                'classeActivite' => $em->getRepository(Entity\ClasseActivite::class)->findAll(),
                'listeActivite' => $em->getRepository(Entity\Activite::class)->findAll(),
                'listeFormatActivite' => $em->getRepository(Entity\FormatActivite::class)->findAll(),
                'listeEtablissement' => $em->getRepository(Entity\Etablissement::class)->findAll(),
                'listeLieu' => $em->getRepository(Entity\Lieu::class)->findAll(),
                'data_class' => null,
                'em' => $em,
            ]
        );

        $twigConfig['form'] = $form->createView();

        $datatable = $this->get('sg_datatables.factory')->create(EmailingDatatable::class);
        $twigConfig['codeListe'] = 'Emailing';
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;
        if ($request->isXmlHttpRequest()) {
            $responseService = $this->get('sg_datatables.response');
            $responseService->setDatatable($datatable);
            $dtQueryBuilder = $responseService->getDatatableQueryBuilder();
            $qb = $dtQueryBuilder->getQb();
            $qb->andWhere('inscription.statut = :statut');
            $qb->setParameter('statut', 'valide');

            return $responseService->getResponse();
        }

        return $this->render('@Uca/UcaGest/Outils/Emailing/Voir.html.twig', $twigConfig);
    }

    /** @Route("/{nom}/{prenom}/{idTypeActivite}/{idClasseActivite}/{idActivite}/{idFormatActivite}/{idCreneau}/{idEncadrant}/{idEtablissement}/{idLieu}", name="UcaGest_EmailingListeEmails", methods={"GET"}) */
    public function emailingListeAdressesrAction(Request $request, $nom = null, $prenom = null, $idTypeActivite = null, $idClasseActivite = null, $idActivite = null, $idFormatActivite = null, $idCreneau = null, $idEncadrant = null, $idEtablissement = null, $idLieu = null)
    {
        $em = $this->getDoctrine()->getManager();

        if (false !== strpos($idCreneau, 'allCreneaux')) {
            $idCreneau = null;
        }

        if (null != $idEncadrant and '0' != $idEncadrant) {
            $encadrant = $em->getRepository(Entity\Utilisateur::class)->find($idEncadrant);
        } else {
            $encadrant = null;
        }
        if (null != $idEtablissement and '0' != $idEtablissement) {
            $etablissement = $em->getRepository(Entity\Etablissement::class)->find($idEtablissement);
        } else {
            $etablissement = null;
        }
        if (null != $idLieu and '0' != $idLieu) {
            $lieu = $em->getRepository(Entity\Lieu::class)->find($idLieu);
        } else {
            $lieu = null;
        }

        $inscriptions = ($em->getRepository(Entity\Inscription::class))->findInscriptionForDesincription($nom, $prenom, 'valide', $idTypeActivite, $idClasseActivite, $idActivite, $idFormatActivite, $idCreneau, $encadrant, $etablissement, $lieu);

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
    public function emailingEnvoyerAction(Request $request)
    {
        $mailer = $this->container->get('mailService');
        $formMail = $this->get('form.factory')->create(EmailingType::class, null);
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
                '@Uca/Email/Contact/ContactEmailing.html.twig',
                ['message' => $message]
            );

            return new JsonResponse([
                'success' => true,
            ]);
        }

        return new JsonResponse([
            'success' => false,
            'form' => $this->renderView('@Uca/UcaGest/Outils/Emailing/Modal.mail.form.html.twig', [
                'formMail' => $formMail->createView(),
            ]),
        ]);
    }
}
