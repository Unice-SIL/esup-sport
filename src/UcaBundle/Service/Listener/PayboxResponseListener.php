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
        $this->logger->error('PAYBOX -- catched : ' . $_SERVER['REQUEST_URI']);
        if ($event->isVerified() && $event->getData()['Erreur'] == 0) {
            $this->logger->error('PAYBOX -- verified ! ');
            $idCommande = $_GET['id'];
            $noCommande = $event->getData()['Ref'];
            $montant = $event->getData()['Mt'];
            $this->logger->error('PAYBOX -- Id: ' . $idCommande);
            $this->logger->error('PAYBOX -- Ref: ' . $noCommande);
            $this->logger->error('PAYBOX -- Mt: ' . $montant);
            $commande = $this->em->getRepository('UcaBundle:Commande')->findOneBy(['id' => $idCommande, 'montantTotal' => $montant / 100]);
            if (!empty($commande) && $commande->getMontantTotal() == $montant / 100) {
                $this->logger->error('PAYBOX -- commande finded ! ');
                $this->logger->error('PAYBOX -- commande->getId: ' . $commande->getId());
                $this->logger->error('PAYBOX -- commande->getNumeroCommande: ' . $commande->getNumeroCommande());
                $commande->changeStatut('termine', ['typePaiement' => 'PAYBOX', 'moyenPaiement' => 'cb']);
                $this->em->persist($commande);
                $this->em->flush();
            } else {
                $this->logger->error('PAYBOX -- commande not finded ! ');
            }
        } else {
            $this->logger->error('PAYBOX -- failed ! ');
        }
    }
}
