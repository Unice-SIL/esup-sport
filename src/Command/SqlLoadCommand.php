<?php

/*
 * Classe -  SqlLoadCommand
 *
 * Commande pour charger les données sql
*/

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class SqlLoadCommand extends Command
{
    protected static $defaultName = 'uca:sql:load';

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Execution d\'un fichier SQL')
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'file to load'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');
        $finder = new Finder();
        $output->writeln(__DIR__.'/../Resources/sql');
        $finder->in(__DIR__.'/../Resources/sql');
        $finder->name($file);

        foreach ($finder as $file) {
            $content = $file->getContents();
            $stmt = $this->em->getConnection()->prepare($content);
            $output->writeln('Execution du fichier : '.$file);
            $output->writeln('...');
            if ($stmt->executeStatement()) {
                $output->writeln('Fichier '.$file.' chargé avec plus ou moins de success !');
                $output->writeln('... En fait on sait pas trop si c\'est chargé. Il faut vérifier :/');
            } else {
                $output->writeln('!!!!!! Erreur pendant l\'execution de la requête !!!!!');
                $output->writeln('Merci de corriger '.$file.' ! ');

                exit;
            }
        }

        return Command::SUCCESS;
    }
}