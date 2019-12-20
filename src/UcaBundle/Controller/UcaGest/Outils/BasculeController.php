<?php

namespace UcaBundle\Controller\UcaGest\Outils;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Entity\Activite;
use UcaBundle\Entity\CommandeDetail;
use UcaBundle\Entity\DhtmlxEvenement;
use UcaBundle\Entity\FormatActivite;
use UcaBundle\Entity\Inscription;
use UcaBundle\Form\BasculeType;

/**
 * @Route("UcaGest")
 * @Isgranted("ROLE_GESTION_BASCULE")
 */
class BasculeController extends Controller
{
    /**
     * @Route("/Bascule", name="UcaGest_BasculeAccueil")
     */
    public function afficherAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $listeActivites = [];
        $activites = $em->getRepository(Activite::class)->findAll();
        foreach ($activites as $key => $activite) {
            $key = ucfirst($activite->getLibelle());
            $listeActivites[$key] = $activite->getId();
        }
        $form = $this->get('form.factory')->create(BasculeType::class, null, ['liste_activites' => $listeActivites]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $request->isMethod('POST')) {
            $this->basculeAction($form->getData()['activites'], $form->getData()['nouvelleDateDebutInscription'], $form->getData()['nouvelleDateFinInscription'], $form->getData()['nouvelleDateDebutEffective'], $form->getData()['nouvelleDateFinEffective']);

            $this->get('uca.flashbag')->addMessageFlashBag('bascule.success', 'success');

            return $this->redirect($request->getUri());
        }
        $twigConfig['form'] = $form->createView();

        return $this->render('@Uca/UcaGest/Outils/Bascule/Bascule.html.twig', $twigConfig);
    }

    private function basculeAction($listeActiviteId, $nouvelleDateDebutInscription, $nouvelleDateFinInscription, $nouvelleDateDebutEffective, $nouvelleDateFinEffective)
    {
        $em = $this->getDoctrine()->getManager();
        $listeInscriptionsBascule = $em->getRepository(Inscription::class)->findInscriptionCreneauxBascule($listeActiviteId);
        foreach ($listeInscriptionsBascule as $inscription) {
            if ('attentevalidationencadrant' == $inscription->getStatut() || 'attentevalidationgestionnaire' == $inscription->getStatut() || 'attenteajoutpanier' == $inscription->getStatut()) {
                $inscription->setStatut('annule', ['motifAnnulation' => 'basculesemestre']);
            } elseif ('valide' == $inscription->getStatut()) {
                $inscription->setStatut('ancienneinscription');
            } elseif ('initialise' == $inscription->getStatut()) {
                $listeCommandeDetail = $em->getRepository(CommandeDetail::class)->findBy(['type' => 'inscription', 'inscription' => $inscription->getId()]);
                foreach ($listeCommandeDetail as $commandeDetail) {
                    $em->remove($commandeDetail);
                }
                $inscription->setStatut('annule', ['motifAnnulation' => 'basculesemestre']);
            }
        }

        $em->getRepository(DhtmlxEvenement::class)->suppressionDateBascule($listeActiviteId, $nouvelleDateDebutEffective);

        foreach ($listeActiviteId as $activiteId) {
            $listeFormatActivite = $em->getRepository(FormatActivite::class)->findByActivite($activiteId);
            if ($listeFormatActivite) {
                foreach ($listeFormatActivite as $formatActivite) {
                    $formatActivite->setDateDebutInscription($nouvelleDateDebutInscription);
                    $formatActivite->setDateFinInscription($nouvelleDateFinInscription);

                    $formatActivite->setDateDebutEffective($nouvelleDateDebutEffective);
                    $formatActivite->setDateFinEffective($nouvelleDateFinEffective);

                    if ($formatActivite->getDateFinPublication() < $formatActivite->getDateFinInscription()) {
                        $formatActivite->setDateFinPublication($formatActivite->getDateFinInscription());
                    }
                    if ($formatActivite->getDateDebutPublication() > $formatActivite->getDateDebutInscription()) {
                        $formatActivite->setDateDebutPublication($formatActivite->getDateDebutInscription());
                    }
                }
            }
        }
        $em->flush();

        return $this->redirectToRoute('UcaGest_BasculeAccueil');
    }
}
