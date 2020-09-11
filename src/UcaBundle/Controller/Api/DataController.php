<?php

/*
 * Classe - DataController:
 *
 * Classe liée à librairie DHTMLX
 * Formatage des données pour la librairie javascirpt
*/

namespace UcaBundle\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DataController extends Controller
{
    /**
     * @Route("/Api/Data", methods={"POST"}, name="DataApi", options={"expose"=true})
     */
    public function DataAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $res = [];
        foreach ($request->get('lists') as $key => $list) {
            if (!isset($list['findBy'])) {
                $res[$key] = $em->getRepository($list['class'])->findAll();
            } else {
                $repo = $list['findBy']['repository'];
                $param = $list['findBy']['param'];
                $res[$key] = $em->getRepository($list['class'])->{$repo}($param);
            }
        }

        return new JsonResponse($res);
    }
}
