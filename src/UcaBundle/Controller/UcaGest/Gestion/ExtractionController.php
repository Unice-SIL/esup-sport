<?php

/*
 * Classe - ExtractionController
 *
 * Gestion de l'extraction des données
 * Construction des fichier execl à partir de la base de donnée
*/

namespace UcaBundle\Controller\UcaGest\Gestion;

use Doctrine\ORM\PersistentCollection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Entity\Activite;
use UcaBundle\Entity\ClasseActivite;
use UcaBundle\Entity\Creneau;
use UcaBundle\Entity\DhtmlxEvenement;
use UcaBundle\Entity\DhtmlxSerie;
use UcaBundle\Entity\Etablissement;
use UcaBundle\Entity\FormatAchatCarte;
use UcaBundle\Entity\FormatActivite;
use UcaBundle\Entity\FormatAvecReservation;
use UcaBundle\Entity\Groupe;
use UcaBundle\Entity\Inscription;
use UcaBundle\Entity\Lieu;
use UcaBundle\Entity\TypeActivite;
use UcaBundle\Entity\Utilisateur;
use UcaBundle\Form\GestionExtractionType;

/**
 * @Route("UcaGest/Extraction")
 * @Isgranted("ROLE_GESTION_EXTRACTION")
 */
class ExtractionController extends Controller
{
    /**
     * @Route("/",name="UcaGest_Extraction")
     */
    public function listerAction(Request $request)
    {
        if ($this->isGranted('ROLE_GESTION_EXTRACTION')) {
            $em = $this->getDoctrine()->getManager();

            $twigConfig['codeListe'] = 'Extraction';
            $form = $this->get('form.factory')->create(
                GestionExtractionType::class,
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

            return $this->render('@Uca/UcaGest/Reporting/Extraction/Voir.html.twig', $twigConfig);
        }

        return $this->redirectToRoute('UcaGest_Accueil');
    }

    /**
     *  @Route("/listeEncadrants", name="UcaGest_ExtractionListeEncadrants")
     */
    public function extractListeEncadrantsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $groupeEncadrant = $em->getRepository(Groupe::class)->findGroupeEncadrant();

        if (!empty($groupeEncadrant[0]->getUtilisateurs())) {
            $titleColumn = [
                'Nom et Prénom',
                "Classe d'activité",
                'Activité',
                'Type de créneau',
                'Jour de créneau',
                'Horaire',
                'Heures',
                'Campus',
                'Salle',
                'Type Encadrant',
                'Capacité',
                'Inscrit S1 2020',
                'Effectif réel S1 2019',
                'Taux horaire net',
                'Coût horaire complet',
                'Profil',
                'Nb heures prévues S2',
                'Montant SF',
                'Heures réalisées S1',
                'Statutaire',
                'Montant conventions',
                'Montant HC',
            ];

            $spreadsheet = new Spreadsheet();
            $spreadsheet->removeSheetByIndex(0);

            $sheet = new Worksheet();
            $sheet->setTitle('Encadrants');

            $this->createExcelHeader($spreadsheet, $sheet, $titleColumn);

            $idCol = 8;

            //Pour chaque encadrant on regarde les créneaux associés et on créer une ligne
            foreach ($groupeEncadrant[0]->getUtilisateurs() as $encadrant) {
                $nomPrenom = $encadrant->getNom().' '.$encadrant->getPrenom();
                foreach ($encadrant->getCreneaux() as $creneau) {
                    if (null != $creneau->getSerie() && 0 != sizeof($creneau->getSerie()->getEvenements())) {
                        $this->createExcelLine($em, $creneau, $sheet, $nomPrenom, $idCol);
                        ++$idCol;
                    }
                }
            }

            return $this->createExcelStreamedResponse($spreadsheet, 'extraction_liste_encadrants_');
        }

        return $this->redirectToRoute('UcaGest_Extraction');
    }

    /**
     * @Route("/extractionApi", name="ExtractionApi", methods={"POST"}, options={"expose"=true})
     */
    public function extractionPersonnaliseeApi(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $titleColumn = [];
        $alphabet = range('A', 'Z');
        $data = $request->request->all();

        //On définit le nom des colonnes pour l'Excel
        foreach ($data as $key => $value) {
            if ('1' === $value) {
                switch ($key) {
                    case 'Créneau':
                        array_push($titleColumn, str_replace('_', ' ', $key));
                        $details = $request->get('Détails_créneau');
                        $titleColumn = $this->addTitleColumn($titleColumn, $details);

                        break;
                    case 'Format_d\'activité':
                        array_push($titleColumn, str_replace('_', ' ', $key));
                        $details = $request->get('Détails_format_d\'activité');
                        $titleColumn = $this->addTitleColumn($titleColumn, $details);

                        break;
                    case 'Inscription':
                        array_push($titleColumn, 'Statut');
                        $details = $request->get('Détails_inscription');
                        $titleColumn = $this->addTitleColumn($titleColumn, $details);

                        break;
                    case 'Statut inscription':
                        break;
                    default:
                        array_push($titleColumn, str_replace('_', ' ', $key));

                        break;
                }
            }
        }

        //Création de la feuille Excel
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $sheet = new Worksheet();
        $sheet->setTitle('Extraction des données');

        $idCol = 8; //On commencera à écrire les lignes à partir de cette colonne

        $this->createExcelHeader($spreadsheet, $sheet, $titleColumn);

        //On récupère tous les enregistrements suivant paramétres choisi
        $lignes = [];
        $main = '';
        if ('1' === $request->get('Créneau')) {
            $lignes = $em->getRepository(Creneau::class)->findAll();
            $main = 'Creneau';
        } elseif ('1' === $request->get("Format_d'activité")) {
            $lignes = $em->getRepository(FormatActivite::class)->findAll();
            $main = 'FormatActivite';
        } elseif ('1' === $request->get('Activité')) {
            $lignes = $em->getRepository(Activite::class)->findAll();
            $main = 'Activite';
        } elseif ('1' === $request->get("Classe_d'activité")) {
            $lignes = $em->getRepository(ClasseActivite::class)->findAll();
            $main = 'ClasseActivite';
        } elseif ('1' === $request->get("Type_d'activité")) {
            $lignes = $em->getRepository(TypeActivite::class)->findAll();
            $main = 'TypeActivite';
        } elseif ('1' === $request->get('Inscription')) {
            if ('0' === $request->get('Statut')) {
                $lignes = $em->getRepository(Inscription::class)->findAll();
            } else {
                $lignes = $em->getRepository(Inscription::class)->findByStatut($request->get('Statut'));
            }
            $main = 'Inscription';
        } elseif ('1' === $request->get('Encadrants')) {
            $groupeEncadrant = $em->getRepository(Groupe::class)->findGroupeEncadrant();
            $lignes = $groupeEncadrant[0]->getUtilisateurs();
            $main = 'Encadrant';
        }

        //On créer les lignes Excel
        if (!empty($lignes)) {
            foreach ($lignes as $ligne) {
                $tab = [];

                //Suivant l'élément principal choisit, on récupére les données
                switch ($main) {
                    case 'Creneau':
                        '1' === $request->get('Encadrants') ? $encadrantValue = $this->getEncadrantValue($ligne->getEncadrants()) : $encadrantValue = null;
                        '1' === $request->get("Type_d'activité") ? $typeActiviteValue = $em->getRepository(TypeActivite::class)->findTypeActiviteByCreneau($ligne->getId()) : $typeActiviteValue = null;
                        '1' === $request->get("Classe_d'activité") ? $classeActiviteValue = $em->getRepository(ClasseActivite::class)->findClasseActiviteByCreneau($ligne->getId()) : $classeActiviteValue = null;
                        '1' === $request->get('Activité') ? $activiteValue = $em->getRepository(Activite::class)->findActiviteByCreneau($ligne->getId()) : $activiteValue = null;
                        '1' === $request->get("Format_d'activité") ? $formatActiviteValue = $ligne->getFormatActivite() : $formatActiviteValue = null;
                        $creneauValue = $ligne;
                        '1' === $request->get('Inscription') ? $inscriptionValue = $this->getInscriptionValue($em, $main, $request->get('Statut'), $ligne->getId()) : $inscriptionValue = null;
                        //Détails créneau
                        '1' === $request->get('Détails_créneau')['Description créneau'] ? $descriptionCreneauValue = $this->getDescriptionCreneau($em, $ligne) : $descriptionCreneauValue = null;
                        '1' === $request->get('Détails_créneau')['Tarif créneau'] ? $tarifCreneauValue = $ligne->getTarif()->getLibelle() : $tarifCreneauValue = null;
                        '1' === $request->get('Détails_créneau')['Capacité créneau'] ? $capaciteCreneauValue = $ligne->getcapacite() : $capaciteCreneauValue = null;
                        '1' === $request->get('Détails_créneau')['Profils autorisés créneau'] ? $profilsAutorisesCreneauValue = $ligne->getProfilsUtilisateurs() : $profilsAutorisesCreneauValue = null;
                        '1' === $request->get('Détails_créneau')['Niveaux sportifs créneau'] ? $niveauxSportifsCreneauValue = $ligne->getNiveauxSportifs() : $niveauxSportifsCreneauValue = null;
                        '1' === $request->get('Détails_créneau')['Eligibilité créneau'] ? $eligibiliteCreneauValue = $this->getEligibiliteCreneau($em, $ligne) : $eligibiliteCreneauValue = null;
                        '1' === $request->get('Détails_créneau')['Période créneau'] ? $periodeCreneauValue = $this->getPeriodeCreneau($em, $ligne) : $periodeCreneauValue = null;
                        '1' === $request->get('Détails_créneau')['Campus'] ? $campusValue = $this->getEtablissementValue($em, $main, $ligne->getId()) : $campusValue = null;
                        '1' === $request->get('Détails_créneau')['Lieu'] ? $lieuValue = $this->getLieuValue($ligne->getLieu()) : $lieuValue = null;

                        break;
                    case 'FormatActivite':
                        '1' === $request->get('Encadrants') ? $encadrantValue = $this->getEncadrantValue($ligne->getEncadrants()) : $encadrantValue = null;
                        '1' === $request->get("Type_d'activité") ? $typeActiviteValue = $em->getRepository(TypeActivite::class)->findTypeActiviteByFormatActivite($ligne->getId()) : $typeActiviteValue = null;
                        '1' === $request->get("Classe_d'activité") ? $classeActiviteValue = $em->getRepository(ClasseActivite::class)->findClasseActiviteByFormatActivite($ligne->getId()) : $classeActiviteValue = null;
                        '1' === $request->get('Activité') ? $activiteValue = $ligne->getActivite() : $activiteValue = null;
                        $formatActiviteValue = $ligne;
                        '1' === $request->get('Inscription') ? $inscriptionValue = $this->getInscriptionValue($em, $main, '1' === $request->get('Statut'), $ligne->getId()) : $inscriptionValue = null;

                        break;
                    case 'Activite':
                        '1' === $request->get('Encadrants') ? $encadrantValue = $this->getEncadrantValue($em->getRepository(Utilisateur::class)->findEncadrantByActivite($ligne->getId())) : $encadrantValue = null;
                        '1' === $request->get("Type_d'activité") ? $typeActiviteValue = $em->getRepository(TypeActivite::class)->findTypeActiviteByActivite($ligne->getId()) : $typeActiviteValue = null;
                        '1' === $request->get("Classe_d'activité") ? $classeActiviteValue = $ligne->getClasseActivite() : $classeActiviteValue = null;
                        $activiteValue = $ligne;
                        '1' === $request->get('Inscription') ? $inscriptionValue = $this->getInscriptionValue($em, $main, '1' === $request->get('Statut'), $ligne->getId()) : $inscriptionValue = null;

                        break;
                    case 'ClasseActivite':
                        '1' === $request->get('Encadrants') ? $encadrantValue = $this->getEncadrantValue($em->getRepository(Utilisateur::class)->findEncadrantByClasseActivite($ligne->getId())) : $encadrantValue = null;
                        '1' === $request->get("Type_d'activité") ? $typeActiviteValue = $ligne->getTypeActivite() : $typeActiviteValue = null;
                        $classeActiviteValue = $ligne;
                        '1' === $request->get('Inscription') ? $inscriptionValue = $this->getInscriptionValue($em, $main, '1' === $request->get('Statut'), $ligne->getId()) : $inscriptionValue = null;

                        break;
                    case 'TypeActivite':
                        '1' === $request->get('Encadrants') ? $encadrantValue = $this->getEncadrantValue($em->getRepository(Utilisateur::class)->findEncadrantByTypeActivite($ligne->getId())) : $encadrantValue = null;
                        $typeActiviteValue = $ligne;
                        '1' === $request->get('Inscription') ? $inscriptionValue = $this->getInscriptionValue($em, $main, '1' === $request->get('Statut'), $ligne->getId()) : $inscriptionValue = null;

                        break;
                    case 'Inscription':
                        '1' === $request->get('Encadrants') ? $encadrantValue = $this->getEncadrantValue($em->getRepository(FormatActivite::class)->find($ligne->getFormatActivite())->getEncadrants()) : $encadrantValue = null;
                        $inscriptionValue = $ligne;

                        break;
                    case 'Encadrant':
                        $encadrantValue = $ligne;

                        break;
                    default:
                        break;
                }

                //Détails Format Activité
                '1' === $request->get('Détails_format_d\'activité')['Description format'] ? $descriptionFormatValue = $formatActiviteValue->getDescription() : $descriptionFormatValue = null; // null != $datesPublications && 'null' != $datesPublications ? $datesPublicationsValue = date_format($formatActiviteValue->getDateDebutEffective(), 'd/m/Y - H:i')."\n".date_format($formatActiviteValue->getDateFinEffective(), 'd/m/Y - H:i') : $datesPublicationsValue = null;
                '1' === $request->get('Détails_format_d\'activité')['Dates effectives'] ? $datesEffectivesValue = [date_format($formatActiviteValue->getDateDebutEffective(), 'd/m/Y - H:i'), date_format($formatActiviteValue->getDateFinEffective(), 'd/m/Y - H:i')] : $datesEffectivesValue = null;
                '1' === $request->get('Détails_format_d\'activité')['Dates inscriptions'] ? $datesInscriptionsValue = [date_format($formatActiviteValue->getDateDebutInscription(), 'd/m/Y - H:i'), date_format($formatActiviteValue->getDateFinInscription(), 'd/m/Y - H:i')] : $datesInscriptionsValue = null;
                '1' === $request->get('Détails_format_d\'activité')['Dates publications'] ? $datesPublicationsValue = [date_format($formatActiviteValue->getDateDebutPublication(), 'd/m/Y - H:i'), date_format($formatActiviteValue->getDateFinPublication(), 'd/m/Y - H:i')] : $datesPublicationsValue = null;
                '1' === $request->get('Détails_format_d\'activité')['Capacité format'] ? $capaciteFormatValue = $formatActiviteValue->getCapacite() : $capaciteFormatValue = null;
                '1' === $request->get('Détails_format_d\'activité')['Statut format'] ? $statutFormatValue = $this->getStatutValue($formatActiviteValue->getStatut()) : $statutFormatValue = null;
                '1' === $request->get('Détails_format_d\'activité')['Payant'] ? $payantFormatValue = $this->getPayantvalue($formatActiviteValue->getEstPayant()) : $payantFormatValue = null;
                '1' === $request->get('Détails_format_d\'activité')['Tarif format'] ? $tarifFormatValue = $this->getTarifValue($formatActiviteValue->getTarif()) : $tarifFormatValue = null;
                '1' === $request->get('Détails_format_d\'activité')['Niveaux sportifs format'] ? $niveauxSportifsFormatValue = $formatActiviteValue->getNiveauxSportifs() : $niveauxSportifsFormatValue = null;
                '1' === $request->get('Détails_format_d\'activité')['Profils autorisés format'] ? $profilsAutorisesFormatValue = $formatActiviteValue->getProfilsUtilisateurs() : $profilsAutorisesFormatValue = null;
                '1' === $request->get('Détails_format_d\'activité')['Autorisations requises format'] ? $autorisationRequisesFormatValue = $formatActiviteValue->getAutorisations() : $autorisationRequisesFormatValue = null;
                '1' === $request->get('Détails_format_d\'activité')['Ressource format'] ? $ressourceFormatValue = $this->getRessourceFormatValue($formatActiviteValue) : $ressourceFormatValue = null;
                '1' === $request->get('Détails_format_d\'activité')['Carte à acheter'] ? $carteAcheterFormatValue = $this->getCarteFormatValue($formatActiviteValue) : $carteAcheterFormatValue = null;
                //Détails Inscription
                '1' === $request->get('Détails_inscription')['Nom et prénom inscrit'] ? $nomPrenomInscriptionValue = $this->getDetailInscription($inscriptionValue, 'nomprenom') : $nomPrenomInscriptionValue = null;
                '1' === $request->get('Détails_inscription')['Date d\'inscription'] ? $dateInscriptionInscriptionValue = $this->getDetailInscription($inscriptionValue, 'dateinscription') : $dateInscriptionInscriptionValue = null;
                '1' === $request->get('Détails_inscription')['Date de validation'] ? $dateValidationInscriptionValue = $this->getDetailInscription($inscriptionValue, 'datevalidation') : $dateValidationInscriptionValue = null;
                '1' === $request->get('Détails_inscription')['Date de desincription'] ? $dateDesinscriptionInscriptionValue = $this->getDetailInscription($inscriptionValue, 'datedesinscription') : $dateDesinscriptionInscriptionValue = null;
                '1' === $request->get('Détails_inscription')['Motif d\'annulation'] ? $motifAnnulationInscriptionValue = $this->getDetailInscription($inscriptionValue, 'motifannulation') : $motifAnnulationInscriptionValue = null;
                '1' === $request->get('Détails_inscription')['Commentaire d\'annulation'] ? $commentaireInscriptionValue = $this->getDetailInscription($inscriptionValue, 'commentaireannulation') : $commentaireInscriptionValue = null;

                //On pousse les données dans le tableau qui va permettre de créer les lignes excel
                isset($encadrantValue) ? array_push($tab, $this->getNomPrenomEncadrant($encadrantValue)) : null;
                isset($typeActiviteValue) ? array_push($tab, $this->getLibelleResult($typeActiviteValue)) : null;
                isset($classeActiviteValue) ? array_push($tab, $this->getLibelleResult($classeActiviteValue)) : null;
                isset($activiteValue) ? array_push($tab, $this->getLibelleResult($activiteValue)) : null;
                //Format activité et details
                isset($formatActiviteValue) ? array_push($tab, $this->getLibelleResult($formatActiviteValue)) : null;
                isset($descriptionFormatValue) ? array_push($tab, $descriptionFormatValue) : null;
                isset($datesEffectivesValue) ? array_push($tab, $datesEffectivesValue[0], $datesEffectivesValue[1]) : null;
                isset($datesInscriptionsValue) ? array_push($tab, $datesInscriptionsValue[0], $datesInscriptionsValue[1]) : null;
                isset($datesPublicationsValue) ? array_push($tab, $datesPublicationsValue[0], $datesPublicationsValue[1]) : null;
                isset($capaciteFormatValue) ? array_push($tab, $capaciteFormatValue) : null;
                isset($statutFormatValue) ? array_push($tab, $statutFormatValue) : null;
                isset($payantFormatValue) ? array_push($tab, $payantFormatValue) : null;
                isset($tarifFormatValue) ? array_push($tab, $tarifFormatValue) : null;
                isset($niveauxSportifsFormatValue) ? array_push($tab, $this->getLibelleResult($niveauxSportifsFormatValue)) : null;
                isset($profilsAutorisesFormatValue) ? array_push($tab, $this->getLibelleResult($profilsAutorisesFormatValue)) : null;
                isset($autorisationRequisesFormatValue) ? array_push($tab, $this->getLibelleResult($autorisationRequisesFormatValue)) : null;
                isset($ressourceFormatValue) ? array_push($tab, $this->getLibelleResult($ressourceFormatValue)) : null;
                isset($carteAcheterFormatValue) ? array_push($tab, $carteAcheterFormatValue) : null;
                //Créneau et détails
                isset($creneauValue) ? array_push($tab, $creneauValue->getFormatActivite()->getLibelle()) : null;
                isset($descriptionCreneauValue) ? array_push($tab, $descriptionCreneauValue) : null;
                isset($tarifCreneauValue) ? array_push($tab, $tarifCreneauValue) : null;
                isset($capaciteCreneauValue) ? array_push($tab, $capaciteCreneauValue) : null;
                isset($profilsAutorisesCreneauValue) ? array_push($tab, $this->getLibelleResult($profilsAutorisesCreneauValue)) : null;
                isset($niveauxSportifsCreneauValue) ? array_push($tab, $this->getLibelleResult($niveauxSportifsCreneauValue)) : null;
                isset($eligibiliteCreneauValue) ? array_push($tab, $eligibiliteCreneauValue) : null;
                isset($periodeCreneauValue) ? array_push($tab, $periodeCreneauValue) : null;
                isset($campusValue) ? array_push($tab, $this->getLibelleResult($campusValue)) : null;
                isset($lieuValue) ? array_push($tab, $this->getLibelleResult($lieuValue)) : null;
                //Inscription et détails
                isset($inscriptionValue) ? array_push($tab, $this->getStatutInscription($inscriptionValue)) : null;
                isset($nomPrenomInscriptionValue) ? array_push($tab, $nomPrenomInscriptionValue) : null;
                isset($dateInscriptionInscriptionValue) ? array_push($tab, $dateInscriptionInscriptionValue) : null;
                isset($dateValidationInscriptionValue) ? array_push($tab, $dateValidationInscriptionValue) : null;
                isset($dateDesinscriptionInscriptionValue) ? array_push($tab, $dateDesinscriptionInscriptionValue) : null;
                isset($motifAnnulationInscriptionValue) ? array_push($tab, $motifAnnulationInscriptionValue) : null;
                isset($commentaireInscriptionValue) ? array_push($tab, $commentaireInscriptionValue) : null;

                //Création des lignes
                if (!empty($tab)) {
                    $index = 0;
                    $numeroLettre = 0;
                    $numeroLettreBis = 0;
                    $styleArray = $this->setStyleArrayForExcel(false, 10);
                    for ($i = 0; $i < sizeof($tab); ++$i) {
                        if ($i < 26) {
                            $sheet->getCell($alphabet[$i].$idCol)->setValue($tab[$index]);
                            $sheet->getStyle($alphabet[$i].$idCol)->getAlignment()->setWrapText(true);
                            $sheet->getStyle($alphabet[$i].$idCol)->applyFromArray($styleArray);
                        } else {
                            $sheet->getCell($alphabet[$numeroLettre].$alphabet[$numeroLettreBis].$idCol)->setValue($tab[$index]);
                            $sheet->getStyle($alphabet[$numeroLettre].$alphabet[$numeroLettreBis].$idCol)->getAlignment()->setWrapText(true);
                            $sheet->getStyle($alphabet[$numeroLettre].$alphabet[$numeroLettreBis].$idCol)->applyFromArray($styleArray);
                            ++$numeroLettreBis;
                        }
                        ++$index;
                    }
                    ++$idCol;
                }
            }
        }

        return $this->createExcelStreamedResponse($spreadsheet, 'extraction_personnalisee_donnees_');
    }

    //Function permettant la création du fichier Excel
    private function createExcelHeader($spreadsheet, $sheet, $titleColumn)
    {
        $styleArray = $this->setStyleArrayForExcel(true, 10);

        //En-tête du fichier excel
        $logo = new Drawing();
        $logo->setName('Logo');
        $logo->setPath('build/images/logo-UCA-large-transp.png');
        $logo->setCoordinates('A1');
        $logo->setWorksheet($sheet, true);

        $spreadsheet->getDefaultStyle()->getAlignment()->setWrapText(true);

        $spreadsheet->addSheet($sheet);
        $sheet->setCellValue('F3', 'DVU Sport');

        $index = 0;
        $alphabet = range('A', 'Z');
        $premiereLettre = 0;
        $deuxiemeLettre = 0;
        $currentLettre = 0;

        for ($i = 0; $i < sizeof($titleColumn); ++$i) {
            if ($i < 26) {
                $sheet->setCellValue($alphabet[$i].'7', $titleColumn[$index]);
                $sheet->getStyle($alphabet[$i].'7')->applyFromArray($styleArray);
                $sheet->getColumnDimension($alphabet[$i])->setAutoSize(true);
                $currentLettre = $i;
            } else {
                $sheet->setCellValue($alphabet[$premiereLettre].$alphabet[$deuxiemeLettre].'7', $titleColumn[$index]);
                $sheet->getStyle($alphabet[$premiereLettre].$alphabet[$deuxiemeLettre].'7')->applyFromArray($styleArray);
                $sheet->getColumnDimension($alphabet[$premiereLettre].$alphabet[$deuxiemeLettre])->setAutoSize(true);
                $currentLettre = [$premiereLettre, $deuxiemeLettre];
                ++$deuxiemeLettre;
                if (26 == $deuxiemeLettre) {
                    $deuxiemeLettre = 0;
                    ++$premiereLettre;
                }
            }

            //On freeze la top bar pour garder le nom des colonnes au scroll
            $sheet->freezePane('A8');

            ++$index;
        }
    }

    private function createExcelLine($em, $creneau, $sheet, $nomPrenom, $idCol)
    {
        $tab = [];
        $styleArray = $this->setStyleArrayForExcel(false, 10);
        $dayOfWeek = ['', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
        $currentYear = new \DateTime();
        $lastYear = new \DateTime();
        $lastYear = $lastYear->modify('- 1 years');

        $DhmtlxDateSerie = $em->getRepository(DhtmlxSerie::class)->findDhtmlxSerieByCreneau($creneau->getId());
        $horaire = '';
        $jour = 0;
        $duree = 0;
        $nbHeuresPrevues = 0;
        $nbHeuresFaites = 0;

        if (null != $DhmtlxDateSerie) {
            $DhmtlxDateEvenement = $em->getRepository(DhtmlxEvenement::class)->findDhtmlxEvenementBySerie($DhmtlxDateSerie[0]->getId());
            if (null != $DhmtlxDateEvenement) {
                $heureDebut = date_format($DhmtlxDateEvenement[0]->getDateDebut(), 'H:i');
                $heureFin = date_format($DhmtlxDateEvenement[0]->getDateFin(), 'H:i');
                $horaire = $heureDebut.' - '.$heureFin;
                $jour = date_format($DhmtlxDateEvenement[0]->getDateDebut(), 'N');
                $duree = $this->convertTimeToFloat($heureFin) - $this->convertTimeToFloat($heureDebut);
            }

            $DhmtlxDateEvenement = $em->getRepository(DhtmlxEvenement::class)->findDhtmlxEvenementBySerieAndSemester($DhmtlxDateSerie[0]->getId(), 1);
            foreach ($DhmtlxDateEvenement as $event) {
                $hD = date_format($event->getDateDebut(), 'H:i');
                $hF = date_format($event->getDateFin(), 'H:i');
                $nbHeuresFaites += $this->convertTimeToFloat($hF) - $this->convertTimeToFloat($hD);
            }

            $DhmtlxDateEvenement = $em->getRepository(DhtmlxEvenement::class)->findDhtmlxEvenementBySerieAndSemester($DhmtlxDateSerie[0]->getId(), 0);
            foreach ($DhmtlxDateEvenement as $event) {
                $hD = date_format($event->getDateDebut(), 'H:i');
                $hF = date_format($event->getDateFin(), 'H:i');
                $nbHeuresPrevues += $this->convertTimeToFloat($hF) - $this->convertTimeToFloat($hD);
            }
        }

        $inscrits = $em->getRepository(Inscription::class)->findInscriptionByCreneauIdAndYear($creneau->getId(), date_format($currentYear, 'Y'));
        $effectifReel = $em->getRepository(Inscription::class)->findInscriptionByCreneauIdAndYear($creneau->getId(), date_format($lastYear, 'Y'));

        $tab = [
            $nomPrenom,
            $creneau->getFormatActivite()->getActivite()->getClasseActivite()->getLibelle(),
            $creneau->getFormatActivite()->getActivite()->getLibelle(),
            '',
            $dayOfWeek[$jour],
            $horaire,
            $duree,
            (null != $creneau->getLieu() ? $creneau->getLieu()->getEtablissement()->getLibelle() : ''),
            (null != $creneau->getLieu() ? $creneau->getLieu()->getLibelle() : ''),
            '',
            $creneau->getCapacite(),
            $inscrits,
            $effectifReel,
            '€',
            '€',
            '',
            $nbHeuresPrevues,
            '€',
            $nbHeuresFaites,
            '',
            '€',
            '€',
        ];

        if (!empty($tab)) {
            $index = 0;
            foreach (range('A', 'V') as $col) {
                $sheet->setCellValue($col.$idCol, $tab[$index]);
                $sheet->getStyle($col.$idCol)->applyFromArray($styleArray);
                if ('N' == $col || 'O' == $col || 'R' == $col || 'U' == $col || 'V' == $col) {
                    $sheet->getStyle($col.$idCol)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
                }
                ++$index;
            }
            ++$idCol;
        }
    }

    private function setStyleArrayForExcel($bold, $size): array
    {
        return [
            'font' => [
                'bold' => $bold,
                'size' => $size,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
    }

    private function createExcelStreamedResponse($spreadsheet, $extract_name)
    {
        $writer = new Xlsx($spreadsheet);
        $response = new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );
        $filename = $extract_name.date('Y-m-d').'_'.date('H-i-s').'.xlsx';
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename='.$filename);

        return $response;
    }

    //Autres fonctions
    private function convertTimeToFloat($heure)
    {
        $heure = explode(':', $heure);

        return $heure[0] + ($heure[1] * 0.02);
    }

    private function getLibelleResult($collection)
    {
        if ($collection instanceof PersistentCollection) {
            $collection = $collection->getValues();
        }
        $result = '';
        if (is_array($collection)) {
            foreach ($collection as $item) {
                $result .= $item->getLibelle()." \n";
            }
        } elseif ('aucun' != $collection) {
            $result .= $collection->getLibelle();
        }

        return $result;
    }

    private function getNomPrenomEncadrant($encadrants)
    {
        $result = '';
        if (is_array($encadrants)) {
            foreach ($encadrants as $encadrant) {
                $result .= mb_strtoupper($encadrant->getNom()).' '.$encadrant->getPrenom()." \n";
            }
        } elseif ('aucun' != $encadrants) {
            $result = mb_strtoupper($encadrants->getNom()).' '.$encadrants->getPrenom();
        } else {
            $result = ' ';
        }

        return $result;
    }

    private function getDescriptionCreneau($em, $creneau)
    {
        $result = ' ';

        $dhtmlxSerie = $em->getRepository(DhtmlxSerie::class)->findDhtmlxSerieByCreneau($creneau->getId());
        if (!empty($dhtmlxSerie)) {
            $dhtmlxEvenement = $em->getRepository(DhtmlxEvenement::class)->findBySerie($dhtmlxSerie[0]->getId());
            if (!empty($dhtmlxEvenement)) {
                $result = $dhtmlxEvenement[0]->getDescription();
            }
        }

        return $result;
    }

    private function getEligibiliteCreneau($em, $creneau)
    {
        $result = 'Non renseigné';

        $dhtmlxSerie = $em->getRepository(DhtmlxSerie::class)->findDhtmlxSerieByCreneau($creneau->getId());
        if (!empty($dhtmlxSerie)) {
            $dhtmlxEvenement = $em->getRepository(DhtmlxEvenement::class)->findBySerie($dhtmlxSerie[0]->getId());
            if (!empty($dhtmlxEvenement)) {
                if (true == $dhtmlxEvenement[0]->getEligibleBonus()) {
                    $result = 'Eligible au bonus';
                } else {
                    $result = 'Non éligible';
                }
            }
        }

        return $result;
    }

    private function getPeriodeCreneau($em, $creneau)
    {
        $result = 'Non renseigné';

        $dhtmlxSerie = $em->getRepository(DhtmlxSerie::class)->findDhtmlxSerieByCreneau($creneau->getId());
        if (!empty($dhtmlxSerie)) {
            $dhtmlxEvenement = $em->getRepository(DhtmlxEvenement::class)->findBySerie($dhtmlxSerie[0]->getId());
            if (!empty($dhtmlxEvenement)) {
                $result = date_format($dhtmlxEvenement[0]->getDateDebut(), 'd/m/Y').' - '.date_format(end($dhtmlxEvenement)->getDateDebut(), 'd/m/Y');
            }
        }

        return $result;
    }

    private function getStatutInscription($inscriptions)
    {
        $result = '';
        $translator = $this->get('translator');
        if (is_array($inscriptions)) {
            foreach ($inscriptions as $inscription) {
                $result .= $translator->trans('common.'.$inscription->getStatut())." \n";
            }
        } elseif ('aucun' != $inscriptions) {
            $result = $translator->trans('common.'.$inscriptions->getStatut());
        } else {
            $result = 'Aucune inscription';
        }

        return $result;
    }

    private function getDetailInscription($inscription, $detail)
    {
        $result = '';
        if (null != $inscription && 'aucun' != $inscription) {
            if (is_array($inscription)) {
                foreach ($inscription as $inscrit) {
                    switch ($detail) {
                        case 'nomprenom':
                            $result .= strtoupper($inscrit->getUtilisateur()->getNom()).' '.$inscrit->getUtilisateur()->getNom()."\n";

                            break;
                        case 'dateinscription':
                            $result .= date_format($inscrit->getDate(), 'd/m/Y')."\n";

                            break;
                        case 'datevalidation':
                            null != $inscrit->getDateValidation() ? $result .= date_format($inscrit->getDateValidation(), 'd/m/Y')."\n" : $result .= "\n";

                            break;
                        case 'datedesinscription':
                            null != $inscrit->getDateDesinscription() ? $result .= date_format($inscrit->getDateDesinscription(), 'd/m/Y')."\n" : $result .= "\n";

                            break;
                        case 'motifannulation':
                            null != $inscrit->getMotifAnnulation() ? $result .= $inscrit->getMotifAnnulation()."\n" : $result .= "\n";

                            break;
                        case 'commentaireannulation':
                            null != $inscrit->getCommentaireAnnulation() ? $result .= $inscrit->getCommentaireAnnulation()."\n" : $result .= "\n";

                            break;
                        default:
                            $result = '';

                            break;
                    }
                }
            } else {
                switch ($detail) {
                    case 'nomprenom':
                        $result .= strtoupper($inscription->getUtilisateur()->getNom()).' '.$inscription->getUtilisateur()->getNom()."\n";

                        break;
                    case 'dateinscription':
                        $result .= date_format($inscription->getDate(), 'd/m/Y')."\n";

                        break;
                    case 'datevalidation':
                        null != $inscription->getDateValidation() ? $result .= date_format($inscription->getDateValidation(), 'd/m/Y')."\n" : $result .= "\n";

                        break;
                    case 'datedesinscription':
                        null != $inscription->getDateDesinscription() ? $result .= date_format($inscription->getDateDesinscription(), 'd/m/Y')."\n" : $result .= "\n";

                        break;
                    case 'motifannulation':
                        null != $inscription->getMotifAnnulation() ? $result .= $inscription->getMotifAnnulation()."\n" : $result .= "\n";

                        break;
                    case 'commentaireannulation':
                        null != $inscription->getCommentaireAnnulation() ? $result .= $inscription->getCommentaireAnnulation()."\n" : $result .= "\n";

                        break;
                    default:
                        $result = '';

                        break;
                }
            }
        } else {
            $result = ' ';
        }

        return $result;
    }

    private function getEncadrantValue($collection)
    {
        if ($collection instanceof PersistentCollection) {
            $collection = $collection->getValues();
        }

        if (0 != sizeof($collection)) {
            return $collection;
        }

        return'aucun';
    }

    private function getInscriptionValue($em, $main, $statut, $id)
    {
        $result = [];

        if ('FormatActivite' == $main || 'Creneau' == $main) {
            $recherche = 'findBy'.$main;
        } else {
            $recherche = 'findInscriptionBy'.$main;
        }

        if ('0' !== $statut) {
            $recherche .= 'AndStatut';
            $result = $em->getRepository(Inscription::class)->{$recherche}($id, $statut);
        } else {
            $result = $em->getRepository(Inscription::class)->{$recherche}($id);
        }

        if ($result instanceof PersistentCollection) {
            $result = $result->getValues();
        }

        if (0 == sizeof($result)) {
            $result = 'aucun';
        }

        return $result;
    }

    private function getEtablissementValue($em, $main, $id)
    {
        $result = [];
        $recherche = 'findEtablissementBy'.$main;
        $result = $em->getRepository(Etablissement::class)->{$recherche}($id);

        if ($result instanceof PersistentCollection) {
            $result = $result->getValues();
        }

        if (0 == sizeof($result)) {
            $result = 'aucun';
        }

        return $result;
    }

    private function getLieuValue($lieu)
    {
        if (null != $lieu) {
            if ($lieu instanceof PersistentCollection) {
                $lieu = $lieu->getValues();
                if (0 != sizeof($lieu)) {
                    return $lieu;
                }
            } elseif ($lieu instanceof Lieu) {
                return $lieu;
            }
        }

        return 'aucun';
    }

    private function getStatutValue($statut)
    {
        if (1 == $statut) {
            return 'Publié';
        }
        if (0 == $statut) {
            return 'En cours de saisie';
        }

        return 'Non renseigné';
    }

    private function getPayantValue($payant)
    {
        if ($payant) {
            return 'Payant';
        }

        return 'Non payant';
    }

    private function getCarteFormatValue($formatActivite)
    {
        if ($formatActivite instanceof FormatAchatCarte) {
            return $formatActivite->getCarte()->getLibelle();
        }

        return ' ';
    }

    private function getRessourceFormatValue($formatActivite)
    {
        if ($formatActivite instanceof FormatAvecReservation) {
            return $formatActivite->getRessource();
        }

        return 'aucun';
    }

    private function getTarifValue($tarif)
    {
        if (null != $tarif) {
            return $tarif->getLibelle();
        }

        return ' ';
    }

    private function addTitleColumn($array, $data)
    {
        foreach ($data as $key => $value) {
            if ('1' === $value) {
                if (false !== strpos($key, 'Dates')) {
                    $date = explode(' ', $key);
                    array_push($array, $date[0].' début '.$date[1], $date[0].' fin '.$date[1]);
                } else {
                    array_push($array, $key);
                }
            }
        }

        return $array;
    }
}
