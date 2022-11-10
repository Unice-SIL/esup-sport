<?php

/*
 * Classe - ShnuControlle
 *
 * Gestion de la partie sport de haut niveau
 * Partie highlight : CRUD
*/

namespace App\Controller\UcaGest\Referentiel;

use App\Form\HighlightType;
use App\Service\Common\FlashBag;
use App\Entity\Uca\ShnuHighlight;
use Doctrine\ORM\EntityManagerInterface;
use App\Datatables\ShnuHighlightDatatable;
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
 * @Route("UcaGest/Shnu")
 */
class ShnuController extends AbstractController
{
    /**
     * @Route("/Highlight", name="UcaGest_Shnu_HighlightLister")
     * @Isgranted("ROLE_GESTION_SHNU_HIGHLIGHT_LECTURE")
     */
    public function listeHighlightAction(Request $request, DatatableFactory $datatableFactory, DatatableResponse $datatableResponse)
    {
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $datatableFactory->create(ShnuHighlightDatatable::class);
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
        if (!$usr->hasRole('ROLE_GESTION_SHNU_HIGHLIGHT_ECRITURE')) {
            $twigConfig['noAddButton'] = true;
        }
        $twigConfig['codeListe'] = 'ShnuHighlight';

        return $this->render('UcaBundle/UcaGest/Referentiel/Shnu/Highlight/Datatable.html.twig', $twigConfig);
    }

    /**
     * @Route("/Highlight/Ajouter", name="UcaGest_ShnuHighlightAjouter", methods={"GET", "POST"})
     * @Isgranted("ROLE_GESTION_SHNU_HIGHLIGHT_ECRITURE")
     */
    public function ajouterHighlightAction(Request $request, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $item = new ShnuHighlight($em);
        $form = $this->createForm(HighlightType::class, $item, ['data_class' => 'App\Entity\Uca\ShnuHighlight']);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            if (null != $item->getVideo()) {
                $em->persist($item);
                $em->flush();
                $flashBag->addActionFlashBag($item, 'Ajouter');
            } else {
                $flashBag->addActionErrorFlashBag($item, 'Ajouter');
            }

            return $this->redirectToRoute('UcaGest_Shnu_HighlightLister');
        }

        $twigConfig['titre'] = 'shnu.highlight';
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();

        return $this->render('UcaBundle/UcaGest/Referentiel/Highlight/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Highlight/Supprimer/{id}", name="UcaGest_ShnuHighlightSupprimer")
     * @Isgranted("ROLE_GESTION_SHNU_HIGHLIGHT_ECRITURE")
     */
    public function supprimerHighlightAction(Request $request, ShnuHighlight $shnuHighlight, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $numeroOrdreMaxActuel = $em->getRepository(ShnuHighlight::class)->max('ordre');
        for ($numeroOrdre = $shnuHighlight->getOrdre() + 1; $numeroOrdre <= $numeroOrdreMaxActuel; ++$numeroOrdre) {
            $logoPartenaireAffecteParChangement = $em->getRepository(ShnuHighlight::class)->findOneBy(['ordre' => $numeroOrdre]);
            $logoPartenaireAffecteParChangement->setOrdre($numeroOrdre - 1);
        }

        $em->remove($shnuHighlight);
        $em->flush();
        $flashBag->addActionFlashBag($shnuHighlight, 'Supprimer');

        return $this->redirectToRoute('UcaGest_Shnu_HighlightLister');
    }

    /**
     * @Route("/Modifier/{id}", name="UcaGest_ShnuHighlightModifier",requirements={"id"="\d+"}, methods={"GET", "POST"})
     * @Isgranted("ROLE_GESTION_SHNU_HIGHLIGHT_ECRITURE")
     */
    public function modifierAction(Request $request, ShnuHighlight $shnuHighlight, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $form = $this->createForm(HighlightType::class, $shnuHighlight, ['data_class' => 'App\Entity\Uca\SHnuHighlight']);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->flush();
            $flashBag->addActionFlashBag($shnuHighlight, 'Modifier');

            return $this->redirectToRoute('UcaGest_Shnu_HighlightLister');
        }

        $twigConfig['titre'] = 'shnu.highlight';
        $twigConfig['item'] = $shnuHighlight;
        $twigConfig['form'] = $form->createView();

        return $this->render('UcaBundle/UcaGest/Referentiel/Highlight/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Highlight/ModifierOrdre/{id}/{action}", name="UcaGest_ShnuHighlightModifierOrdre", options={"expose"=true}, requirements={"id"="\d+"}, methods={"GET"})
     * @Isgranted("ROLE_GESTION_SHNU_HIGHLIGHT_ECRITURE")
     *
     * @param mixed $action
     */
    public function monterOrdreShnuHighlight(Request $request, ShnuHighlight $shnuHighlight, $action, EntityManagerInterface $em)
    {
        if ('monter' == $action) {
            $delta = -1;
            $modificationPossible = $shnuHighlight->getOrdre() > 1;
        } else {
            $delta = +1;
            $modificationPossible = $shnuHighlight->getOrdre() < $em->getRepository(ShnuHighlight::class)->max('ordre');
        }

        if ($modificationPossible) {
            $oldOrdre = $shnuHighlight->getOrdre();
            $newOrdre = $shnuHighlight->getOrdre() + $delta;
            $shnuHighlightAffecteParChangement = $em->getRepository(ShnuHighlight::class)->findOneBy(['ordre' => $newOrdre]);
            $shnuHighlight->setOrdre($newOrdre);
            $shnuHighlightAffecteParChangement->setOrdre($oldOrdre);

            $em->flush();

            return new Response(200);
        }

        return new Response(204);
    }
}
