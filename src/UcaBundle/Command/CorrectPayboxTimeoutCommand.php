<?php

/*
 * Classe - CorrectPayBoxTimoutCommand:
 *
 * Commande en console pour rendre valide les commandes paybox avec le statut timeout 
*/

namespace UcaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UcaBundle\Entity\Commande;

class CorrectPayboxTimeoutCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'uca:correct:paybox:timeout:command';

    protected function configure()
    {
        $this->setDescription('Corrige les commandes ety inscriptions des commandes annulées par le timeout alors qu\'elles ont été payées (problème MEP)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $commandeRepository = $em->getRepository(Commande::class);
        $commandes = $commandeRepository->findByNumerosCommandes([18292, 18290, 18281, 18196, 18182, 18172, 18114]);

        foreach ($commandes as $commande) {
            $user = $commande->getUtilisateur();
            if ($commande) {
                $commande
                    ->setStatut('termine')
                    ->setTypePaiement('PAYBOX')
                    ->setMoyenPaiement('cb')
                    ->setDateAnnulation(null)
                    ->setDateCommande($commande->getDatePanier())
                    ->setDatePaiement($commande->getDatePanier())
                ;
                $commandeDetails = $commande->getCommandeDetails();
                foreach ($commandeDetails as $commandeDetail) {
                    if ($inscription = $commandeDetail->getInscription()) {
                        $inscription->setStatut('valide');
                        $inscription->setMotifAnnulation(null);
                    }
                    $typeAutorisation = $commandeDetail->getTypeAutorisation();
                    if ($typeAutorisation && !$user->getAutorisations()->contains($typeAutorisation)) {
                        $user->addAutorisation($typeAutorisation);
                    }
                }                
            }
        }
        $em->flush();
    }
}
