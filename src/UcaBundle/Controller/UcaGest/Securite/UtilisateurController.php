<?php

namespace UcaBundle\Controller\UcaGest\Securite;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Datatables\UtilisateurDatatable;
use UcaBundle\Entity\StatutUtilisateur;
use UcaBundle\Entity\Utilisateur;
use UcaBundle\Entity\UtilisateurCreditHistorique;
use UcaBundle\Form\UtilisateurType;

/**
 * @route("UcaGest/Utilisateur")
 * @Security("has_role('ROLE_ADMIN')")
 */
class UtilisateurController extends Controller
{
    /**
     * @Route("/", name="UcaGest_UtilisateurLister")
     * @Isgranted("ROLE_GESTION_UTILISATEUR_LECTURE")
     */
    public function listerAction(Request $request)
    {
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $this->get('sg_datatables.factory')->create(UtilisateurDatatable::class);
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;
        if ($isAjax) {
            $responseService = $this->get('sg_datatables.response');
            $responseService->setDatatable($datatable);
            $dtQueryBuilder = $responseService->getDatatableQueryBuilder();
            $qb = $dtQueryBuilder->getQb();

            return $responseService->getResponse();
        }
        // Bouton Ajouter
        $usr = $this->container->get('security.token_storage')->getToken()->getUser();
        if (!$usr->hasRole('ROLE_GESTION_UTILISATEUR_ECRITURE')) {
            $twigConfig['noAddButton'] = true;
        }
        $twigConfig['codeListe'] = 'Utilisateur';

        return $this->render('@Uca/UcaGest/Securite/Utilisateur/Lister.html.twig', $twigConfig);
    }

    /**
     * @Route("/PreInscriptions/telechargerJustificatif/{id}", name="UcaGest_UtilsateurPreInscriptionTelechargerJustificatif")
     * @Isgranted("ROLE_GESTION_UTILISATEUR_ECRITURE")
     */
    public function telechargerPreInscriptionJustificatifAction(Request $request, Utilisateur $item)
    {
        $handler = $this->container->get('vich_uploader.upload_handler');
        $path = $this->get('kernel')->getProjectDir().'/web/upload/public/documents/';
        if ($item->getDocument()) {
            $file = $item->getDocument();
            $response = new BinaryFileResponse($path.$file);
            $mimeTypeGuesser = new FileinfoMimeTypeGuesser();
            if ($mimeTypeGuesser->isSupported()) {
                $response->headers->set('Content-Type', $mimeTypeGuesser->guess($path.$file));
            } else {
                $response->headers->set('Content-Type', 'text/plain');
            }

            return $response;
        }
        $this->get('uca.flashbag')->addActionErrorFlashBag($item, 'document inexistant');

        return $this->redirectToRoute('UcaGest_UtilisateurVoir', ['id' => $item->getId()]);
    }

    /**
     * @Route("/PreInscriptions/{id}/{action}", name="UcaGest_UtilisateurValiderPreInscription",  methods={"GET"})
     * @Isgranted("ROLE_GESTION_UTILISATEUR_ECRITURE")
     */
    public function validerPreInscriptionAction(Request $request, Utilisateur $usr, string $action)
    {
        $em = $this->getDoctrine()->getManager();
        $statutRepo = $em->getRepository('UcaBundle:StatutUtilisateur');
        if ('valider' == $action) {
            $usr->addRole('ROLE_USER');
            $usr->setStatut($statutRepo->find(1));
            $messageTitre = 'confirmation.inscription';
            $messageView = '@Uca/Email/PreInscription/ConfirmationEmail.html.twig';
            $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
            $url = $this->generateUrl('fos_user_registration_confirm', ['token' => $token], true);
            $usr->setConfirmationToken($token);
            $this->get('uca.flashbag')->addActionFlashBag($usr, 'valider');
        } elseif ('refuser' == $action) {
            $usr->setStatut($statutRepo->find(3));
            $messageTitre = 'refus.inscription';
            $messageView = '@Uca/Email/PreInscription/RefusEmail.html.twig';
            $this->get('uca.flashbag')->addActionFlashBag($usr, 'refuser');
        }
        $em->persist($usr);

        $mailer = $this->container->get('mailService');
        $mailer->sendMailWithTemplate(
            $messageTitre,
            $usr->getEmail(),
            $messageView,
            ['user' => $usr, 'confirmationUrl' => $url]
        );
        $this->container->get('vich_uploader.upload_handler')->remove($usr, 'documentFile');
        $em->flush();

        return $this->redirectToRoute('UcaGest_UtilisateurLister');
    }

    /**
     * @Route("/{id}", name="UcaGest_UtilisateurVoir",  methods={"GET","HEAD"})
     * @Isgranted("ROLE_GESTION_UTILISATEUR_LECTURE")
     */
    public function voirAction(Request $request, Utilisateur $item)
    {
        $em = $this->getDoctrine()->getManager();
        $statutRepo = $em->getRepository('UcaBundle:StatutUtilisateur');
        $usr = $this->container->get('security.token_storage')->getToken()->getUser();

        if ($item->getStatut() === $statutRepo->find(2)) {
            $twigConfig['statut'] = 'attente_validation';
        } elseif ($item->getStatut() === $statutRepo->find(3)) {
            $twigConfig['statut'] = 'refuser';
        } elseif ($item->getStatut() === $statutRepo->find(4)) {
            $twigConfig['statut'] = 'bloquer';
        } else {
            $twigConfig['statut'] = 'valider';
        }

        // $twigConfig['encadrant'] = $item->getGroups()->contains($em->getReference(Groupe::class, 3));

        if (!$usr->hasRole('ROLE_GESTION_UTILISATEUR_ECRITURE')) {
            $twigConfig['noEditButton'] = true;
        }
        $twigConfig['item'] = $item;
        $twigConfig['encadrant'] = ($item->hasRole('ROLE_ENCADRANT') && $usr->hasRole('ROLE_GESTION_SCHEDULER_LECTURE'));

        //$twigConfig['encadrant'] = $item->getGroups()->contains($em->getReference(Groupe::class, 3));

        return $this->render('@Uca/UcaGest/Securite/Utilisateur/Voir.html.twig', $twigConfig);
    }

    /**
     * @Route("/scheduler/{id}", name="UcaGest_UtilisateurScheduler")
     * @Isgranted("ROLE_GESTION_SCHEDULER_LECTURE")
     */
    public function voirScheduler(Utilisateur $item)
    {
        $twigConfig['item'] = $item;
        $twigConfig['type'] = 'encadrant';
        $twigConfig['role'] = 'encadrant';

        return $this->render('@Uca/UcaGest/Securite/Utilisateur/Scheduler.html.twig', $twigConfig);
    }

    /**
     * @Route("/Modifier/{id}", name="UcaGest_UtilisateurModifier", methods={"GET","POST"})
     * @Isgranted("ROLE_GESTION_UTILISATEUR_ECRITURE")
     */
    public function modifierAction(Request $request, Utilisateur $item)
    {
        $em = $this->getDoctrine()->getManager();
        $statutRepo = $em->getRepository(StatutUtilisateur::class);
        $form = $this->get('form.factory')->create(UtilisateurType::class, $item, ['action_type' => 'modifier']);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            if ($item->getStatut() === $statutRepo->find(1)) {
                $item->setEnabled(1);
            } else {
                $item->setEnabled(0);
            }
            $em->persist($item);
            $em->flush();
            $this->get('uca.flashbag')->addActionFlashBag($item, 'Modifier');

            return $this->redirectToRoute('UcaGest_UtilisateurLister');
        }
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();

        return $this->render('@Uca/UcaGest/Securite/Utilisateur/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Supprimer/{id}", name="UcaGest_UtilisateurSupprimer")
     * @Isgranted("ROLE_GESTION_UTILISATEUR_ECRITURE")
     */
    public function supprimerAction(Request $request, Utilisateur $item)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($item);
        $em->flush();
        $this->get('uca.flashbag')->addActionFlashBag($item, 'Supprimer');

        return $this->redirectToRoute('UcaGest_UtilisateurLister');
    }

    /**
     * @Route("/Bloquer/{id}", name="UcaGest_UtilisateurBloquer")
     * @Isgranted("ROLE_GESTION_UTILISATEUR_ECRITURE")
     */
    public function bloquerAction(Request $request, Utilisateur $item)
    {
        $em = $this->getDoctrine()->getManager();
        $statutRepo = $em->getRepository(StatutUtilisateur::class);
        $bloquer = $statutRepo->find(4);
        $valide = $statutRepo->find(1);
        if ($item->isEnabled()) {
            $item->setEnabled(false);
            $item->setStatut($bloquer);
            $this->get('uca.flashbag')->addActionFlashBag($item, 'bloquer');
            $messageTitre = 'Votre compte est bloqué';
            $messageView = '@Uca/Email/CompteUtilisateur/UtilisateurBloquerEmail.html.twig';
        } else {
            $item->setEnabled(true);
            $item->setStatut($valide);
            $this->get('uca.flashbag')->addActionFlashBag($item, 'debloquer');
            $messageTitre = 'Votre compte est activé';
            $messageView = '@Uca/Email/CompteUtilisateur/UtilisateurDebloquerEmail.html.twig';
        }
        $em->persist($item);

        $mailer = $this->container->get('mailService');
        $mailer->sendMailWithTemplate(
            $messageTitre,
            $item->getEmail(),
            $messageView,
            ['user' => $item]
        );
        $em->flush();

        return $this->redirectToRoute('UcaGest_UtilisateurLister');
    }

    /**
     * @Route("/AjouterAutorisation/{id}", name="UcaGest_UtilisateurAjouterAutorisation")
     * @Isgranted("ROLE_GESTION_UTILISATEUR_ECRITURE")
     */
    public function ajouterAutorisationAction(Request $request, Utilisateur $item)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->get('form.factory')->create(UtilisateurType::class, $item);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($item);
            $em->flush();
            $this->get('uca.flashbag')->addMessageFlashBag('utilisateur.autorisation.ajouter.success', 'success');

            return $this->redirectToRoute('UcaGest_UtilisateurVoir', ['id' => $item->getId()]);
        }
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();

        return $this->render('@Uca/UcaGest/Securite/Utilisateur/FormulaireAjouterAutorisation.html.twig', $twigConfig);
    }

    /**
     * @Route("/{id}/RenvoyerEmailConfirmation", name="UcaGest_UtilisateurRenvoyerEmailConfirmation")
     * @Isgranted("ROLE_GESTION_UTILISATEUR_LECTURE")
     */
    public function revoyerEmailConfirmationAction(Request $request, Utilisateur $item)
    {
        $em = $this->getDoctrine()->getManager();
        $statutRepo = $em->getRepository(StatutUtilisateur::class);
        $ccUser = $this->container->get('security.token_storage')->getToken()->getUser();
        if ((!($item->isEnabled()) or (null == $item->getLastLogin())) && $item->getStatut() != $statutRepo->find(4)) {
            $this->envoyerEmailConfirmation($item, $ccUser->getEmail());
            $this->get('uca.flashbag')->addMessageFlashBag('utilisateur.envoyer.mailconfirmation.success', 'success');
        } else {
            $this->get('uca.flashbag')->addMessageFlashBag('utilisateur.envoyer.mailconfirmation.failure', 'danger');
        }

        return $this->redirectToRoute('UcaGest_UtilisateurLister');
    }

    /**
     * @Route("/Credit/{id}/Ajouter/{montant]", name="UcaGest_UtilisateurCreditAjouter", methods={"GET","POST"})
     * @Isgranted("ROLE_GESTION_CREDIT_UTILISATEUR_ECRITURE")
     *
     * @param null|mixed $montant
     */
    public function ajouterCreditAction(Request $request, Utilisateur $item, $montant = 0)
    {
        $em = $this->getDoctrine()->getManager();
        $credit = new UtilisateurCreditHistorique($item, $request->get('montant'), null, 'credit', 'Ajout manuel de crédit');
        $form = $this->createForm('UcaBundle\Form\UtilisateurCreditHistoriqueType', $credit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $request->isMethod('POST')) {
            $em->persist($credit);
            $em->flush();
            $this->get('uca.flashbag')->addActionFlashBag($credit, 'Ajouter');

            return $this->redirectToRoute('UcaGest_ReportingCredit');
        }

        $twigConfig['credit'] = $credit;
        $twigConfig['form'] = $form->createView();

        return $this->render('@Uca/UcaGest/Securite/Utilisateur/FormulaireAjouterCredit.html.twig', $twigConfig);
    }

    public function envoyerEmailConfirmation(Utilisateur $user, $cc = null)
    {
        $em = $this->getDoctrine()->getManager();
        $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $user->setConfirmationToken($token);
        $em->persist($user);

        $url = $this->generateUrl('fos_user_registration_confirm', ['token' => $token], true);
        $mailer = $this->container->get('mailService');
        $mailer->sendMailWithTemplate(
            'Activation de compte',
            $user->getEmail(),
            '@User/Registration/email.txt.twig',
            ['user' => $user, 'confirmationUrl' => $url, 'resending' => true],
            $cc
        );
        $em->flush();
    }
}
