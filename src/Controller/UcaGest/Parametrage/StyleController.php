<?php

namespace App\Controller\UcaGest\Parametrage;

use App\Entity\Uca\Style;
use App\Form\StyleType;
use App\Form\StylePreviewType;
use App\Service\Common\Style as StyleService;
use App\Service\Service\StylePreviewService;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("UcaGest/Style")
 * @IsGranted("ROLE_GESTION_PARAMETRAGE")
 */
class StyleController extends AbstractController
{
    /**
     * @Route("/", name="UcaGest_StyleIndex", methods={"GET"})
     * @IsGranted("ROLE_GESTION_PARAMETRAGE")
     */
    public function indexAction(): Response
    {
        return $this->render('UcaBundle/UcaGest/Parametrage/Style/Index.html.twig', []);
    }

    /**
     * @Route("/Preview", name="UcaGest_StylePreview", methods={"GET", "POST"})
     * @IsGranted("ROLE_GESTION_PARAMETRAGE")
     */
    public function previewAction(StyleService $styleService, Request $request, EntityManagerInterface $em, StylePreviewService $previewService): Response
    {
        $styleService->setPreview(true);
        $form = $this->createForm(StylePreviewType::class);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $styleService->saveStyle();
            
            return $this->redirectToRoute('UcaGest_StyleIndex');
        }
        $previewService->setUtilisateur($this->getUser());

        $twigConfig = [];
        $twigConfig['form'] = $form->createView();
        $twigConfig['format'] = $previewService->getFormatAvecCreneau();
        $twigConfig['formatAvecCreneau'] = $previewService->getFormatAvecCreneau();
        $twigConfig['formatSimple'] = $previewService->getFormatSimple();
        $twigConfig['articles'] = $previewService->getArticles();
        $twigConfig['currentDate'] = new \DateTime();
        $twigConfig['etablissements'] = $previewService->getEtablissements();

        return $this->render('UcaBundle/UcaGest/Parametrage/Style/Preview.html.twig', $twigConfig);
    }

    /**
     * @Route("/Modifier", name="UcaGest_StyleModifier", methods={"GET", "POST"})
     * @IsGranted("ROLE_GESTION_PARAMETRAGE")
     */
    public function modifierAction(Request $request, EntityManagerInterface $em): Response
    {
        $style = $em->getRepository(Style::class)->findOneBy(['preview' => true]);
        $form = $this->createForm(StyleType::class, $style);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('UcaGest_StylePreview');
        }

        return $this->render('UcaBundle/UcaGest/Parametrage/Style/Formulaire.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
