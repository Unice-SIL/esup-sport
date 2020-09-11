<?php

/*
 * Classe - StatistiqueController
 *
 * Gestion des statistiques
*/

namespace UcaBundle\Controller\Api;

use DateInterval;
use DatePeriod;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Entity\FormatAchatCarte;
use UcaBundle\Entity\FormatAvecCreneau;
use UcaBundle\Service\Common\Parametrage;

class StatistiqueController extends Controller
{
    /**
     * @Route("/Api/Statistique/NbreInscription", methods={"GET"}, name="StatistiqueApi_NbreInscriptions", options={"expose"=true})
     * @Isgranted("ROLE_GESTION_EXTRACTION")
     */
    public function getNbreInscriptionCoursAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $res = [];

        $classActivites = [];
        foreach ($em->getRepository('UcaBundle:Inscription')->getNbreInscriptionByClasseActiviteForStatut('valide') as $value) {
            $classActivites[] = [$value['libelle'] => $value[1]];
        }
        $activites = [];
        foreach ($em->getRepository('UcaBundle:Inscription')->getNbreInscriptionByActiviteForStatut('valide') as $value) {
            $activites[] = [$value['libelle'] => $value[1]];
        }

        $res = [$classActivites, $activites];

        return new JsonResponse($res);
    }

    /**
     * @Route("/Api/Statistique/DetailsProfils", methods={"GET"}, name="StatistiqueApi_DetailsProfils", options={"expose"=true})
     * @Isgranted("ROLE_GESTION_EXTRACTION")
     */
    public function getProfilUtilisateurAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $listeProfil = [];
        $profilUtilisateurs = $em->getRepository('UcaBundle:ProfilUtilisateur')->findAll();

        foreach ($profilUtilisateurs as $profilUtilisateur) {
            $utilisateurs = $profilUtilisateur->getUtilisateur();
            $listeProfil[] = [$profilUtilisateur->getLibelle() => sizeof($utilisateurs)];
        }

        return new JsonResponse($listeProfil);
    }

    /**
     * @Route("/Api/Statistique/DetailsGenreAge", methods={"GET"}, name="StatistiqueApi_DetailsGenreAge", options={"expose"=true})
     * @Isgranted("ROLE_GESTION_EXTRACTION")
     */
    public function getDetailsGenreAgeAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $translator = $this->get('translator');
        $hommeLibelle = $translator->trans('statistique.utilisateur.homme');
        $femmeLibelle = $translator->trans('statistique.utilisateur.femme');
        $ageLibelle = $translator->trans('statistique.utilisateur.age');
        $na = $translator->trans('statistique.nonrenseigne');
        $dataSexe = [$translator->trans('statistique.utilisateur.homme') => (int) 0, $translator->trans('statistique.utilisateur.femme') => (int) 0];
        $dataAge = ['-20', '20-29', '30-39', '40-49', '+50', $na];
        $utilisateurs = ($this->getDoctrine()->getManager('statistique'))->getRepository('StatistiqueBundle:DataUtilisateur')->findBy(
            ['anneeUniversitaire' => Parametrage::get()->getAnneeUniversitaire()]
        );
        $today = new \DateTime();
        $totalH = 0;
        $totalF = 0;
        foreach ($dataAge as $key) {
            if ($key != $na) {
                $listeUtilisateurAge[$key.' '.$ageLibelle] = $dataSexe;
            } else {
                $listeUtilisateurAge[$key] = $dataSexe;
            }
        }

        foreach ($utilisateurs as $utilisateur) {
            if ('M' == $utilisateur->getSexe()) {
                $sexe = $hommeLibelle;
                ++$totalH;
            } else {
                $sexe = $femmeLibelle;
                ++$totalF;
            }

            $dateNaissance = \DateTime::createFromFormat('m/Y', $utilisateur->getDateNaissance());
            $age = (null != $dateNaissance) ? intval(($today->diff($dateNaissance))->format('%y')) : 0;

            if ($age >= 50) {
                $tranche = '+50 '.$ageLibelle;
            } elseif ($age >= 40) {
                $tranche = '40-49 '.$ageLibelle;
            } elseif ($age >= 30) {
                $tranche = '30-39 '.$ageLibelle;
            } elseif ($age >= 20) {
                $tranche = '20-29 '.$ageLibelle;
            } elseif ($age >= 1) {
                $tranche = '-20 '.$ageLibelle;
            } else {
                $tranche = $na;
            }

            $val = $listeUtilisateurAge[$tranche][$sexe];
            $listeUtilisateurAge[$tranche][$sexe] = $val + 1;
        }

        return new JsonResponse([$listeUtilisateurAge, [[$femmeLibelle => $totalF], [$hommeLibelle => $totalH]]]);
    }

    /**
     * @Route("/Api/Statistique/DetailsPersonnelCategorie",methods={"GET"}, name="StatistiqueApi_DetailsPersonnelCategorie",options={"expose"=true})
     * @Isgranted("ROLE_GESTION_EXTRACTION")
     */
    public function getDetailsPersonnelCategorie(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoStats = $this->getDoctrine()->getManager('statistique')->getRepository('StatistiqueBundle:DataUtilisateur');
        $repoUser = $em->getRepository('UcaBundle:Utilisateur');

        $twigConfig['annee_n'] = Parametrage::get()->getAnneeUniversitaire();
        $twigConfig['annee_n_1'] = Parametrage::get()->getAnneeUniversitaire() - 1;

        // Nombre de personnels pour annee N et N-1
        $nbreUser_n = $repoStats->getCodEtu(Parametrage::get()->getAnneeUniversitaire(), true);
        $twigConfig['nombre_personnel_annee_n'] = $nbreUser_n[2];
        $nbreUser_n_1 = $repoStats->getCodEtu(Parametrage::get()->getAnneeUniversitaire() - 1, true);
        $twigConfig['nombre_personnel_annee_n_1'] = $nbreUser_n_1[2];

        // Nombre de personnels inscrits sur la plateforme pour annee N et N-1
        $listeUsersInscrits_n = $repoUser->getNbreUserInscript(explode(',', $nbreUser_n[1]));
        $twigConfig['nombre_personnel_inscrit_annee_n'] = $listeUsersInscrits_n[2];
        $listeUsersInscrits_n_1 = $repoUser->getNbreUserInscript(explode(',', $nbreUser_n_1[1]));
        $twigConfig['nombre_personnel_inscrit_annee_n_1'] = $nbreUser_n_1[2];

        // Nbre par categorie
        $nbreUserByCat_n = $repoStats->getNbreByCategorie(Parametrage::get()->getAnneeUniversitaire(), explode(',', $listeUsersInscrits_n[1]));
        $nbreUserByCat_A_n = 0;
        $nbreUserByCat_B_n = 0;
        $nbreUserByCat_C_n = 0;
        foreach ($nbreUserByCat_n as $value) {
            switch ($value['categorie']) {
                case 'CAT_A':
                    $nbreUserByCat_A_n = $value[1];

                    break;
                case 'CAT_B':
                    $nbreUserByCat_B_n = $value[1];

                    break;
                case 'CAT_C':
                    $nbreUserByCat_C_n = $value[1];

                    break;
                default:
                    break;
            }
        }
        $twigConfig['nombre_personnel_inscrit_cat_A_annee_n'] = $nbreUserByCat_A_n;
        $twigConfig['nombre_personnel_inscrit_cat_B_annee_n'] = $nbreUserByCat_B_n;
        $twigConfig['nombre_personnel_inscrit_cat_C_annee_n'] = $nbreUserByCat_C_n;
        $nbreUserByCat_n_1 = $repoStats->getNbreByCategorie(Parametrage::get()->getAnneeUniversitaire() - 1, explode(',', $listeUsersInscrits_n_1[1]));
        $nbreUserByCat_A_n_1 = 0;
        $nbreUserByCat_B_n_1 = 0;
        $nbreUserByCat_C_n_1 = 0;
        foreach ($nbreUserByCat_n_1 as $value) {
            switch ($value['categorie']) {
                case 'CAT_A':
                    $nbreUserByCat_A_n_1 = $value[1];

                    break;
                case 'CAT_B':
                    $nbreUserByCat_B_n_1 = $value[1];

                    break;
                case 'CAT_C':
                    $nbreUserByCat_C_n_1 = $value[1];

                    break;
                default:
                    break;
            }
        }
        $twigConfig['nombre_personnel_inscrit_cat_A_annee_n_1'] = $nbreUserByCat_A_n_1;
        $twigConfig['nombre_personnel_inscrit_cat_B_annee_n_1'] = $nbreUserByCat_B_n_1;
        $twigConfig['nombre_personnel_inscrit_cat_C_annee_n_1'] = $nbreUserByCat_C_n_1;

        return new JsonResponse(['data' => $this->render('@Uca/UcaGest/Reporting/Statistiques/TableauPersonnels.html.twig', $twigConfig)->getContent()]);
    }

    /**
     * @Route("/Api/Statistique/DetailsNiveau", methods={"GET"}, name="StatistiqueApi_DetailsNiveau", options={"expose"=true})
     * @Isgranted("ROLE_GESTION_EXTRACTION")
     */
    public function getDetailsNiveau(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $emStat = $this->getDoctrine()->getManager('statistique');

        $listeUtilisateur = $em->getRepository('UcaBundle:Utilisateur')->findAll();
        $tab = [];
        $listeEtudiantParNiveau = [];
        foreach ($listeUtilisateur as $utilisateur) {
            $username = $utilisateur->getUsername();
            $user = $emStat->getRepository('StatistiqueBundle:DataUtilisateur')->findONeBy(['codEtu' => $username, 'anneeUniversitaire' => Parametrage::get()->getAnneeUniversitaire()]);

            if (null != $user) {
                $key = $user->getNiveau();
                if (isset($tab[$key])) {
                    $tab[$key] = $tab[$key] + 1;
                } else {
                    $tab[$key] = 1;
                }
            }
        }

        ksort($tab);
        foreach ($tab as $key => $value) {
            $listeEtudiantParNiveau[] = [$key => $value];
        }

        return new JsonResponse([$listeEtudiantParNiveau]);
    }

    /**
     * @Route("/Api/Statistique/CustomChart", name="UcaGest_CustomChart",  options={"expose"=true}, methods={"POST"})
     * @Isgranted("ROLE_GESTION_EXTRACTION")
     */
    public function createCustomChartAction(Request $request)
    {
        $data = [];

        $data['creneau'] = $request->get('creneau');
        $data['formatActivite'] = $request->get('formatActivite');
        $data['activite'] = $request->get('activite');
        $data['classe_activite'] = $request->get('classe_activite');
        $data['type_activite'] = $request->get('type_activite');
        $data['options'] = $request->get('options');

        $result = $this->getDataToDrawCustomChart($data);

        return new JsonResponse($result);
    }

    /**
     * @Route("/Api/Statistique/InfoConnexion", methods={"GET"}, name="StatistiqueApi_InfoConnexion", options={"expose"=true})
     * @Isgranted("ROLE_GESTION_EXTRACTION")
     */
    public function getInfoConnexion(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $emStat = $this->getDoctrine()->getManager('statistique');
        $translator = $this->get('translator');
        $ageHoraire = $translator->trans('statistique.utilisateur.heure');
        $ageLibelle = $translator->trans('statistique.utilisateur.age');
        $na = $translator->trans('statistique.nonrenseigne');
        $today = new \DateTime();

        $nbreByAge = [];
        $nbreByGenre = [];
        $nbreByStatut = [];

        $listeLogConnexion = $em->getRepository('UcaBundle:LogConnexion')->findAll();
        foreach ($listeLogConnexion as $logConnexion) {
            $utilisateur = $logConnexion->getUtilisateur();
            $username = $utilisateur->getUsername();
            $dataUser = $emStat->getRepository('StatistiqueBundle:DataUtilisateur')->findONeBy(['codEtu' => $username, 'anneeUniversitaire' => Parametrage::get()->getAnneeUniversitaire()]);

            if (null != $dataUser) {
                $horaireConnexion = $logConnexion->getDateConnexion()->format('H');
                $genre = $dataUser->getSexe();
                $dateNaissance = \DateTime::createFromFormat('m/Y', $dataUser->getDateNaissance());
                $profil = $utilisateur->getProfil()->getLibelle();

                if ($horaireConnexion >= 20) {
                    $transheHoraire = '+20 '.$ageHoraire;
                } elseif ($horaireConnexion >= 17) {
                    $transheHoraire = '17-19 '.$ageHoraire;
                } elseif ($horaireConnexion >= 14) {
                    $transheHoraire = '14-16 '.$ageHoraire;
                } elseif ($horaireConnexion >= 12) {
                    $transheHoraire = '12-13 '.$ageHoraire;
                } elseif ($horaireConnexion >= 9) {
                    $transheHoraire = '09-11 '.$ageHoraire;
                } elseif ($horaireConnexion >= 7) {
                    $transheHoraire = '07-08 '.$ageHoraire;
                } elseif ($horaireConnexion >= 0) {
                    $transheHoraire = '-07 '.$ageHoraire;
                } else {
                    $transheHoraire = $na;
                }

                $age = (null != $dateNaissance) ? intval(($today->diff($dateNaissance))->format('%y')) : 0;
                if ($age >= 50) {
                    $trancheAge = '+50 '.$ageLibelle;
                } elseif ($age >= 40) {
                    $trancheAge = '40-49 '.$ageLibelle;
                } elseif ($age >= 30) {
                    $trancheAge = '30-39 '.$ageLibelle;
                } elseif ($age >= 20) {
                    $trancheAge = '20-29 '.$ageLibelle;
                } elseif ($age >= 1) {
                    $trancheAge = '-20 '.$ageLibelle;
                } else {
                    $trancheAge = $na;
                }

                // Gestion des donnees Age
                if (array_key_exists($transheHoraire, $nbreByAge)) {
                    if (array_key_exists($trancheAge, $nbreByAge[$transheHoraire])) {
                        $val = $nbreByAge[$transheHoraire][$trancheAge];
                    } else {
                        $val = 0;
                    }
                } else {
                    $val = 0;
                }
                $nbreByAge[$transheHoraire][$trancheAge] = $val + 1;
                if (0 == $val) {
                    ksort($nbreByAge[$transheHoraire]);
                }

                // Gestion des donnees Genre
                if (array_key_exists($transheHoraire, $nbreByGenre)) {
                    if (array_key_exists($genre, $nbreByGenre[$transheHoraire])) {
                        $val = $nbreByGenre[$transheHoraire][$genre];
                    } else {
                        $val = 0;
                    }
                } else {
                    $val = 0;
                }
                $nbreByGenre[$transheHoraire][$genre] = $val + 1;

                // Gestion des donnees Satut
                if (array_key_exists($transheHoraire, $nbreByStatut)) {
                    if (array_key_exists($profil, $nbreByStatut[$transheHoraire])) {
                        $val = $nbreByStatut[$transheHoraire][$profil];
                    } else {
                        $val = 0;
                    }
                } else {
                    $val = 0;
                }
                $nbreByStatut[$transheHoraire][$profil] = $val + 1;
            }
        }

        ksort($nbreByAge);
        ksort($nbreByGenre);
        ksort($nbreByStatut);

        return new JsonResponse([
            [$nbreByAge, count($nbreByAge)],
            [$nbreByGenre, count($nbreByGenre)],
            [$nbreByStatut, count($nbreByStatut)],
        ]);
    }

    /**
     * @Route("/Api/Statistique/InfoEtudiants", methods={"GET"}, name="StatistiqueApi_InfoEtudiants", options={"expose"=true})
     * @Isgranted("ROLE_GESTION_EXTRACTION")
     */
    public function getInfoEtudiants(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoStats = $this->getDoctrine()->getManager('statistique')->getRepository('StatistiqueBundle:DataUtilisateur');
        $repoUser = $em->getRepository('UcaBundle:Utilisateur');

        $twigConfig['annee_n'] = Parametrage::get()->getAnneeUniversitaire();
        $twigConfig['annee_n_1'] = Parametrage::get()->getAnneeUniversitaire() - 1;

        // Nombre d'etudiants pour annee N et N-1
        $nbreUser_n = $repoStats->getCodEtu(Parametrage::get()->getAnneeUniversitaire(), false);
        $twigConfig['nombre_etu_annee_n'] = $nbreUser_n[2];
        $nbreUser_n_1 = $repoStats->getCodEtu(Parametrage::get()->getAnneeUniversitaire() - 1, false);
        $twigConfig['nombre_etu_annee_n_1'] = $nbreUser_n_1[2];

        // Nombre d'etudiants inscrit sur la plateforme pour annee N et N-1
        $listeUsersInscrits_n = $repoUser->getNbreUserInscript(explode(',', $nbreUser_n[1]));
        $twigConfig['nombre_etu_inscrit_annee_n'] = $listeUsersInscrits_n[2];
        $listeUsersInscrits_n_1 = $repoUser->getNbreUserInscript(explode(',', $nbreUser_n_1[1]));
        $twigConfig['nombre_etu_inscrit_annee_n_1'] = $nbreUser_n_1[2];

        // Nombre d'etudiants inscrit a une activite pour annee N et N-1
        $listeUsersInscritsActivite_n = $repoUser->getNbreUserInscriptActivite(explode(',', $nbreUser_n[1]), 'valide');
        $twigConfig['nombre_etu_inscrit_activite_annee_n'] = $listeUsersInscritsActivite_n;
        $listeUsersInscritsActivite_n_1 = $repoUser->getNbreUserInscriptActivite(explode(',', $nbreUser_n_1[1]), 'ancienneinscription');
        $twigConfig['nombre_etu_inscrit_activite_annee_n_1'] = $listeUsersInscritsActivite_n_1;

        // Nbre Boursier
        $nbreUserBoursier_n = $repoStats->getNbreBoursier(Parametrage::get()->getAnneeUniversitaire(), explode(',', $listeUsersInscrits_n[1]));
        $twigConfig['nombre_etu_inscrit_boursier_annee_n'] = $nbreUserBoursier_n;
        $nbreUserBoursier_n_1 = $repoStats->getNbreBoursier(Parametrage::get()->getAnneeUniversitaire() - 1, explode(',', $listeUsersInscrits_n_1[1]));
        $twigConfig['nombre_etu_inscrit_boursier_annee_n_1'] = $nbreUserBoursier_n_1;

        // Nbre SHNU
        $nbreUserShnu_n = $repoStats->getNbreShnu(Parametrage::get()->getAnneeUniversitaire(), explode(',', $listeUsersInscrits_n[1]));
        $twigConfig['nombre_etu_inscrit_shnu_annee_n'] = $nbreUserShnu_n;
        $nbreUserShnu_n_1 = $repoStats->getNbreShnu(Parametrage::get()->getAnneeUniversitaire() - 1, explode(',', $listeUsersInscrits_n_1[1]));
        $twigConfig['nombre_etu_inscrit_shnu_annee_n_1'] = $nbreUserShnu_n_1;

        return new JsonResponse(['data' => $this->render('@Uca/UcaGest/Reporting/Statistiques/TableauEtudiants.html.twig', $twigConfig)->getContent()]);
    }

    private function getDataToDrawCustomChart($data)
    {
        $em = $this->getDoctrine()->getManager();

        is_numeric($data['creneau']) ? $creneau = $data['creneau'] : $creneau = 0;
        $idFormatActivite = $data['formatActivite'];
        $idActivite = $data['activite'];
        $idClasseActivite = $data['classe_activite'];
        $idTypeActivite = $data['type_activite'];
        $option = $data['options'];

        $search = '';

        $fluxInscriptions = [];
        $nbEtudiantPersonnel = [];
        $frequentationCours = [];
        $recurrenceInscriptions = [];
        $nbAchatCarte = [];

        $dateFin = new \DateTime();
        $dateDebut = new \DateTime();
        $dateDebut = $dateDebut->modify('-1 year');
        $interval = DateInterval::createFromDateString('1 month');
        $period = new DatePeriod($dateDebut, $interval, $dateFin->modify('+1 month'));

        $translator = $this->get('translator');
        $nameMonth = [
            $translator->trans('common.mois.janvier'),
            $translator->trans('common.mois.fevrier'),
            $translator->trans('common.mois.mars'),
            $translator->trans('common.mois.avril'),
            $translator->trans('common.mois.mai'),
            $translator->trans('common.mois.juin'),
            $translator->trans('common.mois.juillet'),
            $translator->trans('common.mois.aout'),
            $translator->trans('common.mois.septembre'),
            $translator->trans('common.mois.octobre'),
            $translator->trans('common.mois.novembre'),
            $translator->trans('common.mois.decembre'),
        ];

        $profilUtilisateurs = $em->getRepository('UcaBundle:ProfilUtilisateur')->findAll();
        foreach ($profilUtilisateurs as $profilUtilisateur) {
            $nbEtudiantPersonnel[$profilUtilisateur->getLibelle()] = 0;
        }

        if (0 != $creneau) {
            $search = 'creneau';
            $DhtmlXSerie = $em->getRepository('UcaBundle:DhtmlxSerie')->find($creneau);
            $inscriptions = $em->getRepository('UcaBundle:Inscription')->findByCreneauAndStatutAndDate($DhtmlXSerie->getCreneau()->getId(), 'valide', $dateDebut, $dateFin);

            $fluxInscriptions = [];

            foreach ($period as $month) {
                $nbInscrit = 0;
                $nbPresent = 0;
                foreach ($inscriptions as $inscription) {
                    if (date_format($month, 'Y-m') == date_format($inscription->getDate(), 'Y-m')) {
                        $appel = $em->getRepository('UcaBundle:Appel')->findAppelByUserAndSerie($inscription->getUtilisateur()->getId(), $DhtmlXSerie->getId());
                        if (null != $appel[0]) {
                            $appel[0]->getPresent() ? $nbPresent++ : null;
                        }

                        ++$nbInscrit;
                        ++$nbEtudiantPersonnel[$inscription->getUtilisateur()->getProfil()->getLibelle()];
                    }
                }
                $numMonth = date_format($month, 'm');
                1 == $numMonth[0] ? $fluxInscriptions[] = [$nameMonth[$numMonth - 1] => $nbInscrit] : $fluxInscriptions[] = [$nameMonth[$numMonth[1] - 1] => $nbInscrit];
                1 == $numMonth[0] ? $frequentationCours[] = [$nameMonth[$numMonth - 1] => $nbPresent] : $frequentationCours[] = [$nameMonth[$numMonth[1] - 1] => $nbPresent];
            }
        } elseif (0 != $idFormatActivite) {
            $search = 'formatActivite';
            $formatActivite = $em->getRepository('UcaBundle:FormatActivite')->find($idFormatActivite);
            $inscriptions = [];

            if ($formatActivite instanceof FormatAvecCreneau) {
                $creneaux = $formatActivite->getCreneaux();
                foreach ($creneaux as $creneau) {
                    $listeInscription = $em->getRepository('UcaBundle:Inscription')->findByCreneau($creneau->getId());
                    foreach ($listeInscription as $inscription) {
                        $inscriptions[] = $inscription;
                    }
                }
            } elseif ($formatActivite instanceof FormatAchatCarte) {
                $inscriptions = $formatActivite->getInscriptionsValidee();
            } else {
                $inscriptions = $formatActivite->getInscriptionsValidee();
            }

            foreach ($period as $month) {
                $nbInscrit = 0;
                $nbPresent = 0;
                $nbCarteAchete = 0;
                foreach ($inscriptions as $inscription) {
                    if (date_format($month, 'Y-m') == date_format($inscription->getDate(), 'Y-m')) {
                        ++$nbInscrit;
                        ++$nbCarteAchete;
                        ++$nbEtudiantPersonnel[$inscription->getUtilisateur()->getProfil()->getLibelle()];
                    }
                }
                $numMonth = date_format($month, 'm');
                1 == $numMonth[0] ? $fluxInscriptions[] = [$nameMonth[$numMonth - 1] => $nbInscrit] : $fluxInscriptions[] = [$nameMonth[$numMonth[1] - 1] => $nbInscrit];
                1 == $numMonth[0] ? $nbAchatCarte[] = [$nameMonth[$numMonth - 1] => $nbCarteAchete] : $nbAchatCarte[] = [$nameMonth[$numMonth[1] - 1] => $nbCarteAchete];
            }
        } elseif (0 != $idActivite) {
            $search = 'activite';
            $inscriptions = [];

            $formatActivites = $em->getRepository('UcaBundle:FormatActivite')->findByActivite($idActivite);
            foreach ($formatActivites as $formatActivite) {
                $listeInscription = $formatActivite->getInscriptionsValidee();
                foreach ($listeInscription as $inscription) {
                    $inscriptions[] = $inscription;
                }
            }

            foreach ($period as $month) {
                $nbInscrit = 0;
                $nbPresent = 0;
                foreach ($inscriptions as $inscription) {
                    if (date_format($month, 'Y-m') == date_format($inscription->getDate(), 'Y-m')) {
                        ++$nbInscrit;
                        ++$nbEtudiantPersonnel[$inscription->getUtilisateur()->getProfil()->getLibelle()];
                    }
                }
                $numMonth = date_format($month, 'm');
                1 == $numMonth[0] ? $fluxInscriptions[] = [$nameMonth[$numMonth - 1] => $nbInscrit] : $fluxInscriptions[] = [$nameMonth[$numMonth[1] - 1] => $nbInscrit];
            }
        } elseif (0 != $idClasseActivite) {
            $search = 'classeActivite';
            $inscriptions = [];
            $activites = $em->getRepository('UcaBundle:Activite')->findByClasseActivite($idClasseActivite);
            foreach ($activites as $activite) {
                $formatActivites = $em->getRepository('UcaBundle:FormatActivite')->findByActivite($activite->getId());
                foreach ($formatActivites as $formatActivite) {
                    $listeInscription = $formatActivite->getInscriptionsValidee();
                    foreach ($listeInscription as $inscription) {
                        $inscriptions[] = $inscription;
                    }
                }
            }
            foreach ($period as $month) {
                $nbInscrit = 0;
                $nbPresent = 0;
                foreach ($inscriptions as $inscription) {
                    if (date_format($month, 'Y-m') == date_format($inscription->getDate(), 'Y-m')) {
                        ++$nbInscrit;
                        ++$nbEtudiantPersonnel[$inscription->getUtilisateur()->getProfil()->getLibelle()];
                    }
                }
                $numMonth = date_format($month, 'm');
                1 == $numMonth[0] ? $fluxInscriptions[] = [$nameMonth[$numMonth - 1] => $nbInscrit] : $fluxInscriptions[] = [$nameMonth[$numMonth[1] - 1] => $nbInscrit];
            }
        } elseif (0 != $idTypeActivite) {
            $search = 'typeActivite';
            $inscriptions = [];
            $classeActivites = $em->getRepository('UcaBundle:ClasseActivite')->findByTypeActivite($idTypeActivite);
            foreach ($classeActivites as $classeActivite) {
                $activites = $em->getRepository('UcaBundle:Activite')->findByClasseActivite($classeActivite->getId());
                foreach ($activites as $activite) {
                    $formatActivites = $em->getRepository('UcaBundle:FormatActivite')->findByActivite($activite->getId());
                    foreach ($formatActivites as $formatActivite) {
                        $listeInscription = $formatActivite->getInscriptionsValidee();
                        foreach ($listeInscription as $inscription) {
                            $inscriptions[] = $inscription;
                        }
                    }
                }
            }

            foreach ($period as $month) {
                $nbInscrit = 0;
                $nbPresent = 0;
                foreach ($inscriptions as $inscription) {
                    if (date_format($month, 'Y-m') == date_format($inscription->getDate(), 'Y-m')) {
                        ++$nbInscrit;
                        ++$nbEtudiantPersonnel[$inscription->getUtilisateur()->getProfil()->getLibelle()];
                    }
                }
                $numMonth = date_format($month, 'm');
                1 == $numMonth[0] ? $fluxInscriptions[] = [$nameMonth[$numMonth - 1] => $nbInscrit] : $fluxInscriptions[] = [$nameMonth[$numMonth[1] - 1] => $nbInscrit];
            }
        } else {
            $search = 'tout';
            $inscriptions = $em->getRepository('UcaBundle:Inscription')->findAll();
            foreach ($period as $month) {
                $nbInscrit = 0;
                $nbPresent = 0;
                foreach ($inscriptions as $inscription) {
                    if (date_format($month, 'Y-m') == date_format($inscription->getDate(), 'Y-m')) {
                        ++$nbInscrit;
                        ++$nbEtudiantPersonnel[$inscription->getUtilisateur()->getProfil()->getLibelle()];
                    }
                }
                $numMonth = date_format($month, 'm');
                1 == $numMonth[0] ? $fluxInscriptions[] = [$nameMonth[$numMonth - 1] => $nbInscrit] : $fluxInscriptions[] = [$nameMonth[$numMonth[1] - 1] => $nbInscrit];
            }
        }

        switch ($option) {
            case 0:
                return [$translator->trans('statistique.options.flux'), $search, $fluxInscriptions, $translator->trans('statistique.graph.title.nbinscription')];

                break;
            case 1:
                $retour = [];
                foreach ($nbEtudiantPersonnel as $key => $value) {
                    $retour[] = [$key => $value];
                }

                return [$translator->trans('statistique.options.nbetudperso'), $search, $retour, ' '];

                break;
            case 2:
                return [$translator->trans('statistique.options.frequentation'), $search, $frequentationCours, $translator->trans('statistique.graph.title.nbpresent')];

                break;
            case 3:
                //récurrence inscription
                return null;

                break;
            case 4:
                return [$translator->trans('statistique.options.nbachatcarte'), $search, $nbAchatCarte, $translator->trans('statistique.graph.title.nbcarteachete')];

                break;
            default:
                return null;

                break;
        }
    }
}
