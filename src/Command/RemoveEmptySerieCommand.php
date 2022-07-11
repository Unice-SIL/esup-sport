<?php

/*
 * Classe - DeleteOldCardCommand:
 *
 * Commande en console pour nettoyer les cartes dont la date de validité a expiré
*/

namespace App\Command;

use App\Repository\DhtmlxSerieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveEmptySerieCommand extends Command
{
    protected static $defaultName = 'uca:remove:empty:serie';

    private $em;
    private $repository;

    public function __construct(EntityManagerInterface $em, DhtmlxSerieRepository $repository)
    {
        $this->em = $em;
        $this->repository = $repository;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Supprime toutes les séries qui ne contiennent aucun événement');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $nbSerieSupprimees = 0;

        $series = $this->repository->findAll();
        foreach ($series as $serie) {
            if (0 == sizeof($serie->getEvenements())) {
                $this->em->remove($serie);
                ++$nbSerieSupprimees;
            }
        }
        $this->em->flush();

        $output->writeln($nbSerieSupprimees.' série(s) supprimée(s)');

        return Command::SUCCESS;
    }
}