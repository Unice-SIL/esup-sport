<?php

namespace UcaBundle\Controller\UcaWeb;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use UcaBundle\Service\Common\FlashBag;
use UcaBundle\Entity\Commande;
use UcaBundle\Entity\CommandeDetail;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\Common\Collections\Criteria;

/**
 * @Route("UcaWeb")
 */
class PanierController extends Controller
{
    /**
     * @Route("/Panier", name="UcaWeb_Panier")
     */
    public function voirAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('uca.timeout')->nettoyageCommandeEtInscription();
        $utilisateur = $this->getUser();
        if ($utilisateur) {
            $panier = $utilisateur->getPanier();
            // if(empty($panier->getId())) {
            //     $em->persist($panier);
            //     $em->flush();
            // }
            $apayer = $utilisateur->getCommandesByStatut('apayer');
            $twigConfig["commande"] = $panier;
            $twigConfig["apayer"] = $apayer;
            $twigConfig["source"] = 'monpanier';
            return $this->render('@Uca/UcaWeb/Commande/DetailCommande.html.twig', $twigConfig);
        } else {
            /* Sinon on redirige vers page de connexion */
            return $this->redirectToRoute('fos_user_security_login');
        }
    }
    
    /**
     * @Route("/SuppressionArticle/{id}", name="UcaWeb_SuppressionArticle")
     */
    public function suppressionAction(Request $request, CommandeDetail $commandeDetail)
    {
        $em = $this->getDoctrine()->getManager();
        $commande =  $commandeDetail->getCommande();
        if ($commandeDetail->traitementPostSuppressionPanier(['motif_annulation' => 'annulationutilisateur', 'commentaire_annulation' => ''])) {
            $em->remove($commandeDetail);
        } else {
            $this->get('uca.flashbag')->addActionErrorFlashBag($this, 'Supprimer');
        }
        return $this->finSuppression($commande);
    }

    /**
     * @Route("/SuppressionToutArticle/{id}", name="UcaWeb_SuppressionToutArticle")
     */
    public function suppressionToutArticleAction(Request $request, Commande $commande)
    {
        $criteria = new Criteria();
        $criteria->orderBy(array("type" => "DESC"));
        $listeCommandeDetails = $commande->getCommandeDetails()->matching($criteria);

        foreach ($listeCommandeDetails->getIterator() as $commandeDetail) {
            $commandeDetail->traitementPostSuppressionPanier(['motif_annulation' => 'annulationutilisateur', 'commentaire_annulation' => '']);
        }
        return $this->finSuppression($commande);
       
    }

    function finSuppression($commande)
    {
        $em = $this->getDoctrine()->getManager();
        $commande->updateMontantTotal();
        if ($commande->getCommandeDetails()->isEmpty()) {
            $em->remove($commande);
        }
        $em->flush();
        return $this->redirectToRoute('UcaWeb_Panier');
    }
}
