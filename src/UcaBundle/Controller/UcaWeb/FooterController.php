<?php

namespace UcaBundle\Controller\UcaWeb;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Form\UtilisateurCgvType;

/**
 * @Route("UcaWeb")
 */
class FooterController extends Controller
{
    /**
     * @Route("/PlanSite", name="UcaWeb_PlanSite")
     */
    public function planSiteAction()
    {
        return $this->render('@Uca/UcaWeb/Footer/PlanSite.html.twig');
    }

    /**
     * @Route("/Accessibilite", name="UcaWeb_Accessibilite")
     */
    public function accessibiliteAction()
    {
        return $this->render('@Uca/UcaWeb/Footer/Accessibilite.html.twig');
    }

    /**
     * @Route("/MentionsLegales", name="UcaWeb_MentionsLegales")
     */
    public function mentionsLegalesAction()
    {
        return $this->render('@Uca/UcaWeb/Footer/EmplacementTexte.html.twig', ['emplacement' => 'Mentions légales']);
    }

    /**
     * @Route("/CGV", name="UcaWeb_CGV")
     */
    public function CGVAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $utilisateur = $this->getUser();
        if ($utilisateur && !$utilisateur->getCgvAcceptees()) {
            $form = $this->get('form.factory')->create(UtilisateurCgvType::class, $utilisateur);
            if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
                if ($utilisateur->getCgvAcceptees()) {
                    $em->persist($utilisateur);
                    $em->flush();
                    $this->get('uca.flashbag')->addTranslatedFlashBag('success', 'cgv.acceptees');
                } else {
                    $twigConfig['form'] = $form->createView();
                    $this->get('uca.flashbag')->addTranslatedFlashBag('danger', 'cgv.refusees');
                }
            } else {
                $this->get('uca.flashbag')->addTranslatedFlashBag('warning', 'cgv.information');
                $twigConfig['form'] = $form->createView();
            }
        }
        $twigConfig['emplacement'] = 'CGV';
        return $this->render('@Uca/UcaWeb/Footer/EmplacementTexte.html.twig', $twigConfig);
    }

    /**
     * @Route("/DonneesPersonnelles", name="UcaWeb_DonneesPersonnelles")
     */
    public function DonneesPersonnellesAction()
    {
        return $this->render('@Uca/UcaWeb/Footer/EmplacementTexte.html.twig', ['emplacement'=> 'Données Personnelles']);
    }
}
