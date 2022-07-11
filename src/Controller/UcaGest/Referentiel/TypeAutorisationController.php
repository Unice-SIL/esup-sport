<?php

/*
 * Classe - TypeAutorisationController
 *
 * Gestion du CRUD pour les types d'autorisation
*/

namespace App\Controller\UcaGest\Referentiel;

use App\Service\Common\FlashBag;
use App\Form\TypeAutorisationType;
use App\Entity\Uca\TypeAutorisation;
use Doctrine\ORM\EntityManagerInterface;
use App\Datatables\TypeAutorisationDatatable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sg\DatatablesBundle\Datatable\DatatableFactory;
use Sg\DatatablesBundle\Response\DatatableResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("UcaGest/TypeAutorisation")
 * @Security("is_granted('ROLE_ADMIN')")
 */
class TypeAutorisationController extends AbstractController
{
    /**
     * @Route("/", name="UcaGest_TypeAutorisationLister")
     * @Isgranted("ROLE_GESTION_TYPE_AUTORISATION_LECTURE")
     */
    public function listerAction(Request $request, DatatableFactory $datatableFactory, DatatableResponse $datatableResponse)
    {
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $datatableFactory->create(TypeAutorisationDatatable::class);
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
        if (!$usr->hasRole('ROLE_GESTION_TYPE_AUTORISATION_ECRITURE')) {
            $twigConfig['noAddButton'] = true;
        }
        $twigConfig['codeListe'] = 'TypeAutorisation';

        return $this->render('UcaBundle/Common/Liste/Datatable.html.twig', $twigConfig);
    }

    /**
     * @Route("/Ajouter", name="UcaGest_TypeAutorisationAjouter", methods={"GET", "POST"})
     * @Isgranted("ROLE_GESTION_TYPE_AUTORISATION_ECRITURE")
     */
    public function ajouterAction(Request $request, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $item = new TypeAutorisation($em);
        $form = $this->createForm(TypeAutorisationType::class, $item);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($item);
            $em->flush();
            $flashBag->addActionFlashBag($item, 'Ajouter');

            return $this->redirectToRoute('UcaGest_TypeAutorisationLister');
        }
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();

        return $this->render('UcaBundle/UcaGest/Referentiel/TypeAutorisation/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Modifier/{id}", name="UcaGest_TypeAutorisationModifier", methods={"GET", "POST"})
     * @Isgranted("ROLE_GESTION_TYPE_AUTORISATION_ECRITURE")
     */
    public function modifierAction(Request $request, TypeAutorisation $item, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $form = $this->createForm(TypeAutorisationType::class, $item);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->flush();
            $flashBag->addActionFlashBag($item, 'Modifier');

            return $this->redirectToRoute('UcaGest_TypeAutorisationLister');
        }
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();

        return $this->render('UcaBundle/UcaGest/Referentiel/TypeAutorisation/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Supprimer/{id}", name="UcaGest_TypeAutorisationSupprimer")
     * @Isgranted("ROLE_GESTION_TYPE_AUTORISATION_ECRITURE")
     */
    public function supprimerAction(Request $request, TypeAutorisation $item, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $em->remove($item);
        $em->flush();
        $flashBag->addActionFlashBag($item, 'Supprimer');

        return $this->redirectToRoute('UcaGest_TypeAutorisationLister');
    }
}
