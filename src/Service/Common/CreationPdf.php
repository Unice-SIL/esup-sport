<?php
/*
 * Classe - CreationPdf
 *
 * Gère la création des pdf
*/

namespace App\Service\Common;

use Spipu\Html2Pdf\Html2Pdf;
use App\Entity\Uca\Parametrage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class CreationPdf
{
    private $em;
    private $templatingService;
    private $view;
    private $parametrage;

    public function __construct(EntityManagerInterface $em, Environment $templatingService)
    {
        $this->em = $em;
        $this->templatingService = $templatingService;
        $this->parametrage = $this->em->getRepository(Parametrage::class)->findOneById(1);
    }

    public function createView($content, array $viewParams)
    {
        $twigConfig = array_merge(['parametrage' => $this->parametrage], $viewParams);
        $this->view = $this->templatingService->render($content, $twigConfig);
    }

    public function createPdf(array $fileParams)
    {
        try {
            $pdf = new HTML2PDF('p', 'A4', 'fr');
            $pdf->setTestIsImage(false);
            $pdf->pdf->SetAuthor($fileParams['author']);
            $pdf->pdf->SetTitle($fileParams['title']);
            $pdf->writeHTML($this->view);
            $pdf->Output($fileParams['output']);
        } catch (HTML2PDF_exception $e) {
            exit($e);
        }
        
        return new Response();
    }

    public function createMultipleView($content, array $viewParams)
    {
        $twigConfig = array_merge(['parametrage' => $this->parametrage], $viewParams);
        $this->view[] = $this->templatingService->render($content, $twigConfig);
    }

    public function createMultiplePdf(array $fileParams)
    {
        // try {
        $pdf = new HTML2PDF('p', 'A4', 'fr');
        $pdf->setTestIsImage(false);
        $pdf->pdf->SetAuthor($fileParams['author']);
        $pdf->pdf->SetTitle($fileParams['title']);
        foreach ($this->view as $view) {
            $pdf->writeHTML($view);
        }
        $pdf->Output($fileParams['output']);
        // } catch (HTML2PDF_exception $e) {
        //     die($e);
        // }
        
        return new Response();
    }
}
