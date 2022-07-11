<?php

/*
 * Classe - PanierController
 *
 * Gestion du panier de l'$utilisateur
 * Affiche la liste des article
 * SUppression d'un seul ou de tous les articles
*/

namespace App\Controller\UcaWeb;

use App\Entity\Uca\Commande;
use App\Service\Common\FlashBag;
use App\Entity\Uca\CommandeDetail;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ValiderPaiementPayboxType;
use App\Service\Securite\TimeoutService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("UcaWeb")
 */
class PanierController extends AbstractController
{
    /**
     * @Route("/Panier", name="UcaWeb_Panier")
     */
    public function voirAction(Request $request, FlashBag $flashBag, TimeoutService $timeoutService, EntityManagerInterface $em)
    {
        $timeoutService->nettoyageCommandeEtInscription();
        $utilisateur = $this->getUser();
        if ($utilisateur) {
            $panier = $utilisateur->getPanier();
            $twigConfig['commande'] = $panier;
            $twigConfig['source'] = 'monpanier';
            $form = $this->createForm(ValiderPaiementPayboxType::class, $panier);
            if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
                if ($panier->getCgvAcceptees()) {
                    $em->persist($panier);
                    $em->flush();

                    return $this->redirectToRoute('UcaWeb_PaiementRecapitulatif', ['id' => $panier->getId(), 'typePaiement' => 'PAYBOX']);
                }
                $flashBag->addTranslatedFlashBag('danger', 'mentions.conditions.nonvalide');
            }
            $twigConfig['form'] = $form->createView();

            return $this->render('UcaBundle/UcaWeb/Commande/DetailCommande.html.twig', $twigConfig);
        }
        // Sinon on redirige vers page de connexion
        return $this->redirectToRoute('UcaWeb_ConnexionSelectionProfil');
    }

    /**
     * @Route("/SuppressionArticle/{id}", name="UcaWeb_SuppressionArticle")
     */
    public function suppressionAction(Request $request, CommandeDetail $commandeDetail, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $commande = $commandeDetail->getCommande();
        if ($commandeDetail->traitementPostSuppressionPanier(['motif_annulation' => 'annulationutilisateur', 'commentaire_annulation' => ''])) {
            $em->remove($commandeDetail);
        } else {
            $flashBag->addActionErrorFlashBag($this, 'Supprimer');
        }

        return $this->finSuppression($commande, $em);
    }

    /**
     * @Route("/SuppressionToutArticle/{id}", name="UcaWeb_SuppressionToutArticle")
     */
    public function suppressionToutArticleAction(Request $request, Commande $commande, EntityManagerInterface $em)
    {
        $criteria = new Criteria();
        $criteria->orderBy(['type' => 'DESC']);
        $listeCommandeDetails = $commande->getCommandeDetails()->matching($criteria);

        foreach ($listeCommandeDetails->getIterator() as $commandeDetail) {
            $commandeDetail->traitementPostSuppressionPanier(['motif_annulation' => 'annulationutilisateur', 'commentaire_annulation' => '']);
        }

        return $this->finSuppression($commande, $em);
    }

    public function finSuppression($commande, EntityManagerInterface $em)
    {
        $commande->updateMontantTotal();
        if ($commande->getCommandeDetails()->isEmpty()) {
            $em->remove($commande);
        }
        $em->flush();

        return $this->redirectToRoute('UcaWeb_Panier');
    }
}
