<?php

namespace UcaBundle\Controller\UcaWeb;

use Doctrine\Common\Collections\Criteria;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Entity\Commande;
use UcaBundle\Entity\CommandeDetail;
use UcaBundle\Form\ValiderPaiementPayboxType;

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
            $twigConfig['commande'] = $panier;
            $twigConfig['source'] = 'monpanier';
            $form = $this->get('form.factory')->create(ValiderPaiementPayboxType::class, $panier);
            if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
                if ($panier->getCgvAcceptees()) {
                    $em->persist($panier);
                    $em->flush();

                    return $this->redirectToRoute('UcaWeb_PaiementRecapitulatif', ['id' => $panier->getId(), 'typePaiement' => 'PAYBOX']);
                }
                $this->get('uca.flashbag')->addTranslatedFlashBag('danger', 'mentions.conditions.nonvalide');
            }
            $twigConfig['form'] = $form->createView();

            return $this->render('@Uca/UcaWeb/Commande/DetailCommande.html.twig', $twigConfig);
        }
        // Sinon on redirige vers page de connexion
        return $this->redirectToRoute('UcaWeb_ConnexionSelectionProfil');
    }

    /**
     * @Route("/SuppressionArticle/{id}", name="UcaWeb_SuppressionArticle")
     */
    public function suppressionAction(Request $request, CommandeDetail $commandeDetail)
    {
        $em = $this->getDoctrine()->getManager();
        $commande = $commandeDetail->getCommande();
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
        $criteria->orderBy(['type' => 'DESC']);
        $listeCommandeDetails = $commande->getCommandeDetails()->matching($criteria);

        foreach ($listeCommandeDetails->getIterator() as $commandeDetail) {
            $commandeDetail->traitementPostSuppressionPanier(['motif_annulation' => 'annulationutilisateur', 'commentaire_annulation' => '']);
        }

        return $this->finSuppression($commande);
    }

    public function finSuppression($commande)
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
