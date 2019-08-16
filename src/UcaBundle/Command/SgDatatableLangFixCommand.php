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

class SgDatatableLangFixCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'uca:datatables:fixLang';
    protected function configure()
    {
        $this->setDescription('Fix la traduction dans SgDatatablesBundle');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $origFile = '%kernel.root_dir%/../src/UcaBundle/Datatables/Response/DatatableQueryBuilder.Orig.php';
        $fixedFile = '%kernel.root_dir%/../src/UcaBundle/Datatables/Response/DatatableQueryBuilder.Fixed.php';
        $dstFile = '%kernel.root_dir%/../vendor/sg/datatablesbundle/Response/DatatableQueryBuilder.php';

        $fs = new Filesystem();
        // $fs->copy($dstFile, $origFile, true);
        // $output->writeln('Fichier DatatableQueryBuilder.php sauvegardé vers /src !');
        // $output->writeln('Problème lors de la sauvegarde du fichier DatatableQueryBuilder.php !');
        $fs->copy($fixedFile, $dstFile, true);
        $output->writeln('Fichier DatatableQueryBuilder.php corrigé dans SgDatatablesBundle !');
        // $output->writeln('Problème lors de la correction du fichier DatatableQueryBuilder.php !');
    }
}

