<?php

namespace UcaBundle\Controller\UcaGest\Outils;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Datatables\LogDatatable;
use Gedmo\Loggable\Entity\LogEntry;
use UcaBundle\Entity\Ressource;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Security("has_role('ROLE_ADMIN')")
 * @Route("UcaGest/Log")
 */
class LogController extends Controller
{
    /**
     * @Route("/Lister/{objectClass}/{objectId}", name="LogLister")
     */
    public function listerAction(Request $request, $objectClass = null, $objectId = null)
    {
        $em = $this->getDoctrine()->getManager();
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $this->get('sg_datatables.factory')->create(LogDatatable::class);
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;

        if ($isAjax) {
            $responseService = $this->get('sg_datatables.response');
            $responseService->setDatatable($datatable);
            $dtQueryBuilder = $responseService->getDatatableQueryBuilder();
            $qb = $dtQueryBuilder->getQb();
            if (!empty($objectClass)) {
                $objectClass = 'UcaBundle\\Entity\\' . $objectClass;
                if (!empty($objectId)){
                    $object = $em->getRepository($objectClass)->find($objectId);
                    // Permet de gérer l'héritage
                    $objectClass = get_class($object);
                    $qb->andWhere('logentry.objectId = :objectId');
                    $qb->setParameter('objectId', $objectId);
                }
                $qb->andWhere('logentry.objectClass = :objectClass');
                $qb->setParameter('objectClass', $objectClass);
            }
            return $responseService->getResponse();
        }

        $twigConfig['codeListe'] = 'Log';
        $twigConfig['noAddButton'] = true;
        $twigConfig['retourBouton'] = true;
        return $this->render('@Uca/Common/Liste/Datatable.html.twig', $twigConfig);
    }

    /**
     * @Route("/Revenir/{id}", name="LogRevenir")
     */
    public function revenirAction(Request $request, LogEntry $item)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('Gedmo\Loggable\Entity\LogEntry');
        $logs = $em->find('UcaBundle\Entity\ClasseActivite', $item->getObjectId());
        $repo->revert($logs, $item->getVersion());
        $em->persist($logs);
        $em->flush();
        return $this->redirectToRoute('LogLister');
    }
}
