<?php

/*
 * Classe - LoadAnnotationTableCommand:
 *
 * Commande en console pour charger les annotation
*/

namespace App\Command;

use App\Service\Common\Annotation;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LoadAnnotationTableCommand extends Command
{
    protected static $defaultName = 'uca:table:annotation:load';

    private $annotation;

    public function __construct(Annotation $annotation)
    {
        $this->annotation = $annotation;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Remplit la table ext_annotation avec les annotations de doctrine');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->annotation->loadEntityAnnotation('App\Entity\Uca');
        $output->writeln('Entité Annotation chargée en base de données !');

        return Command::SUCCESS;
    }
}
