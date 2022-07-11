<?php

/*
 * Classe - ActiviteController
 *
 * Gestion du CRUD pour les activités
*/

namespace App\Controller\UcaGest\Activite;

use App\Service\Common\FlashBag;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Datatables\ActiviteDatatable;
use App\Datatables\FormatActiviteDatatable;
use App\Entity\Uca\Activite;
use App\Form\ActiviteType;
use App\Repository\ActiviteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sg\DatatablesBundle\Datatable\DatatableFactory;
use Sg\DatatablesBundle\Response\DatatableResponse;

/**
 * @Security("is_granted('ROLE_ADMIN')")
 * @Route("UcaGest/Activite")
 */
class ActiviteController extends AbstractController
{
    /**
     * @Route("/", name="UcaGest_ActiviteLister", options = {"expose" = true} , requirements={"id"="\d+"}, methods={"GET"})
     * @Isgranted("ROLE_GESTION_ACTIVITE_LECTURE")
     */
    public function listerAction(Request $request, DatatableFactory $datatableFactory, DatatableResponse $datatableResponse)
    {
        $datatable = $datatableFactory->create(ActiviteDatatable::class);
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;

        if ($request->isXmlHttpRequest()) {
            $responseService = $datatableResponse;
            $responseService->setDatatable($datatable);
            $responseService->getDatatableQueryBuilder();

            return $responseService->getResponse();
        }
        $twigConfig['codeListe'] = 'Activite';
        $usr = $this->getUser();
        if (!$usr->hasRole('ROLE_GESTION_ACTIVITE_ECRITURE')) {
            $twigConfig['noAddButton'] = true;
        }

        return $this->render('UcaBundle/UcaGest/Activite/Activite/Datatable.html.twig', $twigConfig);
    }

    /**
     * @Isgranted("ROLE_GESTION_ACTIVITE_ECRITURE")
     * @Route("/Ajouter", name="UcaGest_ActiviteAjouter", methods={"GET", "POST"})
     */
    public function ajouterAction(Request $request, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $item = new Activite($em);
        $form = $this->createForm(ActiviteType::class, $item); //, $item, ['form_mode' => 'Ajouter']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $request->isMethod('POST')) {
            $em->persist($item);
            $em->flush();
            $flashBag->addActionFlashBag($item, 'Ajouter');

            return $this->redirectToRoute('UcaGest_ActiviteLister');
        }

        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();

        return $this->render('UcaBundle/UcaGest/Activite/Activite/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Isgranted("ROLE_GESTION_ACTIVITE_ECRITURE")
     * @Route("/{id}/Modifier", name="UcaGest_ActiviteModifier", methods={"GET", "POST"})
     */
    public function modifierAction(Request $request, Activite $activite, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $editForm = $this->createForm('App\Form\ActiviteType', $activite); //$activite, ['form_mode' => 'Modifier']);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid() && $request->isMethod('POST')) {
            $flashBag->addActionFlashBag($activite, 'Modifier');
            $em->flush();

            return $this->redirectToRoute('UcaGest_ActiviteLister');
        }

        $twigConfig['item'] = $activite;
        $twigConfig['form'] = $editForm->createView();

        return $this->render('UcaBundle/UcaGest/Activite/Activite/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Isgranted("ROLE_GESTION_ACTIVITE_ECRITURE")
     * @Route("/{id}/Supprimer", name="UcaGest_ActiviteSupprimer")
     */
    public function supprimerAction(Request $request, Activite $activite, FlashBag $flashBag, EntityManagerInterface $em, ActiviteRepository $activiteRepo)
    {
        if (!$activite->getFormatsActivite()->isEmpty()) {
            $flashBag->addActionErrorFlashBag($activite, 'Supprimer');

            return $this->redirectToRoute('UcaGest_ActiviteLister');
        }
        if ($ordreSupprime = (null !== $activite->getOrdre() ? $activite->getOrdre() : false)) {
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
        $flashBag->addActionFlashBag($activite, 'Supprimer');

        return $this->redirectToRoute('UcaGest_ActiviteLister');
    }

    /**
     * @Isgranted("ROLE_GESTION_ACTIVITE_LECTURE")
     * @Route("/{id}", name="UcaGest_ActiviteVoir", methods={"GET"})
     */
    public function voirAction(Request $request, Activite $activite, DatatableFactory $datatableFactory, DatatableResponse $datatableResponse, EntityManagerInterface $em)
    {
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $datatableFactory->create(FormatActiviteDatatable::class);
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;
        if ($isAjax) {
            $responseService = $datatableResponse;
            $responseService->setDatatable($datatable);
            $dtQueryBuilder = $responseService->getDatatableQueryBuilder();
            $qb = $dtQueryBuilder->getQb();
            // dump($qb); die;

            $qb->andWhere('activite.id = :objectId');
            $qb->setParameter('objectId', $activite->getId());

            return $responseService->getResponse();
        }
        $twigConfig['item'] = $activite;

        return $this->render('UcaBundle/UcaGest/Activite/Activite/Voir.html.twig', $twigConfig);
    }

    /**
     * @Isgranted("ROLE_GESTION_ACTIVITE_ECRITURE")
     * @Route("/ModifierOrdre/{id}/{action}", name="UcaGest_ActiviteModifierOrdre", options={"expose"=true}, requirements={"id"="\d+"}, methods={"GET"})
     *
     * @param string $action
     *
     * @return void
     */
    public function modifierOrdreAction(Request $request, Activite $activite, $action, EntityManagerInterface $em, ActiviteRepository $activiteRepo)
    {
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
