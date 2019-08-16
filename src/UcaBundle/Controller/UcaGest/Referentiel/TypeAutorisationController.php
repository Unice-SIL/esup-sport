<?php

namespace UcaBundle\Controller\UcaGest\Referentiel;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use UcaBundle\Datatables\TypeAutorisationDatatable;
use UcaBundle\Entity\TypeAutorisation;
use UcaBundle\Form\TypeAutorisationType;

/**
 * @Route("UcaGest/TypeAutorisation")
 * @Security("has_role('ROLE_ADMIN')")
*/
class TypeAutorisationController extends Controller
{
    /**
     * @Route("/", name="TypeAutorisationLister")
     * @Isgranted("ROLE_GESTION_TYPE_AUTORISATION_LECTURE")
    */
    public function listerAction(Request $request)
    {
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $this->get('sg_datatables.factory')->create(TypeAutorisationDatatable::class);
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
        if (!$usr->hasRole('ROLE_GESTION_TYPE_AUTORISATION_ECRITURE')) {
            $twigConfig['noAddButton'] = true;
        }
        $twigConfig['codeListe'] = 'TypeAutorisation';
        return $this->render('@Uca/Common/Liste/Datatable.html.twig', $twigConfig);
    }

    /**
     * @Route("/Ajouter", name="TypeAutorisationAjouter")
     * @Method({"GET""POST"})
     * @Isgranted("ROLE_GESTION_TYPE_AUTORISATION_ECRITURE")
     */
    public function ajouterAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $item = new TypeAutorisation($em);
        $form = $this->get('form.factory')->create(TypeAutorisationType::class, $item);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($item);
            $em->flush();
            $this->get('uca.flashbag')->addActionFlashBag($item, 'Ajouter');
            return $this->redirectToRoute('TypeAutorisationLister');
        }
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();
        return $this->render('@Uca/UcaGest/Referentiel/TypeAutorisation/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Modifier/{id}", name="TypeAutorisationModifier")
     * @Method({"GET""POST"})
     * @Isgranted("ROLE_GESTION_TYPE_AUTORISATION_ECRITURE")
    */
    public function modifierAction(Request $request, TypeAutorisation $item)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->get('form.factory')->create(TypeAutorisationType::class, $item);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->flush();
            $this->get('uca.flashbag')->addActionFlashBag($item, 'Modifier');
            return $this->redirectToRoute('TypeAutorisationLister');
        }
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();
        return $this->render('@Uca/UcaGest/Referentiel/TypeAutorisation/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Supprimer/{id}", name="TypeAutorisationSupprimer")
     * @Isgranted("ROLE_GESTION_TYPE_AUTORISATION_ECRITURE")
     */
    public function supprimerAction(Request $request, TypeAutorisation $item)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($item);
        $em->flush();
        $this->get('uca.flashbag')->addActionFlashBag($item, 'Supprimer');
        return $this->redirectToRoute('TypeAutorisationLister');
    }
}
