<?php

namespace App\Controller\Security;

use App\Entity\Uca\Utilisateur;
use App\Form\Security\ChangementMotDePasseType;
use App\Form\Security\IdentifiantType;
use App\Service\Securite\RegistrationHandler;
use App\Service\Securite\UtilisateurResetPassword;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class SecurityController. Gestionnaire de connexion.
 */
class SecurityController extends AbstractController
{
    /**
     * @Route("/UcaGest/Connexion", name="security_login")
     */
    public function login(AuthenticationUtils $authenticationUtils, Request $request, UtilisateurResetPassword $resetPassword, FlashBagInterface $flashBag): Response
    {
        // si l'utilisateur est deja connecté, on le redirige vers la page d'accueil
        if ($this->getUser()) {
            return $this->redirectToRoute('UcaWeb_Accueil');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('UserBundle/Security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * @Route("/ChangementMotDePasse/{id}/{token}", name="security_change_password")
     */
    public function changePassword(Request $request, Utilisateur $utilisateur, string $token = null, UtilisateurResetPassword $utilisateurResetPassword)
    {
        if ($this->getUser() && $utilisateur->getId() != $this->getUser()->getId()) {
            return $this->redirectToRoute('UcaWeb_Accueil');
        }
        if ($this->getUser() && null != $token) {
            return $this->redirectToRoute('UcaWeb_Accueil');
        }
        if (!$this->getUser() && null === $token) {
            return $this->redirectToRoute('UcaWeb_Accueil');
        }
        if (!$this->getUser() && $token !== $utilisateur->getConfirmationToken()) {
            return $this->redirectToRoute('UcaWeb_Accueil');
        }

        $form = $this->createForm(ChangementMotDePasseType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute($utilisateurResetPassword->changePassword($utilisateur));
        }

        return $this->render('UserBundle/Security/change_password.html.twig', ['form' => $form->createView(), 'token' => $token]);
    }

    /**
     * @Route("/MotDePassePerdu", name="security_password_forgotten")
     */
    public function passwordForgotten(Request $request, UtilisateurResetPassword $resetPassword)
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('UcaWeb_Accueil');
        }

        $form = $this->createForm(IdentifiantType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $resetPassword->passwordForgotten($form->get('identifiant')->getData());

            return $this->redirectToRoute('security_login');
        }

        return $this->render('UserBundle/Security/password_forgotten.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/ConfirmationCompte/{id}/{token}", name="security_confirm_account")
     */
    public function confirmAccount(Request $request, Utilisateur $user, string $token, RegistrationHandler $registrationHandler)
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('UcaWeb_Accueil');
        }

        if (null === $this->getUser() && $user->getConfirmationToken() === $token) {
            $registrationHandler->confirmAccount($request, $user);

            return $this->redirectToRoute('UcaWeb_CGV');
        }
        $this->addFlash('danger', 'erreur.generale.message');

        return $this->redirectToRoute('security_login');
    }
}
