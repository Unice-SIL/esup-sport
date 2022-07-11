<?php

namespace App\Controller\Security;

use App\Form\RegistrationFormType;
use App\Entity\Uca\Utilisateur;
use App\Service\Securite\RegistrationHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * class RegistrationController Controller qui s'occupe de l'inscription d'un utilisateur.
 */
class RegistrationController extends AbstractController
{
    /**
     * method qui inscrit l'utilisateur.
     *
     * @param RegistrationHandler $registrationHandler gestionnaire d'inscription
     * @Route("/UcaGest/Inscription", name="register")
     */
    public function register(Request $request, RegistrationHandler $registrationHandler): Response
    {
        //si l'utilisateur est deja connecté, on le redirige vers la page d'accueil
        if ($this->getUser()) {
            //exemple pour ajouter un message flash
            return $this->redirectToRoute('UcaWeb_Accueil');
        }

        $user = new Utilisateur();
        $user->addRole('ROLE_USER');
        $form = $this->createForm(RegistrationFormType::class, $user, ['validation_groups' => ['Default', 'registration']]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $registrationHandler->handleRegistration($request, $user);
        }
        if (count($form->getErrors('recaptcha')) > 0) {
            foreach ($form->getErrors('recaptcha') as $errors) {
                $this->addFlash('error', $errors->getMessage());
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * method chargée de l'activation d'un compte user
     *     cette méthode est appelée lors du click sur le lien d'activation envoyé par mail de.
     *
     * @param Utilisateur         $utilisateur         utilisateur concerné par cette activation
     * @param string              $tokenValidation     token correspondant au token généré pour la demande d'activation du compte utilisateur
     * @param RegistrationHandler $registrationHandler gestionnaire d'inscription
     *
     * @Route("/acount_validation/{id}-{token}", name="registration_validate_acount",requirements={"id"="\d+"})
     */
    public function acountValidation(Utilisateur $utilisateur, string $token, RegistrationHandler $registrationHandler): Response
    {
        return $registrationHandler->handleValidationAcount($utilisateur, $token);
    }
}