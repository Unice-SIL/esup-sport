<?php

/*
 * Classe - EtablissementController
 *
 * Gestion du CRUD pour les établissements
*/

namespace App\Controller\UcaGest\Referentiel;

use App\Form\EtablissementType;
use App\Service\Common\FlashBag;
use App\Entity\Uca\Etablissement;
use Doctrine\ORM\EntityManagerInterface;
use App\Datatables\EtablissementDatatable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sg\DatatablesBundle\Datatable\DatatableFactory;
use Sg\DatatablesBundle\Response\DatatableResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("UcaGest/Etablissement")
 * @Security("is_granted('ROLE_ADMIN')")
 */
class EtablissementController extends AbstractController
{
    /**
     * @Route("/", name="UcaGest_EtablissementLister")
     * @Isgranted("ROLE_GESTION_ETABLISSEMENT_LECTURE")
     */
    public function listerAction(Request $request, DatatableFactory $datatableFactory, DatatableResponse $datatableResponse)
    {
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $datatableFactory->create(EtablissementDatatable::class);
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
        if (!$usr->hasRole('ROLE_GESTION_ETABLISSEMENT_ECRITURE')) {
            $twigConfig['noAddButton'] = true;
        }
        $twigConfig['codeListe'] = 'Etablissement';

        return $this->render('UcaBundle/Common/Liste/Datatable.html.twig', $twigConfig);
    }

    /**
     * @Route("/Ajouter", name="UcaGest_EtablissementAjouter", methods={"GET", "POST"})
     * @Isgranted("ROLE_GESTION_ETABLISSEMENT_ECRITURE")
     */
    public function ajouterAction(Request $request, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $item = new Etablissement($em);
        $form = $this->createForm(EtablissementType::class, $item);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($item);
            $em->flush();
            $flashBag->addActionFlashBag($item, 'Ajouter');

            return $this->redirectToRoute('UcaGest_EtablissementLister');
        }
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();

        return $this->render('UcaBundle/UcaGest/Referentiel/Etablissement/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Modifier/{id}", name="UcaGest_EtablissementModifier", methods={"GET", "POST"})
     * @Isgranted("ROLE_GESTION_ETABLISSEMENT_ECRITURE")
     */
    public function modifierAction(Request $request, Etablissement $item, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $form = $this->createForm(EtablissementType::class, $item);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->flush();
            $flashBag->addActionFlashBag($item, 'Modifier');

            return $this->redirectToRoute('UcaGest_EtablissementLister');
        }
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();

        return $this->render('UcaBundle/UcaGest/Referentiel/Etablissement/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Supprimer/{id}", name="UcaGest_EtablissementSupprimer")
     * @Isgranted("ROLE_GESTION_ETABLISSEMENT_ECRITURE")
     */
    public function supprimerAction(Request $request, Etablissement $item, FlashBag $flashBag, EntityManagerInterface $em)
    {
        if (!$item->getRessources()->isEmpty()) {
            $flashBag->addActionErrorFlashBag($item, 'Supprimer');

            return $this->redirectToRoute('UcaGest_EtablissementLister');
        }
        $em->remove($item);
        $em->flush();
        $flashBag->addActionFlashBag($item, 'Supprimer');

        return $this->redirectToRoute('UcaGest_EtablissementLister');
    }

    /**
     * @Route("/{id}", name="UcaGest_EtablissementVoir")
     * @Isgranted("ROLE_GESTION_ETABLISSEMENT_LECTURE")
     */
    public function voirAction(Request $request, Etablissement $item)
    {
        return $this->render('UcaBundle/UcaGest/Referentiel/Etablissement/Voir.html.twig', ['item' => $item]);
    }
}
