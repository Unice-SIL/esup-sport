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

namespace App\Controller\UcaWeb;

use App\Datatables\GestionInscriptionDatatable;
use App\Datatables\MesInscriptionsDatatable;
use App\Entity\Uca\Etablissement;
use App\Entity\Uca\Inscription;
use App\Entity\Uca\Lieu;
use App\Entity\Uca\Utilisateur;
use App\Form\GestionInscriptionType;
use App\Service\Common\FlashBag;
use App\Service\Securite\TimeoutService;
use App\Service\Service\ExtractionInscriptionService;
use App\Service\Service\InscriptionService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sg\DatatablesBundle\Datatable\DatatableFactory;
use Sg\DatatablesBundle\Response\DatatableResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class MesInscriptionsController extends AbstractController
{
    /**
     * @Route("UcaGest/GestionInscription",name="UcaGest_GestionInscription")
     * @Route("UcaWeb/MesInscriptions",name="UcaWeb_MesInscriptions")
     */
    public function listerAction(Request $request, DatatableFactory $datatableFactory, DatatableResponse $datatableResponse, TimeoutService $timeoutService, ExtractionInscriptionService $extraction)
    {
        if ('UcaGest_GestionInscription' == $request->get('_route') && !$this->isGranted('ROLE_GESTION_INSCRIPTION')) {
            return $this->redirectToRoute('UcaWeb_MesInscriptions');
        }
        $timeoutService->nettoyageCommandeEtInscription();
        $isAjax = $request->isXmlHttpRequest();

        if ('UcaGest_GestionInscription' == $request->get('_route')) {
            $form = $this->createForm(GestionInscriptionType::class, null, $extraction->getOptionsInscription());
            $datatable = $datatableFactory->create(GestionInscriptionDatatable::class);
            $twigConfig['form'] = $form->createView();
            $twigConfig['codeListe'] = 'GestionInscription';
        } else {
            $datatable = $datatableFactory->create(MesInscriptionsDatatable::class);
            $twigConfig['codeListe'] = 'MesInscriptions';
        }
        $datatable->buildDatatable();

        $twigConfig['datatable'] = $datatable;
        if ($isAjax) {
            $responseService = $datatableResponse;
            $responseService->setDatatable($datatable);
            $dtQueryBuilder = $responseService->getDatatableQueryBuilder();
            if ('UcaWeb_MesInscriptions' == $request->get('_route')) {
                $qb = $dtQueryBuilder->getQb();
                $qb->andWhere('utilisateur = :objectId');
                $qb->setParameter('objectId', $this->getUser()->getId());
            }

            return $responseService->getResponse();
        }
        // Bouton Ajouter
        $twigConfig['noAddButton'] = true;
        if ('UcaWeb_MesInscriptions' == $request->get('_route')) {
            return $this->render('UcaBundle/Common/Liste/Datatable_UcaWeb.html.twig', $twigConfig);
        }
        // Bouton Tout supprimer
        if ($this->isGranted('ROLE_GESTION_SUPPRESSION_MASSIVE') or $this->isGranted('ROLE_GESTION_INSCRIPTION')) {
            $twigConfig['toutSupprimer'] = true;
        }

        return $this->render('UcaBundle/UcaGest/Reporting/Inscriptions/Datatable.html.twig', $twigConfig);
    }

    /**
     * @Route("UcaWeb/{id}/Annuler", name="UcaWeb_MesInscriptionsAnnuler")
     */
    public function annulerAction(Request $request, Inscription $inscription, InscriptionService $inscriptionService, EntityManagerInterface $em)
    {
        if ($inscription->getUtilisateur() == $this->getUser() && !($request->getScheme().'://'.$request->getHttpHost().$this->generateUrl('UcaGest_GestionInscription') == $request->headers->get('referer'))) {
            $inscriptionService->updateStatutInscriptionsPartenaire($inscription);
            $inscription->setStatut('annule', ['motifAnnulation' => 'annulationutilisateur']);
            $redirect = $this->redirectToRoute('UcaWeb_MesInscriptions');
        } elseif ($this->isGranted('ROLE_GESTION_INSCRIPTION')) {
            $inscriptionService->updateStatutInscriptionsPartenaire($inscription);
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
    public function ajoutPanierAction(Request $request, Inscription $inscription, InscriptionService $inscriptionService, EntityManagerInterface $em)
    {
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
    public function seDesinscrireAction(Request $request, Inscription $inscription, InscriptionService $inscriptionService, EntityManagerInterface $em)
    {
        if ($this->isGranted('ROLE_GESTION_INSCRIPTION') or $this->isGranted('ROLE_ENCADRANT') or $inscription->getUtilisateur() == $this->getUser()) {
            $inscriptionService->setInscription($inscription);
            $inscriptionService->mailDesinscription($inscription);

            $inscriptionService->updateStatutInscriptionsPartenaire($inscription);
            $inscription->seDesinscrire($this->getUser());
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
    public function voirAction(Request $request, Inscription $inscription, EntityManagerInterface $em)
    {
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

        return $this->render('UcaBundle/UcaWeb/Inscription/DetailInscription.html.twig', $twigConfig);
    }

    /**
     * @Route("UcaGest/GestionInscription/DesinscriptionMassive/{nom}/{prenom}/{statut}/{idTypeActivite}/{idClasseActivite}/{idActivite}/{idFormatActivite}/{idCreneau}/{idEncadrant}/{idEtablissement}/{idLieu}", name="UcaGest_GestionInscription_DesincriptionMassive")
     * @Security("is_granted('ROLE_GESTION_SUPPRESSION_MASSIVE') or is_granted('ROLE_GESTION_INSCRIPTION')")
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
    public function desincriptionMassiveAction(Request $request, $nom = null, $prenom = null, $statut = null, $idTypeActivite = null, $idClasseActivite = null, $idActivite = null, $idFormatActivite = null, $idCreneau = null, $idEncadrant = null, $idEtablissement = null, $idLieu = null, FlashBag $flashBag, InscriptionService $inscriptionService, EntityManagerInterface $em)
    {
        if ($this->isGranted('ROLE_GESTION_INSCRIPTION')) {
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

            // Envoie des valeurs à afficher à la modal de confirmation
            if ($request->isXmlHttpRequest()) {
                $inscrit = 0;
                $attentepaiement = 0;
                $validationencadrant = 0;
                $validationgestionnaire = 0;

                // On vérifie si des filtres ont été appliqué pour valider la suprression
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
                        $inscriptionService->updateStatutInscriptionsPartenaire($inscription);
                        $inscription->setStatut('desinscriptionadministrative')
                            ->setDateDesinscription(new \DateTime())
                            ->setUtilisateurDesinscription($this->getUser())
                            ->setNomDesinscription($this->getUser()->getNom())
                            ->setPrenomDesinscription($this->getUser()->getPrenom())
                        ;
                    }
                }
                $em->flush();
                $flashBag->addMessageFlashBag('message.desinscriptionmassive.success', 'success');
            }

            return $this->redirectToRoute('UcaGest_GestionInscription');
        }

        return $this->redirectToRoute('UcaWeb_MesInscriptions');
    }
}
