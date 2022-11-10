<?php

/*
 * Classe - ValidationInscriptionEncadrantController
 *
 * Cas des inscriptions devant être valdiées.
 * Permet de consulter les justificatifs ainsi que les informations de l'utilisateur
 * Permet de valider ou de refuser
 * En cas de refus un motif est précisable
 * Des mails sont envoyés lors de ces étapes
*/

namespace App\Controller\UcaWeb;

use App\Entity\Uca\Inscription;
use App\Entity\Uca\Autorisation;
use App\Service\Common\FlashBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Datatables\InscriptionAValiderDatatable;
use App\Service\Service\InscriptionService;
use Doctrine\ORM\EntityManagerInterface;
use Sg\DatatablesBundle\Datatable\DatatableFactory;
use Sg\DatatablesBundle\Response\DatatableResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("UcaWeb/ValidationInscription")
 * @Security("is_granted('ROLE_ENCADRANT') or is_granted('ROLE_GESTIONNAIRE_VALIDEUR_INSCRIPTION')")
 */
class ValidationInscriptionEncadrantController extends AbstractController
{
    /**
     * @Route("/",name="UcaWeb_InscriptionAValiderLister")
     */
    public function listerAction(Request $request, DatatableFactory $datatableFactory, DatatableResponse $datatableResponse)
    {
        if (('gestionnaire' == $request->get('type') && !$this->isGranted('ROLE_GESTIONNAIRE_VALIDEUR_INSCRIPTION')) || 'encadrant' == $request->get('type') && !$this->isGranted('ROLE_ENCADRANT')) {
            throw $this->createAccessDeniedException();
        }

        $isAjax = $request->isXmlHttpRequest();
        $datatable = $datatableFactory->create(InscriptionAValiderDatatable::class);
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;
        if ($isAjax) {
            $responseService = $datatableResponse;
            $responseService->setDatatable($datatable);
            $dtQueryBuilder = $responseService->getDatatableQueryBuilder();
            $qb = $dtQueryBuilder->getQb();
            if ('gestionnaire' == $request->get('type')) {
                $qb->where('inscription.statut LIKE :statutInscription');
                $qb->setParameter('statutInscription', 'attentevalidationgestionnaire');
            } else {
                $qb->innerJoin('App\Entity\Uca\Utilisateur', 'u', 'WITH', 'u.id = :userId');
                $qb->andWhere('inscription.id IN (:inscriptionsAValider)');
                $qb->andWhere('inscription.statut LIKE :statutInscription');
                $qb->setParameter('inscriptionsAValider', $this->getUser()->getInscriptionsAValider());
                $qb->setParameter('userId', $this->getUser()->getId());
                $qb->setParameter('statutInscription', 'attentevalidationencadrant');
            }

            return $responseService->getResponse();
        }
        // Bouton Ajouter
        $twigConfig['noAddButton'] = true;
        $twigConfig['codeListe'] = 'InscriptionAValider';

        return $this->render('UcaBundle/Common/Liste/Datatable_UcaWeb.html.twig', $twigConfig);
    }

    /**
     * @Route("/{id}",name="UcaWeb_InscriptionAValiderVoir")
     * @Security("is_granted('ROLE_ENCADRANT') or is_granted('ROLE_GESTIONNAIRE_VALIDEUR_INSCRIPTION')")
     */
    public function voirAction(Request $request, Inscription $inscription)
    {
        $twigConfig['item'] = $inscription;

        return $this->render('UcaBundle/UcaWeb/Inscription/ValidationInscriptionEncadrant.html.twig', $twigConfig);
    }

    /**
     * @Route("/ValiderIncriptionParEncadrant/{id}",name="UcaWeb_InscriptionValideeParEncadrant")
     * @Security("is_granted('ROLE_ENCADRANT')")
     */
    public function validationParEncadrantAction(Request $request, Inscription $inscription, FlashBag $flashBag, EntityManagerInterface $em, InscriptionService $inscriptionService)
    {

        foreach ($inscription->getAutorisations()->getIterator() as $autorisation) {
            $autorisation->setValideParEncadrant(true);
            $inscription->updateStatut();
        }

        foreach ($inscription->getEncadrants()->getIterator() as $encadrant) {
            $encadrant->getInscriptionsAValider()->removeElement($inscription);
            $inscription->removeEncadrant($encadrant);
        }
        $em->flush();

        $flashBag->addMessageFlashBag('inscription.confirmation.valider', 'success');

        $inscriptionService->setInscription($inscription);
        $inscriptionService->envoyerMailInscriptionNecessitantValidation();

        return $this->redirectToRoute('UcaWeb_InscriptionAValiderLister', ['type' => 'encadrant']);
    }

    /**
     * @Route("/RefuserIncriptionParEncadrant/{id}",name="UcaWeb_InscriptionRefuseeParEncadrant")
     * @Security("is_granted('ROLE_ENCADRANT')")
     */
    public function refusParEncadrantAction(Request $request, Inscription $inscription, FlashBag $flashBag, EntityManagerInterface $em, InscriptionService $inscriptionService)
    {
        if ($request->isMethod('POST')) {
            $motif = $request->request->get('motifRefus');
        } else {
            $motif = '';
        }
        $inscription->setStatut(
            'annule',
            ['motifAnnulation' => 'inscription.refus.encadrant',
                'commentaireAnnulation' => $motif, ]
        );

        foreach ($inscription->getAutorisations()->getIterator() as $autorisation) {
            $autorisation->setValideParEncadrant(false);
            $autorisation->updateStatut();
        }
        foreach ($inscription->getEncadrants()->getIterator() as $encadrant) {
            $encadrant->getInscriptionsAValider()->removeElement($inscription);
            $inscription->removeEncadrant($encadrant);
        }

        $em->flush();
        $flashBag->addMessageFlashBag('inscription.confirmation.refuser', 'success');

        $inscriptionService->setInscription($inscription);
        $inscriptionService->updateStatutInscriptionsPartenaire($inscription);
        $inscriptionService->envoyerMailInscriptionNecessitantValidation();

        return $this->redirectToRoute('UcaWeb_InscriptionAValiderLister', ['type' => 'encadrant']);
    }

    /**
     * @Route("/telechargerJustificatif/{id}",name="UcaWeb_TelechargerJustificatif", options={"expose"=true})
     * @Security("is_granted('ROLE_ENCADRANT') or is_granted('ROLE_GESTIONNAIRE_VALIDEUR_INSCRIPTION')")
     */
    public function telechargerJustificatifAction(Request $request, Autorisation $autorisation, KernelInterface $kernel)
    {
        $file = $kernel->getProjectDir().'/public/upload/private/fichiers/'.$autorisation->getJustificatif();
        $response = new Response();

        return $response->setContent(file_get_contents($file));
    }

    /**
     * @Route("/ValiderIncriptionParGestionnaire/{id}",name="UcaWeb_InscriptionValideeParGestionnaire")
     * @Security("is_granted('ROLE_GESTIONNAIRE_VALIDEUR_INSCRIPTION')")
     */
    public function validationParGestionnaireAction(Request $request, Inscription $inscription, FlashBag $flashBag, EntityManagerInterface $em, InscriptionService $inscriptionService)
    {
        foreach ($inscription->getAutorisations()->getIterator() as $autorisation) {
            $autorisation->setValideParGestionnaire(true);
            $inscription->updateStatut();
        }

        $em->flush();

        $flashBag->addMessageFlashBag('inscription.confirmation.valider', 'success');

        $inscriptionService->setInscription($inscription);
        $inscriptionService->envoyerMailInscriptionNecessitantValidation();

        return $this->redirectToRoute('UcaWeb_InscriptionAValiderLister', ['type' => 'gestionnaire']);
    }

    /**
     * @Route("/RefuserIncriptionParGestionnaire/{id}",name="UcaWeb_InscriptionRefuseeParGestionnaire")
     * @Security("is_granted('ROLE_GESTIONNAIRE_VALIDEUR_INSCRIPTION')")
     */
    public function refusParGestionnaireAction(Request $request, Inscription $inscription, FlashBag $flashBag, EntityManagerInterface $em, InscriptionService $inscriptionService)
    {
        if ($request->isMethod('POST')) {
            $motif = $request->request->get('motifRefus');
        } else {
            $motif = '';
        }
        $inscription->setStatut(
            'annule',
            [
                'motifAnnulation' => 'inscription.refus.gestionnaire',
                'commentaireAnnulation' => $motif, ]
        );

        foreach ($inscription->getAutorisations()->getIterator() as $autorisation) {
            $autorisation->setValideParGestionnaire(false);
            $autorisation->updateStatut();
        }

        $em->flush();

        $flashBag->addMessageFlashBag('inscription.confirmation.refuser', 'success');

        $inscriptionService->setInscription($inscription);
        $inscriptionService->updateStatutInscriptionsPartenaire($inscription);
        $inscriptionService->envoyerMailInscriptionNecessitantValidation();

        return $this->redirectToRoute('UcaWeb_InscriptionAValiderLister', ['type' => 'gestionnaire']);
    }
}
