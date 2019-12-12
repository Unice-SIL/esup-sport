<?php

namespace UcaBundle\Controller\UcaGest\Outils;

use Doctrine\ORM\Query\Expr\Join;
use Gedmo\Translatable\Entity\Translation;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Datatables\TraductionDatatable;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/** 
 * @Route("UcaGest/Traduction") 
 * @Isgranted("ROLE_GESTION_TRADUCTION_LECTURE")
 */
class TraductionController extends Controller
{
    /**
     * @Route("/", name="UcaGest_TraductionLister")
     */
    public function listerAction(Request $request)
    {
        $queryInfo = new TraductionQuery($this->getDoctrine()->getManager(), $this->getParameter('lang.all'), $this->getParameter('lang.default'));
        $datatable = $this->get('sg_datatables.factory')->create(TraductionDatatable::class);
        $datatable->buildDatatable(['queryInfo' => $queryInfo]);
        $twigConfig['datatable'] = $datatable;

        if ($request->isXmlHttpRequest()) {
            $responseService = $this->get('sg_datatables.response');
            $responseService->setDatatable($datatable);
            $dtQueryBuilder = $responseService->getDatatableQueryBuilder();
            $qb = $dtQueryBuilder->getQb();
            $queryInfo->qbPersonalize($qb);
            return $responseService->getResponse(true, false, false);
        }
        $twigConfig['codeListe'] = 'Traduction';
        $twigConfig['noAddButton'] = true;
        return $this->render('@Uca/Common/Liste/Datatable.html.twig', $twigConfig);
    }

    /**
     * @Isgranted("ROLE_GESTION_TRADUCTION_ECRITURE")
     * @Route("/Modifier", name="UcaGest_TraductionModifier")
     */
    public function modifierAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $request->get('entity');
        $field = $request->get('field');
        $id = trim($request->get('id'));

        $queryInfo = new TraductionQuery($em, $this->getParameter('lang.all'), $this->getParameter('lang.default'));
        $qb = $queryInfo->qbInit();
        $queryInfo->qbPersonalize($qb);
        $queryInfo->qbFindOne($qb, $entity, $field, $id);

        $twigConfig['queryInfo'] = $queryInfo;
        
        $twigConfig['data'] = $qb->getQuery()->getResult()[0];

        $editForm = $this->createForm('UcaBundle\Form\TraductionType', $twigConfig);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid() && $request->isMethod('POST')) {
            foreach ($queryInfo->getCols() as $alias => $col) {
                if (strpos($col['config'], 'write') !== false) {
                    $translation = $em->getRepository(Translation::class)->findOneBy(['locale' => $col['lang'], 'objectClass' => $entity,  'field' => $field, 'foreignKey' => $id]);
                    if ($translation == null) {
                        $translation = (new Translation())->setLocale($col['lang'])->setObjectClass($entity)->setField($field)->setForeignKey($id);
                    }
                    $translation->setContent($editForm['val' . $col['lang']]->getData());
                    $em->persist($translation);
                }
            }
            $em->flush();

            $this->get('uca.flashbag')->addActionFlashBag($translation, 'Modifier');
            return $this->redirectToRoute('UcaGest_TraductionLister');
        }

        $twigConfig["item"] = null;
        $twigConfig["form"] = $editForm->createView();
        return $this->render('@Uca/UcaGest/Outils/Traductions/Formulaire.html.twig', $twigConfig);
    }
}
