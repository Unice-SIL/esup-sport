<?php

/*
 * Classe - DeleteOldCardCommand:
 *
 * Commande en console pour nettoyer les cartes dont la date de validité a expiré
*/

namespace App\Command;

use App\Repository\CommandeDetailRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteOldCardCommand extends Command
{
    protected static $defaultName = 'uca:delete:old:card';

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
        $this->setDescription('Supprime les cartes dont la date de validité est passée.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $nbCartesTrouvees = 0;
        $nbCartesSupprimees = 0;

        $commandeDetails = $this->commandeDetailRepository->findCommandeDetailAncienneCarte();
        $nbCartesTrouvees = sizeof($commandeDetails);
        foreach ($commandeDetails as $commandeDetail) {
            $utilisateur = $commandeDetail->getCommande()->getUtilisateur();
            $typeAutorisation = $commandeDetail->getTypeAutorisation();
            $utilisateur->removeAutorisation($typeAutorisation);
            $this->em->persist($utilisateur);
            ++$nbCartesSupprimees;
        }
        $this->em->flush();

        $output->writeln($nbCartesTrouvees.' carte(s) trouvée(s)');
        $output->writeln($nbCartesSupprimees.' carte(s) supprimée(s)');

        return Command::SUCCESS;
    }
}