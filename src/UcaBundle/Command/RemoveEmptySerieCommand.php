<?php

/*
 * Classe - DeleteOldCardCommand:
 *
 * Commande en console pour nettoyer les cartes dont la date de validité a expiré
*/

namespace UcaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveEmptySerieCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'uca:remove:empty:serie';

    protected function configure()
    {
        $this->setDescription('Supprime toutes les séries qui ne contiennent aucun événement');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $nbSerieSupprimees = 0;

        $series = $em->getRepository('UcaBundle:DhtmlxSerie')->findAll();
        foreach ($series as $serie) {
            if (0 == sizeof($serie->getEvenements())) {
                $em->remove($serie);
                ++$nbSerieSupprimees;
            }
        }
        $em->flush();

        $output->writeln($nbSerieSupprimees.' série(s) supprimée(s)');
    }
}