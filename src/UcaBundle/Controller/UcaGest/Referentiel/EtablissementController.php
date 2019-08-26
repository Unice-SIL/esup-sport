<?php

namespace UcaBundle\Controller\UcaGest\Referentiel;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use UcaBundle\Datatables\EtablissementDatatable;
use UcaBundle\Entity\Etablissement;
use UcaBundle\Form\EtablissementType;

/** 
 * @Route("UcaGest/Etablissement") 
 * @Security("has_role('ROLE_ADMIN')")
*/
class EtablissementController extends Controller
{
    /**
     * @Route("/", name="UcaGest_EtablissementLister")
     * @Isgranted("ROLE_GESTION_ETABLISSEMENT_LECTURE")
    */
    public function listerAction(Request $request)
    {
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $this->get('sg_datatables.factory')->create(EtablissementDatatable::class);
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
        if (!$usr->hasRole('ROLE_GESTION_ETABLISSEMENT_ECRITURE')) {
            $twigConfig['noAddButton'] = true;
        }
        $twigConfig['codeListe'] = 'Etablissement';
        return $this->render('@Uca/Common/Liste/Datatable.html.twig', $twigConfig);
    }

    /**
     * @Route("/Ajouter", name="UcaGest_EtablissementAjouter")
     * @Method({"GET""POST"})
     * @Isgranted("ROLE_GESTION_ETABLISSEMENT_ECRITURE")
     */
    public function ajouterAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $item = new Etablissement($em);
        $form = $this->get('form.factory')->create(EtablissementType::class, $item);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($item);
            $em->flush();
            $this->get('uca.flashbag')->addActionFlashBag($item, 'Ajouter');
            return $this->redirectToRoute('UcaGest_EtablissementLister');
        }
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();
        return $this->render('@Uca/UcaGest/Referentiel/Etablissement/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Modifier/{id}", name="UcaGest_EtablissementModifier")
     * @Method({"GET""POST"})
     * @Isgranted("ROLE_GESTION_ETABLISSEMENT_ECRITURE")
    */
    public function modifierAction(Request $request, Etablissement $item)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->get('form.factory')->create(EtablissementType::class, $item);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->flush();
            $this->get('uca.flashbag')->addActionFlashBag($item, 'Modifier');
            return $this->redirectToRoute('UcaGest_EtablissementLister');
        }
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();
        return $this->render('@Uca/UcaGest/Referentiel/Etablissement/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Supprimer/{id}", name="UcaGest_EtablissementSupprimer")
     * @Isgranted("ROLE_GESTION_ETABLISSEMENT_ECRITURE")
    */
    public function supprimerAction(Request $request, Etablissement $item)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$item->getRessources()->isEmpty()) {
            $this->get('uca.flashbag')->addActionErrorFlashBag($item, 'Supprimer');
            return $this->redirectToRoute('UcaGest_EtablissementLister');
        }
        $em->remove($item);
        $em->flush();
        $this->get('uca.flashbag')->addActionFlashBag($item, 'Supprimer');
        return $this->redirectToRoute('UcaGest_EtablissementLister');
    
    }
    /**
     * @Route("/{id}", name="UcaGest_EtablissementVoir")
     * @Isgranted("ROLE_GESTION_ETABLISSEMENT_LECTURE")
    */
    public function voirAction(Request $request, Etablissement $item)
    {
        return $this->render('@Uca/UcaGest/Referentiel/Etablissement/Voir.html.twig', ['item' => $item]);
    }
}
