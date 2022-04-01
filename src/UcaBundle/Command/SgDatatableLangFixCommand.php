<?php

/*
 * Classe - SgDatatableLangFixCommand :
 *
 * Commande en console pour corriger les traduction du datatable
*/

namespace UcaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class SgDatatableLangFixCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'uca:datatables:fixLang';

    protected function configure()
    {
        $this->setDescription('Fix la traduction dans SgDatatablesBundle');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $origFile = $this->getContainer()->get('kernel')->getRootDir().'/../src/UcaBundle/Datatables/Response/DatatableQueryBuilder.Orig.php';
        $fixedFile = $this->getContainer()->get('kernel')->getRootDir().'/../src/UcaBundle/Datatables/Response/DatatableQueryBuilder.Fixed.php';
        $dstFile = $this->getContainer()->get('kernel')->getRootDir().'/../vendor/sg/datatablesbundle/Response/DatatableQueryBuilder.php';

        $fs = new Filesystem();
        // $fs->copy($dstFile, $origFile, true);
        // $output->writeln('Fichier DatatableQueryBuilder.php sauvegardé vers /src !');
        // $output->writeln('Problème lors de la sauvegarde du fichier DatatableQueryBuilder.php !');
        $fs->copy($fixedFile, $dstFile, true);
        $output->writeln('Fichier DatatableQueryBuilder.php corrigé dans SgDatatablesBundle !');
        // $output->writeln('Problème lors de la correction du fichier DatatableQueryBuilder.php !');

        $fixedFileFilter = $this->getContainer()->get('kernel')->getRootDir().'/../src/UcaBundle/Datatables/Filter/AbstractFilter.Fixed.php';
        $dstFileFilter = $this->getContainer()->get('kernel')->getRootDir().'/../vendor/sg/datatablesbundle/Datatable/Filter/AbstractFilter.php';

        $fs->copy($fixedFileFilter, $dstFileFilter, true);
        $output->writeln('Fichier AbstractFilter.php corrigé dans SgDatatablesBundle !');
    }
}
