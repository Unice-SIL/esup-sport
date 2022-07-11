<?php

/*
 * Classe - UpdateDateFinValiditeCarteCommand:
 *
 * Commande en console pour mettre à jour les dates de validités des cartes
*/

namespace App\Command;

use App\Repository\CommandeDetailRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateDateFinValiditeCarteCommand extends Command
{
    protected static $defaultName = 'uca:update:datevalidite:carte';

    private $em;
    private $commandeDetailRepository;

    public function __construct(EntityManagerInterface $em, CommandeDetailRepository $commandeDetailRepository)
    {
        $this->em = $em;
        $this->commandeDetailRepository = $commandeDetailRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Permet de mettre à jour les dates de validité des cartes');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $nbTrouvee = 0;
        $nbUpdate = 0;

        $commandeDetails = $this->commandeDetailRepository->findCommandeDetailCarteSansDate();
        $nbTrouvee = sizeof($commandeDetails);
        foreach ($commandeDetails as $commandeDetail) {
            if ($commandeDetail->getDateAjoutPanier() >= new DateTime('2020-07-01 00:00:00') && $commandeDetail->getDateAjoutPanier() <= new DateTime('2021-06-30 00:00:00')) {
                $commandeDetail->setDateCarteFinValidite(new DateTime('2021-07-01 00:00:00'));
                ++$nbUpdate;
            } elseif ($commandeDetail->getDateAjoutPanier() >= new DateTime('2019-07-01 00:00:00') && $commandeDetail->getDateAjoutPanier() <= new DateTime('2020-06-30 00:00:00')) {
                $commandeDetail->setDateCarteFinValidite(new DateTime('2020-07-01 00:00:00'));
                ++$nbUpdate;
            } elseif ($commandeDetail->getDateAjoutPanier() >= new DateTime('2018-07-01 00:00:00') && $commandeDetail->getDateAjoutPanier() <= new DateTime('2019-06-30 00:00:00')) {
                $commandeDetail->setDateCarteFinValidite(new DateTime('2019-07-01 00:00:00'));
                ++$nbUpdate;
            }
        }
        $this->em->flush();

        $output->writeln($nbTrouvee.' commandes détails avec autorisation valide trouvée');
        $output->writeln($nbUpdate.' corrigé');

        return Command::SUCCESS;
    }
}