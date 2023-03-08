<?php

namespace App\Controller\UcaGest\Parametrage;

use App\Datatables\PeriodeFermetureDatatable;
use App\Entity\Uca\PeriodeFermeture;
use App\Form\PeriodeFermetureType;
use App\Repository\PeriodeFermetureRepository;
use App\Service\Common\FlashBag;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sg\DatatablesBundle\Datatable\DatatableFactory;
use Sg\DatatablesBundle\Response\DatatableResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Security("is_granted('ROLE_ADMIN')")
 * @Route("UcaGest/PeriodeFermeture")
 */
class PeriodeFermetureController extends AbstractController
{
    /**
     * @Route("/", name="UcaGest_PeriodeFermetureLister", methods={"GET"})
     * @Isgranted("ROLE_GESTION_PARAMETRAGE")
     */
    public function listerAction(Request $request, DatatableFactory $datatableFactory, DatatableResponse $datatableResponse)
    {
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $datatableFactory->create(PeriodeFermetureDatatable::class);
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;
        if ($isAjax) {
            $responseService = $datatableResponse;
            $responseService->setDatatable($datatable);
            $dtQueryBuilder = $responseService->getDatatableQueryBuilder();
            $qb = $dtQueryBuilder->getQb();

            return $responseService->getResponse();
        }
        $twigConfig['codeListe'] = 'PeriodeFermeture';

        return $this->render('UcaBundle/Common/Liste/Datatable.html.twig', $twigConfig);
    }

    /**
     * @Route("/Ajouter", name="UcaGest_PeriodeFermetureAjouter", methods={"GET", "POST"})
     */
    public function ajouterAction(Request $request, FlashBag $flashBag, EntityManagerInterface $em): Response
    {
        $periodeFermeture = new PeriodeFermeture();
        $form = $this->createForm(PeriodeFermetureType::class, $periodeFermeture);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($periodeFermeture);
            $em->flush();
            $flashBag->addActionFlashBag($periodeFermeture, 'Ajouter');

            return $this->redirectToRoute('UcaGest_PeriodeFermetureLister');
        }
        $twigConfig['item'] = $periodeFermeture;
        $twigConfig['codeListe'] = 'periodefermeture.ajouter';
        $twigConfig['form'] = $form->createView();

        return $this->render('UcaBundle/UcaGest/Parametrage/PeriodeFermeture/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Modifier/{id}", name="UcaGest_PeriodeFermetureModifier", methods={"GET", "POST"})
     * @Isgranted("ROLE_GESTION_PARAMETRAGE")
     */
    public function modifierAction(Request $request, PeriodeFermeture $periodeFermeture, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $form = $this->createForm(PeriodeFermetureType::class, $periodeFermeture);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($periodeFermeture);
            $em->flush();
            $flashBag->addActionFlashBag($periodeFermeture, 'Modifier');

            return $this->redirectToRoute('UcaGest_PeriodeFermetureLister');
        }
        $twigConfig['item'] = $periodeFermeture;
        $twigConfig['codeListe'] = 'periodefermeture.modifier';
        $twigConfig['form'] = $form->createView();

        return $this->render('UcaBundle/UcaGest/Parametrage/PeriodeFermeture/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Supprimer/{id}", name="UcaGest_PeriodeFermetureSupprimer", methods={"GET"})
     * @Isgranted("ROLE_GESTION_PARAMETRAGE")
     */
    public function supprimerAction(Request $request, PeriodeFermeture $periodeFermeture, FlashBag $flashBag, EntityManagerInterface $em): Response
    {
        $em->remove($periodeFermeture);
        $em->flush();
        $flashBag->addActionFlashBag($periodeFermeture, 'Supprimer');

        return $this->redirectToRoute('UcaGest_PeriodeFermetureLister');
    }
}
