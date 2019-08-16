<?php
namespace UcaBundle\Controller\UcaWeb;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("UcaWeb/InfosPratiques")
 */
class InfosPratiquesController extends Controller
{

    /**
     * @Route("/", name="UcaWeb_InfosPratiques")
    */
    public function voirAction(Request $request)
    {

        return $this->render('@Uca/UcaWeb/InfosPratiques/Voir.html.twig', array());
    }
}