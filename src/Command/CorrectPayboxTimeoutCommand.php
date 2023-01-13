<?php

/*
 * Classe - CorrectPayBoxTimoutCommand:
 *
 * Commande en console pour rendre valide les commandes paybox avec le statut timeout
*/

namespace App\Command;

use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CorrectPayboxTimeoutCommand extends Command
{
    protected static $defaultName = 'uca:correct:paybox:timeout:command';

    private $em;
    private $commandeRepository;

    public function __construct(EntityManagerInterface $em, CommandeRepository $commandeRepository)
    {
        $this->em = $em;
        $this->commandeRepository = $commandeRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Corrige les commandes ety inscriptions des commandes annulées par le timeout alors qu\'elles ont été payées (problème MEP)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //Ids des commandes annulées
        $liste =[
            48926,49522,48812,48783,48009,48009,48131,47992,46309,43095,43095,42005,41510,40509,40509,40509,
            40509,38892,38493,37882,37680,36722,36609,38333,37506];
        $commandes = $this->commandeRepository->findByIds($liste);

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
        $this->em->flush();

        return Command::SUCCESS;
    }
}