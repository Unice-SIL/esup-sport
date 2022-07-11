<?php

/*
 * Classe - InfosPratiquesController
 *
 * Affichage des infos pratiques.
*/

namespace App\Controller\UcaWeb;

use App\Repository\LieuRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\EtablissementRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("UcaWeb/InfosPratiques")
 */
class InfosPratiquesController extends AbstractController
{
    /**
     * @Route("/", name="UcaWeb_InfosPratiques")
     */
    public function voirAction(Request $request, EtablissementRepository $etablissementRepository, LieuRepository $lieuRepository, EntityManagerInterface $em)
    {
        $etablissements = $etablissementRepository->findAll();
        $lieu = $lieuRepository->findAll();
        $twigConfig['etablissements'] = $etablissements;
        $twigConfig['lieu'] = $lieu;

        return $this->render('UcaBundle/UcaWeb/InfosPratiques/Voir.html.twig', $twigConfig);
    }
}
