<?php

/*
 * Classe - CorrectOldFreeCmdCommand:
 *
 * Commande en console pour nettoyer les formats gratuits
*/

namespace App\Command;

use App\Repository\CommandeDetailRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CorrectOldFreeCmdCommand extends Command
{
    protected static $defaultName = 'uca:correct:old:free:command';

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
        $this->setDescription('Corrige les détails de commandes des anciennes commandes gratuites pour avoir tout le contenu pour l\'extraction Excel');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $listeAnciennesCommandes = $this->commandeDetailRepository->findCommandeDetailPourAncienneCommandeGratuite();
        $nbTrouvees = 0;
        $nbSucces = 0;
        $nbEchec = 0;
        foreach ($listeAnciennesCommandes as $ancienneCommande) {
            $formatActivite = $ancienneCommande->getFormatActivite();
            if (null != $formatActivite) {
                $ancienneCommande->setLibelle($formatActivite->getLibelle());
                $ancienneCommande->setDescription($formatActivite->getDescription());
                $ancienneCommande->setTypeArticle($formatActivite->getFormat());
                ++$nbSucces;
            } else {
                ++$nbEchec;
            }
            ++$nbTrouvees;
        }
        $this->em->flush();

        $output->writeln($nbTrouvees.' ancienne(s) commande(s) gratuite(s) trouvée(s)');
        $output->writeln($nbSucces.' succès');
        $output->writeln($nbEchec.' échecs');

        return Command::SUCCESS;
    }
}