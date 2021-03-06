<?php

/*
 * Classe - ChangePasswordController:
 *
 * Surchage de la classe de FOS (permet de changer les redirections)
*/

namespace UserBundle\Controller\Utilisateur;

use FOS\UserBundle\Controller\RegistrationController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use UcaBundle\Entity\Utilisateur;

class RegistrationController extends BaseController
{
    /**
     * Tell the user to check their email provider.
     */
    public function checkEmailAction(Request $request)
    {
        $userManager = $this->container->get('fos_user.user_manager');
        $email = $request->getSession()->get('fos_user_send_confirmation_email/email');

        if (empty($email)) {
            return new RedirectResponse($this->generateUrl('UcaGest_UtilisateurEnregistrement'));
        }
        //$user = $this->getDoctrine()->getManager()->getRepository(Utilisateur::class)->findOneByEmail($email);
        $user = $userManager->findUserByEmail($email);

        if (null === $user) {
            return new RedirectResponse($this->container->get('router')->generate('fos_user_security_login'));
        }

        $response = parent::checkEmailAction($request);

        $this->get('uca.flashbag')->addActionFlashBag($user, 'Ajouter');

        return $this->redirectToRoute('UcaGest_UtilisateurLister');
    }

    /**
     * Receive the confirmation token from user email provider, login the user.
     *
     * @param string $token
     *
     * @return Response
     */
    public function confirmAction(Request $request, $token)
    {
        $userManager = $this->container->get('fos_user.user_manager');
        $user = $userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            return $this->redirectToRoute('UtilisateurConfirmationInvalide', ['token' => $token]);
        }

        return parent::confirmAction($request, $token);
    }
}
