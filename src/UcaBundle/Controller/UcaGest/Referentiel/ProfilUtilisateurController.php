<?php

namespace UcaBundle\Controller\UcaGest\Referentiel;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use UcaBundle\Datatables\ProfilUtilisateurDatatable;
use UcaBundle\Entity\ProfilUtilisateur;
use UcaBundle\Entity\Tarif;
use UcaBundle\Entity\MontantTarifProfilUtilisateur;
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
        $tarifs = $this->getDoctrine()->getRepository(Tarif::class)->findAll();
        $form = $this->get('form.factory')->create(ProfilUtilisateurType::class, $item);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            // Pour le nouveau profil, ajout d'un montant à -1 pour chaque Tarif existant
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
        $listeRelations = [
            ['relation' => $item->getUtilisateur(), 'message' => 'profilutilisateur.supprimer.erreur.utilisateurs'],
            ['relation' => $item->getFormatsActivite(), 'message' => 'profilutilisateur.supprimer.erreur.formatactivite'],
        ];
        foreach ($listeRelations as $relation) {
            if (!$relation['relation']->isEmpty()) {
                $this->get('uca.flashbag')->addMessageFlashBag($relation['message'], 'danger');
                $r = true;
            }
        }
        if ($r) {
            $this->get('uca.flashbag')->addActionErrorFlashBag($item, 'Supprimer');
            return $this->redirectToRoute('UcaGest_ProfilUtilisateurLister');
        } else {
            $em->remove($item);
            $em->flush();
            $this->get('uca.flashbag')->addActionFlashBag($item, 'Supprimer');
            return $this->redirectToRoute('UcaGest_ProfilUtilisateurLister');
        }
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
