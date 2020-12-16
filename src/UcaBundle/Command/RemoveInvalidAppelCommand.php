<?php

/*
 * Classe - RemoveInvalidAppelCommand:
 *
 * Commande en console pour nettoyer les cartes dont la date de validité a expiré
*/

namespace UcaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveInvalidAppelCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'uca:remove:invalid:appel';

    protected function configure()
    {
        $this->setDescription('Supprime tous les appels invalides');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $nbAppelSupprimees = 0;

        $appels = $em->getRepository('UcaBundle:Appel')->findAll();
        foreach ($appels as $appel) {
            $dhtmlxEvenement = $appel->getDhtmlxEvenement();
            if ($dhtmlxEvenement->getFormatSimple()) {
                $inscription = $em->getRepository('UcaBundle:Inscription')->findOneBy(['utilisateur' => $appel->getUtilisateur(), 'formatActivite' => $dhtmlxEvenement->getFormatSimple()]);
                if (in_array($inscription->getStatut(), ['annule', 'desinscrit', 'ancienneinscription', 'desinscriptionadministrative'])) {
                    $em->remove($appel);
                    ++$nbAppelSupprimees;
                }
            } else {
                $creneau = $dhtmlxEvenement->getSerie()->getCreneau();
                $inscription = $em->getRepository('UcaBundle:Inscription')->findOneBy(['utilisateur' => $appel->getUtilisateur(), 'creneau' => $creneau]);
                if (in_array($inscription->getStatut(), ['annule', 'desinscrit', 'ancienneinscription', 'desinscriptionadministrative'])) {
                    $em->remove($appel);
                    ++$nbAppelSupprimees;
                }
            }
        }
        $em->flush();

        $output->writeln($nbAppelSupprimees.' appel(s) supprimé(s)');
    }
}