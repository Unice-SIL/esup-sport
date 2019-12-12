<?php

namespace UcaBundle\Controller\UcaGest\Referentiel;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use UcaBundle\Entity\ImageFond;
use UcaBundle\Datatables\ImageFondDatatable;
use UcaBundle\Form\ImageFondType;
use UcaBundle\Service\Common\FlashBag;


/**
 * @Security("has_role('ROLE_ADMIN')")
 * @Route("UcaGest/ImageFond")
 */
class ImageFondController extends Controller
{
    /**
     * @Route("/", name="UcaGest_ImageFondLister")
     * @Isgranted("ROLE_GESTION_IMAGEFOND_LECTURE")
    */
    public function listerAction(Request $request)
    {
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $this->get('sg_datatables.factory')->create(ImageFondDatatable::class);
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
        $twigConfig['noAddButton'] = true;
        $twigConfig['codeListe'] = 'ImageFond';
        return $this->render('@Uca/Common/Liste/Datatable.html.twig', $twigConfig);
    }


     /**
     * @Route("/Supprimer/{id}", name="UcaGest_ImageFondSupprimer")
     * @Isgranted("ROLE_GESTION_IMAGEFOND_ECRITURE")
    */
    public function supprimerAction(Request $request, ImageFond $imageFond)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($imageFond);
        $em->flush();
        $this->get('uca.flashbag')->addActionFlashBag($imageFond, 'Supprimer');
        return $this->redirectToRoute('UcaGest_EtablissementLister');
    
    }

    /** 
     * @Route("/Modifier/{id}", name="UcaGest_ImageFondModifier",requirements={"id"="\d+"}, methods={"GET", "POST"})
     * @Isgranted("ROLE_GESTION_IMAGEFOND_ECRITURE")
    */
    public function modifierAction(Request $request, ImageFond $imageFond)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->get('form.factory')->create(ImageFondType::class, $imageFond);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($imageFond);
            $em->flush();
            $this->get('uca.flashbag')->addActionFlashBag($imageFond, 'Modifier');
            return $this->redirectToRoute('UcaGest_ImageFondLister');
        }
        $twigConfig['item'] = $imageFond;
        $twigConfig['form'] = $form->createView();
        return $this->render('@Uca/UcaGest/Referentiel/ImageFond/Formulaire.html.twig', $twigConfig);
    }
}
