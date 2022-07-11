<?php

/*
 * Classe - TarifController
 *
 * Gestion du CRUD pour les tarifs
*/

namespace App\Controller\UcaGest\Referentiel;

use App\Form\TarifType;
use App\Entity\Uca\Tarif;
use App\Service\Common\FlashBag;
use App\Datatables\TarifDatatable;
use App\Repository\TarifRepository;
use App\Entity\Uca\ProfilUtilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Uca\MontantTarifProfilUtilisateur;
use Sg\DatatablesBundle\Datatable\DatatableFactory;
use Sg\DatatablesBundle\Response\DatatableResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Security("is_granted('ROLE_ADMIN')")
 * @Route("UcaGest/Tarif")
 */
class TarifController extends AbstractController
{
    /**
     * @Route("/", name="UcaGest_TarifLister")
     * @Isgranted("ROLE_GESTION_TARIF_LECTURE")
     */
    public function listerAction(Request $request, DatatableFactory $datatableFactory, DatatableResponse $datatableResponse, TarifRepository $repository)
    {
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $datatableFactory->create(TarifDatatable::class);
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;
        if ($isAjax) {
            $responseService = $datatableResponse;
            $responseService->setDatatable($datatable);
            $builder = $responseService->getDatatableQueryBuilder();
            $repository->listAll($builder->getQb());

            return $responseService->getResponse();
        }
        // Bouton Ajouter
        $usr = $this->getUser();
        if (!$usr->hasRole('ROLE_GESTION_TARIF_ECRITURE')) {
            $twigConfig['noAddButton'] = true;
        }
        // return $this->render('UcaBundle/UcaGest/Referentiel/Tarif/Lister.html.twig', $twigConfig);
        $twigConfig['codeListe'] = 'Tarif';

        return $this->render('UcaBundle/Common/Liste/Datatable.html.twig', $twigConfig);
    }

    /**
     * @Route("/Ajouter", name="UcaGest_TarifAjouter", methods={"GET", "POST"})
     * @Isgranted("ROLE_GESTION_TARIF_ECRITURE")
     */
    public function ajouterAction(Request $request, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $item = new Tarif();
        $profilsUtilisateurs = $em->getRepository(ProfilUtilisateur::class)->findAll();
        foreach ($profilsUtilisateurs as $profil) {
            $item->addMontant(new MontantTarifProfilUtilisateur($item, $profil, 0));
        }
        $form = $this->createForm(TarifType::class, $item);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($item);
            $em->flush();
            $flashBag->addActionFlashBag($item, 'Ajouter');

            return $this->redirectToRoute('UcaGest_TarifLister');
        }
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();
        $twigConfig['profils'] = $profilsUtilisateurs;

        return $this->render('UcaBundle/UcaGest/Referentiel/Tarif/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Supprimer/{id}", name="UcaGest_TarifSupprimer",requirements={"id"="\d+"})
     * @Isgranted("ROLE_GESTION_TARIF_ECRITURE")
     */
    public function supprimerAction(Request $request, Tarif $item, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $r = false;
        $listeRelations = [
            ['relation' => $item->getTypesAutorisation(), 'message' => 'tarif.supprimer.erreur.autorisations'],
            ['relation' => $item->getRessources(), 'message' => 'tarif.supprimer.erreur.ressources'],
            ['relation' => $item->getCreneaux(), 'message' => 'tarif.supprimer.erreur.creneaux'],
            ['relation' => $item->getFormatsActivite(), 'message' => 'tarif.supprimer.erreur.formatsactivite'],
        ];
        foreach ($listeRelations as $relation) {
            if (!$relation['relation']->isEmpty()) {
                $flashBag->addMessageFlashBag($relation['message'], 'danger');
                $r = true;
            }
        }
        if ($r) {
            $flashBag->addActionErrorFlashBag($item, 'Supprimer');

            return $this->redirectToRoute('UcaGest_TarifLister');
        }
        $em->remove($item);
        $em->flush();
        $flashBag->addActionFlashBag($item, 'Supprimer');

        return $this->redirectToRoute('UcaGest_TarifLister');
    }

    /**
     * @Route("/Modifier/{id}", name="UcaGest_TarifModifier",requirements={"id"="\d+"}, methods={"GET", "POST"})
     * @Isgranted("ROLE_GESTION_TARIF_ECRITURE")
     */
    public function modifierAction(Request $request, Tarif $item, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $form = $this->createForm(TarifType::class, $item);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($item);
            $em->flush();
            $flashBag->addActionFlashBag($item, 'Modifier');

            return $this->redirectToRoute('UcaGest_TarifLister');
        }
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();

        return $this->render('UcaBundle/UcaGest/Referentiel/Tarif/Formulaire.html.twig', $twigConfig);
    }
}
