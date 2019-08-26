<?php

namespace UserBundle\Controller\Groupe;

use Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\Controller\GroupController as BaseController;
use UcaBundle\Form\GroupeType;
use Symfony\Component\HttpFoundation\RedirectResponse;

class GroupController extends BaseController
{
    /**
     * Edit one group, show the edit form.
     *
     * @param Request $request
     * @param string  $groupName
     *
     * @return Response
     */
    public function editAction(Request $request, $groupName)
    {
        $response = parent::editAction($request, $groupName);
        
        if (is_a($response, RedirectResponse::class)) {
            return $this->redirectToRoute('UcaGest_GroupeLister');
        } else{
            return $response;
        }
    }

    public function newAction(Request $request)
    {
        $response = parent::newAction($request);
        
        if (is_a($response, RedirectResponse::class)) {
            return $this->redirectToRoute('UcaGest_GroupeLister');
        } else{
            return $response;
        }
    }
}