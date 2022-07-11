<?php

/*
 * Classe - AccueilController
 *
 * Gestion de l'acceuil de l'application (public)
 * Gestion de la page de connexion
*/

namespace App\Controller\UcaWeb;

use App\Repository\ActualiteRepository;
use App\Repository\ClasseActiviteRepository;
use App\Repository\FormatAchatCarteRepository;
use App\Repository\FormatAvecCreneauRepository;
use App\Repository\FormatAvecReservationRepository;
use App\Repository\FormatSimpleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Service\SelectionProfil;

class AccueilController extends AbstractController
{
    /**
     * @Route("/", name="UcaWeb_Accueil", methods={"GET","HEAD"}))
     */
    public function accueilAction(
        Request $request, 
        FormatSimpleRepository $formatSimpleRepository, 
        ClasseActiviteRepository $classeActiviteRepository, 
        ActualiteRepository $actualiteRepository, 
        FormatAvecCreneauRepository $formatAvecCreneauRepository,
        FormatAvecReservationRepository $formatAvecReservationRepository,
        FormatAchatCarteRepository $formatAchatCarteRepository
    )
    {
        $twigConfig['item_format_activite'] = $formatSimpleRepository->findByPromouvoir();
        $twigConfig['item_format_avec_creaneau'] = $formatAvecCreneauRepository->findByPromouvoir();
        $twigConfig['item_format_avec_ressource'] = $formatAvecReservationRepository->findByPromouvoir();
        $twigConfig['item_format_avec_carte'] = $formatAchatCarteRepository->findByPromouvoir();

        $twigConfig['item_class_activite'] = $classeActiviteRepository->findAll();
        $twigConfig['item_actualite'] = $actualiteRepository->findBy(
            [],
            ['ordre' => 'ASC']
        );

        return $this->render('UcaBundle/UcaWeb/Accueil/Main.html.twig', $twigConfig);
    }

    /**
     * @Route("/Connexion/SelectionProfil", name="UcaWeb_ConnexionSelectionProfil", methods={"GET","HEAD"})
     */
    public function selectionProfilAction(Request $request, SelectionProfil $selectionProfil)
    {
        $twigConfig['selectionProfil'] = $selectionProfil;

        return $this->render('UcaBundle/UcaWeb/Utilisateur/LoginSelectionProfil.html.twig', $twigConfig);
    }
}
