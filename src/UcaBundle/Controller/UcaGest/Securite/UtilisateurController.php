<?php

namespace UcaBundle\Controller\UcaGest\Securite;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use UcaBundle\Entity\Utilisateur;
use UcaBundle\Entity\Groupe;
use UcaBundle\Form\UtilisateurType;
use UcaBundle\Datatables\UtilisateurDatatable;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;
use UcaBundle\Entity\StatutUtilisateur;

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
        $path = $this->get('kernel')->getProjectDir() . '/web/upload/public/documents/';
        if ($item->getDocument()) {
            $file = $item->getDocument();
            $response = new BinaryFileResponse($path . $file);
            $mimeTypeGuesser = new FileinfoMimeTypeGuesser();
            if ($mimeTypeGuesser->isSupported())  $response->headers->set('Content-Type', $mimeTypeGuesser->guess($path . $file));
            else $response->headers->set('Content-Type', 'text/plain');
            return $response;
        } else {
            $this->get('uca.flashbag')->addActionErrorFlashBag($item, 'document inexistant');
            return $this->redirectToRoute('UcaGest_UtilisateurVoir', ['id' => $item->getId()]);
        }
    }

    /**
     * @Route("/PreInscriptions/{id}/{action}", name="UcaGest_UtilisateurValiderPreInscription",  methods={"GET"})
     * @Isgranted("ROLE_GESTION_UTILISATEUR_ECRITURE")
     */
    public function validerPreInscriptionAction(Request $request, Utilisateur $usr, String $action)
    {
        $em = $this->getDoctrine()->getManager();
        $statutRepo = $em->getRepository('UcaBundle:StatutUtilisateur');
        if ($action == 'valider') {
            $usr->setEnabled(true);
            $usr->addRole("ROLE_USER");
            $usr->setStatut($statutRepo->find(1));
            $messageTitre = 'confirmation.inscription';
            $messageView = '@Uca/Email/PreInscription/ConfirmationEmail.html.twig';
            $usr->setConfirmationToken(rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '='));
            $this->get('uca.flashbag')->addActionFlashBag($usr, 'valider');
        } else if ($action == 'refuser') {
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
            ['user' => $usr]
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
        $twigConfig['item'] = $item;
        if ($item->getStatut() === $em->getRepository('UcaBundle:StatutUtilisateur')->find(2)) $twigConfig['validation'] = true;
        if ($item->getStatut() === $em->getRepository('UcaBundle:StatutUtilisateur')->find(3)) $twigConfig['refus'] = true;
        $twigConfig["encadrant"] = $item->getGroups()->contains($em->getReference(Groupe::class, 3));
        $usr = $this->container->get('security.token_storage')->getToken()->getUser();
        if (!$usr->hasRole('ROLE_GESTION_UTILISATEUR_ECRITURE')) {
            $twigConfig['noEditButton'] = true;
        }
        $twigConfig["encadrant"] = $item->getGroups()->contains($em->getReference(Groupe::class, 3));
        return $this->render('@Uca/UcaGest/Securite/Utilisateur/Voir.html.twig', $twigConfig);
    }

    /**
     * @Route("/scheduler/{id}", name="UcaGest_UtilisateurScheduler")
     * @Isgranted("ROLE_GESTION_SCHEDULER_LECTURE")
     */
    public function voirScheduler(Utilisateur $item)
    {
        $twigConfig['item'] = $item;
        $twigConfig['type'] = "encadrant";
        $twigConfig['role'] = "encadrant";
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
        $enCours = $statutRepo->find(2);
        $refuser = $statutRepo->find(3);
        $valider = $statutRepo->find(1);
        $bloquer = $statutRepo->find(4);
        $form = $this->get('form.factory')->create(UtilisateurType::class, $item, ['action_type' => 'modifier']);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $statut = $item->getStatut();
            if ($statut === $enCours Or $statut === $refuser or $statut === $bloquer) 
                $item->setEnabled(0);
            elseif($statut === $valider) 
                $item->setEnabled(1);
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
        $statutRepo =  $em->getRepository(StatutUtilisateur::class);
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
            return $this->redirectToRoute('UcaGest_UtilisateurVoir', array('id' => $item->getId()));
        }
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();
        return $this->render('@Uca/UcaGest/Securite/Utilisateur/FormulaireAjouterAutorisation.html.twig', $twigConfig);
    }

}
