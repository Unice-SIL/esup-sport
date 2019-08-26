<?php

namespace UcaBundle\Controller\UcaGest\Referentiel;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Datatables\TarifDatatable;
use UcaBundle\Entity\MontantTarifProfilUtilisateur;
use UcaBundle\Entity\ProfilUtilisateur;
use UcaBundle\Entity\Tarif;
use UcaBundle\Form\TarifType;

/** 
 * @Security("has_role('ROLE_ADMIN')")
 * @Route("UcaGest/Tarif") 
 */
class TarifController extends Controller
{

    /** 
     * @Route("/", name="UcaGest_TarifLister")
     * @Isgranted("ROLE_GESTION_TARIF_LECTURE")
     */
    public function listerAction(Request $request)
    {
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $this->get('sg_datatables.factory')->create(TarifDatatable::class);
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;
        if ($isAjax) {
            $repository = $this->getDoctrine()->getRepository(Tarif::class);
            $responseService = $this->get('sg_datatables.response');
            $responseService->setDatatable($datatable);
            $builder = $responseService->getDatatableQueryBuilder();
            $repository->listAll($builder->getQb());
            return $responseService->getResponse();
        }
        // Bouton Ajouter
        $usr = $this->container->get('security.token_storage')->getToken()->getUser();
        if (!$usr->hasRole('ROLE_GESTION_TARIF_ECRITURE')) {
            $twigConfig['noAddButton'] = true;
        }
        // return $this->render('@Uca/UcaGest/Referentiel/Tarif/Lister.html.twig', $twigConfig);
        $twigConfig['codeListe'] = 'Tarif';
        return $this->render('@Uca/Common/Liste/Datatable.html.twig', $twigConfig);
    }

    /** 
     * @Route("/Ajouter", name="UcaGest_TarifAjouter") 
     * @Method({"GET", "POST"})
     * @Isgranted("ROLE_GESTION_TARIF_ECRITURE")
     */
    public function ajouterAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $item = new Tarif();
        $profilsUtilisateurs = $this->getDoctrine()->getRepository(ProfilUtilisateur::class)->findAll();
        foreach ($profilsUtilisateurs as $profil) {
            $item->addMontant(new MontantTarifProfilUtilisateur($item, $profil, 0));
        }
        $form = $this->get('form.factory')->create(TarifType::class, $item);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($item);
            $em->flush();
            $this->get('uca.flashbag')->addActionFlashBag($item, 'Ajouter');
            return $this->redirectToRoute('UcaGest_TarifLister');
        }
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();
        $twigConfig['profils'] = $profilsUtilisateurs;
        return $this->render('@Uca/UcaGest/Referentiel/Tarif/Formulaire.html.twig', $twigConfig);
    }

    /** 
     * @Route("/Supprimer/{id}", name="UcaGest_TarifSupprimer",requirements={"id"="\d+"}) 
     * @Isgranted("ROLE_GESTION_TARIF_ECRITURE")
     */
    public function supprimerAction(Request $request, Tarif $item)
    {
        $r = false;
        $em = $this->getDoctrine()->getManager();
        $listeRelations = [
            ['relation' => $item->getTypesAutorisation(), 'message' => 'tarif.supprimer.erreur.autorisations'],
            ['relation' => $item->getRessources(), 'message' => 'tarif.supprimer.erreur.ressources'],
            ['relation' => $item->getCreneaux(), 'message' => 'tarif.supprimer.erreur.creneaux'],
            ['relation' => $item->getFormatsActivite(), 'message' => "tarif.supprimer.erreur.formatsactivite"]
        ];
        foreach ($listeRelations as $relation) {
            if (!$relation['relation']->isEmpty()) {
                $this->get('uca.flashbag')->addMessageFlashBag($relation['message'], 'danger');
                $r = true;
            }
        }
        if ($r) {
            $this->get('uca.flashbag')->addActionErrorFlashBag($item, 'Supprimer');
            return $this->redirectToRoute('UcaGest_TarifLister');
        } else {
            $em->remove($item);
            $em->flush();
            $this->get('uca.flashbag')->addActionFlashBag($item, 'Supprimer');
            return $this->redirectToRoute('UcaGest_TarifLister');
        }
    }

    /** 
     * @Route("/Modifier/{id}", name="UcaGest_TarifModifier",requirements={"id"="\d+"}) 
     * @Method({"GET", "POST"})
     * @Isgranted("ROLE_GESTION_TARIF_ECRITURE")
     */
    public function modifierAction(Request $request, Tarif $item)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->get('form.factory')->create(TarifType::class, $item);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($item);
            $em->flush();
            $this->get('uca.flashbag')->addActionFlashBag($item, 'Modifier');
            return $this->redirectToRoute('UcaGest_TarifLister');
        }
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();
        return $this->render('@Uca/UcaGest/Referentiel/Tarif/Formulaire.html.twig', $twigConfig);
    }
}
