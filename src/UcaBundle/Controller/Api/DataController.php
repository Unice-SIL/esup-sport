<?php

namespace UcaBundle\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Method;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Annotation\Template;
use UcaBundle\Entity\IntervalleDate;
use Symfony\Component\HttpFoundation\JsonResponse;

class DataController extends Controller
{
    /**
     * @Route("/Api/Data", methods={"POST"}, name="DataApi", options={"expose"=true})
     */
    public function DataAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $res = [];
        foreach($request->get('lists') as $key => $list) {
            if(!isset($list['findBy'])){
                $res[$key] = $em->getRepository($list['class'])->findAll();

            }
            else{
                $repo = $list["findBy"]["repository"];
                $param = $list["findBy"]["param"];
                $res[$key] = $em->getRepository($list['class'])->$repo($param);
            
            }
        }

        return new JsonResponse($res);
    }
}
