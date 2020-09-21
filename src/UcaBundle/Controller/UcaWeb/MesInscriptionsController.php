<?php

/*
 * Classe - MesInscriptionsController
 *
 * Gestion des inscription côté Web
 * Inscription de l'utilisateur
 * Annulation d'une inscription
 * Ajouter un élement au panier
 * La desincription massive est intégré à ce contrôleur
*/

namespace UcaBundle\Controller\UcaWeb;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Datatables\GestionInscriptionDatatable;
use UcaBundle\Datatables\MesInscriptionsDatatable;
use UcaBundle\Entity\Activite;
use UcaBundle\Entity\ClasseActivite;
use UcaBundle\Entity\Etablissement;
use UcaBundle\Entity\FormatActivite;
use UcaBundle\Entity\Groupe;
use UcaBundle\Entity\Inscription;
use UcaBundle\Entity\Lieu;
use UcaBundle\Entity\TypeActivite;
use UcaBundle\Entity\Utilisateur;
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
                    'listeEncadrant' => $em->getRepository(Groupe::class)->findByLibelle('Encadrant')[0]->getUtilisateurs(),
                    'listeEtablissement' => $em->getRepository(Etablissement::class)->findAll(),
                    'listeLieu' => $em->getRepository(Lieu::class)->findAll(),
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
        //Bouton Tout supprimer
        if ($this->isGranted('ROLE_GESTION_SUPPRESSION_MASSIVE') or $this->isGranted('ROLE_GESTION_INSCRIPTION')) {
            $twigConfig['toutSupprimer'] = true;
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
        } else {
            $redirect = $this->redirectToRoute('UcaWeb_MesInscriptions');
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
        $inscriptionService->ajoutPanier();
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
            $inscription->setUtilisateurDesinscription($this->getUser());
            $inscription->setNomDesinscription($this->getUser()->getNom());
            $inscription->setPrenomDesinscription($this->getUser()->getPrenom());
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
        } else {
            return $this->redirectToRoute('UcaWeb_MesInscriptions');
        }

        return $this->render('@Uca/UcaWeb/Inscription/DetailInscription.html.twig', $twigConfig);
    }

    /**
     * @Route("UcaGest/GestionInscription/DesinscriptionMassive/{nom}/{prenom}/{statut}/{idTypeActivite}/{idClasseActivite}/{idActivite}/{idFormatActivite}/{idCreneau}/{idEncadrant}/{idEtablissement}/{idLieu}", name="UcaGest_GestionInscription_DesincriptionMassive")
     * @Security("has_role('ROLE_GESTION_SUPPRESSION_MASSIVE') or has_role('ROLE_GESTION_INSCRIPTION')")
     *
     * @param null|mixed $idActivite
     * @param null|mixed $idEncadrant
     * @param null|mixed $idEtablissement
     * @param null|mixed $idLieu
     * @param null|mixed $nom
     * @param null|mixed $prenom
     * @param null|mixed $statut
     * @param null|mixed $idTypeActivite
     * @param null|mixed $idClasseActivite
     * @param null|mixed $idFormatActivite
     * @param null|mixed $idCreneau
     */
    public function desincriptionMassiveAction(Request $request, $nom = null, $prenom = null, $statut = null, $idTypeActivite = null, $idClasseActivite = null, $idActivite = null, $idFormatActivite = null, $idCreneau = null, $idEncadrant = null, $idEtablissement = null, $idLieu = null)
    {
        if ($this->isGranted('ROLE_GESTION_INSCRIPTION')) {
            $em = $this->getDoctrine()->getManager();
            $inscriptions = [];
            $compteurFiltre = 0;

            null != $nom and 'null' != $nom ? $compteurFiltre++ : $nom = null;
            null != $prenom and 'null' != $prenom ? $compteurFiltre++ : $prenom = null;
            null != $statut and '0' != $statut ? $compteurFiltre++ : $statut = null;
            null != $idTypeActivite and '0' != $idTypeActivite ? $compteurFiltre++ : $idTypeActivite = null;
            null != $idClasseActivite and '0' != $idClasseActivite ? $compteurFiltre++ : $idClasseActivite = null;
            null != $idActivite and '0' != $idActivite ? $compteurFiltre++ : $idActivite = null;
            null != $idFormatActivite and '0' != $idFormatActivite ? $compteurFiltre++ : $idFormatActivite = null;
            if (null != $idCreneau and '0' != $idCreneau) {
                if (false === strpos($idCreneau, 'allCreneaux')) {
                    ++$compteurFiltre;
                } else {
                    $idCreneau = null;
                }
            } else {
                $idCreneau = null;
            }
            if (null != $idEncadrant and '0' != $idEncadrant) {
                $encadrant = $em->getRepository(Utilisateur::class)->find($idEncadrant);
                ++$compteurFiltre;
            } else {
                $encadrant = null;
            }
            if (null != $idEtablissement and '0' != $idEtablissement) {
                $etablissement = $em->getRepository(Etablissement::class)->find($idEtablissement);
                ++$compteurFiltre;
            } else {
                $etablissement = null;
            }
            if (null != $idLieu and '0' != $idLieu) {
                $lieu = $em->getRepository(Lieu::class)->find($idLieu);
                ++$compteurFiltre;
            } else {
                $lieu = null;
            }

            $inscriptions = $em->getRepository(Inscription::class)->findInscriptionForDesincription($nom, $prenom, $statut, $idTypeActivite, $idClasseActivite, $idActivite, $idFormatActivite, $idCreneau, $encadrant, $etablissement, $lieu);

            //Envoie des valeurs à afficher à la modal de confirmation
            if ($request->isXmlHttpRequest()) {
                $inscrit = 0;
                $attentepaiement = 0;
                $validationencadrant = 0;
                $validationgestionnaire = 0;

                //On vérifie si des filtres ont été appliqué pour valider la suprression
                if ($compteurFiltre > 0 and !empty($inscriptions)) {
                    foreach ($inscriptions as $inscription) {
                        if (null != $inscription) {
                            switch ($inscription->getStatut()) {
                                case 'valide':
                                    $inscrit++;

                                    break;
                                case 'attentepaiement':
                                    $attentepaiement++;

                                    break;
                                case 'attentevalidationgestionnaire':
                                    $validationgestionnaire++;

                                    break;
                                case 'attentevalidationencadrant':
                                    $validationencadrant++;

                                    break;
                            }
                        }
                    }
                    $response = new Response(json_encode([
                        'filtre' => true,
                        'valide' => $inscrit,
                        'attentepaiement' => $attentepaiement,
                        'attentevalidationgestionnaire' => $validationgestionnaire,
                        'attentevalidationencadrant' => $validationencadrant,
                    ]));
                } else {
                    $response = new Response(json_encode([
                        'filtre' => false,
                    ]));
                }

                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }

            if ($compteurFiltre > 0 and !empty($inscriptions)) {
                foreach ($inscriptions as $inscription) {
                    if (null != $inscription) {
                        $inscription->setStatut('desinscriptionadministrative');
                        $inscription->setDateDesinscription(new \DateTime());
                        $inscription->setUtilisateurDesinscription($this->getUser());
                        $inscription->setNomDesinscription($this->getUser()->getNom());
                        $inscription->setPrenomDesinscription($this->getUser()->getPrenom());
                    }
                }
                $em->flush();
                $this->get('uca.flashbag')->addMessageFlashBag('message.desinscriptionmassive.success', 'success');
            }

            return $this->redirectToRoute('UcaGest_GestionInscription');
        }

        return $this->redirectToRoute('UcaWeb_MesInscriptions');
    }
}
