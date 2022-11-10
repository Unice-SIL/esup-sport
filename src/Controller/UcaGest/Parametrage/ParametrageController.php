<?php

/*
 * Classe -  ParametrageController
 *
 * Consulter et modifier les paramêtres globaux du site
*/

namespace App\Controller\UcaGest\Parametrage;

use App\Form\ParametrageType;
use App\Entity\Uca\Parametrage;
use App\Service\Common\FlashBag;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ParametrageRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("UcaGest/Parametrage")
 * @Isgranted("ROLE_GESTION_PARAMETRAGE")
 */
class ParametrageController extends AbstractController
{
    /**
     * @Route("/", name="UcaGest_Parametrage")
     * @Isgranted("ROLE_GESTION_PARAMETRAGE")
     */
    public function listerAction(Request $request, ParametrageRepository $paramRepo)
    {
        $item = $paramRepo->findOneById(1);
        $twigConfig['item'] = $item;

        return $this->render('UcaBundle/UcaGest/Parametrage/Parametrage.html.twig', $twigConfig);
    }

    /**
     * @Route("/Modifier/{id}", name="UcaGest_ParametrageModifier",requirements={"id"="\d+"}, methods={"GET", "POST"})
     * @Isgranted("ROLE_GESTION_PARAMETRAGE")
     */
    public function modifierAction(Request $request, Parametrage $item, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $form = $this->createForm(ParametrageType::class, $item);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($item);
            $em->flush();
            $flashBag->addActionFlashBag($item, 'Modifier');

            return $this->redirectToRoute('UcaGest_Parametrage');
        }
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();

        return $this->render('UcaBundle/UcaGest/Parametrage/Formulaire.html.twig', $twigConfig);
    }
}
