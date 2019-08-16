<?php

namespace UcaBundle\Controller\UcaGest\Referentiel;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use UcaBundle\Entity\Actualite;
use UcaBundle\Datatables\ActualiteDatatable;
use UcaBundle\Form\ActualiteType;
use UcaBundle\Service\Common\FlashBag;


/**
 * @Security("has_role('ROLE_ADMIN')")
 * @Route("UcaGest/Actualite")
 */
class ActualiteController extends Controller
{
    /**
     * @Route("/", name="ActualiteLister")
     * @Isgranted("ROLE_GESTION_ACTUALITE_LECTURE")
    */
    public function listerAction(Request $request)
    {
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $this->get('sg_datatables.factory')->create(ActualiteDatatable::class);
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
        if (!$usr->hasRole('ROLE_GESTION_ACTUALITE_ECRITURE')) {
            $twigConfig['noAddButton'] = true;
        }
        $twigConfig['codeListe'] = 'Actualite';
        return $this->render('@Uca/Common/Liste/Datatable.html.twig', $twigConfig);
    }

    /**
     * @Route("/Ajouter", name="ActualiteAjouter")
     * @Method({"GET""POST"})
     * @Isgranted("ROLE_GESTION_ACTUALITE_ECRITURE")
     */
    public function ajouterAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $item = new Actualite();
        $item->setOrdre(count($this->getDoctrine()->getRepository(Actualite::class)->findAll()));
        $form = $this->get('form.factory')->create(ActualiteType::class, $item);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($item);
            $em->flush();
            $this->get('uca.flashbag')->addActionFlashBag($item, 'Ajouter');
            return $this->redirectToRoute('ActualiteLister');
        }

        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();
        return $this->render('@Uca/UcaGest/Referentiel/Actualite/Formulaire.html.twig', $twigConfig);
    }


     /**
     * @Route("/Supprimer/{id}", name="ActualiteSupprimer")
     * @Isgranted("ROLE_GESTION_ACTUALITE_ECRITURE")
    */
    public function supprimerAction(Request $request, Actualite $actualite)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($actualite);
        $em->flush();
        $this->get('uca.flashbag')->addActionFlashBag($actualite, 'Supprimer');
        return $this->redirectToRoute('ActualiteLister');
    
    }

    /** 
     * @Route("/Modifier/{id}", name="ActualiteModifier",requirements={"id"="\d+"}) 
     * @Method({"GET", "POST"})
     * @Isgranted("ROLE_GESTION_ACTUALITE_ECRITURE")
    */
    public function modifierAction(Request $request, actualite $actualite)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->get('form.factory')->create(ActualiteType::class, $actualite);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($actualite);
            $em->flush();
            $this->get('uca.flashbag')->addActionFlashBag($actualite, 'Modifier');
            return $this->redirectToRoute('ActualiteLister');
        }
        $twigConfig['item'] = $actualite;
        $twigConfig['form'] = $form->createView();
        return $this->render('@Uca/UcaGest/Referentiel/Actualite/Formulaire.html.twig', $twigConfig);
    }
}
