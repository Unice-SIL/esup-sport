<?php

/*
 * Classe - UpdateListeEncadrantCreneauCommand:
 *
 * Commande en console pour mettre à jour les listes d'encadrants des créneaux
*/

namespace App\Command;

use App\Repository\CreneauRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateListeEncadrantCreneauCommand extends Command
{
    protected static $defaultName = 'uca:update:listeencadrant:creneau';

    private $em;
    private $creneauRepository;

    public function __construct(EntityManagerInterface $em, CreneauRepository $creneauRepository)
    {
        $this->em = $em;
        $this->creneauRepository = $creneauRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Permet de mettre à jour la liste des encadrants des créneaux suite à l\'ajout de ce nouveau champ dans l\'entité ');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $nbTrouvee = 0;
        $nbUpdate = 0;

        $creneaux = $this->creneauRepository->findAll();
        $nbTrouvee = sizeof($creneaux);
        foreach ($creneaux as $creneau) {
            $creneau->updateListeEncadrants();
            ++$nbUpdate;
        }
        $this->em->flush();
        $output->writeln($nbTrouvee.' créneaux trouvés');
        $output->writeln($nbUpdate.'créneaux mis à jour');

        return Command::SUCCESS;
    }
}