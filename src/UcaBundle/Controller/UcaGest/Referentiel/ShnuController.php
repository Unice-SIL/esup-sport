<?php

/*
 * Classe - ShnuControlle
 *
 * Gestion de la partie sport de haut niveau
 * Partie highlight : CRUD
*/

namespace UcaBundle\Controller\UcaGest\Referentiel;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Datatables\ShnuHighlightDatatable;
use UcaBundle\Entity\ShnuHighlight;
use UcaBundle\Form\HighlightType;

/**
 * @Security("has_role('ROLE_ADMIN')")
 * @Route("UcaGest/Shnu")
 */
class ShnuController extends Controller
{
    /**
     * @Route("/Highlight", name="UcaGest_Shnu_HighlightLister")
     * @Isgranted("ROLE_GESTION_SHNU_HIGHLIGHT_LECTURE")
     */
    public function listeHighlightAction(Request $request)
    {
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $this->get('sg_datatables.factory')->create(ShnuHighlightDatatable::class);
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;
        if ($isAjax) {
            $responseService = $this->get('sg_datatables.response');
            $responseService->setDatatable($datatable);
            $dtQueryBuilder = $responseService->getDatatableQueryBuilder();
            $qb = $dtQueryBuilder->getQb();

            return $responseService->getResponse();
        }
        // Bouton Ajouter
        $usr = $this->container->get('security.token_storage')->getToken()->getUser();
        if (!$usr->hasRole('ROLE_GESTION_SHNU_HIGHLIGHT_ECRITURE')) {
            $twigConfig['noAddButton'] = true;
        }
        $twigConfig['codeListe'] = 'ShnuHighlight';

        return $this->render('@Uca/UcaGest/Referentiel/Shnu/Highlight/Datatable.html.twig', $twigConfig);
    }

    /**
     * @Route("/Highlight/Ajouter", name="UcaGest_ShnuHighlightAjouter", methods={"GET", "POST"})
     * @Isgranted("ROLE_GESTION_SHNU_HIGHLIGHT_ECRITURE")
     */
    public function ajouterHighlightAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $item = new ShnuHighlight($em);
        $form = $this->get('form.factory')->create(HighlightType::class, $item, ['data_class' => 'UcaBundle\Entity\ShnuHighlight']);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            if (null != $item->getVideo()) {
                $em->persist($item);
                $em->flush();
                $this->get('uca.flashbag')->addActionFlashBag($item, 'Ajouter');
            } else {
                $this->get('uca.flashbag')->addActionErrorFlashBag($item, 'Ajouter');
            }

            return $this->redirectToRoute('UcaGest_Shnu_HighlightLister');
        }

        $twigConfig['titre'] = 'shnu.highlight';
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();

        return $this->render('@Uca/UcaGest/Referentiel/Highlight/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Highlight/Supprimer/{id}", name="UcaGest_ShnuHighlightSupprimer")
     * @Isgranted("ROLE_GESTION_SHNU_HIGHLIGHT_ECRITURE")
     */
    public function supprimerHighlightAction(Request $request, ShnuHighlight $shnuHighlight)
    {
        $em = $this->getDoctrine()->getManager();

        $numeroOrdreMaxActuel = $em->getRepository(ShnuHighlight::class)->max('ordre');
        for ($numeroOrdre = $shnuHighlight->getOrdre() + 1; $numeroOrdre <= $numeroOrdreMaxActuel; ++$numeroOrdre) {
            $logoPartenaireAffecteParChangement = $em->getRepository(ShnuHighlight::class)->findOneBy(['ordre' => $numeroOrdre]);
            $logoPartenaireAffecteParChangement->setOrdre($numeroOrdre - 1);
        }

        $em->remove($shnuHighlight);
        $em->flush();
        $this->get('uca.flashbag')->addActionFlashBag($shnuHighlight, 'Supprimer');

        return $this->redirectToRoute('UcaGest_Shnu_HighlightLister');
    }

    /**
     * @Route("/Modifier/{id}", name="UcaGest_ShnuHighlightModifier",requirements={"id"="\d+"}, methods={"GET", "POST"})
     * @Isgranted("ROLE_GESTION_SHNU_HIGHLIGHT_ECRITURE")
     */
    public function modifierAction(Request $request, ShnuHighlight $shnuHighlight)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->get('form.factory')->create(HighlightType::class, $shnuHighlight, ['data_class' => 'UcaBundle\Entity\SHnuHighlight']);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->flush();
            $this->get('uca.flashbag')->addActionFlashBag($shnuHighlight, 'Modifier');

            return $this->redirectToRoute('UcaGest_Shnu_HighlightLister');
        }

        $twigConfig['titre'] = 'shnu.highlight';
        $twigConfig['item'] = $shnuHighlight;
        $twigConfig['form'] = $form->createView();

        return $this->render('@Uca/UcaGest/Referentiel/Highlight/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Highlight/ModifierOrdre/{id}/{action}", name="UcaGest_ShnuHighlightModifierOrdre", options={"expose"=true}, requirements={"id"="\d+"}, methods={"GET"})
     * @Isgranted("ROLE_GESTION_SHNU_HIGHLIGHT_ECRITURE")
     *
     * @param mixed $action
     */
    public function monterOrdreShnuHighlight(Request $request, ShnuHighlight $shnuHighlight, $action)
    {
        $em = $this->getDoctrine()->getManager();

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
