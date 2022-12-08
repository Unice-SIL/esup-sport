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
        $liste =[
            33669,33743,33525,33527,33528,33532,33533,33534,33535,33536,33553,33560,33562,33564,33568,33569,33593,33594,33904,33911,33960,33969,
            33599,33601,33602,33614,33616,33617,33624,33623,33627,33650,33665,33664,33667,33670,33696,33727,33744,33757,33769,33915,33926,33958,
            33775,33776,33778,33780,33781,33792,33794,33799,33828, 33829,33830,33846,33858,33859,33879,33882,33886,33892,33894,33937];
        $commandes = $this->commandeRepository->findByNumerosCommandes($liste);

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