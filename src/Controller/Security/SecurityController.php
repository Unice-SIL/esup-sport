<?php

namespace App\Controller\Security;

use App\Entity\Uca\Utilisateur;
use App\Form\Security\ChangementMotDePasseType;
use App\Form\Security\IdentifiantType;
use App\Service\Securite\RegistrationHandler;
use App\Service\Securite\UtilisateurResetPassword;
use Doctrine\DBAL\DBALException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class SecurityController. Gestionnaire de connexion.
 */
class SecurityController extends AbstractController
{
    public const INSTRUCTION_RESET_PASSWORD = 'instruction-reset-password';
    public const RESET_PASSWORD = 'reset-password';

    /**
     * @Route("/UcaGest/Connexion", name="security_login")
     */
    public function login(AuthenticationUtils $authenticationUtils, Request $request, UtilisateurResetPassword $resetPassword, FlashBagInterface $flashBag): Response
    {
        // Variable qui contient le message d'erreur de réinitialisation
        $errorMessage = null;

        //si l'utilisateur est deja connecté, on le redirige vers la page d'accueil
        if ($this->getUser()) {
            //exemple pour ajouter un message flash
            return $this->redirectToRoute('homepage');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        // Réinitialisation du mot de passe en cours
        if (self::RESET_PASSWORD === $this->getInstance($request)) {
            // Mot de passe valide
            $isValidResetPassword = $resetPassword->isValidResetPassword($request->query->get('username'), $request->query->get('key'));

            // Lien d'email invalide, pas d'autre erreur, on send un message flash
            if (false === $isValidResetPassword) {
                $error = true;
                $errorMessage = 'app.reset_password.message.expired_email';
            }
        }

        return $this->render('UserBundle/Security/login.html.twig', [
            'is_valid_browser' => false === (bool) preg_match('/Trident\/\d{1,2}.\d{1,2}; rv:([0-9]*)/', $_SERVER['HTTP_USER_AGENT']) && false === (bool) preg_match('/Edge/i', $_SERVER['HTTP_USER_AGENT']),
            'last_username' => $lastUsername,
            'instance' => $this->getInstance($request),
            'RESET_PASSWORD' => self::RESET_PASSWORD,
            'INSTRUCTION_RESET_PASSWORD' => self::INSTRUCTION_RESET_PASSWORD,
            'error' => $error,
            'errorMessage' => $errorMessage,
        ]);
    }

    /**
     * @Route("/instruction_reset_password", name="instruction_reset_password", methods={"POST"}, options={"expose"=true})
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function instructionResetPassword(Request $request, UtilisateurResetPassword $resetPassword): JsonResponse
    {
        return new JsonResponse($resetPassword->sendInstructionResetPassword($request->request->get('username')));
    }

    /**
     * @Route("/reset_password", name="reset_password", methods={"POST"}, options={"expose"=true})
     *
     * @throws DBALException
     */
    public function passwordReset(Request $request, UtilisateurResetPassword $resetPassword): JsonResponse
    {
        return new JsonResponse($resetPassword->resetPassword($request->request->get('username'), $request->request->get('password')));
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout(): void
    {
        $this->addFlash('success', 'app.logout.disconected');
    }

    /**
     * Fonction qui définit l'instance de l'action login.
     */
    public function getInstance(Request $request): string
    {
        $instance = self::INSTRUCTION_RESET_PASSWORD;
        if (!empty($request->query->get('username')) && !empty($request->query->get('key'))) {
            $instance = self::RESET_PASSWORD;
        }

        return $instance;
    }

    /**
     * @Route("/ChangementMotDePasse/{id}/{token}", name="security_change_password")
     */
    public function changePassword(Request $request, Utilisateur $utilisateur, string $token = null, UtilisateurResetPassword $utilisateurResetPassword)
    {
        if (null === $token || (null !== $token && $token === $utilisateur->getConfirmationToken())) {
            $form = $this->createForm(ChangementMotDePasseType::class, $utilisateur);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                return $this->redirectToRoute($utilisateurResetPassword->changePassword($utilisateur));
            }

            return $this->render('UserBundle/Security/change_password.html.twig', ['form' => $form->createView(), 'token' => $token]);
        }

        return $this->redirectToRoute('UcaWeb_Accueil');
    }

    /**
     * @Route("/MotDePassePerdu", name="security_password_forgotten")
     */
    public function passwordForgotten(Request $request, UtilisateurResetPassword $resetPassword)
    {
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
        if (null === $this->getUser() && $user->getConfirmationToken() === $token) {
            $registrationHandler->confirmAccount($request, $user);

            return $this->redirectToRoute('UcaWeb_CGV');
        }
        $this->addFlash('danger', 'erreur.generale.message');

        return $this->redirectToRoute('security_login');
    }
}
