<?php

/*
 * Classe - UpdateListeEncadrantCreneauCommand:
 *
 * Commande en console pour mettre à jour les listes d'encadrants des créneaux
*/

namespace UcaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateListeEncadrantCreneauCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'uca:update:listeencadrant:creneau';

    protected function configure()
    {
        $this->setDescription('Permet de mettre à jour la liste des encadrants des créneaux suite à l\'ajout de ce nouveau champ dans l\'entité ');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $nbTrouvee = 0;
        $nbUpdate = 0;

        $creneaux = $em->getRepository('UcaBundle:Creneau')->findAll();
        $nbTrouvee = sizeof($creneaux);
        foreach ($creneaux as $creneau) {
            $creneau->updateListeEncadrants();
            ++$nbUpdate;
        }
        $em->flush();
        $output->writeln($nbTrouvee.' créneaux trouvés');
        $output->writeln($nbUpdate.'créneaux mis à jour');
    }
}
