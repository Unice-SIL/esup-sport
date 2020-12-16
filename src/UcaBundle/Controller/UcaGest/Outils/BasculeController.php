<?php

/*
 * Classe - BasculeController
 *
 * Gestion des deux types bascules de l'application
*/

namespace UcaBundle\Controller\UcaGest\Outils;

use DateInterval;
use DatePeriod;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Entity\Activite;
use UcaBundle\Entity\Autorisation;
use UcaBundle\Entity\CommandeDetail;
use UcaBundle\Entity\DhtmlxEvenement;
use UcaBundle\Entity\DhtmlxSerie;
use UcaBundle\Entity\FormatAchatCarte;
use UcaBundle\Entity\FormatAvecCreneau;
use UcaBundle\Entity\FormatAvecReservation;
use UcaBundle\Entity\FormatSimple;
use UcaBundle\Entity\Inscription;
use UcaBundle\Entity\Lieu;
use UcaBundle\Entity\Materiel;
use UcaBundle\Entity\UtilisateurCreditHistorique;
use UcaBundle\Form\BasculeAnneeUniversitaireType;
use UcaBundle\Form\BasculeType;

/**
 * @Route("UcaGest")
 * @Isgranted("ROLE_GESTION_BASCULE")
 */
class BasculeController extends Controller
{
    /**
     * @Route("/Bascule", name="UcaGest_BasculeAccueil")
     *
     * Fonction qui permet de récupérer toutes les informations et créer des formulaires pour réaliser la bascule semestrielle
     */
    public function afficherAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $listeActivites = [];
        $activites = $em->getRepository(Activite::class)->findAll();
        foreach ($activites as $key => $activite) {
            $key = ucfirst($activite->getLibelle());
            $listeActivites[$key] = $activite->getId();
        }
        $form = $this->get('form.factory')->create(BasculeType::class, null, ['liste_activites' => $listeActivites]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $request->isMethod('POST')) {
            $this->basculeAction($form->getData()['activites'], $form->getData()['nouvelleDateDebutInscription'], $form->getData()['nouvelleDateFinInscription'], $form->getData()['nouvelleDateDebutEffective'], $form->getData()['nouvelleDateFinEffective']);

            $this->get('uca.flashbag')->addMessageFlashBag('bascule.success', 'success');

            return $this->redirect($request->getUri());
        }
        $twigConfig['form'] = $form->createView();

        return $this->render('@Uca/UcaGest/Outils/Bascule/Bascule.html.twig', $twigConfig);
    }

    /**
     * @Route("/Bascule/AnneeUniversitaire", name="UcaGest_BasculeAnneeUniversitaireAccueil")
     *
     * Fonction qui permet de récupérer toutes les informations et créer des formulaires pour réaliser la bascule d'année universitaire
     */
    public function voirAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->get('form.factory')->create(BasculeAnneeUniversitaireType::class, ['em' => $em]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $request->isMethod('POST')) {
            $messageFlashbag = $this->basculeAnneeUniversitaireAction($form->getData());
            $this->get('uca.flashbag')->addMessageFlashBag($messageFlashbag[0], $messageFlashbag[1]);

            return $this->redirect($request->getUri());
        }

        $twigConfig['form'] = $form->createView();

        return $this->render('@Uca/UcaGest/Outils/BasculeAnneeUniversitaire/Voir.html.twig', $twigConfig);
    }

    /**
     * Fonction qui permet de faire la bascule semestrielle.
     *
     * @param [type] $listeActiviteId
     * @param [type] $nouvelleDateDebutInscription
     * @param [type] $nouvelleDateFinInscription
     * @param [type] $nouvelleDateDebutEffective
     * @param [type] $nouvelleDateFinEffective
     */
    private function basculeAction($listeActiviteId, $nouvelleDateDebutInscription, $nouvelleDateFinInscription, $nouvelleDateDebutEffective, $nouvelleDateFinEffective)
    {
        $em = $this->getDoctrine()->getManager();
        $listeInscriptionsBascule = $em->getRepository(Inscription::class)->findInscriptionCreneauxBascule($listeActiviteId);
        foreach ($listeInscriptionsBascule as $inscription) {
            if ('attentevalidationencadrant' == $inscription->getStatut() || 'attentevalidationgestionnaire' == $inscription->getStatut() || 'attenteajoutpanier' == $inscription->getStatut()) {
                $inscription->setStatut('annule', ['motifAnnulation' => 'basculesemestre']);
            } elseif ('valide' == $inscription->getStatut()) {
                $inscription->setStatut('ancienneinscription');
            } elseif ('initialise' == $inscription->getStatut()) {
                $listeCommandeDetail = $em->getRepository(CommandeDetail::class)->findBy(['type' => 'inscription', 'inscription' => $inscription->getId()]);
                foreach ($listeCommandeDetail as $commandeDetail) {
                    $em->remove($commandeDetail);
                }
                $inscription->setStatut('annule', ['motifAnnulation' => 'basculesemestre']);
            }
        }

        $em->getRepository(DhtmlxEvenement::class)->suppressionDateBascule($listeActiviteId, $nouvelleDateDebutEffective);

        foreach ($listeActiviteId as $activiteId) {
            $listeFormatActivite = $em->getRepository(FormatAvecCreneau::class)->findByActivite($activiteId);
            if ($listeFormatActivite) {
                foreach ($listeFormatActivite as $formatActivite) {
                    $formatActivite->setDateDebutInscription($nouvelleDateDebutInscription);
                    $formatActivite->setDateFinInscription($nouvelleDateFinInscription);

                    $formatActivite->setDateDebutEffective($nouvelleDateDebutEffective);
                    $formatActivite->setDateFinEffective($nouvelleDateFinEffective);

                    if ($formatActivite->getDateFinPublication() < $formatActivite->getDateFinInscription()) {
                        $formatActivite->setDateFinPublication($formatActivite->getDateFinInscription());
                    }
                    if ($formatActivite->getDateDebutPublication() > $formatActivite->getDateDebutInscription()) {
                        $formatActivite->setDateDebutPublication($formatActivite->getDateDebutInscription());
                    }
                }
            }
        }
        $em->flush();

        return $this->redirectToRoute('UcaGest_BasculeAccueil');
    }

    /**
     * Fonction qui permet de faire la bascule d'année universitaire.
     *
     * @param [type] $data
     */
    private function basculeAnneeUniversitaireAction($data)
    {
        ini_set('max_execution_time', 0);
        $listeActivite = [];
        $listeOptionCreneau = [];
        $listeLieu = [];
        $listeMateriel = [];

        $em = $data['em'];
        $nouvelleDateDebutInscription = $data['nouvelleDateDebutInscription'];
        $nouvelleDateFinInscription = $data['nouvelleDateFinInscription'];
        $nouvelleDateDebutEffective = $data['nouvelleDateDebutEffective'];
        $nouvelleDateFinEffective = $data['nouvelleDateFinEffective'];
        $nbClasseEtActivite = $data['nbClasseEtActivite'];
        $nbLieu = $data['nbLieu'];
        $nbMateriel = $data['nbMateriel'];
        $basculeClasse = array_slice($data, 1, $nbClasseEtActivite);
        $basculeLieu = array_slice($data, $nbClasseEtActivite + 2, $nbLieu);
        $basculeMateriel = array_slice($data, $nbClasseEtActivite + $nbLieu + 3, $nbMateriel);

        $messageFlashbag = [];
        $cmpt = 0;

        //Suppression (changement de statut) de toutes les inscriptions à chaque bascule
        $listeInscription = $em->getRepository(Inscription::class)->findInscriptionBascule();
        foreach ($listeInscription as $inscription) {
            $inscription->setStatut('ancienneinscription');
        }
        $em->flush();
        //Bascule des classes d'activités et activités

        //On récupère l'id des activités à basculer et l'option choisi pour les créneaux
        foreach ($basculeClasse as $key => $value) {
            if (false !== strpos($key, 'Activite') && $value) {
                $listeActivite[] = explode('-', $key)[1];
            } elseif (false !== strpos($key, 'optionCreneau') && (0 === $value || 1 === $value)) {
                $indexActivite = explode('-', $key)[1];
                $listeOptionCreneau[$indexActivite] = $value;
            }
        }

        if (sizeof($listeActivite) > 0) {
            ++$cmpt;
        }
        foreach ($listeActivite as $id) {
            $listeFormatActivite = $em->getRepository(FormatAvecCreneau::class)->findByActivite($id);
            if ($listeFormatActivite) {
                foreach ($listeFormatActivite as $formatActivite) {
                    $formatActivite->setDateDebutInscription($nouvelleDateDebutInscription);
                    $formatActivite->setDateFinInscription($nouvelleDateFinInscription);

                    $formatActivite->setDateDebutEffective($nouvelleDateDebutEffective);
                    $formatActivite->setDateFinEffective($nouvelleDateFinEffective);

                    if ($formatActivite->getDateFinPublication() < $formatActivite->getDateFinInscription()) {
                        $formatActivite->setDateFinPublication($formatActivite->getDateFinInscription());
                    }
                    if ($formatActivite->getDateDebutPublication() > $formatActivite->getDateDebutInscription()) {
                        $formatActivite->setDateDebutPublication($formatActivite->getDateDebutInscription());
                    }
                }
                $listeCreneau = $formatActivite->getCreneaux();

                $em->flush();
                if (1 == $listeOptionCreneau[$id]) {
                    //Duplique tous les créneaux
                    foreach ($listeCreneau as $creneau) {
                        if (null != $creneau->getSerie() && null != $creneau->getSerie()->getEvenements()[0]) {
                            $serie = $creneau->getSerie();
                            $serie->setDateDebut($nouvelleDateDebutEffective);
                            $serie->setDateFin($nouvelleDateFinEffective);
                            $evenement = $creneau->getSerie()->getEvenements()[0];
                            foreach ($serie->getEvenements() as $event) {
                                foreach ($event->getAppels() as $appel) {
                                    $em->remove($appel);
                                }
                                $em->remove($event);
                            }
                            $interval = DateInterval::createFromDateString('1 day');
                            $period = new DatePeriod($nouvelleDateDebutEffective, $interval, $nouvelleDateFinEffective);
                            foreach ($period as $dt) {
                                if (date_format($dt, 'w') == date_format($evenement->getDateDebut(), 'w')) {
                                    $tmp = clone $dt;
                                    $dateDebut = $dt->setTime(
                                        date_format($evenement->getDateDebut(), 'H'),
                                        date_format($evenement->getDateDebut(), 'i'),
                                        date_format($evenement->getDateDebut(), 's')
                                    );
                                    $dateFin = $tmp->setTime(
                                        date_format($evenement->getDateFin(), 'H'),
                                        date_format($evenement->getDateFin(), 'i'),
                                        date_format($evenement->getDateFin(), 's')
                                    );
                                    $new_evenement = $this->createEvenement($evenement, $dateDebut, $dateFin, $serie);
                                    $serie->addEvenement($new_evenement);
                                    $em->persist($new_evenement);
                                    // $em->persist($serie);
                                }
                            }
                        }
                    }
                    $em->flush();
                } elseif (0 == $listeOptionCreneau[$id]) {
                    //Supprime tous les créneaux
                    foreach ($listeCreneau as $creneau) {
                        $listeDhtmlXSerie = $em->getRepository(DhtmlxSerie::class)->findByCreneau($creneau->getId());
                        foreach ($listeDhtmlXSerie as $serie) {
                            $listeDhtmlXEvenement = $em->getRepository(DhtmlxEvenement::class)->findBySerie($serie->getId());
                            foreach ($listeDhtmlXEvenement as $evenement) {
                                foreach ($evenement->getAppels() as $appel) {
                                    $em->remove($appel);
                                }
                                $em->remove($evenement);
                            }
                            $em->remove($serie);
                        }
                        $em->remove($creneau);
                    }
                    $em->flush();
                }
            }
        }

        //Bascule des événements (changement statut inscription)
        if ($data['basculeDesEvenements']) {
            $listeEvent = $em->getRepository(FormatSimple::class)->findFormatSimpleByDate($nouvelleDateDebutEffective);
            foreach ($listeEvent as $event) {
                $inscriptions = $event->getInscriptions();
                foreach ($inscriptions as $insc) {
                    $insc->setStatut('ancienneinscription');
                }
            }
            $em->flush();
            ++$cmpt;
        }

        //Suppression des réservations de ressources et équipements
        if ($data['basculeDesReservations']) {
            $listeInscription = $em->getRepository(Inscription::class)->findReservation();
            foreach ($listeInscription as $inscription) {
                $inscription->setStatut('ancienneinscription');
            }
            $em->flush();
            ++$cmpt;
        }

        //Duplication des formats de réservation de ressources
        if ($data['dupliquerFormatAvecReservation']) {
            $formats = $em->getRepository(FormatAvecReservation::class)->findAll();
            foreach ($formats as $format) {
                $format->setDateDebutEffective($nouvelleDateDebutEffective);
                $format->setDateFinEffective($nouvelleDateFinEffective);
                $format->setDateDebutInscription($nouvelleDateDebutInscription);
                $format->setDateFinInscription($nouvelleDateFinInscription);
                if ($format->getDateFinPublication() < $format->getDateFinInscription()) {
                    $format->setDateFinPublication($format->getDateFinInscription());
                }
                if ($format->getDateDebutPublication() > $format->getDateDebutInscription()) {
                    $format->setDateDebutPublication($format->getDateDebutInscription());
                }
            }
            $seriesReservation = $em->getRepository(DhtmlxSerie::class)->findByCreneau(null);
            foreach ($seriesReservation as $serie) {
                $serie->setDateDebut($nouvelleDateDebutEffective);
                $serie->setDateFin($nouvelleDateFinEffective);
                if ($serie->getEvenements()) {
                    $evenement = $serie->getEvenements()[0];
                    foreach ($serie->getEvenements() as $event) {
                        foreach ($event->getAppels() as $appel) {
                            $em->remove($appel);
                        }
                        $em->remove($event);
                    }
                    $interval = DateInterval::createFromDateString('1 day');
                    $period = new DatePeriod($nouvelleDateDebutEffective, $interval, $nouvelleDateFinEffective);
                    foreach ($period as $dt) {
                        if (date_format($dt, 'w') == date_format($evenement->getDateDebut(), 'w')) {
                            $tmp = clone $dt;
                            $dateDebut = $dt->setTime(
                                date_format($evenement->getDateDebut(), 'H'),
                                date_format($evenement->getDateDebut(), 'i'),
                                date_format($evenement->getDateDebut(), 's')
                            );
                            $dateFin = $tmp->setTime(
                                date_format($evenement->getDateFin(), 'H'),
                                date_format($evenement->getDateFin(), 'i'),
                                date_format($evenement->getDateFin(), 's')
                            );
                            $new_evenement = $this->createEvenement($evenement, $dateDebut, $dateFin, $serie);
                            $serie->addEvenement($new_evenement);
                            $em->persist($new_evenement);
                        }
                    }
                }
            }
            $em->flush();
            ++$cmpt;
        }

        //Bascule des lieux
        foreach ($basculeLieu as $key => $value) {
            if (1 == $value) {
                $listeLieu[] = explode('-', $key)[1];
            }
        }
        if (sizeof($listeLieu) > 0) {
            ++$cmpt;
            foreach ($listeLieu as $id) {
                $lieu = $em->getRepository(Lieu::class)->find($id);
                $listeFormatActivite = $lieu->getFormatResa();
                foreach ($listeFormatActivite as $formatActivite) {
                    $formatActivite->setDateDebutEffective($nouvelleDateDebutEffective);
                    $formatActivite->setDateFinEffective($nouvelleDateFinEffective);
                    $formatActivite->setDateDebutInscription($nouvelleDateDebutInscription);
                    $formatActivite->setDateFinInscription($nouvelleDateFinInscription);
                }
            }
            $em->flush();
        }

        //Bascule des Equipements
        foreach ($basculeMateriel as $key => $value) {
            if (1 == $value) {
                $listeMateriel[] = explode('-', $key)[1];
            }
        }
        if (sizeof($listeMateriel) > 0) {
            ++$cmpt;
            foreach ($listeMateriel as $id) {
                $materiel = $em->getRepository(Materiel::class)->find($id);
                $listeFormatActivite = $materiel->getFormatResa();
                foreach ($listeFormatActivite as $formatActivite) {
                    $formatActivite->setDateDebutEffective($nouvelleDateDebutEffective);
                    $formatActivite->setDateFinEffective($nouvelleDateFinEffective);
                    $formatActivite->setDateDebutInscription($nouvelleDateDebutInscription);
                    $formatActivite->setDateFinInscription($nouvelleDateFinInscription);
                }
            }
            $em->flush();
        }

        //Suppression des crédits
        $listeCredit = $em->getRepository(UtilisateurCreditHistorique::class)->findAll();
        if ($data['basculeCredit']) {
            foreach ($listeCredit as $credit) {
                if ('valide' == $credit->getStatut()) {
                    $credit->setStatut('annule');
                }
            }
            $em->flush();
        }
        //Suppression des cartes et cotisations
        if ($data['basculeCarteEtCotisation']) {
            //Autorisations
            $autorisations = $em->getRepository(Autorisation::class)->findAll();
            foreach ($autorisations as $autorisation) {
                $em->remove($autorisation);
            }
            $em->flush();

            //Cartes
            $commandeDetails = $em->getRepository('UcaBundle:CommandeDetail')->findCommandeDetailAncienneCarte();
            foreach ($commandeDetails as $commandeDetail) {
                $utilisateur = $commandeDetail->getCommande()->getUtilisateur();
                $typeAutorisation = $commandeDetail->getTypeAutorisation();
                $utilisateur->removeAutorisation($typeAutorisation);
                $em->persist($utilisateur);
            }
            $em->flush();
            ++$cmpt;
        }
        //Duplication des formats d'achat de carte
        if ($data['dupliquerFormatAchatCarte']) {
            $formats = $em->getRepository(FormatAchatCarte::class)->findAll();
            foreach ($formats as $format) {
                $format->setDateDebutEffective($nouvelleDateDebutEffective);
                $format->setDateFinEffective($nouvelleDateFinEffective);
                $format->setDateDebutInscription($nouvelleDateDebutInscription);
                $format->setDateFinInscription($nouvelleDateFinInscription);
                if ($format->getDateFinPublication() < $format->getDateFinInscription()) {
                    $format->setDateFinPublication($format->getDateFinInscription());
                }
                if ($format->getDateDebutPublication() > $format->getDateDebutInscription()) {
                    $format->setDateDebutPublication($format->getDateDebutInscription());
                }
            }
            $em->flush();
            ++$cmpt;
        }

        if ($cmpt > 0) {
            $messageFlashbag = ['bascule.success', 'success'];
        } else {
            $messageFlashbag = ['bascule.aucun.choix', 'danger'];
        }

        return $messageFlashbag;
    }

    /**
     * Fonction qui permet de créer un nouvel événement lors de la duplication de créneau de la bascule d'année universitaire.
     *
     * @param [type] $dateDebut
     * @param [type] $dateFin
     * @param [type] $serie
     */
    private function createEvenement(DhtmlxEvenement $evenement, $dateDebut, $dateFin, $serie)
    {
        $new_evenement = new DhtmlxEvenement();
        $new_evenement->setReservabilite($evenement->getReservabilite());
        $new_evenement->setFormatSimple($evenement->getFormatSimple());
        $new_evenement->setDescription($evenement->getDescription());
        $new_evenement->setDependanceSerie($evenement->getDependanceSerie());
        $new_evenement->setEligibleBonus($evenement->getEligibleBonus());
        $new_evenement->setDateDebut($dateDebut);
        $new_evenement->setDateFin($dateFin);
        $new_evenement->setSerie($serie);

        return $new_evenement;
    }
}
