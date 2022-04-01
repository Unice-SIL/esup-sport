<?php

/*
 * classe - PayboxResponseListener
 *
 * Service gérant la réponse de l'application en fonction des réponses paybox
*/

namespace UcaBundle\Service\Listener;

use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\PayboxBundle\Event\PayboxResponseEvent;
use Psr\Log\LoggerInterface;
use UcaBundle\Service\Common\MailService;
use UcaBundle\Service\Common\Parametrage;

class PayboxResponseListener
{
    private $em;
    private $logger;
    private $mailer;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger, MailService $mailer)
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->mailer = $mailer;
    }

    public function onPayboxIpnResponse(PayboxResponseEvent $event)
    {
        $this->logger->info('PAYBOX -- catched : '.$_SERVER['REQUEST_URI']);
        if ($event->isVerified() && 0 == $event->getData()['Erreur']) {
            $this->logger->info('PAYBOX -- verified ! ');
            $idCommande = $_GET['id'];
            $noCommande = $event->getData()['Ref'];
            $montant = $event->getData()['Mt'];
            $this->logger->info('PAYBOX -- Id: '.$idCommande);
            $this->logger->info('PAYBOX -- Ref: '.$noCommande);
            $this->logger->info('PAYBOX -- Mt: '.$montant);
            $commande = $this->em->getRepository('UcaBundle:Commande')->findOneBy(['id' => $idCommande, 'montantTotal' => $montant / 100]);
            if (!empty($commande) && $commande->getMontantTotal() == $montant / 100) {
                $this->logger->info('PAYBOX -- commande found ! ');
                $this->logger->info('PAYBOX -- commande->getId: '.$commande->getId());
                $this->logger->info('PAYBOX -- commande->getNumeroCommande: '.$commande->getNumeroCommande());
                $commande->changeStatut('termine', ['typePaiement' => 'PAYBOX', 'moyenPaiement' => 'cb']);
                $this->em->persist($commande);
                $this->em->flush();
            } elseif ($commande = $this->em->getRepository('UcaBundle:Commande')->findOneBy(['id' => $idCommande])) {
                $this->logger->error('PAYBOX -- commande trouvée mais le montant n\'est pas le bon ! ');

                $this->mailer->sendMailWithTemplate(
                    'Erreur retour paiement PAYBOX',
                    Parametrage::getMailContact(),
                    '@Uca/Email/Commande/ErreurMontantPaybox.html.twig',
                    ['montantPaybox' => ($montant / 100), 'commande' => $commande]
                );
            } else {
                $this->logger->error('PAYBOX -- commande not found ! ');
            }
        } else {
            $this->logger->error('PAYBOX -- failed ! ');
        }
    }
}
