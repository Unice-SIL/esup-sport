<?php
namespace UcaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class LoadAnnotationTableCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'uca:table:annotation:load';

    protected function configure()
    {
        $this->setDescription('Remplit la table ext_annotation avec les annotations de doctrine');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->get('uca.annotation.tools')->loadEntityAnnotation('UcaBundle');
        $output->writeln('Entité Annotation chargée en base de données !');
    }
}

