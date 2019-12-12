<?php

namespace UcaBundle\Controller\UcaWeb;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Datatables\GestionInscriptionDatatable;
use UcaBundle\Datatables\MesInscriptionsDatatable;
use UcaBundle\Entity\Activite;
use UcaBundle\Entity\ClasseActivite;
use UcaBundle\Entity\FormatActivite;
use UcaBundle\Entity\Inscription;
use UcaBundle\Entity\TypeActivite;
use UcaBundle\Form\GestionInscriptionType;

/**
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class MesInscriptionsController extends Controller
{
    /**
     * @Route("UcaGest/GestionInscription",name="UcaGest_GestionInscription")
     * @Route("UcaWeb/MesInscriptions",name="UcaWeb_MesInscriptions")
     */
    public function listerAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if ('UcaGest_GestionInscription' == $request->get('_route') && !$this->isGranted('ROLE_GESTION_INSCRIPTION')) {
            return $this->redirectToRoute('UcaWeb_MesInscriptions');
        }
        $this->get('uca.timeout')->nettoyageCommandeEtInscription();
        $isAjax = $request->isXmlHttpRequest();
        if ('UcaGest_GestionInscription' == $request->get('_route')) {
            $datatable = $this->get('sg_datatables.factory')->create(GestionInscriptionDatatable::class);
            $twigConfig['codeListe'] = 'GestionInscription';
            $form = $this->get('form.factory')->create(
                GestionInscriptionType::class,
                [
                    'typeActivite' => $em->getRepository(TypeActivite::class)->findAll(),
                    'classeActivite' => $em->getRepository(ClasseActivite::class)->findAll(),
                    'listeActivite' => $em->getRepository(Activite::class)->findAll(),
                    'listeFormatActivite' => $em->getRepository(FormatActivite::class)->findAll(),
                    'data_class' => null,
                    'em' => $em,
                ]
            );
            $twigConfig['form'] = $form->createView();
        } else {
            $datatable = $this->get('sg_datatables.factory')->create(MesInscriptionsDatatable::class);
            $twigConfig['codeListe'] = 'MesInscriptions';
        }
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;
        if ($isAjax) {
            $responseService = $this->get('sg_datatables.response');
            $responseService->setDatatable($datatable);
            $dtQueryBuilder = $responseService->getDatatableQueryBuilder();
            $qb = $dtQueryBuilder->getQb();
            if ('UcaWeb_MesInscriptions' == $request->get('_route')) {
                $qb->andWhere('utilisateur = :objectId');
                $qb->setParameter('objectId', $this->getUser()->getId());
            }

            return $responseService->getResponse();
        }
        // Bouton Ajouter
        $twigConfig['noAddButton'] = true;
        if ('UcaWeb_MesInscriptions' == $request->get('_route')) {
            return $this->render('@Uca/Common/Liste/Datatable_UcaWeb.html.twig', $twigConfig);
        }

        return $this->render('@Uca/UcaGest/Reporting/Inscriptions/Datatable.html.twig', $twigConfig);
    }

    /**
     * @Route("UcaWeb/{id}/Annuler", name="UcaWeb_MesInscriptionsAnnuler")
     */
    public function annulerAction(Request $request, Inscription $inscription)
    {
        $em = $this->getDoctrine()->getManager();
        if ($inscription->getUtilisateur() == $this->getUser() && !($request->getScheme().'://'.$request->getHttpHost().$this->generateUrl('UcaGest_GestionInscription') == $request->headers->get('referer'))) {
            $inscription->setStatut('annule', ['motifAnnulation' => 'annulationutilisateur']);
            $redirect = $this->redirectToRoute('UcaWeb_MesInscriptions');
        } elseif ($this->isGranted('ROLE_GESTION_INSCRIPTION')) {
            $inscription->setStatut('annule', ['motifAnnulation' => 'annulationgestionnaire']);
            $redirect = $this->redirectToRoute('UcaGest_GestionInscription');
        }
        $em->flush();

        return $redirect;
    }

    /**
     * @Route("UcaWeb/{id}/AjoutPanier", name="UcaWeb_MesInscriptionsAjoutPanier")
     */
    public function ajoutPanierAction(Request $request, Inscription $inscription)
    {
        $em = $this->getDoctrine()->getManager();
        $inscriptionService = $this->get('uca.inscription');
        $inscriptionService->setInscription($inscription);
        $inscriptionService->ajoutPanier($inscription);
        $inscription->setStatut('attentepaiement');
        $em->flush();
        if ($inscription->getUtilisateur() == $this->getUser() && !($request->getScheme().'://'.$request->getHttpHost().$this->generateUrl('UcaGest_GestionInscription') == $request->headers->get('referer'))) {
            return $this->redirectToRoute('UcaWeb_Panier');
        }
        if ($this->isGranted('ROLE_GESTION_INSCRIPTION')) {
            return $this->redirectToRoute('UcaGest_GestionInscription');
        }
    }

    /**
     * @Route("UcaWeb/{id}/SeDesinscrire", name="UcaWeb_MesInscriptionsSeDesinscrire")
     */
    public function seDesinscrireAction(Request $request, Inscription $inscription)
    {
        if ($this->isGranted('ROLE_GESTION_INSCRIPTION') or $this->isGranted('ROLE_ENCADRANT') or $inscription->getUtilisateur() == $this->getUser()) {
            $inscriptionService = $this->get('uca.inscription');
            $inscriptionService->setInscription($inscription);
            $inscriptionService->mailDesinscription($inscription);

            $em = $this->getDoctrine()->getManager();
            $inscription->setStatut('desinscrit');
            $inscription->setDateDesinscription(new \DateTime());
            $em->flush();
            if ($inscription->getUtilisateur() == $this->getUser() && !($request->getScheme().'://'.$request->getHttpHost().$this->generateUrl('UcaGest_GestionInscription') == $request->headers->get('referer'))) {
                return $this->redirectToRoute('UcaWeb_MesInscriptions');
            }
            if ($this->isGranted('ROLE_GESTION_INSCRIPTION')) {
                return $this->redirectToRoute('UcaGest_GestionInscription');
            }
        } else {
            return $this->redirectToRoute('UcaWeb_MesInscriptions');
        }
    }

    /**
     * @Route("UcaWeb/MesInscriptions/{id}",name="UcaWeb_MesInscriptionsVoir")
     * @Route("UcaGest/GestionInscription/{id}",name="UcaGest_GestionInscriptionVoir")
     */
    public function voirAction(Request $request, Inscription $inscription)
    {
        $em = $this->getDoctrine()->getManager();
        $isAjax = $request->isXmlHttpRequest();
        $twigConfig['noAddButton'] = true;
        $twigConfig['codeListe'] = 'Inscription';
        $twigConfig['retourBouton'] = true;
        $twigConfig['inscription'] = $inscription;
        if ($inscription->getUtilisateur() == $this->getUser() && !($request->getScheme().'://'.$request->getHttpHost().$this->generateUrl('UcaGest_GestionInscription') == $request->headers->get('referer'))) {
            $twigConfig['source'] = 'mesinscriptions';
        } elseif ($this->isGranted('ROLE_GESTION_INSCRIPTION')) {
            $twigConfig['source'] = 'gestioninscription';
        }

        return $this->render('@Uca/UcaWeb/Inscription/DetailInscription.html.twig', $twigConfig);
    }
}
