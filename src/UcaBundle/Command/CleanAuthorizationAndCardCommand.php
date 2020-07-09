<?php

namespace UcaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanAuthorizationAndCardCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'uca:clean:authorization:card';

    protected function configure()
    {
        $this->setDescription('Supprime les cartes et autorisations dont la date est passée.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $nbAutorisations = 0;
        $nbCartes = 0;
        $statutValide = ['attentevalidationencadrant', 'attentevalidationgestionnaire', 'attenteajoutpanier', 'attentepaiement', 'valide'];

        $autorisations = $em->getRepository('UcaBundle:Autorisation'::class)->findFinishedAutorisations();
        foreach ($autorisations as $autorisation) {
            $inscription = $autorisation->getInscription();
            if (in_array($inscription->getStatut(), $statutValide)) {
                $inscription->setStatut('ancienneinscription');
            }
            $em->remove($autorisation);
            ++$nbAutorisations;
        }

        $formats = $em->getRepository('UcaBundle:FormatAchatCarte'::class)->findFinishedFormats();
        if (sizeof($formats) > 0) {
            foreach ($formats as $format) {
                if (sizeof($format->getInscriptions()) > 0) {
                    $typeAutorisation = $em->getRepository('UcaBundle:TypeAutorisation'::class)->find($format->getCarte()->getId());
                    foreach ($format->getInscriptions() as $inscription) {
                        if (in_array($inscription->getStatut(), $statutValide)) {
                            $inscription->setStatut('ancienneinscription');
                            $utilisateur = $inscription->getUtilisateur();
                            $utilisateur->removeAutorisation($typeAutorisation);
                            ++$nbCartes;
                        }
                    }
                }
            }
        }
        $em->flush();

        $output->writeln($nbAutorisations.' autorisation(s) supprimée(s)');
        $output->writeln($nbCartes.' carte(s) supprimée(s)');
    }
}
