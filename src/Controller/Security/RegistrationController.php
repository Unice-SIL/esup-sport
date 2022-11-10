<?php

namespace App\Controller\Security;

use App\Entity\Uca\Utilisateur;
use App\Service\Securite\RegistrationHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * class RegistrationController Controller qui s'occupe de l'inscription d'un utilisateur.
 */
class RegistrationController extends AbstractController
{
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
