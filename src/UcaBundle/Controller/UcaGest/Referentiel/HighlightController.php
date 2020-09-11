<?php

/*
 * Classe - HighlightController
 *
 * Gestion du CRUD pour les highlights
*/

namespace UcaBundle\Controller\UcaGest\Referentiel;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Datatables\HighlightDatatable;
use UcaBundle\Entity\Highlight;
use UcaBundle\Form\HighlightType;

/**
 * @Security("has_role('ROLE_ADMIN')")
 * @Route("UcaGest/Highlight")
 */
class HighlightController extends Controller
{
    /**
     * @Route("/", name="UcaGest_HighlightLister")
     * @Isgranted("ROLE_GESTION_HIGHLIGHT_LECTURE")
     */
    public function listerAction(Request $request)
    {
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $this->get('sg_datatables.factory')->create(HighlightDatatable::class);
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
        if (!$usr->hasRole('ROLE_GESTION_HIGHLIGHT_ECRITURE')) {
            $twigConfig['noAddButton'] = true;
        }
        $twigConfig['codeListe'] = 'Highlight';

        return $this->render('@Uca/UcaGest/Referentiel/Highlight/Datatable.html.twig', $twigConfig);
    }

    /**
     * @Route("/Ajouter", name="UcaGest_HighlightAjouter", methods={"GET", "POST"})
     * @Isgranted("ROLE_GESTION_HIGHLIGHT_ECRITURE")
     */
    public function ajouterAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $item = new Highlight($em);
        $form = $this->get('form.factory')->create(HighlightType::class, $item, ['data_class' => 'UcaBundle\Entity\Highlight']);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            if (null != $item->getVideo()) {
                $em->persist($item);
                $em->flush();
                $this->get('uca.flashbag')->addActionFlashBag($item, 'Ajouter');
            } else {
                $this->get('uca.flashbag')->addActionErrorFlashBag($item, 'Ajouter');
            }

            return $this->redirectToRoute('UcaGest_HighlightLister');
        }

        $twigConfig['titre'] = 'highlight';
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();

        return $this->render('@Uca/UcaGest/Referentiel/Highlight/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Supprimer/{id}", name="UcaGest_HighlightSupprimer")
     * @Isgranted("ROLE_GESTION_HIGHLIGHT_ECRITURE")
     */
    public function supprimerAction(Request $request, Highlight $highlight)
    {
        $em = $this->getDoctrine()->getManager();

        $numeroOrdreMaxActuel = $em->getRepository(Highlight::class)->max('ordre');
        for ($numeroOrdre = $highlight->getOrdre() + 1; $numeroOrdre <= $numeroOrdreMaxActuel; ++$numeroOrdre) {
            $logoPartenaireAffecteParChangement = $em->getRepository(Highlight::class)->findOneBy(['ordre' => $numeroOrdre]);
            $logoPartenaireAffecteParChangement->setOrdre($numeroOrdre - 1);
        }

        $em->remove($highlight);
        $em->flush();
        $this->get('uca.flashbag')->addActionFlashBag($highlight, 'Supprimer');

        return $this->redirectToRoute('UcaGest_HighlightLister');
    }

    /**
     * @Route("/Modifier/{id}", name="UcaGest_HighlightModifier",requirements={"id"="\d+"}, methods={"GET", "POST"})
     * @Isgranted("ROLE_GESTION_HIGHLIGHT_ECRITURE")
     */
    public function modifierAction(Request $request, Highlight $highlight)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->get('form.factory')->create(HighlightType::class, $highlight, ['data_class' => 'UcaBundle\Entity\Highlight']);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->flush();
            $this->get('uca.flashbag')->addActionFlashBag($highlight, 'Modifier');

            return $this->redirectToRoute('UcaGest_HighlightLister');
        }
        $twigConfig['titre'] = 'highlight';
        $twigConfig['item'] = $highlight;
        $twigConfig['form'] = $form->createView();

        return $this->render('@Uca/UcaGest/Referentiel/Highlight/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/HighlightModifierOrdre/{id}/{action}", name="UcaGest_HighlightModifierOrdre", options={"expose"=true}, requirements={"id"="\d+"}, methods={"GET"})
     * @Isgranted("ROLE_GESTION_HIGHLIGHT_ECRITURE")
     *
     * @param mixed $action
     */
    public function monterOrdreHighlight(Request $request, Highlight $highlight, $action)
    {
        // $em = $this->getDoctrine()->getManager();
        // $highlights = $em->getRepository(Highlight::class)->findAll();
        // if ('monter' == $action) {
        //     $condition = $highlight->getOrdre() > 0;
        //     $oldOrdre = $highlight->getOrdre();
        //     $newOrdre = $highlight->getOrdre() - 1;
        // } elseif ('descendre' == $action) {
        //     $condition = $highlight->getOrdre() < count($highlights) - 1;
        //     $oldOrdre = $highlight->getOrdre();
        //     $newOrdre = $highlight->getOrdre() + 1;
        // }
        // // Si c'est le premier dans l'ordre et qu'on veut monter encore
        // if ($condition) {
        //     $actuAffecteeParChangement = $em->getRepository(Highlight::class)->findOneByOrdre($newOrdre);
        //     $actuAffecteeParChangement->setOrdre($oldOrdre);
        //     $highlight->setOrdre($newOrdre);
        //     $em->flush();

        //     return new Response(200);
        // }

        // return new Response(204);

        $em = $this->getDoctrine()->getManager();

        if ('monter' == $action) {
            $delta = -1;
            $modificationPossible = $highlight->getOrdre() > 1;
        } else {
            $delta = +1;
            $modificationPossible = $highlight->getOrdre() < $em->getRepository(Highlight::class)->max('ordre');
        }

        if ($modificationPossible) {
            $oldOrdre = $highlight->getOrdre();
            $newOrdre = $highlight->getOrdre() + $delta;
            $highlightAffecteParChangement = $em->getRepository(Highlight::class)->findOneBy(['ordre' => $newOrdre]);

            $highlight->setOrdre($newOrdre);
            $highlightAffecteParChangement->setOrdre($oldOrdre);

            $em->flush();

            return new Response(200);
        }

        return new Response(204);
    }
}
