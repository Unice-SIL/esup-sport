<?php

namespace App\Command;

use App\Entity\Uca\LogoParametrable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Vich\UploaderBundle\FileAbstraction\ReplacingFile;

class UcaLogosInitCommand extends Command
{
    protected static $defaultName = 'uca:logos:init';
    protected static $defaultDescription = 'Initialize logos';

    private $em;
    private $projectDir;

    public function __construct(EntityManagerInterface $em, string $projectDir)
    {
        parent::__construct();
        $this->em = $em;
        $this->projectDir = $projectDir;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $logos = $this->em->getRepository(LogoParametrable::class)->findAll();
        $fs = new Filesystem();

        $uploadDir = $this->projectDir.'/public/upload/public/images/logos';
        $assetDir = $this->projectDir.'/assets/images/';
        if (!$fs->exists($uploadDir)) {
            $fs->mkdir($uploadDir);
        }
        $uploadDir .= '/';

        foreach ($logos as $logo) {
            $file = '';
            if ($logo->getImage() === '') {
                switch ($logo->getId()) {
                    case 1:
                        $file = 'logoHeader.png';
                        $fs->copy($assetDir.'logo.png', $uploadDir.$file);
                        break;
                    case 2:
                        $file = 'logoFooter.png';
                        $fs->copy($assetDir.'logo-uca.png', $uploadDir.$file);
                        break;
                    case 3:
                        $file = 'logoPDF.png';
                        $fs->copy($assetDir.'logo_black.png', $uploadDir.$file);
                        break;
                    case 4:
                        $file = 'logoLogin.png';
                        $fs->copy($assetDir.'logo-UCA-large-transp.png', $uploadDir.$file);
                        break;
                    case 5:
                        $file = 'logoEmail.png';
                        $fs->copy($assetDir.'logo_black.png', $uploadDir.$file);
                        break;
                    case 6:
                        $file = 'logoExcel.png';
                        $fs->copy($assetDir.'logo-UCA-large-transp.png', $uploadDir.$file);
                        break;
                    case 7:
                        $file = 'logoCarousel.png';
                        $fs->copy($assetDir.'logo-uca.png', $uploadDir.$file);
                        break;
                    case 8:
                        $file = 'favicon.ico';
                        $fs->copy($assetDir.'favicon.ico', $uploadDir.$file);
                    default:
                        break;
                }
                if ($file !== '') {
                    $logo->setImage(new ReplacingFile($file, false));
                    $this->em->persist($logo);
                }
            }
        }
        $this->em->flush();

        return Command::SUCCESS;
    }
}
