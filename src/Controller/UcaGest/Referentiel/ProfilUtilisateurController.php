<?php

/*
 * Classe - ProfilUtilisateurController
 *
 * Gestion du CRUD pour les profils utilisateurs
*/

namespace App\Controller\UcaGest\Referentiel;

use App\Entity\Uca\Tarif;
use App\Service\Common\FlashBag;
use App\Form\ProfilUtilisateurType;
use App\Repository\TarifRepository;
use App\Entity\Uca\ProfilUtilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Datatables\ProfilUtilisateurDatatable;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Uca\MontantTarifProfilUtilisateur;
use Sg\DatatablesBundle\Datatable\DatatableFactory;
use Sg\DatatablesBundle\Response\DatatableResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("UcaGest/ProfilUtilisateur")
 * @Security("is_granted('ROLE_ADMIN')")
 */
class ProfilUtilisateurController extends AbstractController
{
    /**
     * @Route("/", name="UcaGest_ProfilUtilisateurLister")
     * @Isgranted("ROLE_GESTION_PROFIL_UTILISATEUR_LECTURE")
     */
    public function listerAction(Request $request, DatatableFactory $datatableFactory, DatatableResponse $datatableResponse)
    {
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $datatableFactory->create(ProfilUtilisateurDatatable::class);
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
        if (!$usr->hasRole('ROLE_GESTION_PROFIL_UTILISATEUR_ECRITURE')) {
            $twigConfig['noAddButton'] = true;
        }
        $twigConfig['codeListe'] = 'ProfilUtilisateur';

        return $this->render('UcaBundle/Common/Liste/Datatable.html.twig', $twigConfig);
    }

    /**
     * @Route("/Ajouter", name="UcaGest_ProfilUtilisateurAjouter")
     * @Isgranted("ROLE_GESTION_PROFIL_UTILISATEUR_ECRITURE")
     */
    public function ajouterAction(Request $request, FlashBag $flashBag, TarifRepository $tarifRepository, EntityManagerInterface $em)
    {
        $item = new ProfilUtilisateur($em);
        $tarifs = $tarifRepository->findAll();
        $form = $this->createForm(ProfilUtilisateurType::class, $item);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $tarifs = $em->getRepository(Tarif::class)->findAll();
            // Pour le nouveau profil, ajout d'un montant à -1 pour chaque Tarif existant
            foreach ($tarifs as $tarif) {
                $montantTarifProfil = new MontantTarifProfilUtilisateur($tarif, $item, -1);
                $em->persist($montantTarifProfil);
            }
            $em->persist($item);
            $em->flush();
            $flashBag->addActionFlashBag($item, 'Ajouter');
            $flashBag->addMessageFlashBag('tarif.mettre.jour', 'warning');

            return $this->redirectToRoute('UcaGest_ProfilUtilisateurLister');
        }
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();

        return $this->render('UcaBundle/UcaGest/Referentiel/ProfilUtilisateur/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Supprimer/{id}", name="UcaGest_ProfilUtilisateurSupprimer")
     * @Isgranted("ROLE_GESTION_PROFIL_UTILISATEUR_ECRITURE")
     */
    public function supprimerAction(Request $request, ProfilUtilisateur $item, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $r = false;

        if (!($utilisateurs = $item->getUtilisateur())->isEmpty()) {
            $r = true;
            $param = '<ul>';
            foreach ($utilisateurs as $utilisateur) {
                $param .= '<li>'.$utilisateur->getNom().' '.$utilisateur->getPrenom().'</li>';
            }

            $param .= '</ul>';

            $flashBag->addMessageFlashBag('profilutilisateur.supprimer.erreur.utilisateurs', 'danger', ['%utilisateurs%' => $param]);
        }

        if (!($enfants = $item->getEnfants())->isEmpty()) {
            $r = true;
            $param = '<ul>';
            foreach ($enfants as $enfant) {
                $param .= '<li>'.$enfant->getLibelle().'</li>';
            }

            $param .= '</ul>';

            $flashBag->addMessageFlashBag('profilutilisateur.supprimer.erreur.profils', 'danger', ['%profils%' => $param]);
        }

        if (!($formats = $item->getFormatsActivite())->isEmpty()) {
            $r = true;
            $param = '<ul>';
            foreach ($formats as $format) {
                $param .= '<li>'.$format->getFormatActivite()->getLibelle().'</li>';
            }

            $param .= '</ul>';

            $flashBag->addMessageFlashBag('profilutilisateur.supprimer.erreur.formatactivite', 'danger', ['%formats%' => $param]);
        }

        
        if ($r) {
            $flashBag->addActionErrorFlashBag($item, 'Supprimer');

            return $this->redirectToRoute('UcaGest_ProfilUtilisateurLister');
        }
        $em->remove($item);
        $em->flush();
        $flashBag->addActionFlashBag($item, 'Supprimer');

        return $this->redirectToRoute('UcaGest_ProfilUtilisateurLister');
    }

    /**
     * @Route("/Modifier/{id}", name="UcaGest_ProfilUtilisateurModifier")
     * @Isgranted("ROLE_GESTION_PROFIL_UTILISATEUR_ECRITURE")
     */
    public function modifierAction(Request $request, ProfilUtilisateur $item, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $form = $this->createForm(ProfilUtilisateurType::class, $item);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($item);
            $em->flush();
            $flashBag->addActionFlashBag($item, 'Modifier');

            return $this->redirectToRoute('UcaGest_ProfilUtilisateurLister');
        }
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();

        return $this->render('UcaBundle/UcaGest/Referentiel/ProfilUtilisateur/Formulaire.html.twig', $twigConfig);
    }
}
