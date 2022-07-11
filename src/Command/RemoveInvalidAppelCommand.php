<?php

/*
 * Classe - RemoveInvalidAppelCommand:
 *
 * Commande en console pour nettoyer les cartes dont la date de validité a expiré
*/

namespace App\Command;

use App\Repository\AppelRepository;
use App\Repository\InscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveInvalidAppelCommand extends Command
{
    protected static $defaultName = 'uca:remove:invalid:appel';

    private $em;
    private $appelRepository;
    private $inscriptionRepository;

    public function __construct(EntityManagerInterface $em, AppelRepository $appelRepository, InscriptionRepository $inscriptionRepository)
    {
        $this->em = $em;
        $this->appelRepository = $appelRepository;
        $this->inscriptionRepository = $inscriptionRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Supprime tous les appels invalides');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $nbAppelSupprimees = 0;

        $appels = $this->appelRepository->findAll();
        foreach ($appels as $appel) {
            $dhtmlxEvenement = $appel->getDhtmlxEvenement();
            if ($dhtmlxEvenement->getFormatSimple()) {
                $inscription = $this->inscriptionRepository->findOneBy(['utilisateur' => $appel->getUtilisateur(), 'formatActivite' => $dhtmlxEvenement->getFormatSimple()]);
                if (in_array($inscription->getStatut(), ['annule', 'desinscrit', 'ancienneinscription', 'desinscriptionadministrative'])) {
                    $this->em->remove($appel);
                    ++$nbAppelSupprimees;
                }
            } else {
                $creneau = $dhtmlxEvenement->getSerie()->getCreneau();
                $inscription = $this->inscriptionRepository->findOneBy(['utilisateur' => $appel->getUtilisateur(), 'creneau' => $creneau]);
                if (in_array($inscription->getStatut(), ['annule', 'desinscrit', 'ancienneinscription', 'desinscriptionadministrative'])) {
                    $this->em->remove($appel);
                    ++$nbAppelSupprimees;
                }
            }
        }
        $this->em->flush();

        $output->writeln($nbAppelSupprimees.' appel(s) supprimé(s)');

        return Command::SUCCESS;
    }
}