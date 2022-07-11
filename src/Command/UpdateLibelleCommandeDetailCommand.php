<?php

/*
 * Classe - UpdateLibelleCommandeDetailCommand:
 *
 * Commande en console pour mettre à jour les libellés des anciennes commandes
*/

namespace App\Command;

use App\Repository\CommandeDetailRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateLibelleCommandeDetailCommand extends Command
{
    protected static $defaultName = 'uca:update:libelle:commandedetail';

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
        $this->setDescription('Permet de mettre à jour les libellés des anciens détails de commandes');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $nbTrouvee = 0;
        $nbUpdate = 0;

        $commandeDetails = $this->commandeDetailRepository->findByLibelle(null);
        $nbTrouvee = sizeof($commandeDetails);
        foreach ($commandeDetails as $commandeDetail) {
            if ($commandeDetail->getTypeAutorisation()) {
                $commandeDetail->setLibelle($commandeDetail->getTypeAutorisation()->getLibelle());
                $commandeDetail->setDescription($commandeDetail->getTypeAutorisation()->getLibelle());
                ++$nbUpdate;
            } elseif ($commandeDetail->getReservabilite()) {
                $commandeDetail->setLibelle($commandeDetail->getReservabilite()->getRessource()->getLibelle());
                $commandeDetail->setDescription($commandeDetail->getFormatActivite()->getDescription());
                ++$nbUpdate;
            } elseif ($commandeDetail->getFormatActivite()) {
                $commandeDetail->setLibelle($commandeDetail->getFormatActivite()->getLibelle());
                $commandeDetail->setDescription($commandeDetail->getFormatActivite()->getDescription());
                ++$nbUpdate;
            }
        }
        $this->em->flush();

        $output->writeln($nbTrouvee.' commandes sans libellé trouvées');
        $output->writeln($nbUpdate.' libellé corrigé');

        return Command::SUCCESS;
    }
}