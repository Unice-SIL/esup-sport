<?php

/*
 * Classe - UtilisateurCreditHistorique
 *
 * Gestion du CRUD pour les utilisateur
 * Valdider un utilisateur et consulter les justificatifs
*/

namespace App\Controller\UcaGest\Securite;

use App\Form\UtilisateurType;
use App\Entity\Uca\Utilisateur;
use App\Service\Common\FlashBag;
use App\Entity\Uca\CommandeDetail;
use App\Service\Common\MailService;
use App\Entity\Uca\StatutUtilisateur;
use App\Datatables\UtilisateurDatatable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Uca\UtilisateurCreditHistorique;
use App\Repository\StatutUtilisateurRepository;
use App\Service\Securite\RegistrationHandler;
use Symfony\Component\Routing\Annotation\Route;
use Sg\DatatablesBundle\Datatable\DatatableFactory;
use Sg\DatatablesBundle\Response\DatatableResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Vich\UploaderBundle\Handler\UploadHandler;

/**
 * @route("UcaGest/Utilisateur")
 * @Security("is_granted('ROLE_ADMIN')")
 */
class UtilisateurController extends AbstractController
{
    /**
     * @Route("/", name="UcaGest_UtilisateurLister")
     * @Isgranted("ROLE_GESTION_UTILISATEUR_LECTURE")
     */
    public function listerAction(Request $request, DatatableFactory $datatableFactory, DatatableResponse $datatableResponse)
    {
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $datatableFactory->create(UtilisateurDatatable::class);
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;
        if ($isAjax) {
            $responseService = $datatableResponse;
            $responseService->setDatatable($datatable);
            $dtQueryBuilder = $responseService->getDatatableQueryBuilder();
            $qb = $dtQueryBuilder->getQb();

            return $responseService->getResponse();
        }
        // Bouton Ajouter
        $usr = $this->getUser();
        if (!$usr->hasRole('ROLE_GESTION_UTILISATEUR_ECRITURE')) {
            $twigConfig['noAddButton'] = true;
        }
        $twigConfig['codeListe'] = 'Utilisateur';

        return $this->render('UcaBundle/UcaGest/Securite/Utilisateur/Lister.html.twig', $twigConfig);
    }

    /**
     * @Route("/Enregistrement", name="UcaGest_UtilisateurEnregistrement")
     *
     * @param Request $request
     * @return void
     */
    public function creationUtilisateur(Request $request, RegistrationHandler $registrationHandler) {
        $utilisateur = (new Utilisateur())->setPlainPassword(Utilisateur::getRandomPassword());
        $form = $this->createForm(UtilisateurType::class, $utilisateur, ['action_type' => 'enregistrement']);
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $registrationHandler->createUser($utilisateur);

            return $this->redirectToRoute('UcaGest_UtilisateurLister');
        }

        return $this->render('UserBundle/Registration/register.html.twig', ['form' => $form->createView(), 'item' => $utilisateur]);
    }

    /**
     * @Route("/PreInscriptions/telechargerJustificatif/{id}", name="UcaGest_UtilsateurPreInscriptionTelechargerJustificatif")
     * @Isgranted("ROLE_GESTION_UTILISATEUR_ECRITURE")
     */
    public function telechargerPreInscriptionJustificatifAction(Request $request, Utilisateur $item, FlashBag $flashBag, KernelInterface $kernel)
    {
        $path = $kernel->getProjectDir().'/public/upload/public/documents/';
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
        $flashBag->addActionErrorFlashBag($item, 'document inexistant');

        return $this->redirectToRoute('UcaGest_UtilisateurVoir', ['id' => $item->getId()]);
    }

    /**
     * @Route("/PreInscriptions/{id}/{action}", name="UcaGest_UtilisateurValiderPreInscription",  methods={"GET"})
     * @Isgranted("ROLE_GESTION_UTILISATEUR_ECRITURE")
     */
    public function validerPreInscriptionAction(Request $request, Utilisateur $usr, string $action, FlashBag $flashBag, MailService $mailer, EntityManagerInterface $em, StatutUtilisateurRepository $statutRepo, UploadHandler $uploadHandler, TranslatorInterface $translator)
    {
        if ('valider' == $action) {
            $usr->addRole('ROLE_USER');
            $usr->setStatut($statutRepo->find(1));
            $messageTitre = $translator->trans('confirmation.inscription');
            $messageView = 'UcaBundle/Email/PreInscription/ConfirmationEmail.html.twig';
            $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
            $url = $this->generateUrl('registration_validate_acount', ['id' => $usr->getId(), 'token' => $token], true);
            $usr->setConfirmationToken($token);
            $flashBag->addActionFlashBag($usr, 'valider');
        } elseif ('refuser' == $action) {
            $usr->setStatut($statutRepo->find(3));
            $messageTitre = $translator->trans('refus.inscription');
            $messageView = 'UcaBundle/Email/PreInscription/RefusEmail.html.twig';
            $flashBag->addActionFlashBag($usr, 'refuser');
        }
        $em->persist($usr);

        $mailer->sendMailWithTemplate(
            $messageTitre,
            $usr->getEmail(),
            $messageView,
            ['user' => $usr, 'confirmationUrl' => $url]
        );
        $uploadHandler->remove($usr, 'documentFile');
        $em->flush();

        return $this->redirectToRoute('UcaGest_UtilisateurLister');
    }

    /**
     * @Route("/{id}", name="UcaGest_UtilisateurVoir",  methods={"GET","HEAD"})
     * @Isgranted("ROLE_GESTION_UTILISATEUR_LECTURE")
     */
    public function voirAction(Request $request, Utilisateur $item, EntityManagerInterface $em, StatutUtilisateurRepository $statutRepo)
    {
        $usr = $this->getUser();

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

        return $this->render('UcaBundle/UcaGest/Securite/Utilisateur/Voir.html.twig', $twigConfig);
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

        return $this->render('UcaBundle/UcaGest/Securite/Utilisateur/Scheduler.html.twig', $twigConfig);
    }

    /**
     * @Route("/Modifier/{id}", name="UcaGest_UtilisateurModifier", methods={"GET","POST"})
     * @Isgranted("ROLE_GESTION_UTILISATEUR_ECRITURE")
     */
    public function modifierAction(Request $request, Utilisateur $item, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $statutRepo = $em->getRepository(StatutUtilisateur::class);
        $form = $this->createForm(UtilisateurType::class, $item, ['action_type' => 'modifier']);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $oldAutorisations = json_decode($this->get('session')->get('oldItem'));
            if ($statutRepo->find(1) === $item->getStatut()) {
                $item->setEnabled(1);
            } else {
                $item->setEnabled(0);
            }

            //si ajout d'une carte, on va mettre à jour la date de fin de validité de la commande la plus récente pour que l'utilisateur puisse s'inscrire
            foreach ($item->getAutorisations() as $autorisation) {
                if (!in_array($autorisation->getId(), $oldAutorisations) && 4 == $autorisation->getComportement()->getId()) {
                    $commandeDetail = $em->getRepository(CommandeDetail::class)->findCommandeDetailWithAutorisationByUser($item->getId(), $autorisation->getId());
                    if ($commandeDetail) {
                        $commandeDetail[0]->setDateCarteFinValidite(null);
                    }
                }
            }
            $em->persist($item);
            $em->flush();
            $flashBag->addActionFlashBag($item, 'Modifier');

            return $this->redirectToRoute('UcaGest_UtilisateurLister');
        }

        //On récupère les id des autorisations actuelles de l'utilisateur pour pouvoir voir si on lui en a ajouté pour les gérer
        $oldAutorisations = [];
        foreach ($item->getAutorisations() as $autorisation) {
            $oldAutorisations[] = $autorisation->getId();
        }
        $this->get('session')->set('oldItem', json_encode($oldAutorisations));

        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();

        return $this->render('UcaBundle/UcaGest/Securite/Utilisateur/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Supprimer/{id}", name="UcaGest_UtilisateurSupprimer")
     * @Isgranted("ROLE_GESTION_UTILISATEUR_ECRITURE")
     */
    public function supprimerAction(Request $request, Utilisateur $item, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $em->remove($item);
        $em->flush();
        $flashBag->addActionFlashBag($item, 'Supprimer');

        return $this->redirectToRoute('UcaGest_UtilisateurLister');
    }

    /**
     * @Route("/Bloquer/{id}", name="UcaGest_UtilisateurBloquer")
     * @Isgranted("ROLE_GESTION_UTILISATEUR_ECRITURE")
     */
    public function bloquerAction(Request $request, Utilisateur $item, FlashBag $flashBag, MailService $mailer, EntityManagerInterface $em)
    {
        $statutRepo = $em->getRepository(StatutUtilisateur::class);
        $bloquer = $statutRepo->find(4);
        $valide = $statutRepo->find(1);
        if ($item->isEnabled()) {
            $item->setEnabled(false);
            $item->setStatut($bloquer);
            $flashBag->addActionFlashBag($item, 'bloquer');
            $messageTitre = 'Votre compte est bloqué';
            $messageView = 'UcaBundle/Email/CompteUtilisateur/UtilisateurBloquerEmail.html.twig';
        } else {
            $item->setEnabled(true);
            $item->setStatut($valide);
            $flashBag->addActionFlashBag($item, 'debloquer');
            $messageTitre = 'Votre compte est activé';
            $messageView = 'UcaBundle/Email/CompteUtilisateur/UtilisateurDebloquerEmail.html.twig';
        }
        $em->persist($item);

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
    public function ajouterAutorisationAction(Request $request, Utilisateur $item, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $form = $this->createForm(UtilisateurType::class, $item);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($item);
            $em->flush();
            $flashBag->addMessageFlashBag('utilisateur.autorisation.ajouter.success', 'success');

            return $this->redirectToRoute('UcaGest_UtilisateurVoir', ['id' => $item->getId()]);
        }
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();

        return $this->render('UcaBundle/UcaGest/Securite/Utilisateur/FormulaireAjouterAutorisation.html.twig', $twigConfig);
    }

    /**
     * @Route("/{id}/RenvoyerEmailConfirmation", name="UcaGest_UtilisateurRenvoyerEmailConfirmation")
     * @Isgranted("ROLE_GESTION_UTILISATEUR_LECTURE")
     */
    public function revoyerEmailConfirmationAction(Request $request, Utilisateur $item, FlashBag $flashBag, MailService $mailer, EntityManagerInterface $em)
    {
        $statutRepo = $em->getRepository(StatutUtilisateur::class);
        $ccUser = $this->getUser();
        if ((!($item->isEnabled()) or (null == $item->getLastLogin())) && $item->getStatut() != $statutRepo->find(4)) {
            $this->envoyerEmailConfirmation($item, $ccUser->getEmail(), $mailer, $em);
            $flashBag->addMessageFlashBag('utilisateur.envoyer.mailconfirmation.success', 'success');
        } else {
            $flashBag->addMessageFlashBag('utilisateur.envoyer.mailconfirmation.failure', 'danger');
        }

        return $this->redirectToRoute('UcaGest_UtilisateurLister');
    }

    /**
     * @Route("/Credit/{id}/Ajouter", name="UcaGest_UtilisateurCreditAjouter", methods={"GET","POST"})
     * @Route("/Credit/{id}/{refCommande}/{refAvoir}/Reporter/{montant}", name="UcaGest_UtilisateurCreditReporter", methods={"GET","POST"})
     * @Isgranted("ROLE_GESTION_CREDIT_UTILISATEUR_ECRITURE")
     *
     * @param null|mixed $montant
     * @param null|mixed $refAvoir
     * @param null|mixed $refCommande
     */
    public function ajouterCreditAction(Request $request, Utilisateur $item, $refAvoir = null, $refCommande = null, $montant = 0, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $operation = ('UcaGest_UtilisateurCreditReporter' == $request->get('_route')) ? "Report d'avoir" : 'Ajout manuel de crédit';
        $titreForm = ('UcaGest_UtilisateurCreditReporter' == $request->get('_route')) ? 'utilisateur.credit.reporter.title' : 'utilisateur.credit.ajouter.title';
        $credit = new UtilisateurCreditHistorique($item, $request->get('montant'), $refAvoir, 'credit', $operation);
        $form = $this->createForm('App\Form\UtilisateurCreditHistoriqueType', $credit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $request->isMethod('POST')) {
            $em->persist($credit);
            $em->flush();
            $flashBag->addActionFlashBag($credit, 'Ajouter');

            return $this->redirectToRoute('UcaGest_ReportingCredit');
        }

        $twigConfig['credit'] = $credit;
        $twigConfig['form'] = $form->createView();
        $twigConfig['titreForm'] = $titreForm;

        return $this->render('UcaBundle/UcaGest/Securite/Utilisateur/FormulaireAjouterCredit.html.twig', $twigConfig);
    }

    public function envoyerEmailConfirmation(Utilisateur $user, $cc = null, MailService $mailer, EntityManagerInterface $em)
    {
        $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $user->setConfirmationToken($token);
        $em->persist($user);

        $url = $this->generateUrl('registration_validate_acount', ['id' => $user->getId(), 'token' => $token], true);
        $mailer->sendMailWithTemplate(
            'Activation de compte',
            $user->getEmail(),
            'UserBundle/Registration/email.txt.twig',
            ['user' => $user, 'confirmationUrl' => $url, 'resending' => true],
            $cc
        );
        $em->flush();
    }
}
