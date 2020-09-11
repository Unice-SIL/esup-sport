<?php

/*
 * Classe - Actualité:
 *
 * Gestion du CRUD pour les actualités
*/

namespace UcaBundle\Controller\UcaGest\Referentiel;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Datatables\ActualiteDatatable;
use UcaBundle\Entity\Actualite;
use UcaBundle\Form\ActualiteType;

/**
 * @Security("has_role('ROLE_ADMIN')")
 * @Route("UcaGest/Actualite")
 */
class ActualiteController extends Controller
{
    /**
     * @Route("/", name="UcaGest_ActualiteLister")
     * @Isgranted("ROLE_GESTION_ACTUALITE_LECTURE")
     */
    public function listerAction(Request $request)
    {
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $this->get('sg_datatables.factory')->create(ActualiteDatatable::class);
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
        if (!$usr->hasRole('ROLE_GESTION_ACTUALITE_ECRITURE')) {
            $twigConfig['noAddButton'] = true;
        }
        $twigConfig['codeListe'] = 'Actualite';

        return $this->render('@Uca/UcaGest/Referentiel/Actualite/Datatable.html.twig', $twigConfig);
    }

    /**
     * @Route("/Ajouter", name="UcaGest_ActualiteAjouter", methods={"GET", "POST"})
     * @Isgranted("ROLE_GESTION_ACTUALITE_ECRITURE")
     */
    public function ajouterAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $item = new Actualite();
        $item->setOrdre(count($this->getDoctrine()->getRepository(Actualite::class)->findAll()));
        $form = $this->get('form.factory')->create(ActualiteType::class, $item);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($item);
            $em->flush();
            $this->get('uca.flashbag')->addActionFlashBag($item, 'Ajouter');

            return $this->redirectToRoute('UcaGest_ActualiteLister');
        }

        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();

        return $this->render('@Uca/UcaGest/Referentiel/Actualite/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Supprimer/{id}", name="UcaGest_ActualiteSupprimer")
     * @Isgranted("ROLE_GESTION_ACTUALITE_ECRITURE")
     */
    public function supprimerAction(Request $request, Actualite $actualite)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($actualite);
        $em->flush();
        $this->get('uca.flashbag')->addActionFlashBag($actualite, 'Supprimer');

        return $this->redirectToRoute('UcaGest_ActualiteLister');
    }

    /**
     * @Route("/Modifier/{id}", name="UcaGest_ActualiteModifier",requirements={"id"="\d+"}, methods={"GET", "POST"})
     * @Isgranted("ROLE_GESTION_ACTUALITE_ECRITURE")
     */
    public function modifierAction(Request $request, Actualite $actualite)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->get('form.factory')->create(ActualiteType::class, $actualite);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($actualite);
            $em->flush();
            $this->get('uca.flashbag')->addActionFlashBag($actualite, 'Modifier');

            return $this->redirectToRoute('UcaGest_ActualiteLister');
        }
        $twigConfig['item'] = $actualite;
        $twigConfig['form'] = $form->createView();

        return $this->render('@Uca/UcaGest/Referentiel/Actualite/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/ActualiteModifierOrdre/{id}/{action}", name="UcaGest_ActualiteModifierOrdre", options={"expose"=true}, requirements={"id"="\d+"}, methods={"GET"})
     * @Isgranted("ROLE_GESTION_ACTUALITE_ECRITURE")
     *
     * @param mixed $action
     */
    public function monterOrdreActualite(Request $request, Actualite $actualite, $action)
    {
        $em = $this->getDoctrine()->getManager();
        $actualites = $em->getRepository(Actualite::class)->findAll();
        if ('monter' == $action) {
            $condition = $actualite->getOrdre() > 0;
            $oldOrdre = $actualite->getOrdre();
            $newOrdre = $actualite->getOrdre() - 1;
        } elseif ('descendre' == $action) {
            $condition = $actualite->getOrdre() < count($actualites) - 1;
            $oldOrdre = $actualite->getOrdre();
            $newOrdre = $actualite->getOrdre() + 1;
        }
        // Si c'est le premier dans l'ordre et qu'on veut monter encore
        if ($condition) {
            $actuAffecteeParChangement = $em->getRepository(Actualite::class)->findOneByOrdre($newOrdre);
            $actuAffecteeParChangement->setOrdre($oldOrdre);
            $actualite->setOrdre($newOrdre);
            $em->flush();

            return new Response(200);
        }

        return new Response(204);
    }
}
