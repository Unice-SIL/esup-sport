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

class DeleteOldCardCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'uca:delete:old:card';

    protected function configure()
    {
        $this->setDescription('Supprime les cartes dont la date de validité est passée.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $nbCartesTrouvees = 0;
        $nbCartesSupprimees = 0;

        $commandeDetails = $em->getRepository('UcaBundle:CommandeDetail')->findCommandeDetailAncienneCarte();
        $nbCartesTrouvees = sizeof($commandeDetails);
        foreach ($commandeDetails as $commandeDetail) {
            $utilisateur = $commandeDetail->getCommande()->getUtilisateur();
            $typeAutorisation = $commandeDetail->getTypeAutorisation();
            $utilisateur->removeAutorisation($typeAutorisation);
            $em->persist($utilisateur);
            ++$nbCartesSupprimees;
        }
        $em->flush();

        $output->writeln($nbCartesTrouvees.' carte(s) trouvée(s)');
        $output->writeln($nbCartesSupprimees.' carte(s) supprimée(s)');
    }
}
