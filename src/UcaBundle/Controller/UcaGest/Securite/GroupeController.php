<?php

namespace UcaBundle\Controller\UcaGest\Securite;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Datatables\GroupeDatatable;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use UcaBundle\Entity\Groupe;

/* use Gedmo\Translat   able\TranslatableListener;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Method;
use Symfony\Component\Routing\Annotation\Template;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Contracts\Translation\TranslatorInterface;
use UcaBundle\Entity\Groupe;
use UcaBundle\Form\GroupeType; */


/**
 * @Route("UcaGest/Groupe")
 * @Security("has_role('ROLE_ADMIN')")
*/
class GroupeController extends Controller
{

    /** 
     * @Route("/", name="GroupeLister") 
     * @Isgranted("ROLE_GESTION_GROUPE_LECTURE")
    */
    public function listerAction(Request $request)
    {
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $this->get('sg_datatables.factory')->create(GroupeDatatable::class);
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
        if (!$usr->hasRole('ROLE_GESTION_GROUPE_ECRITURE')) {
            $twigConfig['noAddButton'] = true;
        }
        $twigConfig['codeListe'] = 'Groupe';
        return $this->render('@Uca/UcaGest/Securite/Groupe/Lister.html.twig', $twigConfig);
    }

    /**
     * @Route("/{id}/Supprimer", name="GroupeSupprimer") 
     * @Isgranted("ROLE_GESTION_GROUPE_ECRITURE")
     */
    public function supprimerAction(Request $request, Groupe $groupe)
    {
        $gm = $this->container->get('fos_user.group_manager');
        if (!$groupe->getUtilisateurs()->isEmpty()) {
            $this->get('uca.flashbag')->addMessageFlashBag('Impossible de supprimer ce groupe, des utilisateur y sont affectés', 'danger');
            return $this->redirectToRoute('GroupeLister');
        }
        $gm->deleteGroup($groupe);
        $this->get('uca.flashbag')->addActionFlashBag($groupe, 'Supprimer');
        return $this->redirectToRoute('GroupeLister');
    }

    /*
     * @Route("/Groupe/Ajouter", name="GroupeAjouter")

    public function ajouterAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $item = new Groupe('');
        $form = $this->get('form.factory')->create(GroupeType::class, $item, ['roles' => $this->getRoles()]);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($item);
            $em->flush();
            $this->get('uca.flashbag')->addActionFlashBag($item, 'Ajouter');
            return $this->redirectToRoute('GroupeLister');
        }
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();
        return $this->render('@Uca/Common/Formulaire/Simple.html.twig', $twigConfig);
    }
    
 
     * @Route("/Groupe/Modifier/{id}", name="GroupeModifier")
     
    public function modifierAction(Request $request, Groupe $item)
    {
        $em = $this->getDoctrine()->getManager();
        // dump(item); die;
        $form = $this->get('form.factory')->create(GroupeType::class, $item, ['roles' => $this->getRoles()]);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->flush();
            $this->get('uca.flashbag')->addActionFlashBag($item, 'Modifier');
            return $this->redirectToRoute('GroupeLister');
        }
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();
        return $this->render('@Uca/Common/Formulaire/Simple.html.twig', $twigConfig);
    }

     * @Route("/Groupe/Supprimer/{id}", name="GroupeSupprimer")
 
    public function supprimerAction(Request $request, Groupe $item)
    {
        $t = $this->get('translator');
        $em = $this->getDoctrine()->getManager();
        $em->remove($item);
        $em->flush();
        $this->get('uca.flashbag')->addActionFlashBag($item, 'Supprimer');
        return $this->redirectToRoute('GroupeLister');
    }

    public function getRoles()
    {
        $roles = $this->container->getParameter('security.role_hierarchy.roles');
        return array_combine(array_keys($roles), array_keys($roles));
    }
*/
}
