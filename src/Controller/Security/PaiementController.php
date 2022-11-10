<?php

/*
 * Classe - PaiementController
 *
 * Gestion du paimeent pour l'application
 * Gestion du paiement par PAYBOX (via un service)
 * Gestion du paiement au BDS
 * Gesiton du paiement par crédit utilisateur
*/

namespace App\Controller\Security;

use App\Entity\Uca\Commande;
use App\Entity\Uca\UtilisateurCreditHistorique;
use App\Form\NumeroChequeType;
use App\Repository\CommandeRepository;
use App\Service\Securite\PayBox;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

class PaiementController extends AbstractController
{
    /**
     * @Route("/UcaWeb/Paiement/Recapitulatif/{id}", name="UcaWeb_PaiementRecapitulatif")
     */
    public function paiementRecapitulatifAction(Request $request, Commande $commande, PayBox $paybox, EntityManagerInterface $em)
    {
        $typePaiement = $request->get('typePaiement');
        $moyenPaiement = 'PAYBOX' == $typePaiement ? 'cb' : null;
        $commande->changeStatut('apayer', ['typePaiement' => $typePaiement, 'moyenPaiement' => $moyenPaiement]);
        $em->flush();
        if ($commande->getUtilisateur()->getCreditTotal() >= $commande->getMontantTotal()) {
            $moyenPaiement = 'credit';
            $typePaiement = 'credit';
            $usr = $commande->getUtilisateur();
            $commande->setCreditUtilise(min($usr->getCreditTotal(), $commande->getMontantTotal()));
            $creditHistorique = new UtilisateurCreditHistorique($usr, min($usr->getCreditTotal(), $commande->getMontantTotal()), null, 'debit', "Règlement d'une commande");
            $creditHistorique->setCommandeAssociee($commande->getId());
            $usr->addCredit($creditHistorique);
            $commande->changeStatut('termine', ['typePaiement' => $typePaiement, 'moyenPaiement' => $moyenPaiement]);
            $em->flush();

            $twigConfig['status'] = 'success';
            $twigConfig['source'] = $moyenPaiement;
            $twigConfig['commande'] = $commande;

            return $this->render('UcaBundle/UcaWeb/Commande/PaiementValidation.html.twig', $twigConfig);
        }
        if ($commande->getMontantTotal() > 0 && $commande->getUtilisateur()->getCreditTotal() > 0 && $commande->getUtilisateur()->getCreditTotal() < $commande->getMontantTotal()) {
            $usr = $commande->getUtilisateur();
            $commande->setCreditUtilise($usr->getCreditTotal());
            $creditHistorique = new UtilisateurCreditHistorique($usr, $usr->getCreditTotal(), null, 'debit', "Règlement d'une commande");
            $creditHistorique->setCommandeAssociee($commande->getId());
            $usr->addCredit($creditHistorique);
            $em->flush();
        }

        if ('PAYBOX' == $typePaiement) {
            $commande->setMontantPaybox($commande->getMontantAPayer());
            $em->flush();
        }

        if ('PAYBOX' == $typePaiement) {
            $paybox->setCommande($commande);
            $twigConfig['url'] = $paybox->getUrl();
            $twigConfig['form'] = $paybox->getForm();
        }

        $twigConfig['panier'] = $commande;

        return $this->render('UcaBundle/UcaWeb/Commande/RecapitulatifPaiement.html.twig', $twigConfig);
    }

    /**
     * @Route("/UcaWeb/Paiement/Validation/{id}/{source}", name="UcaWeb_PaiementValidationCheque")
     */
    public function paiementValidationChequeAction(Request $request, Commande $commande, string $source)
    {
        //     UcaWeb/Paiement/Validation/30?typePaiement=BDS&source=gestioncaisse&moyenPaiement=espece&urlHistory=-1

        $twigConfig['status'] = 'success';
        $twigConfig['source'] = $source;
        $twigConfig['commande'] = $commande;

        return $this->render('UcaBundle/UcaWeb/Commande/PaiementValidation.html.twig', $twigConfig);
    }

    /**
     * @Route("/UcaWeb/Paiement/Validation/{id}", name="UcaWeb_PaiementValidation")
     */
    public function paiementValidationAction(Request $request, Commande $commande, RouterInterface $router, EntityManagerInterface $em)
    {
        $source = $request->get('source');
        $typePaiement = $request->get('typePaiement');
        $moyenPaiement = $request->get('moyenPaiement');
        if ('BDS' == $typePaiement) {
            $commande->setNomEncaisseur($this->getUser()->getNom());
            $commande->setPrenomEncaisseur($this->getUser()->getPrenom());
            $commande->setUtilisateurEncaisseur($this->getUser());
        }

        if (
            !$commande->getCommandeDetails()->isEmpty()
            && ('apayer' == $commande->getStatut() && 'localhost' == $request->server->get('HTTP_HOST') && in_array($source, ['monpanier', 'mescommandes'])
                || 'apayer' == $commande->getStatut() & $this->isGranted('ROLE_GESTION_PAIEMENT_COMMANDE') && 'gestioncaisse' == $source
                || 'panier' == $commande->getStatut() & 0 == $commande->getMontantTotal() && in_array($source, ['monpanier', 'mescommandes'])
                || 'panier' == $commande->getStatut() & $commande->getUtilisateur()->getCreditTotal() > 0 && in_array($source, ['monpanier', 'mescommandes']))
        ) {
            // if (($ancienCredit = $commande->getUtilisateur()->getCreditTotal()) > 0) {
            //     $montant = ($ancienCredit < $commande->getMontantTotal()) ? $ancienCredit : $commande->getMontantTotal();
            //     $usr = $commande->getUtilisateur();
            //     $commande->setCreditUtilise($montant);
            //     $creditHistorique = new UtilisateurCreditHistorique($usr, $montant, null, 'debit', "Règlement d'une commande");
            //     $creditHistorique->setCommandeAssociee($commande->getId());
            //     $usr->addCredit($creditHistorique);
            // }
            $commande->changeStatut('termine', ['typePaiement' => $typePaiement, 'moyenPaiement' => $moyenPaiement, 'em' => $em]);

            if ('cheque' == $moyenPaiement) {
                $form = $this->createForm(NumeroChequeType::class);
                $form->handleRequest($request);
                $validation = ($form->isSubmitted() && $form->isValid());
                if ($request->isMethod('POST') && $validation) {
                    $commande->setNumeroCheque($form->getData()['numeroCheque']);
                    $em->persist($commande);
                    $em->flush();
                    $url = $router->generate('UcaWeb_PaiementValidationCheque', ['id' => $commande->getId(), 'source' => 'gestioncaisse']);

                    return new JsonResponse([
                        'formValid' => true,
                        'redirection' => $url,
                    ]);
                }

                return new JsonResponse([
                    'formValid' => false,
                    'form' => $this->renderView('UcaBundle/UcaWeb/Commande/Modal.PaiementValidation.Form.html.twig', [
                        'form' => $form->createView(),
                    ]),
                ]);
            }
            $em->flush();
            $twigConfig['status'] = 'success';
        } elseif ('termine' == $commande->getStatut()) {
            if ($this->isGranted('ROLE_GESTION_COMMANDES')) {
                return $this->redirectToRoute('UcaGest_ReportingCommandeDetails', ['id' => $commande->getId()]);
            }

            return $this->redirectToRoute('UcaWeb_MesCommandesVoir', ['id' => $commande->getId()]);
        } else {
            $twigConfig['status'] = 'canceled';
        }
        $twigConfig['source'] = $source;
        $twigConfig['commande'] = $commande;

        return $this->render('UcaBundle/UcaWeb/Commande/PaiementValidation.html.twig', $twigConfig);
    }

    /**
     * @Route("/UcaWeb/Paiement/Retour/{status}", name="UcaWeb_PaiementRetourPaybox")
     *
     * @param mixed $status
     */
    public function paiementRetourPayboxAction(Request $request, $status, CommandeRepository $commandeRepository, EntityManagerInterface $em)
    {
        $retour = $request->get('Erreur');
        if ($retour) {
            '00000' == $retour ? $this->addFlash('success', 'paybox.error.'.$retour) : $this->addFlash('danger', 'paybox.error.'.$retour);
        }
        $noCommande = $request->get('Ref');
        $montant = $request->get('Mt');
        $commande = $commandeRepository->findOneBy(['numeroCommande' => $noCommande, 'montantTotal' => $montant / 100]);
        $twigConfig['source'] = 'monpanier';
        $twigConfig['status'] = $status;
        $twigConfig['commande'] = $commande;

        return $this->render('UcaBundle/UcaWeb/Commande/PaiementValidation.html.twig', $twigConfig);
    }

    /**
     * @codeCoverageIgnore
     */
    private function getErrorMessages(\Symfony\Component\Form\Form $form)
    {
        $errors = [];

        foreach ($form->getErrors() as $key => $error) {
            if ($form->isRoot()) {
                $errors['#'][] = $error->getMessage();
            } else {
                $errors[] = $error->getMessage();
            }
        }

        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $errors[$child->getName()] = $this->getErrorMessages($child);
            }
        }

        return $errors;
    }
}
