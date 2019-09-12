<?php

namespace UserBundle\Controller\Utilisateur;

use Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\Controller\ChangePasswordController as FoSController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use FOS\UserBundle\FOSUserEvents;

class ChangePasswordController extends FoSController
{
    /**
     * @param Request $request
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
