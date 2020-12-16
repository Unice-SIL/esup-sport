<?php

/*
 * Classe - UpdateDateFinValiditeCarteCommand:
 *
 * Commande en console pour mettre à jour les dates de validités des cartes
*/

namespace UcaBundle\Command;

use DateTime;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateDateFinValiditeCarteCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'uca:update:datevalidite:carte';

    protected function configure()
    {
        $this->setDescription('Permet de mettre à jour les dates de validité des cartes');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $nbTrouvee = 0;
        $nbUpdate = 0;

        $commandeDetails = $em->getRepository('UcaBundle:CommandeDetail')->findCommandeDetailCarteSansDate();
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
        $em->flush();

        $output->writeln($nbTrouvee.' commandes détails avec autorisation valide trouvée');
        $output->writeln($nbUpdate.' corrigé');
    }
}
