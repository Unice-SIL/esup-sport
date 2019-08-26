<?php

namespace UcaBundle\Controller\UcaWeb;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

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
        return $this->render('@Uca/UcaWeb/Footer/EmplacementTexte.html.twig', ['emplacement'=> 'Mentions légales']);
    }

    /**
     * @Route("/CGU", name="UcaWeb_CGU")
     */
    public function CGUAction()
    {
        return $this->render('@Uca/UcaWeb/Footer/EmplacementTexte.html.twig', ['emplacement'=> 'CGU']);
    }

    /**
     * @Route("/CGV", name="UcaWeb_CGV")
     */
    public function CGVAction()
    {
        return $this->render('@Uca/UcaWeb/Footer/EmplacementTexte.html.twig', ['emplacement'=> 'CGV']);
    }
}
