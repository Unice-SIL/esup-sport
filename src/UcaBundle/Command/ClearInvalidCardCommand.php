<?php

/*
 * Classe - CleanAuthorizationAndCardCommand:
 *
 * Commande en console pour nettoyer les cartes dont la date de validité a expiré
*/

namespace UcaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearInvalidCardCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'uca:clean:authorization:card';

    protected function configure()
    {
        $this->setDescription('Supprime les cartes dont la date de validité est passée.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $nbCartes = 0;

        $commandeDetails = $em->getRepository('UcaBundle::CommandeDetail')->findCommandeDetailWithAutorisationInvalid();
        foreach ($commandeDetails as $commandeDetail) {
            $utilisateur = $commandeDetail->getCommande()->getUtilisateur();
            $typeAutorisation = $commandeDetail->getTypeAutorisation();
            $utilisateur->removeAutorisation($typeAutorisation);
            $em->persist($utilisateur);
            ++$nbCartes;
        }
        $em->flush();

        $output->writeln($nbCartes.' carte(s) supprimée(s)');
    }
}
