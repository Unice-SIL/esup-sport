<?php

/*
 * Classe - ClasseActiviteController
 *
 * Gestion du CRUD pour les classes d'activités
*/

namespace UcaBundle\Controller\UcaGest\Activite;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Datatables\ClasseActiviteDatatable;
use UcaBundle\Entity\ClasseActivite;
use UcaBundle\Form\ClasseActiviteType;

/**
 * @Security("has_role('ROLE_ADMIN')")
 * @Route("UcaGest/ClasseActivite")
 */
class ClasseActiviteController extends Controller
{
    /**
     * @Route("/", name="UcaGest_ClasseActiviteLister")
     * @Isgranted("ROLE_GESTION_CLASSE_ACTIVITE_LECTURE")
     */
    public function listerAction(Request $request)
    {
        /* dump($this->get('security.authorization_checker')); die;
        // dump($this->get('fos_user.security.controller'));die;
        if (!$this->getUser() || !$this->getUser()->hasRole('GESTION_CLASSE_ACTIVITE'))
            throw new \Exception('Access Denied');
        // if (!$this->isGranted('GESTION_CLASSE_ACTIVITE'))
        //     throw new \Exception('Access Denied');
        // $this->denyAccessUnlessGranted('GESTION_CLASSE_ACTIVITE');
        */

        $isAjax = $request->isXmlHttpRequest();
        $datatable = $this->get('sg_datatables.factory')->create(ClasseActiviteDatatable::class);
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
        if (!$usr->hasRole('ROLE_GESTION_CLASSE_ACTIVITE_ECRITURE')) {
            $twigConfig['noAddButton'] = true;
        }

        $twigConfig['codeListe'] = 'ClasseActivite';

        return $this->render('@Uca/Common/Liste/Datatable.html.twig', $twigConfig);
    }

    /**
     * @Route("/Ajouter", name="UcaGest_ClasseActiviteAjouter")
     * @Isgranted("ROLE_GESTION_CLASSE_ACTIVITE_ECRITURE")
     */
    public function ajouterAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $item = new ClasseActivite($em);
        $form = $this->get('form.factory')->create(ClasseActiviteType::class, $item);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($item);
            $em->flush();
            $this->get('uca.flashbag')->addActionFlashBag($item, 'Ajouter');

            return $this->redirectToRoute('UcaGest_ClasseActiviteLister');
        }
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();

        return $this->render('@Uca/UcaGest/Activite/ClasseActivite/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Modifier/{id}", name="UcaGest_ClasseActiviteModifier")
     * @Isgranted("ROLE_GESTION_CLASSE_ACTIVITE_ECRITURE")
     */
    public function modifierAction(Request $request, ClasseActivite $item)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->get('form.factory')->create(ClasseActiviteType::class, $item);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->flush();
            $this->get('uca.flashbag')->addActionFlashBag($item, 'Modifier');

            return $this->redirectToRoute('UcaGest_ClasseActiviteLister');
        }
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();

        return $this->render('@Uca/UcaGest/Activite/ClasseActivite/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Supprimer/{id}", name="UcaGest_ClasseActiviteSupprimer")
     * @Isgranted("ROLE_GESTION_CLASSE_ACTIVITE_ECRITURE")
     */
    public function supprimerAction(Request $request, ClasseActivite $item)
    {
        $t = $this->get('translator');
        $em = $this->getDoctrine()->getManager();
        if (!$item->getActivites()->isEmpty()) {
            $this->get('uca.flashbag')->addActionErrorFlashBag($item, 'Supprimer');

            return $this->redirectToRoute('UcaGest_ClasseActiviteLister');
        }
        $em->remove($item);
        $em->flush();
        $this->get('uca.flashbag')->addActionFlashBag($item, 'Supprimer');

        return $this->redirectToRoute('UcaGest_ClasseActiviteLister');
    }
}
