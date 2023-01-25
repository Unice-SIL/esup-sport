<?php

/*
 * Classe - Actualité:
 *
 * Gestion du CRUD pour les actualités
*/

namespace App\Controller\UcaGest\Referentiel;

use App\Form\ActualiteType;
use App\Entity\Uca\Actualite;
use App\Service\Common\FlashBag;
use App\Datatables\ActualiteDatatable;
use Doctrine\ORM\EntityManagerInterface;
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
 * @Route("UcaGest/Actualite")
 */
class ActualiteController extends AbstractController
{
    /**
     * @Route("/", name="UcaGest_ActualiteLister")
     * @Isgranted("ROLE_GESTION_ACTUALITE_LECTURE")
     */
    public function listerAction(Request $request, DatatableFactory $datatableFactory, DatatableResponse $datatableResponse)
    {
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $datatableFactory->create(ActualiteDatatable::class);
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
        if (!$usr->hasRole('ROLE_GESTION_ACTUALITE_ECRITURE')) {
            $twigConfig['noAddButton'] = true;
        }
        $twigConfig['codeListe'] = 'Actualite';

        return $this->render('UcaBundle/UcaGest/Referentiel/Actualite/Datatable.html.twig', $twigConfig);
    }

    /**
     * @Route("/Ajouter", name="UcaGest_ActualiteAjouter", methods={"GET", "POST"})
     * @Isgranted("ROLE_GESTION_ACTUALITE_ECRITURE")
     */
    public function ajouterAction(Request $request, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $item = new Actualite();
        $item->setOrdre($em->getRepository(Actualite::class)->findMaxOrdre() + 1);
        $form = $this->createForm(ActualiteType::class, $item);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($item);
            $em->flush();
            $flashBag->addActionFlashBag($item, 'Ajouter');

            return $this->redirectToRoute('UcaGest_ActualiteLister');
        }

        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();

        return $this->render('UcaBundle/UcaGest/Referentiel/Actualite/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Supprimer/{id}", name="UcaGest_ActualiteSupprimer")
     * @Isgranted("ROLE_GESTION_ACTUALITE_ECRITURE")
     */
    public function supprimerAction(Request $request, Actualite $actualite, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $actus = $em->getRepository(Actualite::class)->findByOrdreSuperieur($actualite->getOrdre());
        foreach ($actus as $actu) {
            $actu->setOrdre($actu->getOrdre() - 1);
        }
        $em->remove($actualite);
        $em->flush();
        $flashBag->addActionFlashBag($actualite, 'Supprimer');

        return $this->redirectToRoute('UcaGest_ActualiteLister');
    }

    /**
     * @Route("/Modifier/{id}", name="UcaGest_ActualiteModifier",requirements={"id"="\d+"}, methods={"GET", "POST"})
     * @Isgranted("ROLE_GESTION_ACTUALITE_ECRITURE")
     */
    public function modifierAction(Request $request, Actualite $actualite, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $form = $this->createForm(ActualiteType::class, $actualite);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($actualite);
            $em->flush();
            $flashBag->addActionFlashBag($actualite, 'Modifier');

            return $this->redirectToRoute('UcaGest_ActualiteLister');
        }
        $twigConfig['item'] = $actualite;
        $twigConfig['form'] = $form->createView();

        return $this->render('UcaBundle/UcaGest/Referentiel/Actualite/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/ActualiteModifierOrdre/{id}/{action}", name="UcaGest_ActualiteModifierOrdre", options={"expose"=true}, requirements={"id"="\d+"}, methods={"GET"})
     * @Isgranted("ROLE_GESTION_ACTUALITE_ECRITURE")
     *
     * @param mixed $action
     */
    public function monterOrdreActualite(Request $request, Actualite $actualite, $action, EntityManagerInterface $em)
    {
        $actualites = $em->getRepository(Actualite::class)->findAll();
        if ('monter' == $action) {
            $condition = $actualite->getOrdre() > 1;
            $oldOrdre = $actualite->getOrdre();
            $newOrdre = $actualite->getOrdre() - 1;
        } elseif ('descendre' == $action) {
            $condition = $actualite->getOrdre() < count($actualites);
            $oldOrdre = $actualite->getOrdre();
            $newOrdre = $actualite->getOrdre() + 1;
        }
        // Si c'est le premier dans l'ordre et qu'on veut monter encore
        if ($condition) {
            $actuAffecteeParChangement = $em->getRepository(Actualite::class)->findOneByOrdre($newOrdre);
            if (null !== $actuAffecteeParChangement) {
                $actuAffecteeParChangement->setOrdre($oldOrdre);
            }
            $actualite->setOrdre($newOrdre);
            $em->flush();

            return new Response(200);
        }

        return new Response(204);
    }
}
