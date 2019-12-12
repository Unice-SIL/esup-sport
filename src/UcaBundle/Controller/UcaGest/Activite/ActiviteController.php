<?php

namespace UcaBundle\Controller\UcaGest\Activite;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use UcaBundle\Entity\Activite;
use UcaBundle\Entity\FormatActivite;
use UcaBundle\Datatables\ActiviteDatatable;
use UcaBundle\Datatables\FormatActiviteDatatable;
use UcaBundle\Form\ActiviteType;
use UcaBundle\Service\Common\FlashBag;


/**
 * @Security("has_role('ROLE_ADMIN')")
 * @Route("UcaGest/Activite")
 */
class ActiviteController extends Controller
{
    /**
     * @Route("/", name="UcaGest_ActiviteLister", options = {"expose" = true} , requirements={"id"="\d+"}, methods={"GET"})
     * @Isgranted("ROLE_GESTION_ACTIVITE_LECTURE")
     */
    public function listerAction(Request $request)
    {
        $datatable = $this->get('sg_datatables.factory')->create(ActiviteDatatable::class);
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;

        if ($request->isXmlHttpRequest()) {
            $responseService = $this->get('sg_datatables.response');
            $responseService->setDatatable($datatable);
            $responseService->getDatatableQueryBuilder();
            return $responseService->getResponse();
        }
        $twigConfig['codeListe'] = 'Activite';
        $usr = $this->container->get('security.token_storage')->getToken()->getUser();
        if (!$usr->hasRole('ROLE_GESTION_ACTIVITE_ECRITURE')) {
            $twigConfig['noAddButton'] = true;
        }
        return $this->render('@Uca/Common/Liste/Datatable.html.twig', $twigConfig);
    }

    /**
     * @Isgranted("ROLE_GESTION_ACTIVITE_ECRITURE")
     * @Route("/Ajouter", name="UcaGest_ActiviteAjouter", methods={"GET", "POST"})
     */
    public function ajouterAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $item = new Activite($em);
        $form = $this->get('form.factory')->create(ActiviteType::class, $item); //, $item, ['form_mode' => 'Ajouter']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $request->isMethod('POST')) {
            $em->persist($item);
            $em->flush();
            $this->get('uca.flashbag')->addActionFlashBag($item, 'Ajouter');
            return $this->redirectToRoute('UcaGest_ActiviteLister');
        }

        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();
        return $this->render('@Uca/UcaGest/Activite/Activite/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Isgranted("ROLE_GESTION_ACTIVITE_ECRITURE")
     * @Route("/{id}/Modifier", name="UcaGest_ActiviteModifier", methods={"GET", "POST"})
     */
    public function modifierAction(Request $request, Activite $activite)
    {
        $em = $this->getDoctrine()->getManager();
        $editForm = $this->createForm('UcaBundle\Form\ActiviteType', $activite); //$activite, ['form_mode' => 'Modifier']);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid() && $request->isMethod('POST')) {
            $this->get('uca.flashbag')->addActionFlashBag($activite, 'Modifier');
            $em->flush();
            return $this->redirectToRoute('UcaGest_ActiviteLister');
        }

        $twigConfig["item"] = $activite;
        $twigConfig["form"] = $editForm->createView();
        return $this->render('@Uca/UcaGest/Activite/Activite/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Isgranted("ROLE_GESTION_ACTIVITE_ECRITURE")
     * @Route("/{id}/Supprimer", name="UcaGest_ActiviteSupprimer")
     */
    public function supprimerAction(Request $request, Activite $activite)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$activite->getFormatsActivite()->isEmpty()) {
            $this->get('uca.flashbag')->addActionErrorFlashBag($activite, 'Supprimer');
            return $this->redirectToRoute('UcaGest_ActiviteLister');
        }
        $em->remove($activite);
        $em->flush();
        $this->get('uca.flashbag')->addActionFlashBag($activite, 'Supprimer');
        return $this->redirectToRoute('UcaGest_ActiviteLister');
    }

    /**
     * @Isgranted("ROLE_GESTION_ACTIVITE_LECTURE")
     * @Route("/{id}", name="UcaGest_ActiviteVoir", methods={"GET"})
     */
    public function voirAction(Request $request, Activite $activite)
    {

        $em = $this->getDoctrine()->getManager();
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $this->get('sg_datatables.factory')->create(FormatActiviteDatatable::class);
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;
        if ($isAjax) {
            $responseService = $this->get('sg_datatables.response');
            $responseService->setDatatable($datatable);
            $dtQueryBuilder = $responseService->getDatatableQueryBuilder();
            $qb = $dtQueryBuilder->getQb();
            // dump($qb); die;

            $qb->andWhere('activite.id = :objectId');
            $qb->setParameter('objectId', $activite->getId());

            return $responseService->getResponse();
        }
        $twigConfig["item"] = $activite;
        return $this->render('@Uca/UcaGest/Activite/Activite/Voir.html.twig', $twigConfig);
    }
}
