<?php

namespace UcaBundle\Service\Listener;

use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\PayboxBundle\Event\PayboxResponseEvent;
use Psr\Log\LoggerInterface;

class PayboxResponseListener
{
    private $em;
    private $logger;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    public function onPayboxIpnResponse(PayboxResponseEvent $event)
    {
        $this->logger->error('PAYBOX -- catched ! ');
        if ($event->isVerified()) {
            $this->logger->error('PAYBOX -- verified ! ');
            $noCommande = $event->getData()['Ref'];
            $montant = $event->getData()['Mt'];
            $this->logger->error('PAYBOX -- Ref: ' . $noCommande);
            $this->logger->error('PAYBOX -- Mt: ' . $montant);
            $commande = $this->em->getRepository('UcaBundle:Commande')->findOneBy(['numeroCommande' => $noCommande, 'montantTotal' => $montant / 100]);
            if (!empty($commande) && $commande->getMontantTotal() == $montant / 100) {
                $this->logger->error('PAYBOX -- commande finded ! ');
                $commande->changeStatut('termine', ['typePaiement' => 'PAYBOX', 'moyenPaiement' => 'cb']);
                $this->em->persist($commande);
                $this->em->flush();
            }
        }
    }
}
