<?php

namespace UcaBundle\Controller\UcaWeb;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use UcaBundle\Entity\Utilisateur;
use UcaBundle\Form\UtilisateurType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;


/**
 * @Route("UcaWeb")
 */
class UtilisateurController extends Controller
{
    /**
     * @Route("/DemandeInscription",name="UcaWeb_preInscription")
     * @Method({"GET", "POST"})
     */
    public function preInscriptionAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $statuRepo = $em->getRepository('UcaBundle:StatutUtilisateur');
        $um = $this->container->get('fos_user.user_manager');
        $usr = $um->createUser();
        $form = $this->get('form.factory')->create(UtilisateurType::class, $usr, ['action_type' => 'preInscription']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $request->isMethod('POST') && $form->isValid()) {
            $usr->setStatut($statuRepo->find(2));
            $em->persist($usr);
            $em->flush();

            $mailer = $this->container->get('mailService');
            $mailer->sendMailWithTemplate(
                'Confirmation.demande.inscription',
                $usr->getEmail(),
                '@Uca/Email/PreInscription/PreInscriptionEmail.html.twig',
                ['nom' => $usr->getNom(), 'prenom' => $usr->getPrenom()]
            );

            $listUser = ($em->getRepository('UcaBundle:Utilisateur'))->findByRole('ROLE_GESTION_ACTIVITE_ECRITURE');
            foreach ($listUser as $user) {
                $setTo[$user['email']] = ucfirst($user['prenom']) . ' ' . ucfirst($user['nom']);
            }
         
            $mailer->sendMailWithTemplate(
                'demande.validation',
                $setTo,
                '@Uca/Email/PreInscription/DemandeValidationEmail.html.twig',
                ['id' => $usr->getId(), 'nom' => $usr->getNom(), 'prenom' => $usr->getPrenom()]
            );
            
            return $this->redirectToRoute('UcaWeb_preInscription_confirmation');
        }
        $twigConfig['item'] = $usr;
        $twigConfig['form'] = $form->createView();
        return $this->render('@Uca/UcaWeb/Utilisateur/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/ConfirmationDemande",name="UcaWeb_preInscription_confirmation")
     * @Method({"GET", "POST"})
     */
    public function preInscriptionActionConfirmation(Request $request)
    {
        return $this->render('@Uca/UcaWeb/Utilisateur/Confirmation.html.twig');
    }

    /**
     * @Route("/MonCompte",name="UcaWeb_MonCompte")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function voirAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $item = $this->getUser();
        $isEncadrant = $item->hasGroup($em->getRepository("UcaBundle:Groupe")->findOneByName("Encadrant"));

        $twigConfig['type'] = "encadrant";
        if ($isEncadrant) {
            $twigConfig['role'] = "encadrant";
        } else {
            $twigConfig['role'] = "user";
        }

        $twigConfig['item'] = $item;
        $twigConfig['activiteSouscrite'] = $this->FormatParActivite($item);
        return $this->render('@Uca/UcaWeb/Utilisateur/Voir.html.twig', $twigConfig);
    }

    /**
     * @Route("/MonCompte/Modifier",name="UcaWeb_MonCompte_Modifier")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function modifierAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $item = $this->getUser();
        $form = $this->get('form.factory')->create(UtilisateurType::class, $item, ['action_type' => 'profil']);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($item);
            $em->flush();
            return $this->redirectToRoute('UcaWeb_MonCompte');
        }
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();
        return $this->render('@Uca/UcaWeb/Utilisateur/ModifierMonCompte.html.twig', $twigConfig);
    }

    public function FormatParActivite(Utilisateur $item)
    {
        $listeActivite = [];
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQuery('SELECT fa FROM UcaBundle\Entity\FormatActivite fa JOIN fa.inscriptions i');
        $formatsActivites = $qb->getResult();
        foreach ($formatsActivites as $format) {
            $listeActivite[$format->getActivite()->getLibelle()][] = $format->getLibelle();
        }
        return $listeActivite;
    }
    
    /**
     * @Route("/Confirmation/Invalide/{token}", name="UtilisateurConfirmationInvalide")
     */
    public function confirmationExpireeAction(Request $request, String $token)
    {
        return $this->render('@Uca/UcaWeb/Utilisateur/ConfirmationInvalide.html.twig');
    }
    
    // public function commandeDetailParCommande(Utilisateur $item){
    //     $em = $this->getDoctrine()->getManager();
    //     $commandes = $em->getRepository('UcaBundle:Commande')->findBy(['utilisateur' => $item->getId()]);
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
