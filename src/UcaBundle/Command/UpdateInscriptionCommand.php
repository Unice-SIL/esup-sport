<?php

/*
 * Classe - UpdateInscriptionCommand:
 *
 * Commande en console pour mettre à jour les inscriptions
*/

namespace UcaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateInscriptionCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'uca:update:inscription';

    protected function configure()
    {
        $this->setDescription('Permet de mettre à jour les inscriptions');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $nbTrouvee = 0;
        $nbUpdate = 0;

        $inscriptions = $em->getRepository('UcaBundle:Inscription')->findAll();
        $nbTrouvee = sizeof($inscriptions);
        foreach ($inscriptions as $inscription) {
            if ($inscription->getFormatActivite() && $inscription->getUtilisateur()) {
                $inscription->setNomInscrit($inscription->getUtilisateur()->getNom());
                $inscription->setPrenomInscrit($inscription->getUtilisateur()->getPrenom());
                $inscription->setLibelle($inscription->getFormatActivite()->getLibelle());
                $inscription->setDescription($inscription->getFormatActivite()->getDescription());
                ++$nbUpdate;
            }
        }
        $em->flush();

        $output->writeln($nbTrouvee.' inscriptions trouvées');
        $output->writeln($nbUpdate.' inscriptions mise à jour');
    }
}
