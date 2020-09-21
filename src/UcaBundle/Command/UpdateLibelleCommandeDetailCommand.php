<?php

/*
 * Classe - UpdateLibelleCommandeDetailCommand:
 *
 * Commande en console pour mettre à jour les libellés des anciennes commandes
*/

namespace UcaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateLibelleCommandeDetailCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'uca:update:libelle:commandedetail';

    protected function configure()
    {
        $this->setDescription('Permet de mettre à jour les libellés des anciens détails de commandes');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $nbTrouvee = 0;
        $nbUpdate = 0;

        $commandeDetails = $em->getRepository('UcaBundle:CommandeDetail')->findByLibelle(null);
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
        $em->flush();

        $output->writeln($nbTrouvee.' commandes sans libellé trouvées');
        $output->writeln($nbUpdate.' libellé corrigé');
    }
}
