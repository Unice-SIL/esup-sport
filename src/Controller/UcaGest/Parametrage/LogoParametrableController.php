<?php

/*
 * Classe - LogoParametrableController
 *
 * Gestion du CRUD pour les logos parametrables
*/

namespace App\Controller\UcaGest\Parametrage;

use App\Form\LogoParametrableType;
use App\Service\Common\FlashBag;
use App\Entity\Uca\LogoParametrable;
use Doctrine\ORM\EntityManagerInterface;
use App\Datatables\LogoParametrableDatatable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sg\DatatablesBundle\Datatable\DatatableFactory;
use Sg\DatatablesBundle\Response\DatatableResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Security("is_granted('ROLE_ADMIN')")
 * @Route("UcaGest/LogoParametrable")
 */
class LogoParametrableController extends AbstractController
{
    /**
     * @Route("/", name="UcaGest_LogoParametrableLister")
     * @Isgranted("ROLE_GESTION_PARAMETRAGE")
     */
    public function listerAction(Request $request, DatatableFactory $datatableFactory, DatatableResponse $datatableResponse)
    {
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $datatableFactory->create(LogoParametrableDatatable::class);
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;
        if ($isAjax) {
            $responseService = $datatableResponse;
            $responseService->setDatatable($datatable);
            $dtQueryBuilder = $responseService->getDatatableQueryBuilder();
            $qb = $dtQueryBuilder->getQb();

            return $responseService->getResponse();
        }
        // Bouton Ajouter
        $usr = $this->getUser();
        $twigConfig['codeListe'] = 'LogoParametrable';
        $twigConfig['noAddButton'] = true;

        return $this->render('UcaBundle/Common/Liste/Datatable.html.twig', $twigConfig);
    }

    /**
     * @Route("/Modifier/{id}", name="UcaGest_LogoParametrableModifier",requirements={"id"="\d+"}, methods={"GET", "POST"})
     * @Isgranted("ROLE_GESTION_PARAMETRAGE")
     */
    public function modifierAction(Request $request, LogoParametrable $logoParametrable, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $form = $this->createForm(LogoParametrableType::class, $logoParametrable);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($logoParametrable);
            $em->flush();
            $flashBag->addActionFlashBag($logoParametrable, 'Modifier');

            return $this->redirectToRoute('UcaGest_LogoParametrableLister');
        }
        $twigConfig['item'] = $logoParametrable;
        $twigConfig['codeListe'] = 'logoparametrable.modifier';
        $twigConfig['form'] = $form->createView();

        return $this->render('UcaBundle/UcaGest/Parametrage/Logo/Formulaire.html.twig', $twigConfig);
    }
}
