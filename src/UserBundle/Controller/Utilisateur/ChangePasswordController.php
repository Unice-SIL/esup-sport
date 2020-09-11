<?php

/*
 * Classe - ChangePasswordController:
 *
 * Surchage de la classe de FOS (permet une redirection)
*/

namespace UserBundle\Controller\Utilisateur;

use FOS\UserBundle\Controller\ChangePasswordController as FoSController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class ChangePasswordController extends FoSController
{
    /**
     * @return Response $response
     */
    public function changePasswordAction(Request $request)
    {
        $usr = $this->getUser();
        $response = parent::changePasswordAction($request);
        if (is_a($response, RedirectResponse::class)) {
            $response = $this->redirectToRoute('UcaWeb_MonCompte');
            // $this->get('uca.flashbag')->addActionFlashBag($usr, 'motdepassemodifie');
        }

        return $response;
    }
}
