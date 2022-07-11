<?php

/*
 * Classe - TraductionController
 *
 * Va permettre l'afficher et l'édition des traductions
*/

namespace App\Controller\UcaGest\Outils;

use App\Service\Common\FlashBag;
use Gedmo\Translatable\Entity\Translation;
use Symfony\Component\HttpFoundation\Request;
use App\Datatables\TraductionDatatable;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sg\DatatablesBundle\Datatable\DatatableFactory;
use Sg\DatatablesBundle\Response\DatatableResponse;

/**
 * @Route("UcaGest/Traduction")
 * @Isgranted("ROLE_GESTION_TRADUCTION_LECTURE")
 */
class TraductionController extends AbstractController
{
    /**
     * @Route("/", name="UcaGest_TraductionLister")
     */
    public function listerAction(Request $request, EntityManagerInterface $em, DatatableFactory $datatableFactory, DatatableResponse $datatableResponse)
    {
        $queryInfo = new TraductionQuery();
        $queryInfo->init($em, $this->getParameter('lang.all'), $this->getParameter('lang.default'));
        $datatable = $datatableFactory->create(TraductionDatatable::class);
        $datatable->buildDatatable(['queryInfo' => $queryInfo]);
        $twigConfig['datatable'] = $datatable;

        if ($request->isXmlHttpRequest()) {
            $responseService = $datatableResponse;
            $responseService->setDatatable($datatable);
            $dtQueryBuilder = $responseService->getDatatableQueryBuilder();
            $qb = $dtQueryBuilder->getQb();
            $queryInfo->qbPersonalize($qb);

            return $responseService->getResponse(true, false, false);
        }
        $twigConfig['codeListe'] = 'Traduction';
        $twigConfig['noAddButton'] = true;

        return $this->render('UcaBundle/Common/Liste/Datatable.html.twig', $twigConfig);
    }

    /**
     * @Isgranted("ROLE_GESTION_TRADUCTION_ECRITURE")
     * @Route("/Modifier", name="UcaGest_TraductionModifier")
     */
    public function modifierAction(Request $request, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $entity = $request->get('entity');
        $field = $request->get('field');
        $id = trim($request->get('id'));

        $queryInfo = new TraductionQuery();
        $queryInfo->init($em, $this->getParameter('lang.all'), $this->getParameter('lang.default'));
        $qb = $queryInfo->qbInit();
        $queryInfo->qbPersonalize($qb);
        $queryInfo->qbFindOne($qb, $entity, $field, $id);

        $twigConfig['queryInfo'] = $queryInfo;

        $twigConfig['data'] = $qb->getQuery()->getResult()[0];

        $editForm = $this->createForm('App\Form\TraductionType', $twigConfig);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid() && $request->isMethod('POST')) {
            foreach ($queryInfo->getCols() as $alias => $col) {
                if (false !== strpos($col['config'], 'write')) {
                    $translation = $em->getRepository(Translation::class)->findOneBy(['locale' => $col['lang'], 'objectClass' => $entity,  'field' => $field, 'foreignKey' => $id]);
                    if (null == $translation) {
                        $translation = (new Translation())->setLocale($col['lang'])->setObjectClass($entity)->setField($field)->setForeignKey($id);
                    }
                    $translation->setContent($editForm['val'.$col['lang']]->getData());
                    $em->persist($translation);
                }
            }
            $em->flush();

            $flashBag->addActionFlashBag($translation, 'Modifier');

            return $this->redirectToRoute('UcaGest_TraductionLister');
        }

        $twigConfig['item'] = null;
        $twigConfig['form'] = $editForm->createView();

        return $this->render('UcaBundle/UcaGest/Outils/Traductions/Formulaire.html.twig', $twigConfig);
    }
}
