<?php

/*
 * Classe - ActiviteController
 *
 * Gestion du CRUD pour les activités
*/

namespace UcaBundle\Controller\UcaGest\Activite;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Datatables\ActiviteDatatable;
use UcaBundle\Datatables\FormatActiviteDatatable;
use UcaBundle\Entity\Activite;
use UcaBundle\Form\ActiviteType;

/**
 * @Security("has_role('ROLE_ADMIN')")
 * @Route("UcaGest/Activite")
 */
class ActiviteController extends Controller
{
    /**
     * @Route("/", name="UcaGest_ActiviteLister", options = {"expose" = true} , requirements={"id"="\d+"}, methods={"GET"})
     * @Isgranted("ROLE_GESTION_ACTIVITE_LECTURE")
     */
    public function listerAction(Request $request)
    {
        $datatable = $this->get('sg_datatables.factory')->create(ActiviteDatatable::class);
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;

        if ($request->isXmlHttpRequest()) {
            $responseService = $this->get('sg_datatables.response');
            $responseService->setDatatable($datatable);
            $responseService->getDatatableQueryBuilder();

            return $responseService->getResponse();
        }
        $twigConfig['codeListe'] = 'Activite';
        $usr = $this->container->get('security.token_storage')->getToken()->getUser();
        if (!$usr->hasRole('ROLE_GESTION_ACTIVITE_ECRITURE')) {
            $twigConfig['noAddButton'] = true;
        }

        return $this->render('@Uca/UcaGest/Activite/Activite/Datatable.html.twig', $twigConfig);
    }

    /**
     * @Isgranted("ROLE_GESTION_ACTIVITE_ECRITURE")
     * @Route("/Ajouter", name="UcaGest_ActiviteAjouter", methods={"GET", "POST"})
     */
    public function ajouterAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $item = new Activite($em);
        $form = $this->get('form.factory')->create(ActiviteType::class, $item); //, $item, ['form_mode' => 'Ajouter']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $request->isMethod('POST')) {
            $em->persist($item);
            $em->flush();
            $this->get('uca.flashbag')->addActionFlashBag($item, 'Ajouter');

            return $this->redirectToRoute('UcaGest_ActiviteLister');
        }

        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();

        return $this->render('@Uca/UcaGest/Activite/Activite/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Isgranted("ROLE_GESTION_ACTIVITE_ECRITURE")
     * @Route("/{id}/Modifier", name="UcaGest_ActiviteModifier", methods={"GET", "POST"})
     */
    public function modifierAction(Request $request, Activite $activite)
    {
        $em = $this->getDoctrine()->getManager();
        $editForm = $this->createForm('UcaBundle\Form\ActiviteType', $activite); //$activite, ['form_mode' => 'Modifier']);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid() && $request->isMethod('POST')) {
            $this->get('uca.flashbag')->addActionFlashBag($activite, 'Modifier');
            $em->flush();

            return $this->redirectToRoute('UcaGest_ActiviteLister');
        }

        $twigConfig['item'] = $activite;
        $twigConfig['form'] = $editForm->createView();

        return $this->render('@Uca/UcaGest/Activite/Activite/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Isgranted("ROLE_GESTION_ACTIVITE_ECRITURE")
     * @Route("/{id}/Supprimer", name="UcaGest_ActiviteSupprimer")
     */
    public function supprimerAction(Request $request, Activite $activite)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$activite->getFormatsActivite()->isEmpty()) {
            $this->get('uca.flashbag')->addActionErrorFlashBag($activite, 'Supprimer');

            return $this->redirectToRoute('UcaGest_ActiviteLister');
        }
        if ($ordreSupprime = (null !== $activite->getOrdre() ? $activite->getOrdre() : false)) {
            $activiteRepo = $em->getRepository('UcaBundle:Activite');
            $max = $activiteRepo->maxOrdreActivite();
            if ($max !== $ordreSupprime && null !== $ordreSupprime) {
                for ($i = $ordreSupprime; $i < $max; ++$i) {
                    $activiteImpactee = $activiteRepo->findOneBy(['ordre' => $i + 1]);
                    $activiteImpactee->setOrdre($i);
                    $em->persist($activiteImpactee);
                }
            }
        }
        $em->remove($activite);
        $em->flush();
        $this->get('uca.flashbag')->addActionFlashBag($activite, 'Supprimer');

        return $this->redirectToRoute('UcaGest_ActiviteLister');
    }

    /**
     * @Isgranted("ROLE_GESTION_ACTIVITE_LECTURE")
     * @Route("/{id}", name="UcaGest_ActiviteVoir", methods={"GET"})
     */
    public function voirAction(Request $request, Activite $activite)
    {
        $em = $this->getDoctrine()->getManager();
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $this->get('sg_datatables.factory')->create(FormatActiviteDatatable::class);
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;
        if ($isAjax) {
            $responseService = $this->get('sg_datatables.response');
            $responseService->setDatatable($datatable);
            $dtQueryBuilder = $responseService->getDatatableQueryBuilder();
            $qb = $dtQueryBuilder->getQb();
            // dump($qb); die;

            $qb->andWhere('activite.id = :objectId');
            $qb->setParameter('objectId', $activite->getId());

            return $responseService->getResponse();
        }
        $twigConfig['item'] = $activite;

        return $this->render('@Uca/UcaGest/Activite/Activite/Voir.html.twig', $twigConfig);
    }

    /**
     * @Isgranted("ROLE_GESTION_ACTIVITE_ECRITURE")
     * @Route("/ModifierOrdre/{id}/{action}", name="UcaGest_ActiviteModifierOrdre", options={"expose"=true}, requirements={"id"="\d+"}, methods={"GET"})
     *
     * @param string $action
     *
     * @return void
     */
    public function modifierOrdreAction(Request $request, Activite $activite, $action)
    {
        $em = $this->getDoctrine()->getManager();
        $activiteRepo = $em->getRepository('UcaBundle:Activite');
        $max = $activiteRepo->maxOrdreActivite();
        $ancienOrdre = $activite->getOrdre();
        $estPremier = 1 == $ancienOrdre;
        $estDernier = $max == $ancienOrdre;
        $nonInitialise = null === $max;

        if ('monter' === $action && !$estPremier && !$nonInitialise && null !== $ancienOrdre) {
            $delta = -1;
        } elseif ('descendre' === $action && !$estDernier && !$nonInitialise && null !== $ancienOrdre) {
            $delta = +1;
        } elseif (null === $ancienOrdre && !$nonInitialise) {
            $ordre = $max + 1;
        } elseif ($nonInitialise) {
            $ordre = 0;
        } else {
            return new Response(200);
        }

        if (!isset($ordre)) {
            $ordre = $ancienOrdre + $delta;
            $activiteImpactee = $activiteRepo->findOneBy(['ordre' => $ordre]);
            $activiteImpactee->setOrdre($ancienOrdre);
            $em->persist($activiteImpactee);
        }

        $activite->setOrdre($ordre);
        $em->persist($activite);
        $em->flush();

        return new Response(200);
    }
}
