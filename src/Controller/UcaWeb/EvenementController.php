<?php

/*
 * Classe - EvenementController
 *
 * Liste des évenements
*/

namespace App\Controller\UcaWeb;

use App\Repository\FormatSimpleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("UcaWeb/Evenement")
 */
class EvenementController extends AbstractController
{
    /**
     * @Route("/{page}", name="UcaWeb_Evenement", defaults={"page" = 1})
     *
     * @param mixed $page
     */
    public function listAction(Request $request, $page, FormatSimpleRepository $formatSimpleRepository)
    {
        if ($page < 1) {
            $page = 1;
        }

        $evenements = $formatSimpleRepository->PromotionsPagination($page, 5);

        $pagination = [
            'page' => $page,
            'nbPages' => ceil(count($evenements) / 5),
            'nomRoute' => 'UcaWeb_Evenement',
            'paramsRoute' => [],
        ];

        $twigConfig['pagination'] = $pagination;
        $twigConfig['evenements'] = $evenements;

        return $this->render('UcaBundle/UcaWeb/Evenement/Lister.html.twig', $twigConfig);
    }
}
