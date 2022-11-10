<?php

/*
 * Classe - TypeActiviteController
 *
 * Gestion du CRUD pour les types d'activités
*/

namespace App\Controller\UcaGest\Activite;

use App\Form\TypeActiviteType;
use App\Entity\Uca\TypeActivite;
use App\Service\Common\FlashBag;
use Doctrine\ORM\EntityManagerInterface;
use App\Datatables\TypeActiviteDatatable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sg\DatatablesBundle\Datatable\DatatableFactory;
use Sg\DatatablesBundle\Response\DatatableResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Security("is_granted('ROLE_ADMIN')")
 * @Route("UcaGest/TypeActivite")
 */
class TypeActiviteController extends AbstractController
{
    /**
     * @Route("/", name="UcaGest_TypeActiviteLister")
     * @Isgranted("ROLE_GESTION_TYPE_ACTIVITE_LECTURE")
     */
    public function listerAction(Request $request, DatatableFactory $datatableFactory, DatatableResponse $datatableResponse)
    {
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $datatableFactory->create(TypeActiviteDatatable::class);
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
        if (!$usr->hasRole('ROLE_GESTION_TYPE_ACTIVITE_ECRITURE')) {
            $twigConfig['noAddButton'] = true;
        }
        $twigConfig['codeListe'] = 'TypeActivite';

        return $this->render('UcaBundle/Common/Liste/Datatable.html.twig', $twigConfig);
    }

    /**
     * @Route("/Ajouter", name="UcaGest_TypeActiviteAjouter")
     * @Isgranted("ROLE_GESTION_TYPE_ACTIVITE_ECRITURE")
     */
    public function ajouterAction(Request $request, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $item = new TypeActivite($em);
        $form = $this->createForm(TypeActiviteType::class, $item);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($item);
            $em->flush();
            $flashBag->addActionFlashBag($item, 'Ajouter');

            return $this->redirectToRoute('UcaGest_TypeActiviteLister');
        }
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();

        return $this->render('UcaBundle/UcaGest/Activite/TypeActivite/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Modifier/{id}", name="UcaGest_TypeActiviteModifier")
     * @Isgranted("ROLE_GESTION_TYPE_ACTIVITE_ECRITURE")
     */
    public function modifierAction(Request $request, TypeActivite $item, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $form = $this->createForm(TypeActiviteType::class, $item);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($item);
            $em->flush();
            $flashBag->addActionFlashBag($item, 'Modifier');

            return $this->redirectToRoute('UcaGest_TypeActiviteLister');
        }
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();

        return $this->render('UcaBundle/UcaGest/Activite/TypeActivite/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Supprimer/{id}", name="UcaGest_TypeActiviteSupprimer")
     * @Isgranted("ROLE_GESTION_TYPE_ACTIVITE_ECRITURE")
     */
    public function supprimerAction(Request $request, TypeActivite $item, FlashBag $flashBag, EntityManagerInterface $em)
    {
        if (!$item->getClasseActivite()->isEmpty()) {
            $flashBag->addActionErrorFlashBag($item, 'Supprimer');

            return $this->redirectToRoute('UcaGest_TypeActiviteLister');
        }
        $em->remove($item);
        $em->flush();
        $flashBag->addActionFlashBag($item, 'Supprimer');

        return $this->redirectToRoute('UcaGest_TypeActiviteLister');
    }
}
