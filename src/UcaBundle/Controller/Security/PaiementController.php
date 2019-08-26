<?php

namespace UcaBundle\Controller\Security;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
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
        $moyenPaiement = $typePaiement == 'PAYBOX' ? 'cb' : null;
        $commande->changeStatut('apayer', ['typePaiement' => $typePaiement, 'moyenPaiement' => $moyenPaiement]);
        $em->flush();
        if ($typePaiement == 'PAYBOX') {
            $paybox = $this->get('uca.paybox');
            $paybox->setCommande($commande);
            $twigConfig["url"] = $paybox->getUrl();
            $twigConfig["form"] = $paybox->getForm();
        }
        $twigConfig["panier"] = $commande;
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
        if (
            !$commande->getCommandeDetails()->isEmpty()
            && ($commande->getStatut() == 'apayer' && $request->server->get('HTTP_HOST') == 'localhost' && in_array($source, ['monpanier', 'mescommandes'])
                || $commande->getStatut() == 'apayer' & $this->isGranted('ROLE_GESTION_PAIEMENT_COMMANDE') && $source == 'gestioncaisse'
                || $commande->getStatut() == 'panier' & $commande->getMontantTotal() == 0 && in_array($source, ['monpanier', 'mescommandes']))
        ) {
            $commande->changeStatut('termine', ['typePaiement' => $typePaiement, 'moyenPaiement' => $moyenPaiement]);
            $em->flush();
            $twigConfig["status"] = 'success';
        } else {
            $twigConfig["status"] = 'canceled';
        }
        $twigConfig["source"] = $source;
        $twigConfig["commande"] = $commande;
        return $this->render('@Uca/UcaWeb/Commande/PaiementValidation.html.twig', $twigConfig);
    }

    /**
     * @Route("/UcaWeb/Paiement/Retour/{status}", name="UcaWeb_PaiementRetourPaybox")
     */
    public function paiementRetourPayboxAction(Request $request, $status)
    {
        $em = $this->getDoctrine()->getManager();
        $noCommande = $request->get('Ref');
        $montant = $request->get('Mt');
        $commande = $em->getRepository('UcaBundle:Commande')->findOneBy(['numeroCommande' => $noCommande, 'montantTotal' => $montant / 100]);
        $twigConfig["source"] = 'monpanier';
        $twigConfig["status"] = $status;
        $twigConfig["commande"] = $commande;
        return $this->render('@Uca/UcaWeb/Commande/PaiementValidation.html.twig', $twigConfig);
    }
}
