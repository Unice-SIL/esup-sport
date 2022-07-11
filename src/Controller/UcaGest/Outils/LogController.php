<?php

/*
 * Classe - LogController
 *
 * Gestion des log: permet la consultation des logs
*/

namespace App\Controller\UcaGest\Outils;

use App\Datatables\LogDatatable;
use Gedmo\Loggable\Entity\LogEntry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sg\DatatablesBundle\Datatable\DatatableFactory;
use Sg\DatatablesBundle\Response\DatatableResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Security("is_granted('ROLE_ADMIN')")
 * @Route("UcaGest/Log")
 */
class LogController extends AbstractController
{
    /**
     * @Route("/Lister/{objectClass}/{objectId}", name="UcaGest_LogLister")
     *
     * @param null|mixed $objectClass
     * @param null|mixed $objectId
     */
    public function listerAction(Request $request, $objectClass = null, $objectId = null, DatatableFactory $datatableFactory, DatatableResponse $datatableResponse, EntityManagerInterface $em)
    {
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $datatableFactory->create(LogDatatable::class);
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;

        if ($isAjax) {
            $responseService = $datatableResponse;
            $responseService->setDatatable($datatable);
            $dtQueryBuilder = $responseService->getDatatableQueryBuilder();
            $qb = $dtQueryBuilder->getQb();
            if (!empty($objectClass)) {
                $objectClass = 'App\\Entity\\Uca\\'.$objectClass;
                if (!empty($objectId)) {
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

        return $this->render('UcaBundle/Common/Liste/Datatable.html.twig', $twigConfig);
    }

    /**
     * @Route("/Revenir/{id}", name="UcaGest_LogRevenir")
     */
    public function revenirAction(Request $request, LogEntry $item, EntityManagerInterface $em)
    {
        $repo = $em->getRepository('Gedmo\Loggable\Entity\LogEntry');
        $logs = $em->find('App\Entity\Uca\ClasseActivite', $item->getObjectId());
        $repo->revert($logs, $item->getVersion());
        $em->persist($logs);
        $em->flush();

        return $this->redirectToRoute('UcaGest_LogLister');
    }
}
