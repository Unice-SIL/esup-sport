<?php

namespace UcaBundle\Controller\Security;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Entity\Commande;

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
                || 'panier' == $commande->getStatut() & 0 == $commande->getMontantTotal() && in_array($source, ['monpanier', 'mescommandes']))
        ) {
            $commande->changeStatut('termine', ['typePaiement' => $typePaiement, 'moyenPaiement' => $moyenPaiement]);
            $em->flush();
            $twigConfig['status'] = 'success';
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
        $noCommande = $request->get('Ref');
        $montant = $request->get('Mt');
        $commande = $em->getRepository('UcaBundle:Commande')->findOneBy(['numeroCommande' => $noCommande, 'montantTotal' => $montant / 100]);
        $twigConfig['source'] = 'monpanier';
        $twigConfig['status'] = $status;
        $twigConfig['commande'] = $commande;

        return $this->render('@Uca/UcaWeb/Commande/PaiementValidation.html.twig', $twigConfig);
    }
}
