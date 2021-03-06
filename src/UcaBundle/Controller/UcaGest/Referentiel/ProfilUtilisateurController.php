<?php

/*
 * Classe - ProfilUtilisateurController
 *
 * Gestion du CRUD pour les profils utilisateurs
*/

namespace UcaBundle\Controller\UcaGest\Referentiel;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Datatables\ProfilUtilisateurDatatable;
use UcaBundle\Entity\MontantTarifProfilUtilisateur;
use UcaBundle\Entity\ProfilUtilisateur;
use UcaBundle\Entity\Tarif;
use UcaBundle\Form\ProfilUtilisateurType;

/**
 * @Route("UcaGest/ProfilUtilisateur")
 * @Security("has_role('ROLE_ADMIN')")
 */
class ProfilUtilisateurController extends Controller
{
    /**
     * @Route("/", name="UcaGest_ProfilUtilisateurLister")
     * @Isgranted("ROLE_GESTION_PROFIL_UTILISATEUR_LECTURE")
     */
    public function listerAction(Request $request)
    {
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $this->get('sg_datatables.factory')->create(ProfilUtilisateurDatatable::class);
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
        if (!$usr->hasRole('ROLE_GESTION_PROFIL_UTILISATEUR_ECRITURE')) {
            $twigConfig['noAddButton'] = true;
        }
        $twigConfig['codeListe'] = 'ProfilUtilisateur';

        return $this->render('@Uca/Common/Liste/Datatable.html.twig', $twigConfig);
    }

    /**
     * @Route("/Ajouter", name="UcaGest_ProfilUtilisateurAjouter")
     * @Isgranted("ROLE_GESTION_PROFIL_UTILISATEUR_ECRITURE")
     */
    public function ajouterAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $item = new ProfilUtilisateur($em);
        $form = $this->get('form.factory')->create(ProfilUtilisateurType::class, $item);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $tarifs = $this->getDoctrine()->getRepository(Tarif::class)->findAll();
            // Pour le nouveau profil, ajout d'un montant ?? -1 pour chaque Tarif existant
            foreach ($tarifs as $tarif) {
                $montantTarifProfil = new MontantTarifProfilUtilisateur($tarif, $item, -1);
                $em->persist($montantTarifProfil);
            }
            $em->persist($item);
            $em->flush();
            $this->get('uca.flashbag')->addActionFlashBag($item, 'Ajouter');
            $this->get('uca.flashbag')->addMessageFlashBag('tarif.mettre.jour', 'warning');

            return $this->redirectToRoute('UcaGest_ProfilUtilisateurLister');
        }
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();

        return $this->render('@Uca/UcaGest/Referentiel/ProfilUtilisateur/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Supprimer/{id}", name="UcaGest_ProfilUtilisateurSupprimer")
     * @Isgranted("ROLE_GESTION_PROFIL_UTILISATEUR_ECRITURE")
     */
    public function supprimerAction(Request $request, ProfilUtilisateur $item)
    {
        $r = false;
        $em = $this->getDoctrine()->getManager();

        if (!($utilisateurs = $item->getUtilisateur())->isEmpty()) {
            $r = true;
            $param = '<ul>';
            foreach ($utilisateurs as $utilisateur) {
                $param .= '<li>'.$utilisateur->getNom().' '.$utilisateur->getPrenom().'</li>';
            }

            $param .= '</ul>';

            $this->get('uca.flashbag')->addMessageFlashBag('profilutilisateur.supprimer.erreur.utilisateurs', 'danger', ['%utilisateurs%' => $param]);
        }

        if (!($enfants = $item->getEnfants())->isEmpty()) {
            $r = true;
            $param = '<ul>';
            foreach ($enfants as $enfant) {
                $param .= '<li>'.$enfant->getLibelle().'</li>';
            }

            $param .= '</ul>';

            $this->get('uca.flashbag')->addMessageFlashBag('profilutilisateur.supprimer.erreur.profils', 'danger', ['%profils%' => $param]);
        }

        if (!($formats = $item->getFormatsActivite())->isEmpty()) {
            $r = true;
            $param = '<ul>';
            foreach ($formats as $format) {
                $param .= '<li>'.$format->getFormatActivite()->getLibelle().'</li>';
            }

            $param .= '</ul>';

            $this->get('uca.flashbag')->addMessageFlashBag('profilutilisateur.supprimer.erreur.formatactivite', 'danger', ['%formats%' => $param]);
        }

        
        if ($r) {
            $this->get('uca.flashbag')->addActionErrorFlashBag($item, 'Supprimer');

            return $this->redirectToRoute('UcaGest_ProfilUtilisateurLister');
        }
        $em->remove($item);
        $em->flush();
        $this->get('uca.flashbag')->addActionFlashBag($item, 'Supprimer');

        return $this->redirectToRoute('UcaGest_ProfilUtilisateurLister');
    }

    /**
     * @Route("/Modifier/{id}", name="UcaGest_ProfilUtilisateurModifier")
     * @Isgranted("ROLE_GESTION_PROFIL_UTILISATEUR_ECRITURE")
     */
    public function modifierAction(Request $request, ProfilUtilisateur $item)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->get('form.factory')->create(ProfilUtilisateurType::class, $item);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($item);
            $em->flush();
            $this->get('uca.flashbag')->addActionFlashBag($item, 'Modifier');

            return $this->redirectToRoute('UcaGest_ProfilUtilisateurLister');
        }
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();

        return $this->render('@Uca/UcaGest/Referentiel/ProfilUtilisateur/Formulaire.html.twig', $twigConfig);
    }
}
