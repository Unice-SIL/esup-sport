<?php

/*
 * Classe - UpdateInscriptionCommand:
 *
 * Commande en console pour mettre à jour les inscriptions
*/

namespace App\Command;

use App\Repository\InscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateInscriptionCommand extends Command
{
    protected static $defaultName = 'uca:update:inscription';

    private $em;
    private $inscriptionRepository;

    public function __construct(EntityManagerInterface $em, InscriptionRepository $inscriptionRepository)
    {
        $this->em = $em;
        $this->inscriptionRepository = $inscriptionRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Permet de mettre à jour les inscriptions');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $nbTrouvee = 0;
        $nbUpdate = 0;

        $inscriptions = $this->inscriptionRepository->findAll();
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
        $this->em->flush();

        $output->writeln($nbTrouvee.' inscriptions trouvées');
        $output->writeln($nbUpdate.' inscriptions mise à jour');

        return Command::SUCCESS;
    }
}