<?php

/*
 * Classe - ReportingCommandes
 *
 * Gestion des actions liees au Reporting
 * Reporting des commandes et des crédit
 * Gestion de l'export des fichier en $pdf
 * Gestion des avoirs
*/

namespace App\Controller\UcaGest\Gestion;

use App\Datatables\DetailsCommandeDatatable;
use App\Datatables\GestionCommandesDatatable;
use App\Entity\Uca\Commande;
use App\Entity\Uca\CommandeDetail;
use App\Entity\Uca\UtilisateurCreditHistorique;
use App\Repository\CommandeDetailRepository;
use App\Service\Common\CreationPdf;
use App\Service\Common\FlashBag;
use App\Service\Service\ExtractionExcelService;
use App\Service\Service\InscriptionService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sg\DatatablesBundle\Datatable\DatatableFactory;
use Sg\DatatablesBundle\Response\DatatableResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("UcaGest/Reporting")
 * @Isgranted("ROLE_GESTION_COMMANDES")
 */
class ReportingController extends AbstractController
{
    /**
     * @Route("/Commandes",name="UcaGest_ReportingCommandes")
     *
     * @param null|mixed $avoir
     *
     * Fonction qui retourne la page reporting commande dans laquelle on retrouve un datatable listant toutes les commandes
     */
    public function listerAction(Request $request, Commande $item = null, DatatableFactory $datatableFactory, DatatableResponse $datatableResponse)
    {
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $datatableFactory->create(GestionCommandesDatatable::class);
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;
        if ($isAjax) {
            $responseService = $datatableResponse;
            $responseService->setDatatable($datatable);
            $dtQueryBuilder = $responseService->getDatatableQueryBuilder();
            $qb = $dtQueryBuilder->getQb();
            $qb->where('commande.statut NOT LIKE :panier');
            $qb->setParameter('panier', 'panier');

            return $responseService->getResponse();
        }

        // Bouton Ajouter
        $twigConfig['noAddButton'] = true;
        $twigConfig['codeListe'] = 'ReportingCommandes';

        if ($this->isGranted('ROLE_GESTION_PAIEMENT_COMMANDE') or $this->isGranted('ROLE_GESTION_COMMANDES')) {
            // Ajout du bouton exporter toutes les factures
            $twigConfig['exportAll'] = true;
            // Ajout du bouton extraire les commandes
            $twigConfig['gestionButtons'] = true;
        }

        return $this->render('UcaBundle/UcaGest/Reporting/Commande/Datatable.html.twig', $twigConfig);
    }

    /**
     * @Route("/Commande/{id}",name="UcaGest_ReportingCommandeDetails", methods={"GET"})
     * @Route("/Avoir/{id}/{refAvoir}", name="UcaGest_AvoirDetails", methods={"GET"})
     *
     * @param null|mixed $refAvoir
     *
     * Fonction qui permet de voir le détail d'une commande
     */
    public function voirAction(Request $request, Commande $commande, $refAvoir = null, DatatableFactory $datatableFactory, DatatableResponse $datatableResponse, EntityManagerInterface $em)
    {
        /*foreach ($commande->getCommandeDetails() as $cmdDetails) {
            dump($cmdDetails->getLibelle());
        }
        die;*/
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $datatableFactory->create(DetailsCommandeDatatable::class);
        $creditRepo = $em->getRepository(UtilisateurCreditHistorique::class);
        $usr = $this->getUser();
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;
        if ($isAjax) {
            $responseService = $datatableResponse;
            $responseService->setDatatable($datatable);
            $dtQueryBuilder = $responseService->getDatatableQueryBuilder();
            $qb = $dtQueryBuilder->getQb();
            $qb->where('commandedetail.commande = :commande');
            $qb->setParameter('commande', $commande);
            if ('UcaGest_AvoirDetails' == $request->get('_route')) {
                $qb->andWhere('commandedetail.referenceAvoir= :refAvoir');
                $qb->setParameter('refAvoir', $refAvoir);
            }

            return $responseService->getResponse();
        }

        if ($listeCartes = $commande->hasFormatAchatCarte()) {
            if ('termine' == $commande->getStatut()) {
                foreach ($listeCartes as $carteTerminee) {
                    if (null == $carteTerminee->getNumeroCarte()) {
                        $twigConfig['editCardButton'] = true;
                    }
                }
                $twigConfig['cartes'] = $listeCartes;
            } elseif ('avoir' == $commande->getStatut()) {
                $tabCartes = [];
                foreach ($listeCartes as $carteAvoir) {
                    if (null == $carteAvoir->getAvoir()) {
                        $tabCartes[] = $carteAvoir;

                        if (null == $carteAvoir->getNumeroCarte()) {
                            $twigConfig['editCardButton'] = true;
                        }
                    }
                }
                if (!empty($tabCartes)) {
                    $twigConfig['cartes'] = $tabCartes;
                }
            }
        }

        $twigConfig['noAddButton'] = true;
        $twigConfig['codeListe'] = 'DetailsCommande';
        $twigConfig['retourBouton'] = true;
        $twigConfig['commande'] = $commande;
        $twigConfig['source'] = 'reporting';

        if ('UcaGest_AvoirDetails' == $request->get('_route')) {
            $twigConfig['refAvoir'] = (int) $refAvoir;
            $creditAssocie = $creditRepo->findOneBy(['avoir' => (int) $refAvoir]);
            if ('avoir' == $commande->getStatut() && $usr->hasRole('ROLE_GESTION_CREDIT_UTILISATEUR_ECRITURE') && 'annule' == $creditAssocie->getStatut()) {
                $twigConfig['ReportButton'] = true;
            }

            return $this->render('UcaBundle/UcaGest/Reporting/Avoir/DetailsAvoir.html.twig', $twigConfig);
        }

        return $this->render('UcaBundle/UcaGest/Reporting/Commande/DetailsCommande.html.twig', $twigConfig);
    }

    /**
     * @Route("/{id}/Avoir/Ajouter", name="UcaGest_AvoirAjouter", options={"expose"=true}, methods={"GET", "POST"}, requirements={"id"="\d+"})
     * @Isgranted("ROLE_GESTION_AVOIR")
     *
     * Fonction qui permet de générer un avoir à partir d'un commande
     */
    public function ajouterAvoirAction(Request $request, Commande $item, FlashBag $flashBag, EntityManagerInterface $em, CommandeDetailRepository $commandeDetailRepository, InscriptionService $inscriptionService)
    {
        $usr = $item->getUtilisateur();
        $oldRef = $commandeDetailRepository->max('referenceAvoir');
        $form = $this->createForm('App\Form\AvoirType', $item);
        $form->handleRequest($request);
        $dtAvoir = new \DateTime();
        $montant = 0;

        if ($form->isSubmitted() && $form->isValid() && $request->isMethod('POST')) {
            $flashBag->addMessageFlashBag('avoir.ajouter.success', 'success');
            foreach ($item->getAvoirCommandeDetails() as $cmdDetails) {
                $montant += $cmdDetails->getMontant();
                $cmdDetails
                    ->setReferenceAvoir($oldRef + 1)
                    ->setAvoir($item)
                    ->setDateAvoir($dtAvoir)
                ;
                if ($cmdDetails->getTypeAutorisation()) {
                    $cmdDetails->getCommande()->getUtilisateur()->removeAutorisation($cmdDetails->getTypeAutorisation());
                    foreach ($cmdDetails->getCommande()->getCommandeDetails() as $cd) {
                        if ($inscription = $cd->getInscription()) {
                            $inscriptionService->updateStatutInscriptionsPartenaire($inscription);
                            $inscription->setStatut('ancienneinscription');
                        }
                    }
                    $em->flush();
                } elseif ($inscription = $cmdDetails->getInscription()) {
                    $inscriptionService->updateStatutInscriptionsPartenaire($inscription);
                    $inscription
                        ->setStatut('ancienneinscription')
                        ->seDesinscrire($usr, true)
                    ;
                    $em->flush();
                }
            }

            $credit = new UtilisateurCreditHistorique($usr, $montant, $oldRef + 1, 'credit', "Génération d'avoir");
            $em->persist($credit);
            $credit->setCommandeAssociee($item->getId());
            $usr->AddCredit($credit);
            $item->changeStatut('avoir');
            $em->flush();

            return $this->redirectToRoute('UcaGest_ReportingCommandes');
        }

        $twigConfig['commande'] = $item;
        $twigConfig['form'] = $form->createView();
        $twigConfig['codeListe'] = 'ClasseActivite';

        return $this->render('UcaBundle/UcaGest/Reporting/Avoir/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Commande/InformationsCarte/{id}/Modifier", name="UcaGest_CommandeDetails_InformationsCarte", methods={"GET","POST"}, requirements={"id"="\d+"})
     */
    public function modifierInformationsCarteAction(Request $request, CommandeDetail $item, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $commande = $item->getCommande();
        if (!$commande->hasFormatAchatCarte()) {
            return $this->redirectToRoute('UcaGest_ReportingCommandeDetails', ['id' => $item->getId()]);
        }
        $form = $this->createForm('App\Form\CommandeDetailInformationsCarteType', $item, [
            'date_paiement' => $commande->getDatePaiement(),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $request->isMethod('POST')) {
            $em->flush();
            $flashBag->addActionFlashBag($item, 'Modifier');

            return $this->redirectToRoute('UcaGest_ReportingCommandeDetails', ['id' => $commande->getId()]);
        }

        $twigConfig['commandeDetail'] = $item;
        $twigConfig['form'] = $form->createView();

        return $this->render('UcaBundle/UcaGest/Reporting/Commande/FormulaireNumeroCarte.html.twig', $twigConfig);
    }

    /**
     * @Route("/Commandes/Extraction/{dateDebut}/{dateFin}/{numCommande}/{numRecu}/{nom}/{prenom}/{montant}/{statut}/{moyen}/{recherche}" , name="UcaGest_ReportingCommandesExtraction", options={"expose"=true})
     *
     * @param mixed $dateDebut
     * @param mixed $dateFin
     * @param mixed $nom
     * @param mixed $prenom
     * @param mixed $numCommande
     * @param mixed $numRecu
     * @param mixed $montant
     * @param mixed $statut
     * @param mixed $moyen
     * @param mixed $recherche
     */
    public function commandesExtractionAction(Request $request, $dateDebut, $dateFin, $nom, $prenom, $numCommande, $numRecu, $montant, $statut, $moyen, $recherche, FlashBag $flashBag, ExtractionExcelService $extractionService, EntityManagerInterface $em)
    {
        $dateDebut = 'null' !== $dateDebut ? \DateTime::createFromFormat('Y-m-d', $dateDebut)->setTime(0, 0, 0) : null;
        $dateFin = 'null' !== $dateFin ? \DateTime::createFromFormat('Y-m-d', $dateFin)->setTime(23, 59, 59) : null;
        'null' != $nom ?: $nom = null;
        'null' != $prenom ?: $prenom = null;
        'null' != $montant ?: $montant = null;
        'null' != $numCommande ?: $numCommande = null;
        'null' != $numRecu ?: $numRecu = null;
        'null' != $statut ?: $statut = null;
        'null' != $moyen ?: $moyen = null;
        $recherche = ('null' != $recherche) ? str_replace('/', '-', $recherche) : null;

        $cmdDetailsPayant = $em->getRepository(CommandeDetail::class)
            ->findExtractedCommandeDetails($dateDebut, $dateFin, $nom, $prenom, $statut, $moyen, $montant, $numCommande, $numRecu, $recherche, true)
        ;
        $cmdDetailsAll = $em->getRepository(CommandeDetail::class)
            ->findExtractedCommandeDetails($dateDebut, $dateFin, $nom, $prenom, $statut, $moyen, $montant, $numCommande, $numRecu, $recherche)
        ;

        if (!empty($cmdDetailsAll)) {
            $datas = ['Fiche de caisse' => $cmdDetailsPayant, 'Liste commandes' => $cmdDetailsAll];
            $extractionService->getExtractionReportingCommande($datas, $dateDebut, $dateFin);
            $writer = $extractionService->getWriter();
            $response = new StreamedResponse(
                function () use ($writer) {
                    $writer->save('php://output');
                }
            );

            $filename = 'extract_commandes_'.date('Y-m-d').'_'.date('H-i-s').'.xlsx';
            $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $response->headers->set('Content-Disposition', 'attachment;filename='.$filename);

            return $response;
        }

        $flashBag->addMessageFlashBag('common.aucune.facture', 'danger');

        return $this->redirectToRoute('UcaGest_ReportingCommandes');
    }

    /**
     * @Route("/Commandes/ExportAll/{dateDebut}/{dateFin}/{numCommande}/{numRecu}/{nom}/{prenom}/{montant}/{statut}/{moyen}/{recherche}", name="UcaGest_ReportingCommandesExportAll", options={"expose"=true})
     * @Security("is_granted('ROLE_GESTION_COMMANDES')")
     *
     * @param mixed $dateDebut
     * @param mixed $dateFin
     * @param mixed $nom
     * @param mixed $prenom
     * @param mixed $recherche
     * @param mixed $numCommande
     * @param mixed $numRecu
     * @param mixed $montant
     * @param mixed $statut
     * @param mixed $moyen
     */
    public function exportAllAction(Request $request, $dateDebut, $dateFin, $nom, $prenom, $numCommande, $numRecu, $montant, $statut, $moyen, $recherche, FlashBag $flashBag, CreationPdf $pdf, EntityManagerInterface $em)
    {
        $dateDebut = 'null' !== $dateDebut ? \DateTime::createFromFormat('Y-m-d', $dateDebut)->setTime(0, 0, 0) : null;
        $dateFin = 'null' !== $dateFin ? \DateTime::createFromFormat('Y-m-d', $dateFin)->setTime(23, 59, 59) : null;
        'null' != $nom ?: $nom = null;
        'null' != $prenom ?: $prenom = null;
        'null' != $montant ?: $montant = null;
        'null' != $numCommande ?: $numCommande = null;
        'null' != $numRecu ?: $numRecu = null;
        'null' != $statut ?: $statut = null;
        'null' != $moyen ?: $moyen = null;
        $recherche = ('null' != $recherche) ? str_replace('/', '-', $recherche) : null;

        $commandes = $em->getRepository(Commande::class)
            ->findExtractedCommandes($dateDebut, $dateFin, $nom, $prenom, $statut, $moyen, $montant, $numCommande, $numRecu, $recherche)
        ;

        if (!empty($commandes)) {
            foreach ($commandes as $commande) {
                $twigConfig = ['commande' => $commande];
                $pdf->createMultipleView('UcaBundle/UcaWeb/Commande/Facture.html.twig', $twigConfig);
            }

            $pdf->createMultiplePdf(['author' => 'Université de Nice', 'title' => 'Facture', 'output' => 'Factures.pdf']);
        } else {
            $flashBag->addMessageFlashBag('common.aucune.facture', 'danger');

            return $this->redirectToRoute('UcaGest_ReportingCommandes');
        }
    }
}
