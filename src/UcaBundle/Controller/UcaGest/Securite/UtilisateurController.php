<?php

namespace UcaBundle\Controller\UcaGest\Securite;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use UcaBundle\Entity\Utilisateur;
use UcaBundle\Entity\Groupe;
use UcaBundle\Form\UtilisateurType;
use UcaBundle\Datatables\UtilisateurDatatable;

/**
 * @route("UcaGest/Utilisateur")
 * @Security("has_role('ROLE_ADMIN')")
*/
class UtilisateurController extends Controller
{
    /**
     * @Route("/", name="UtilisateurLister")
     * @Isgranted("ROLE_GESTION_UTILISATEUR_LECTURE")
    */
    public function listerAction(Request $request)
    {
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $this->get('sg_datatables.factory')->create(UtilisateurDatatable::class);
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
        if (!$usr->hasRole('ROLE_GESTION_UTILISATEUR_ECRITURE')) {
            $twigConfig['noAddButton'] = true;
        }
        $twigConfig['codeListe'] = 'Utilisateur';
        return $this->render('@Uca/UcaGest/Securite/Utilisateur/Lister.html.twig', $twigConfig);
    }

    /**
     * @Route("/{id}", name="UtilisateurVoir")
     * @Method("GET")
     * @Isgranted("ROLE_GESTION_UTILISATEUR_LECTURE")
    */
    public function voirAction(Utilisateur $item)
    {
        $em = $this->getDoctrine()->getManager();
        
        $twigConfig['item'] = $item;

        $twigConfig["encadrant"] = $item->getGroups()->contains($em->getReference(Groupe::class, 3));
        return $this->render('@Uca/UcaGest/Securite/Utilisateur/Voir.html.twig', $twigConfig);
    }

    /**
     * @Route("/scheduler/{id}", name="UtilisateurScheduler")
     * @Isgranted("ROLE_GESTION_SCHEDULER_LECTURE")
    */
    public function voirScheduler(Utilisateur $item){
        $twigConfig['item'] = $item;
        $twigConfig['type'] = "encadrant";
        $twigConfig['role'] = "encadrant";
        return $this->render('@Uca/UcaGest/Securite/Utilisateur/Scheduler.html.twig', $twigConfig);     
    }

    /**
     * @Route("/Modifier/{id}", name="UtilisateurModifier")
     * @Method({"GET""POST"})
     * @Isgranted("ROLE_GESTION_UTILISATEUR_ECRITURE")
     */
    public function modifierAction(Request $request, Utilisateur $item)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->get('form.factory')->create(UtilisateurType::class, $item , ['action_type' => 'modifier']);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($item);
            $em->flush();
            $this->get('uca.flashbag')->addActionFlashBag($item, 'Modifier');
            return $this->redirectToRoute('UtilisateurLister');
        }
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();
        return $this->render('@Uca/UcaGest/Securite/Utilisateur/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Supprimer/{id}", name="UtilisateurSupprimer")
     * @Isgranted("ROLE_GESTION_UTILISATEUR_ECRITURE")
    */
    public function supprimerAction(Request $request, Utilisateur $item)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($item);
        $em->flush();
        $this->get('uca.flashbag')->addActionFlashBag($item, 'Supprimer');
        return $this->redirectToRoute('UtilisateurLister');
    }

    /**
     * @Route("/Bloquer/{id}", name="UtilisateurBloquer")
     * @Isgranted("ROLE_GESTION_UTILISATEUR_LECTURE")
    */
    public function bloquerAction(Request $request, Utilisateur $item)
    {
        $em = $this->getDoctrine()->getManager();
        if ($item->isEnabled()) 
        { 
            $item->setEnabled(false); 
            $this->get('uca.flashbag')->addActionFlashBag($item, 'Bloquer');
        }
        else 
        {
            $item->setEnabled(true); 
            $this->get('uca.flashbag')->addActionFlashBag($item, 'Debloquer');
        }
        $em->persist($item);
        $em->flush();
        return $this->redirectToRoute('UtilisateurLister');
    }

    /**
     * @Route("/AjouterAutorisation/{id}", name="UtilisateurAjouterAutorisation")
     * @Isgranted("ROLE_GESTION_UTILISATEUR_ECRITURE")
     */
    public function ajouterAutorisationAction(Request $request, Utilisateur $item)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->get('form.factory')->create(UtilisateurType::class, $item);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            
            $em->persist($item);
            $em->flush();
            $this->get('uca.flashbag')->addMessageFlashBag('utilisateur.autorisation.ajouter.success', 'success');
            return $this->redirectToRoute('UtilisateurVoir', array('id' => $item->getId()));
        }
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();
        return $this->render('@Uca/UcaGest/Securite/Utilisateur/FormulaireAjouterAutorisation.html.twig', $twigConfig);
    }


}
