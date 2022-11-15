<?php

/*
 * classe - PayboxResponseListener
 *
 * Service gérant la réponse de l'application en fonction des réponses paybox
*/

namespace App\Service\Listener;

use App\Repository\CommandeRepository;
use App\Service\Common\MailService;
use App\Service\Common\Parametrage;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\PayboxBundle\Event\PayboxResponseEvent;
use Psr\Log\LoggerInterface;

class PayboxResponseListener
{
    private $em;
    private $logger;
    private $commandeRepository;
    private $mailer;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger, MailService $mailer, CommandeRepository $commandeRepository)
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->mailer = $mailer;
        $this->commandeRepository = $commandeRepository;
    }

    public function onPayboxIpnResponse(PayboxResponseEvent $event)
    {
        $this->logger->notice('PAYBOX -- catched : '.$_SERVER['REQUEST_URI']);
        if ($event->isVerified() && 0 == $event->getData()['Erreur']) {
            $this->logger->notice('PAYBOX -- verified ! ');
            $idCommande = $_GET['id'];
            $noCommande = $event->getData()['Ref'];
            $montant = $event->getData()['Mt'];
            $this->logger->notice('PAYBOX -- Id: '.$idCommande);
            $this->logger->notice('PAYBOX -- Ref: '.$noCommande);
            $this->logger->notice('PAYBOX -- Mt: '.$montant);
            $commande = $this->commandeRepository->findOneBy(['id' => $idCommande, 'montantPaybox' => $montant / 100]);
            if (!empty($commande) && $commande->getMontantAPayer() == $montant / 100) {
                $this->logger->notice('PAYBOX -- commande found ! ');
                $this->logger->notice('PAYBOX -- commande->getId: '.$commande->getId());
                $this->logger->notice('PAYBOX -- commande->getNumeroCommande: '.$commande->getNumeroCommande());
                $commande->changeStatut('termine', ['typePaiement' => 'PAYBOX', 'moyenPaiement' => 'cb']);
                $this->em->persist($commande);
                $this->em->flush();
            } elseif ($commande = $this->commandeRepository->findOneBy(['id' => $idCommande])) {
                $this->logger->error('PAYBOX -- commande trouvée mais le montant n\'est pas le bon ! ');

                if ('test' !== $_ENV['APP_ENV']) {
                    // @codeCoverageIgnoreStart
                    $this->mailer->sendMailWithTemplate(
                        'Erreur retour paiement PAYBOX',
                        Parametrage::getMailContact(),
                        '@Uca/Email/Commande/ErreurMontantPaybox.html.twig',
                        ['montantPaybox' => ($montant / 100), 'commande' => $commande]
                    );
                    // @codeCoverageIgnoreEnd
                }
            } else {
                $this->logger->error('PAYBOX -- commande not found ! ');
            }
        } else {
            $this->logger->error('PAYBOX -- failed ! ');
        }
    }
}
