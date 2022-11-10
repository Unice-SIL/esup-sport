<?php

/*
 * Classe - StatistiqueController
 *
 * Gestion des statistiques
*/

namespace App\Controller\Api;

use DateInterval;
use DatePeriod;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Uca\FormatAchatCarte;
use App\Entity\Uca\FormatAvecCreneau;
use App\Repository\ActiviteRepository;
use App\Repository\AppelRepository;
use App\Repository\ClasseActiviteRepository;
use App\Repository\DhtmlxSerieRepository;
use App\Repository\FormatActiviteRepository;
use App\Repository\InscriptionRepository;
use App\Repository\KpiGenerauxEtudiantsRepository;
use App\Repository\KpiGenerauxPersonnelsRepository;
use App\Repository\NbUserByElementRepository;
use App\Repository\NbUserByGenreAndAgeRepository;
use App\Repository\NbUserByHoraireAndElementRepository;
use App\Repository\ProfilUtilisateurRepository;
use App\Service\Common\Parametrage;
use Symfony\Contracts\Translation\TranslatorInterface;

class StatistiqueController extends AbstractController
{
    /**
     * @Route("/Api/Statistique/NbreInscription", methods={"GET"}, name="StatistiqueApi_NbreInscriptions", options={"expose"=true})
     * @Isgranted("ROLE_GESTION_EXTRACTION")
     */
    public function getNbreInscriptionCoursAction(Request $request, InscriptionRepository $inscriptionRepository)
    {
        return new JsonResponse([$inscriptionRepository->getNbreInscriptionByClasseActiviteForStatut('valide'), $inscriptionRepository->getNbreInscriptionByActiviteForStatut('valide')]);
    }

    /**
     * @Route("/Api/Statistique/DetailsProfils", methods={"GET"}, name="StatistiqueApi_DetailsProfils", options={"expose"=true})
     * @Isgranted("ROLE_GESTION_EXTRACTION")
     */
    public function getProfilUtilisateurAction(Request $request, NbUserByElementRepository $nbUserByElementRepository)
    {
        $profilUtilisateurs = $nbUserByElementRepository->findByType(1);

        $listeProfil = [];
        $data = [];

        foreach ($profilUtilisateurs as $profilUtilisateur) {
            $listeProfil[] = $profilUtilisateur->getLibelle();
            $data[] = $profilUtilisateur->getNombreUser();
        }

        return new JsonResponse(['profils' => $listeProfil, 'data' => [['Nombre d\'utilisateur' => $data]]]);
    }

    /**
     * @Route("/Api/Statistique/DetailsGenreAge", methods={"GET"}, name="StatistiqueApi_DetailsGenreAge", options={"expose"=true})
     * @Isgranted("ROLE_GESTION_EXTRACTION")
     */
    public function getDetailsGenreAgeAction(Request $request, NbUserByGenreAndAgeRepository $nbUserByGenreAndAgeRepository, TranslatorInterface $translator)
    {
        $ageLibelle = $translator->trans('statistique.utilisateur.age');
        // $na = $translator->trans('statistique.nonrenseigne');
        // $hommeLibelle = $translator->trans('statistique.utilisateur.homme');
        // $femmeLibelle = $translator->trans('statistique.utilisateur.femme');

        $nbUtilisateurs = $nbUserByGenreAndAgeRepository->findAll();
        $data = [];
        $totalF = 0;
        $totalH = 0;
        foreach ($nbUtilisateurs as $nbUtilisateur) {
            $data[$nbUtilisateur->getAge().' '.$ageLibelle][$nbUtilisateur->getGenre()] = $nbUtilisateur->getNombreUser();
            if ('F' == $nbUtilisateur->getGenre()) {
                $totalF += $nbUtilisateur->getNombreUser();
            } else {
                $totalH += $nbUtilisateur->getNombreUser();
            }
        }

        return new JsonResponse([
            $data,
            [
                ['libelle' => 'F', 'nbr' => $totalF],
                ['libelle' => 'M', 'nbr' => $totalH],
            ],
        ]);
    }

    /**
     * @Route("/Api/Statistique/DetailsPersonnelCategorie",methods={"GET"}, name="StatistiqueApi_DetailsPersonnelCategorie",options={"expose"=true})
     * @Isgranted("ROLE_GESTION_EXTRACTION")
     */
    public function getDetailsPersonnelCategorie(Request $request, KpiGenerauxPersonnelsRepository $repoKpiPersonnels)
    {
        $anneeUniversitaire = Parametrage::get()->getAnneeUniversitaire();
        $twigConfig['annee_n'] = $anneeUniversitaire;
        $twigConfig['annee_n_1'] = $anneeUniversitaire - 1;

        $twigConfig['kpi_annee_n'] = $repoKpiPersonnels->findOneByAnneeUniversitaire($anneeUniversitaire);
        $twigConfig['kpi_annee_n_1'] = $repoKpiPersonnels->findOneByAnneeUniversitaire($anneeUniversitaire - 1);

        return new JsonResponse(['data' => $this->render('UcaBundle/UcaGest/Reporting/Statistiques/TableauPersonnels.html.twig', $twigConfig)->getContent()]);
    }

    /**
     * @Route("/Api/Statistique/DetailsNiveau", methods={"GET"}, name="StatistiqueApi_DetailsNiveau", options={"expose"=true})
     * @Isgranted("ROLE_GESTION_EXTRACTION")
     */
    public function getDetailsNiveau(Request $request, NbUserByElementRepository $nbUserByElementRepository)
    {
        $profilUtilisateurs = $nbUserByElementRepository->findByType(2);

        $listeProfil = [];
        $data = [];

        foreach ($profilUtilisateurs as $profilUtilisateur) {
            $listeProfil[] = $profilUtilisateur->getLibelle();
            $data[] = $profilUtilisateur->getNombreUser();
        }

        return new JsonResponse(['niveau' => $listeProfil, 'data' => [['Nombre d\'utilisateur' => $data]]]);
    }

    /**
     * @Route("/Api/Statistique/CustomChart", name="UcaGest_CustomChart",  options={"expose"=true}, methods={"POST"})
     * @Isgranted("ROLE_GESTION_EXTRACTION")
     */
    public function createCustomChartAction(
        Request $request, 
        ProfilUtilisateurRepository $profilUtilisateurRepository,
        DhtmlxSerieRepository $dhtmlxSerieRepository,
        InscriptionRepository $inscriptionRepository,
        AppelRepository $appelRepository,
        FormatActiviteRepository $formatActiviteRepository,
        ActiviteRepository $activiteRepository,
        ClasseActiviteRepository $classeActiviteRepository
    )
    {
        $data = [];

        $data['creneau'] = $request->get('creneau');
        $data['formatActivite'] = $request->get('formatActivite');
        $data['activite'] = $request->get('activite');
        $data['classe_activite'] = $request->get('classe_activite');
        $data['type_activite'] = $request->get('type_activite');
        $data['options'] = $request->get('options');

        $result = $this->getDataToDrawCustomChart(
            $data,
            $profilUtilisateurRepository,
            $dhtmlxSerieRepository,
            $inscriptionRepository,
            $appelRepository,
            $formatActiviteRepository,
            $activiteRepository,
            $classeActiviteRepository
        );

        return new JsonResponse($result);
    }

    /**
     * @Route("/Api/Statistique/InfoConnexion", methods={"GET"}, name="StatistiqueApi_InfoConnexion", options={"expose"=true})
     * @Isgranted("ROLE_GESTION_EXTRACTION")
     */
    public function getInfoConnexion(Request $request, NbUserByHoraireAndElementRepository $nbUserRepository, TranslatorInterface $translator)
    {
        $horaireLibelle = $translator->trans('statistique.utilisateur.heure');
        $noConnexionLibelle = $translator->trans('statistique.aucune_connexion');
        $ageLibelle = $translator->trans('statistique.utilisateur.age');
        // $na = $translator->trans('statistique.nonrenseigne');

        // Horaire et profil
        $profilUtilisateurs = $nbUserRepository->findByType(1);
        $dataProfil = [];
        foreach ($profilUtilisateurs as $profilUtilisateur) {
            if (null != $profilUtilisateur->getHoraire()) {
                $dataProfil[$profilUtilisateur->getHoraire().' '.$horaireLibelle][$profilUtilisateur->getLibelle()] = $profilUtilisateur->getNombreUser();
            } else {
                $dataProfil[$noConnexionLibelle][$profilUtilisateur->getLibelle()] = $profilUtilisateur->getNombreUser();
            }
        }

        // Horaire et age
        $ageUtilisateurs = $nbUserRepository->findByType(2);
        $dataAge = [];
        foreach ($ageUtilisateurs as $ageUtilisateur) {
            if (null != $profilUtilisateur->getHoraire()) {
                $dataAge[$ageUtilisateur->getHoraire().' '.$horaireLibelle][$ageUtilisateur->getLibelle().' '.$ageLibelle] = $ageUtilisateur->getNombreUser();
            } else {
                $dataAge[$noConnexionLibelle][$ageUtilisateur->getLibelle().' '.$ageLibelle] = $ageUtilisateur->getNombreUser();
            }
        }

        // Horaire et genre
        $genreUtilisateurs = $nbUserRepository->findByType(3);
        $dataGenre = [];
        foreach ($genreUtilisateurs as $genreUtilisateur) {
            if (null != $profilUtilisateur->getHoraire()) {
                $dataGenre[$genreUtilisateur->getHoraire().' '.$horaireLibelle][$genreUtilisateur->getLibelle()] = $genreUtilisateur->getNombreUser();
            } else {
                $dataGenre[$noConnexionLibelle][$genreUtilisateur->getLibelle()] = $genreUtilisateur->getNombreUser();
            }
        }

        return new JsonResponse([
            [$dataAge, count($dataAge)],
            [$dataGenre, count($dataGenre)],
            [$dataProfil, count($dataProfil)],
        ]);
    }

    /**
     * @Route("/Api/Statistique/InfoEtudiants", methods={"GET"}, name="StatistiqueApi_InfoEtudiants", options={"expose"=true})
     * @Isgranted("ROLE_GESTION_EXTRACTION")
     */
    public function getInfoEtudiants(Request $request, KpiGenerauxEtudiantsRepository $repoKpiEtudiants)
    {
        $anneeUniversitaire = Parametrage::get()->getAnneeUniversitaire();
        $twigConfig['annee_n'] = $anneeUniversitaire;
        $twigConfig['annee_n_1'] = $anneeUniversitaire - 1;

        $twigConfig['kpi_annee_n'] = $repoKpiEtudiants->findOneByAnneeUniversitaire($anneeUniversitaire);
        $twigConfig['kpi_annee_n_1'] = $repoKpiEtudiants->findOneByAnneeUniversitaire($anneeUniversitaire - 1);

        return new JsonResponse(['data' => $this->render('UcaBundle/UcaGest/Reporting/Statistiques/TableauEtudiants.html.twig', $twigConfig)->getContent()]);
    }

    private function getDataToDrawCustomChart(
        $data,
        ProfilUtilisateurRepository $profilUtilisateurRepository,
        DhtmlxSerieRepository $dhtmlxSerieRepository,
        InscriptionRepository $inscriptionRepository,
        AppelRepository $appelRepository,
        FormatActiviteRepository $formatActiviteRepository,
        ActiviteRepository $activiteRepository,
        ClasseActiviteRepository $classeActiviteRepository,
        TranslatorInterface $translator
    )
    {

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

        $profilUtilisateurs = $profilUtilisateurRepository->findAll();
        foreach ($profilUtilisateurs as $profilUtilisateur) {
            $nbEtudiantPersonnel[$profilUtilisateur->getLibelle()] = 0;
        }

        if (0 != $creneau) {
            $search = 'creneau';
            $DhtmlXSerie = $dhtmlxSerieRepository->find($creneau);
            $inscriptions = $inscriptionRepository->findByCreneauAndStatutAndDate($DhtmlXSerie->getCreneau()->getId(), 'valide', $dateDebut, $dateFin);

            $fluxInscriptions = [];

            foreach ($period as $month) {
                $nbInscrit = 0;
                $nbPresent = 0;
                foreach ($inscriptions as $inscription) {
                    if (date_format($month, 'Y-m') == date_format($inscription->getDate(), 'Y-m')) {
                        $appel = $appelRepository->findAppelByUserAndSerie($inscription->getUtilisateur()->getId(), $DhtmlXSerie->getId());
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
            $formatActivite = $formatActiviteRepository->find($idFormatActivite);
            $inscriptions = [];

            if ($formatActivite instanceof FormatAvecCreneau) {
                $creneaux = $formatActivite->getCreneaux();
                foreach ($creneaux as $creneau) {
                    $listeInscription = $inscriptionRepository->findByCreneau($creneau->getId());
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

            $formatActivites = $formatActiviteRepository->findByActivite($idActivite);
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
            $activites = $activiteRepository->findByClasseActivite($idClasseActivite);
            foreach ($activites as $activite) {
                $formatActivites = $formatActiviteRepository->findByActivite($activite->getId());
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
            $classeActivites = $classeActiviteRepository->findByTypeActivite($idTypeActivite);
            foreach ($classeActivites as $classeActivite) {
                $activites = $activiteRepository->findByClasseActivite($classeActivite->getId());
                foreach ($activites as $activite) {
                    $formatActivites = $formatActiviteRepository->findByActivite($activite->getId());
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
            $inscriptions = $inscriptionRepository->findAll();
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
