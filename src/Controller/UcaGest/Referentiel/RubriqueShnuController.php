<?php

/*
 * Classe - RubriqueShnuController
 *
 * Gestion de la partie sport de haut niveau
 * Partie Rubrique SHNU : CRUD
*/

namespace App\Controller\UcaGest\Referentiel;

use App\Datatables\ShnuRubriqueDatatable;
use App\Entity\Uca\ShnuRubrique;
use App\Form\RubriqueShnuType;
use App\Service\Common\FlashBag;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sg\DatatablesBundle\Datatable\DatatableFactory;
use Sg\DatatablesBundle\Response\DatatableResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Security("is_granted('ROLE_ADMIN')")
 * @Route("UcaGest/Shnu")
 */
class RubriqueShnuController extends AbstractController
{
    /**
     * @Route("/Rubrique", name="UcaGest_Shnu_RubriqueLister")
     * @Isgranted("ROLE_GESTION_SHNU_RUBRIQUE_LECTURE")
     */
    public function listerAction(Request $request, DatatableFactory $datatableFactory, DatatableResponse $datatableResponse)
    {
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $datatableFactory->create(ShnuRubriqueDatatable::class);
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
        if (!$usr->hasRole('ROLE_GESTION_SHNU_RUBRIQUE_ECRITURE')) {
            $twigConfig['noAddButton'] = true;
        }
        $twigConfig['codeListe'] = 'ShnuRubrique';

        return $this->render('UcaBundle/UcaGest/Referentiel/Shnu/Rubrique/Datatable.html.twig', $twigConfig);
    }

    /**
     * @Route("/Rubrique/Ajouter", name="UcaGest_ShnuRubriqueAjouter", methods={"GET", "POST"})
     * @Isgranted("ROLE_GESTION_SHNU_RUBRIQUE_ECRITURE")
     */
    public function ajouterRubriqueAction(Request $request, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $item = new ShnuRubrique($em);
        $form = $this->createForm(RubriqueShnuType::class, $item);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($item);
            $em->flush();
            $flashBag->addActionFlashBag($item, 'Ajouter');

            return $this->redirectToRoute('UcaGest_Shnu_RubriqueLister');
        }

        $twigConfig['titre'] = 'shnu.rubrique';
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();

        return $this->render('UcaBundle/UcaGest/Referentiel/Shnu/Rubrique/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Rubrique/Supprimer/{id}", name="UcaGest_ShnuRubriqueSupprimer")
     * @Isgranted("ROLE_GESTION_SHNU_RUBRIQUE_ECRITURE")
     */
    public function supprimerAction(Request $request, ShnuRubrique $rubriqueSHNU, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $numeroOrdreMaxActuel = $em->getRepository(ShnuRubrique::class)->max('ordre');
        for ($numeroOrdre = $rubriqueSHNU->getOrdre() + 1; $numeroOrdre <= $numeroOrdreMaxActuel; ++$numeroOrdre) {
            $rubriqueAffecteParChangement = $em->getRepository(ShnuRubrique::class)->findOneBy(['ordre' => $numeroOrdre]);
            $rubriqueAffecteParChangement->setOrdre($numeroOrdre - 1);
        }

        $em->remove($rubriqueSHNU);
        $em->flush();
        $flashBag->addActionFlashBag($rubriqueSHNU, 'Supprimer');

        return $this->redirectToRoute('UcaGest_Shnu_RubriqueLister');
    }

    /**
     * @Route("/Rubrique/Modifier/{id}", name="UcaGest_ShnuRubriqueModifier",requirements={"id"="\d+"}, methods={"GET", "POST"})
     * @Isgranted("ROLE_GESTION_SHNU_RUBRIQUE_ECRITURE")
     */
    public function modifierAction(Request $request, ShnuRubrique $rubriqueSHNU, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $form = $this->createForm(RubriqueShnuType::class, $rubriqueSHNU);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->flush();
            $flashBag->addActionFlashBag($rubriqueSHNU, 'Modifier');

            return $this->redirectToRoute('UcaGest_Shnu_RubriqueLister');
        }

        $twigConfig['titre'] = 'shnu.rubrique';
        $twigConfig['item'] = $rubriqueSHNU;
        $twigConfig['form'] = $form->createView();

        return $this->render('UcaBundle/UcaGest/Referentiel/Shnu/Rubrique/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Rubrique/ModifierOrdre/{id}/{action}", name="UcaGest_ShnuRubriqueModifierOrdre", options={"expose"=true}, requirements={"id"="\d+"}, methods={"GET"})
     * @Isgranted("ROLE_GESTION_SHNU_RUBRIQUE_ECRITURE")
     *
     * @param mixed $action
     */
    public function modifierOrdreAction(Request $request, ShnuRubrique $rubriqueSHNU, $action, EntityManagerInterface $em)
    {
        if ('monter' == $action) {
            $delta = -1;
            $modificationPossible = $rubriqueSHNU->getOrdre() > 1;
        } else {
            $delta = +1;
            $modificationPossible = $rubriqueSHNU->getOrdre() < $em->getRepository(ShnuRubrique::class)->max('ordre');
        }

        if ($modificationPossible) {
            $oldOrdre = $rubriqueSHNU->getOrdre();
            $newOrdre = $rubriqueSHNU->getOrdre() + $delta;
            $rubriqueSHNUAffecteParChangement = $em->getRepository(ShnuRubrique::class)->findOneBy(['ordre' => $newOrdre]);
            $rubriqueSHNU->setOrdre($newOrdre);
            $rubriqueSHNUAffecteParChangement->setOrdre($oldOrdre);

            $em->flush();

            return new Response(200);
        }

        return new Response(204);
    }
}
