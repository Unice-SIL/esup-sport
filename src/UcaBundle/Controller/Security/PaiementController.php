<?php

/*
 * Classe - PaiementController
 *
 * Gestion du paimeent pour l'application
 * Gestion du paiement par PAYBOX (via un service)
 * Gestion du paiement au BDS
 * Gesiton du paiement par crédit utilisateur
*/

namespace UcaBundle\Controller\Security;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Entity\Commande;
use UcaBundle\Entity\UtilisateurCreditHistorique;
use UcaBundle\Form\NumeroChequeType;

class PaiementController extends Controller
{
    /**
     * @Route("/UcaWeb/Paiement/Recapitulatif/{id}", name="UcaWeb_PaiementRecapitulatif")
     */
    public function paiementRecapitulatifAction(Request $request, Commande $commande)
    {
        $em = $this->getDoctrine()->getManager();
        $typePaiement = $request->get('typePaiement');
        $moyenPaiement = 'PAYBOX' == $typePaiement ? 'cb' : null;
        $commande->changeStatut('apayer', ['typePaiement' => $typePaiement, 'moyenPaiement' => $moyenPaiement]);
        $em->flush();
        if ($commande->getUtilisateur()->getCreditTotal() >= $commande->getMontantTotal()) {
            $moyenPaiement = 'credit';
            $typePaiement = 'credit';
            $usr = $commande->getUtilisateur();
            $commande->setCreditUtilise($commande->getMontantTotal());
            $creditHistorique = new UtilisateurCreditHistorique($usr, $commande->getMontantTotal(), null, 'debit', "Règlement d'une commande");
            $creditHistorique->setCommandeAssociee($commande->getId());
            $usr->addCredit($creditHistorique);
            $commande->changeStatut('termine', ['typePaiement' => $typePaiement, 'moyenPaiement' => $moyenPaiement]);
            $em->flush();

            $twigConfig['status'] = 'success';
            $twigConfig['source'] = $moyenPaiement;
            $twigConfig['commande'] = $commande;

            return $this->render('@Uca/UcaWeb/Commande/PaiementValidation.html.twig', $twigConfig);
        }

        if ('PAYBOX' == $typePaiement) {
            $paybox = $this->get('uca.paybox');
            $paybox->setCommande($commande);
            $twigConfig['url'] = $paybox->getUrl();
            $twigConfig['form'] = $paybox->getForm();
        }

        $twigConfig['panier'] = $commande;

        return $this->render('@Uca/UcaWeb/Commande/RecapitulatifPaiement.html.twig', $twigConfig);
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

        return $this->render('@Uca/UcaWeb/Commande/PaiementValidation.html.twig', $twigConfig);
    }

    /**
     * @Route("/UcaWeb/Paiement/Validation/{id}", name="UcaWeb_PaiementValidation")
     */
    public function paiementValidationAction(Request $request, Commande $commande)
    {
        $em = $this->getDoctrine()->getManager();
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
            if ($ancienCredit = $commande->getUtilisateur()->getCreditTotal() > 0) {
                $montant = ($ancienCredit < $commande->getMontantTotal()) ? $ancienCredit : $commande->getMontantTotal();
                $usr = $commande->getUtilisateur();
                $commande->setCreditUtilise($montant);
                $creditHistorique = new UtilisateurCreditHistorique($usr, $montant, null, 'debit', "Règlement d'une commande");
                $creditHistorique->setCommandeAssociee($commande->getId());
                $usr->addCredit($creditHistorique);
            }
            $commande->changeStatut('termine', ['typePaiement' => $typePaiement, 'moyenPaiement' => $moyenPaiement]);

            if ('cheque' == $moyenPaiement) {
                $form = $this->get('form.factory')->create(NumeroChequeType::class);
                $form->handleRequest($request);
                $validation = ($form->isSubmitted() && $form->isValid());
                if ($request->isMethod('POST') && $validation) {
                    $commande->setNumeroCheque($form->getData()['numeroCheque']);
                    $em->persist($commande);
                    $em->flush();
                    $url = $this->get('router')->generate('UcaWeb_PaiementValidationCheque', ['id' => $commande->getId(), 'source' => 'gestioncaisse']);

                    return new JsonResponse([
                        'formValid' => true,
                        'redirection' => $url,
                    ]);
                }

                return new JsonResponse([
                    'formValid' => false,
                    'form' => $this->renderView('@Uca/UcaWeb/Commande/Modal.PaiementValidation.Form.html.twig', [
                        'form' => $form->createView(),
                    ]),
                ]);
            }
            $em->flush();
            $twigConfig['status'] = 'success';
        } elseif ('termine' == $commande->getStatut()) {
            return $this->redirectToRoute('UcaGest_ReportingCommandeDetails', ['id' => $commande->getId()]);
        } else {
            $twigConfig['status'] = 'canceled';
        }
        $twigConfig['source'] = $source;
        $twigConfig['commande'] = $commande;

        return $this->render('@Uca/UcaWeb/Commande/PaiementValidation.html.twig', $twigConfig);
    }

    /**
     * @Route("/UcaWeb/Paiement/Retour/{status}", name="UcaWeb_PaiementRetourPaybox")
     *
     * @param mixed $status
     */
    public function paiementRetourPayboxAction(Request $request, $status)
    {
        $em = $this->getDoctrine()->getManager();
        $retour = $request->get('Erreur');
        if ($retour) {
            '00000' == $retour ? $this->addFlash('success', 'paybox.error.'.$retour) : $this->addFlash('danger', 'paybox.error.'.$retour);
        }
        $noCommande = $request->get('Ref');
        $montant = $request->get('Mt');
        $commande = $em->getRepository('UcaBundle:Commande')->findOneBy(['numeroCommande' => $noCommande, 'montantTotal' => $montant / 100]);
        $twigConfig['source'] = 'monpanier';
        $twigConfig['status'] = $status;
        $twigConfig['commande'] = $commande;

        return $this->render('@Uca/UcaWeb/Commande/PaiementValidation.html.twig', $twigConfig);
    }

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
