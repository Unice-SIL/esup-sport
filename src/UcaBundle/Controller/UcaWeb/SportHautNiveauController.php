<?php
namespace UcaBundle\Controller\UcaWeb;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("UcaWeb/Sport")
 */
class SportHautNiveauController extends Controller
{
    /**
     * @Route("/", name="UcaWeb_SportVoir")
    */
    public function listerAction(Request $request)
    {

        

        return $this->render('@Uca/UcaWeb/SportHautNiveau/Voir.html.twig',array());
    }
}