<?php

/*
 * Classe - SgDatatableLangFixCommand :
 *
 * Commande en console pour corriger les traduction du datatable
*/

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class SgDatatableLangFixCommand extends Command
{
    protected static $defaultName = 'uca:datatables:fixLang';

    private $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Fix la traduction dans SgDatatablesBundle');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $origFile = $this->projectDir.'/src/Datatables/Response/DatatableQueryBuilder.Orig.php';
        $fixedFile = $this->projectDir.'/src/Datatables/Response/DatatableQueryBuilder.Fixed.php';
        $dstFile = $this->projectDir.'/vendor/sg/datatablesbundle/Response/DatatableQueryBuilder.php';

        $fs = new Filesystem();
        // $fs->copy($dstFile, $origFile, true);
        // $output->writeln('Fichier DatatableQueryBuilder.php sauvegardé vers /src !');
        // $output->writeln('Problème lors de la sauvegarde du fichier DatatableQueryBuilder.php !');
        $fs->copy($fixedFile, $dstFile, true);
        $output->writeln('Fichier DatatableQueryBuilder.php corrigé dans SgDatatablesBundle !');
        // $output->writeln('Problème lors de la correction du fichier DatatableQueryBuilder.php !');

        $fixedFileFilter = $this->projectDir.'/src/Datatables/Filter/AbstractFilter.Fixed.php';
        $dstFileFilter = $this->projectDir.'/vendor/sg/datatablesbundle/Datatable/Filter/AbstractFilter.php';

        $fs->copy($fixedFileFilter, $dstFileFilter, true);
        $output->writeln('Fichier AbstractFilter.php corrigé dans SgDatatablesBundle !');

        return Command::SUCCESS;
    }
}