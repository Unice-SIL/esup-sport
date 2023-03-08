<?php

namespace App\Controller\UcaGest\Referentiel;

use App\Entity\Uca\NiveauSportif;
use App\Form\NiveauSportifType;
use App\Datatables\NiveauSportifDatatable;
use App\Service\Common\FlashBag;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sg\DatatablesBundle\Datatable\DatatableFactory;
use Sg\DatatablesBundle\Response\DatatableResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_ADMIN")
 * @Route("UcaGest/NiveauSportif")
 */
class NiveauSportifController extends AbstractController
{
    /**
     * @Route("/", name="UcaGest_NiveauSportifLister", methods={"GET"})
     * @IsGranted("ROLE_GESTION_NIVEAUSPORTIF_LECTURE")
     */
    public function listerAction(Request $request, DatatableFactory $datatableFactory, DatatableResponse $datatableResponse): Response
    {
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $datatableFactory->create(NiveauSportifDatatable::class);
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
        if (!$usr->hasRole('ROLE_GESTION_NIVEAUSPORTIF_ECRITURE')) {
            $twigConfig['noAddButton'] = true;
        }
        $twigConfig['codeListe'] = 'NiveauSportif';

        return $this->render('UcaBundle/Common/Liste/Datatable.html.twig', $twigConfig);
    }

    /**
     * @Route("/Ajouter", name="UcaGest_NiveauSportifAjouter", methods={"GET", "POST"})
     * @IsGranted("ROLE_GESTION_NIVEAUSPORTIF_ECRITURE")
     */
    public function ajouterAction(Request $request, EntityManagerInterface $entityManager, FlashBag $flashBag): Response
    {
        $niveauSportif = new NiveauSportif();
        $form = $this->createForm(NiveauSportifType::class, $niveauSportif);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($niveauSportif);
            $entityManager->flush();
            $flashBag->addActionFlashBag($niveauSportif, 'Ajouter');

            return $this->redirectToRoute('UcaGest_NiveauSportifLister');
        }
        $twigConfig['item'] = $niveauSportif;
        $twigConfig['titre'] = 'niveausportif';
        $twigConfig['form'] = $form->createView();

        return $this->render('UcaBundle/UcaGest/Referentiel/NiveauSportif/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Modifier/{id}", name="UcaGest_NiveauSportifModifier", methods={"GET", "POST"})
     * @IsGranted("ROLE_GESTION_NIVEAUSPORTIF_ECRITURE")
     */
    public function modifierAction(Request $request, NiveauSportif $niveauSportif, EntityManagerInterface $entityManager, FlashBag $flashBag): Response
    {
        $form = $this->createForm(NiveauSportifType::class, $niveauSportif);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $flashBag->addActionFlashBag($niveauSportif, 'Modifier');

            return $this->redirectToRoute('UcaGest_NiveauSportifLister');
        }
        $twigConfig['item'] = $niveauSportif;
        $twigConfig['titre'] = 'niveausportif';
        $twigConfig['form'] = $form->createView();

        return $this->render('UcaBundle/UcaGest/Referentiel/NiveauSportif/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Supprimer/{id}", name="UcaGest_NiveauSportifSupprimer")
     * @IsGranted("ROLE_GESTION_NIVEAUSPORTIF_ECRITURE")
     */
    public function supprimerAction(NiveauSportif $niveauSportif, EntityManagerInterface $entityManager, FlashBag $flashBag): Response
    {
        $r = false;
        $listeRelations = [
            ['relation' => $niveauSportif->getCreneaux(), 'message' => 'niveausportif.supprimer.erreur.creneaux'],
            ['relation' => $niveauSportif->getFormatsActivite(), 'message' => 'niveausportif.supprimer.erreur.formatsactivite'],
        ];
        foreach ($listeRelations as $relation) {
            if (!$relation['relation']->isEmpty()) {
                $flashBag->addMessageFlashBag($relation['message'], 'danger');
                $r = true;
            }
        }
        if ($r) {
            $flashBag->addActionErrorFlashBag($niveauSportif, 'Supprimer');
        } else {
            $entityManager->remove($niveauSportif);
            $entityManager->flush();
            $flashBag->addActionFlashBag($niveauSportif, 'Supprimer');
        }

        return $this->redirectToRoute('UcaGest_NiveauSportifLister');
    }
}
