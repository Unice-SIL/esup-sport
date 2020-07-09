<?php

namespace UcaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CorrectOldFreeCmdCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'uca:correct:old:free:command';

    protected function configure()
    {
        $this->setDescription('Corrige les détails de commandes des anciennes commandes gratuites pour avoir tout le contenu pour l\'extraction Excel');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $listeAnciennesCommandes = $em->getRepository('UcaBundle:CommandeDetail')->findCommandeDetailPourAncienneCommandeGratuite();
        $nbTrouvees = 0;
        $nbSucces = 0;
        $nbEchec = 0;
        foreach ($listeAnciennesCommandes as $ancienneCommande) {
            $formatActivite = $ancienneCommande->getFormatActivite();
            if (null != $formatActivite) {
                $ancienneCommande->setLibelle($formatActivite->getLibelle());
                $ancienneCommande->setDescription($formatActivite->getDescription());
                $ancienneCommande->setTypeArticle($formatActivite->getFormat());
                ++$nbSucces;
            } else {
                ++$nbEchec;
            }
            ++$nbTrouvees;
        }
        $em->flush();

        $output->writeln($nbTrouvees.' ancienne(s) commande(s) gratuite(s) trouvée(s)');
        $output->writeln($nbSucces.' succès');
        $output->writeln($nbEchec.' échecs');
    }
}
