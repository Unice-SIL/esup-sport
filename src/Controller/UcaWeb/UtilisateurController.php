<?php

/*
 * Classe - UtilisateurController
 *
 * Gestion des utilisateur côté web.
 * page mon profil, édition d'information, de modification de mot de passe
 * Consulter ses crédits
*/

namespace App\Controller\UcaWeb;

use App\Entity\Uca\Commande;
use App\Form\UtilisateurType;
use App\Entity\Uca\Utilisateur;
use App\Service\Common\MailService;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\StatutUtilisateurRepository;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\RouterInterface;

/**
 * @Route("UcaWeb")
 */
class UtilisateurController extends AbstractController
{
    /**
     * @Route("/DemandeInscription",name="UcaWeb_preInscription", methods={"GET","POST"})
     */
    public function preInscriptionAction(Request $request, MailService $mailer, EntityManagerInterface $em, StatutUtilisateurRepository $statuRepo, UtilisateurRepository $utilisateurRepository, UserPasswordHasherInterface $encoder, RouterInterface $router)
    {
        $usr = new Utilisateur();
        $form = $this->createForm(UtilisateurType::class, $usr, ['action_type' => 'preInscription']);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $request->isMethod('POST') && $form->isValid()) {
            $usr->setPassword($encoder->hashPassword($usr, $usr->getPlainPassword()));
            $usr->setStatut($statuRepo->find(2));
            $em->persist($usr);
            $em->flush();

            $mailer->sendMailWithTemplate(
                null,
                $usr->getEmail(),
                'PreInscriptionEmail',
                ['nom' => $usr->getNom(), 'prenom' => $usr->getPrenom()]
            );

            $listUser = $utilisateurRepository->findByRole('ROLE_GESTION_ACTIVITE_ECRITURE');
            foreach ($listUser as $user) {
                $setTo[] = new Address($user['email'], ucfirst($user['prenom']).' '.ucfirst($user['nom']));
            }

            $lienUtilisateur = $router->generate('UcaGest_UtilisateurVoir', ['id' => $usr->getId()]);

            $mailer->sendMailWithTemplate(
                null,
                $setTo,
                'DemandeValidationEmail',
                ['id' => $usr->getId(), 'nom' => $usr->getNom(), 'prenom' => $usr->getPrenom(), 'lienUtilisateur' => $request->getScheme().'://'.$request->getHttpHost().$lienUtilisateur]
            );

            return $this->redirectToRoute('UcaWeb_preInscription_confirmation');
        }
        $twigConfig['item'] = $usr;
        $twigConfig['form'] = $form->createView();

        return $this->render('UcaBundle/UcaWeb/Utilisateur/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/ConfirmationDemande",name="UcaWeb_preInscription_confirmation", methods={"GET","POST"})
     */
    public function preInscriptionActionConfirmation(Request $request)
    {
        return $this->render('UcaBundle/UcaWeb/Utilisateur/Confirmation.html.twig');
    }

    /**
     * @Route("/MonCompte",name="UcaWeb_MonCompte")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function voirAction(Request $request, EntityManagerInterface $em)
    {
        $item = $this->getUser();
        $isEncadrant = $item->hasRole('ROLE_ENCADRANT');

        $twigConfig['type'] = 'encadrant';
        if ($isEncadrant) {
            $twigConfig['role'] = 'encadrant';
        } else {
            $twigConfig['role'] = 'user';
        }

        $twigConfig['item'] = $item;
        $twigConfig['activiteSouscrite'] = $this->FormatParActivite($item, $em);

        return $this->render('UcaBundle/UcaWeb/Utilisateur/Voir.html.twig', $twigConfig);
    }

    /**
     * @Route("/MonCompte/Modifier",name="UcaWeb_MonCompte_Modifier", methods={"GET","POST"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function modifierAction(Request $request, EntityManagerInterface $em)
    {
        $item = $this->getUser();
        $form = $this->createForm(UtilisateurType::class, $item, ['action_type' => 'profil']);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($item);
            $em->flush();

            return $this->redirectToRoute('UcaWeb_MonCompte');
        }
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();

        return $this->render('UcaBundle/UcaWeb/Utilisateur/ModifierMonCompte.html.twig', $twigConfig);
    }

    public function FormatParActivite(Utilisateur $item, EntityManagerInterface $em)
    {
        $listeActivite = [];
        $qb = $em->createQuery('SELECT fa FROM App\Entity\Uca\FormatActivite fa JOIN fa.inscriptions i');
        $formatsActivites = $qb->getResult();
        foreach ($formatsActivites as $format) {
            $listeActivite[$format->getActivite()->getLibelle()][] = $format->getLibelle();
        }

        return $listeActivite;
    }

    /**
     * @Route("/Confirmation/Invalide/{token}", name="UtilisateurConfirmationInvalide")
     */
    public function confirmationExpireeAction(Request $request, string $token)
    {
        return $this->render('UcaBundle/UcaWeb/Utilisateur/ConfirmationInvalide.html.twig');
    }

    // public function commandeDetailParCommande(Utilisateur $item){
    //     $em = $this->getDoctrine()->getManager();
    //     $commandes = $em->getRepository(Commande::class)->findBy(['utilisateur' => $item->getId()]);
    //     foreach ($commandes as $commande){
    //         $commandeDetails = $commande->getCommandeDetail();
    //         foreach ($commandeDetails as $commandeDetail) {
    //             $listeCommandes[$commande->getId()][] = $commandeDetail;
    //         }
    //     }
    //     return $listeCommandes;
    // }
    // public function getDate(Utilisateur $item){

    // }
}
