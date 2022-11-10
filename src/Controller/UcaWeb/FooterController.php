<?php

/*
 * Classe - FooterController
 *
 * Gestion du menu de bas de page
*/

namespace App\Controller\UcaWeb;

use App\Form\UtilisateurCgvType;
use App\Service\Common\FlashBag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("UcaWeb")
 */
class FooterController extends AbstractController
{
    /**
     * @Route("/PlanSite", name="UcaWeb_PlanSite")
     */
    public function planSiteAction()
    {
        return $this->render('UcaBundle/UcaWeb/Footer/PlanSite.html.twig');
    }

    /**
     * @Route("/Accessibilite", name="UcaWeb_Accessibilite")
     */
    public function accessibiliteAction()
    {
        return $this->render('UcaBundle/UcaWeb/Footer/Accessibilite.html.twig');
    }

    /**
     * @Route("/MentionsLegales", name="UcaWeb_MentionsInformations")
     */
    public function mentionsLegalesAction()
    {
        return $this->render('UcaBundle/UcaWeb/Footer/EmplacementTexte.html.twig', ['emplacement' => 'Mentions légales']);
    }

    /**
     * @Route("/CGV", name="UcaWeb_CGV")
     */
    public function CGVAction(Request $request, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $utilisateur = $this->getUser();
        if ($utilisateur && !$utilisateur->getCgvAcceptees()) {
            $form = $this->createForm(UtilisateurCgvType::class, $utilisateur);
            if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
                if ($utilisateur->getCgvAcceptees()) {
                    $em->persist($utilisateur);
                    $em->flush();
                    $flashBag->addTranslatedFlashBag('success', 'cgv.acceptees');
                } else {
                    $twigConfig['form'] = $form->createView();
                    $flashBag->addTranslatedFlashBag('danger', 'cgv.refusees');
                }
            } else {
                $flashBag->addTranslatedFlashBag('warning', 'cgv.information');
                $twigConfig['form'] = $form->createView();
            }
        }
        $twigConfig['emplacement'] = 'CGV';

        return $this->render('UcaBundle/UcaWeb/Footer/EmplacementTexte.html.twig', $twigConfig);
    }

    /**
     * @Route("/DonneesPersonnelles", name="UcaWeb_DonneesPersonnelles")
     */
    public function DonneesPersonnellesAction()
    {
        return $this->render('UcaBundle/UcaWeb/Footer/EmplacementTexte.html.twig', ['emplacement' => 'Données Personnelles']);
    }
}
