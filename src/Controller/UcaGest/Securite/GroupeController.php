<?php

/*
 * Classe - GroupeController
 *
 * Gestion des groupes : CRUD
 * Les groupes permette de regrouper les rôles de sécurité
*/

namespace App\Controller\UcaGest\Securite;

use App\Entity\Uca\Groupe;
use App\Service\Common\FlashBag;
use App\Datatables\GroupeDatatable;
use App\Form\GroupeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sg\DatatablesBundle\Datatable\DatatableFactory;
use Sg\DatatablesBundle\Response\DatatableResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("UcaGest/Groupe")
 * @Security("is_granted('ROLE_ADMIN')")
 */
class GroupeController extends AbstractController
{
    /**
     * @Route("/", name="UcaGest_GroupeLister")
     * @Isgranted("ROLE_GESTION_GROUPE_LECTURE")
     */
    public function listerAction(Request $request, DatatableFactory $datatableFactory, DatatableResponse $datatableResponse)
    {
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $datatableFactory->create(GroupeDatatable::class);
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
        if (!$usr->hasRole('ROLE_GESTION_GROUPE_ECRITURE')) {
            $twigConfig['noAddButton'] = true;
        }
        $twigConfig['codeListe'] = 'Groupe';

        return $this->render('UcaBundle/UcaGest/Securite/Groupe/Lister.html.twig', $twigConfig);
    }

    /**
     * @Route("/{id}/Supprimer", name="UcaGest_GroupeSupprimer")
     * @Isgranted("ROLE_GESTION_GROUPE_ECRITURE")
     */
    public function supprimerAction(Request $request, Groupe $groupe, FlashBag $flashBag, EntityManagerInterface $em)
    {
        if (!$groupe->getUtilisateurs()->isEmpty()) {
            $flashBag->addMessageFlashBag('groupe.supprimer.danger', 'danger');

            return $this->redirectToRoute('UcaGest_GroupeLister');
        }
        $em->remove($groupe);
        $em->flush();
        $flashBag->addActionFlashBag($groupe, 'Supprimer');

        return $this->redirectToRoute('UcaGest_GroupeLister');
    }

    /**
     * @Route("/Modifier/{id}", name="UcaGest_GroupeModifier")
     * @Isgranted("ROLE_GESTION_GROUPE_LECTURE")
     */
    public function modifierAction(Request $request, Groupe $groupe, EntityManagerInterface $em) {
        $form = $this->createForm(GroupeType::class, $groupe);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'groupe.modifier.succes');

            return $this->redirectToRoute('UcaGest_GroupeLister');
        }

        return $this->render('UserBundle/Group/edit.html.twig', ['form' => $form->createView(), 'id' => $groupe->getId()]);
    }

    /**
     * @Route("/Ajouter", name="UcaGest_GroupeAjouter")
     * @Isgranted("ROLE_GESTION_GROUPE_LECTURE")
     */
    public function ajouterAction(Request $request, EntityManagerInterface $em) {
        $groupe = new Groupe('');
        $form = $this->createForm(GroupeType::class, $groupe);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($groupe);
            $em->flush();
            $this->addFlash('success', 'groupe.ajouter.succes');

            return $this->redirectToRoute('UcaGest_GroupeLister');
        }

        return $this->render('UserBundle/Group/new.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/Voir/{id}", name="UcaGest_GroupeVoir")
     * @Isgranted("ROLE_GESTION_GROUPE_LECTURE")
     */
    public function voirAction(Groupe $groupe) {
        return $this->render('UserBundle/Group/show.html.twig', ['group' => $groupe]);
    }
}
