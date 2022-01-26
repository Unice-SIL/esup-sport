<?php

/*
 * Classe - ReportingCommandes
 *
 * Gestion des actions liees au Reporting
 * Reporting des commandes et des crédit
 * Gestion de l'export des fichier en $pdf
 * Gestion des avoirs
*/

namespace UcaBundle\Controller\UcaGest\Gestion;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Datatables\DetailsCommandeDatatable;
use UcaBundle\Datatables\GestionCommandesDatatable;
use UcaBundle\Entity\Commande;
use UcaBundle\Entity\CommandeDetail;
use UcaBundle\Entity\UtilisateurCreditHistorique;
use UcaBundle\Form\GestionCommandeType;

/**
 * @Route("UcaGest/Reporting")
 * @Isgranted("ROLE_GESTION_COMMANDES")
 */
class ReportingController extends Controller
{
    /**
     * @Route("/Commandes",name="UcaGest_ReportingCommandes")
     *
     * @param null|mixed $avoir
     *
     * Fonction qui retourne la page reporting commande dans laquelle on retrouve un datatable listant toutes les commandes
     */
    public function listerAction(Request $request, Commande $item = null)
    {
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $this->get('sg_datatables.factory')->create(GestionCommandesDatatable::class);
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;
        //$form = $this->get('form.factory')->create(GestionCommandeType::class);
        //$twigConfig['form'] = $form->createView();
        if ($isAjax) {
            $responseService = $this->get('sg_datatables.response');
            $responseService->setDatatable($datatable);
            $dtQueryBuilder = $responseService->getDatatableQueryBuilder();
            $qb = $dtQueryBuilder->getQb();
            $qb->where('commande.statut NOT LIKE :panier');
            $qb->setParameter('panier', 'panier');
            /*if ('UcaGest_ReportingAvoirs' == $request->get('_route')) {
                $qb->andWhere('commande.id = :id');
                $qb->setParameter('id', $item->getId());
                $twigConfig['avoirReporting'] = true;
            }*/

            return $responseService->getResponse();
        }

        // Bouton Ajouter
        $twigConfig['noAddButton'] = true;
        $twigConfig['codeListe'] = 'ReportingCommandes';

        if ($this->isGranted('ROLE_GESTION_PAIEMENT_COMMANDE') or $this->isGranted('ROLE_GESTION_COMMANDES')) {
            //Ajout du bouton exporter toutes les factures
            $twigConfig['exportAll'] = true;
            //Ajout du bouton extraire les commandes
            $twigConfig['gestionButtons'] = true;
        }

        return $this->render('@Uca/UcaGest/Reporting/Commande/Datatable.html.twig', $twigConfig);
    }

    /**
     * @Route("/Commande/{id}",name="UcaGest_ReportingCommandeDetails", methods={"GET"})
     * @Route("/Avoir/{id}/{refAvoir}", name="UcaGest_AvoirDetails", methods={"GET"})
     *
     * @param null|mixed $refAvoir
     *
     * Fonction qui permet de voir le détail d'une commande
     */
    public function voirAction(Request $request, Commande $commande, $refAvoir = null)
    {
        /*foreach ($commande->getCommandeDetails() as $cmdDetails) {
            dump($cmdDetails->getLibelle());
        }
        die;*/
        $em = $this->getDoctrine()->getManager();
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $this->get('sg_datatables.factory')->create(DetailsCommandeDatatable::class);
        $creditRepo = $em->getRepository(UtilisateurCreditHistorique::class);
        $usr = $this->container->get('security.token_storage')->getToken()->getUser();
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;
        if ($isAjax) {
            $responseService = $this->get('sg_datatables.response');
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

            return $this->render('@Uca/UcaGest/Reporting/Avoir/DetailsAvoir.html.twig', $twigConfig);
        }

        return $this->render('@Uca/UcaGest/Reporting/Commande/DetailsCommande.html.twig', $twigConfig);
    }

    /**
     * @Route("/{id}/Avoir/Ajouter", name="UcaGest_AvoirAjouter", options={"expose"=true}, methods={"GET", "POST"}, requirements={"id"="\d+"})
     * @Isgranted("ROLE_GESTION_AVOIR")
     *
     * Fonction qui permet de générer un avoir à partir d'un commande
     */
    public function ajouterAvoirAction(Request $request, Commande $item)
    {
        $em = $this->getDoctrine()->getManager();
        $usr = ($item->getUtilisateur());
        $oldRef = $em->getRepository('UcaBundle:CommandeDetail')->max('referenceAvoir');
        $form = $this->createForm('UcaBundle\Form\AvoirType', $item);
        $form->handleRequest($request);
        $dtAvoir = new \DateTime();
        $montant = 0;

        if ($form->isSubmitted() && $form->isValid() && $request->isMethod('POST')) {
            $this->get('uca.flashbag')->addMessageFlashBag('avoir.ajouter.success', 'success');
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
                        if ($cd->getInscription()) {
                            $cd->getInscription()->setStatut('ancienneinscription')->updateNbInscrits(false);
                        }
                    }
                    $em->flush();
                } elseif ($cmdDetails->getInscription()) {
                    $cmdDetails->getInscription()
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

        return $this->render('@Uca/UcaGest/Reporting/Avoir/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Commande/InformationsCarte/{id}/Modifier", name="UcaGest_CommandeDetails_InformationsCarte", methods={"GET","POST"}, requirements={"id"="\d+"})
     */
    public function modifierInformationsCarteAction(Request $request, CommandeDetail $item)
    {
        $commande = $item->getCommande();
        if (!$commande->hasFormatAchatCarte()) {
            return $this->redirectToRoute('UcaGest_ReportingCommandeDetails', ['id' => $item->getId()]);
        }
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm('UcaBundle\Form\CommandeDetailInformationsCarteType', $item, [
            'date_paiement' => $commande->getDatePaiement(),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $request->isMethod('POST')) {
            $em->flush();
            $this->get('uca.flashbag')->addActionFlashBag($item, 'Modifier');

            return $this->redirectToRoute('UcaGest_ReportingCommandeDetails', ['id' => $commande->getId()]);
        }

        $twigConfig['commandeDetail'] = $item;
        $twigConfig['form'] = $form->createView();

        return $this->render('@Uca/UcaGest/Reporting/Commande/FormulaireNumeroCarte.html.twig', $twigConfig);
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
    public function commandesExtractionAction(Request $request, $dateDebut, $dateFin, $nom, $prenom, $numCommande, $numRecu, $montant, $statut, $moyen, $recherche)
    {
        $em = $this->getDoctrine()->getManager();
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
            $extractionService = $this->container->get('uca.extraction.excel');
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

        $this->get('uca.flashbag')->addMessageFlashBag('common.aucune.facture', 'danger');

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
    public function exportAllAction(Request $request, $dateDebut, $dateFin, $nom, $prenom, $numCommande, $numRecu, $montant, $statut, $moyen, $recherche)
    {
        $em = $this->getDoctrine()->getManager();
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
            $pdf = $this->container->get('uca.creationpdf');
            foreach ($commandes as $commande) {
                $twigConfig = ['commande' => $commande];
                $pdf->createMultipleView('@Uca/UcaWeb/Commande/Facture.html.twig', $twigConfig);
            }

            $pdf->createMultiplePdf(['author' => 'Université de Nice', 'title' => 'Facture', 'output' => 'Factures.pdf']);
        } else {
            $this->get('uca.flashbag')->addMessageFlashBag('common.aucune.facture', 'danger');

            return $this->redirectToRoute('UcaGest_ReportingCommandes');
        }
    }
}
