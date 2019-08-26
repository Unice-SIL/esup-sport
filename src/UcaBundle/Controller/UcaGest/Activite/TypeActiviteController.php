<?php

namespace UcaBundle\Controller\UcaGest\Activite;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use UcaBundle\Datatables\TypeActiviteDatatable;
use UcaBundle\Entity\TypeActivite;
use UcaBundle\Form\TypeActiviteType;


/** 
 * @Security("has_role('ROLE_ADMIN')")
 * @Route("UcaGest/TypeActivite")
*/
class TypeActiviteController extends Controller
{
    /**
     * @Route("/", name="UcaGest_TypeActiviteLister")
     * @Isgranted("ROLE_GESTION_TYPE_ACTIVITE_LECTURE")
     */
    public function listerAction(Request $request)
    {
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $this->get('sg_datatables.factory')->create(TypeActiviteDatatable::class);
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;
        if ($isAjax) {
            $responseService = $this->get('sg_datatables.response');
            $responseService->setDatatable($datatable);
            $dtQueryBuilder = $responseService->getDatatableQueryBuilder();
            $qb = $dtQueryBuilder->getQb();
            return $responseService->getResponse();
        }
        // Bouton Ajouter
        $usr = $this->container->get('security.token_storage')->getToken()->getUser();
        if (!$usr->hasRole('ROLE_GESTION_TYPE_ACTIVITE_ECRITURE')) {
            $twigConfig['noAddButton'] = true;
        }
        $twigConfig['codeListe'] = 'TypeActivite';
        return $this->render('@Uca/Common/Liste/Datatable.html.twig', $twigConfig);
    }

    /**
     * @Route("/Ajouter", name="UcaGest_TypeActiviteAjouter")
     * @Isgranted("ROLE_GESTION_TYPE_ACTIVITE_ECRITURE")
     */
    public function ajouterAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $item = new TypeActivite($em);
        $form = $this->get('form.factory')->create(TypeActiviteType::class, $item);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($item);
            $em->flush();
            $this->get('uca.flashbag')->addActionFlashBag($item, 'Ajouter');
            return $this->redirectToRoute('UcaGest_TypeActiviteLister');
        }
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();
        return $this->render('@Uca/UcaGest/Activite/TypeActivite/Formulaire.html.twig', $twigConfig);
    }
    
    /**
     * @Route("/Modifier/{id}", name="UcaGest_TypeActiviteModifier")
     * @Isgranted("ROLE_GESTION_TYPE_ACTIVITE_ECRITURE")
     */
    public function modifierAction(Request $request, TypeActivite $item) 
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->get('form.factory')->create(TypeActiviteType::class, $item);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($item);
            $em->flush();
            $this->get('uca.flashbag')->addActionFlashBag($item, 'Modifier');
            return $this->redirectToRoute('UcaGest_TypeActiviteLister');
        }
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();
        return $this->render('@Uca/UcaGest/Activite/TypeActivite/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Supprimer/{id}", name="UcaGest_TypeActiviteSupprimer")
     * @Isgranted("ROLE_GESTION_TYPE_ACTIVITE_ECRITURE")
     */
    public function supprimerAction(Request $request, TypeActivite $item) 
    {
        $em = $this->getDoctrine()->getManager();
        if (!$item->getClasseActivite()->isEmpty()) {
            $this->get('uca.flashbag')->addActionErrorFlashBag($item, 'Supprimer');
            return $this->redirectToRoute('UcaGest_TypeActiviteLister');
        }
        $em->remove($item);
        $em->flush();
        $this->get('uca.flashbag')->addActionFlashBag($item, 'Supprimer');
        return $this->redirectToRoute('UcaGest_TypeActiviteLister');
    }
}
