<?php

/*
 * Classe - TexteController
 *
 * Lister et modifier les textes
*/

namespace App\Controller\UcaGest\Referentiel;

use App\Entity\Uca\Texte;
use App\Service\Common\FlashBag;
use App\Datatables\TexteDatatable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sg\DatatablesBundle\Datatable\DatatableFactory;
use Sg\DatatablesBundle\Response\DatatableResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("UcaGest/Texte")
 * @Security("is_granted('ROLE_ADMIN')")
 */
class TexteController extends AbstractController
{
    /**
     * @Route("/", name="UcaGest_TexteLister")
     * @Isgranted("ROLE_GESTION_TEXTE_LECTURE")
     */
    public function listerAction(Request $request, DatatableFactory $datatableFactory, DatatableResponse $datatableResponse)
    {
        $datatable = $datatableFactory->create(TexteDatatable::class);
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;

        if ($request->isXmlHttpRequest()) {
            $responseService = $datatableResponse;
            $responseService->setDatatable($datatable);
            $responseService->getDatatableQueryBuilder();

            return $responseService->getResponse();
        }

        $twigConfig['noAddButton'] = true;
        $twigConfig['codeListe'] = 'Texte';

        return $this->render('UcaBundle/Common/Liste/Datatable.html.twig', $twigConfig);
    }

    /**
     * @Route("/{id}/Modifier", name="UcaGest_TexteModifier", methods={"GET", "POST"})
     * @Isgranted("ROLE_GESTION_TEXTE_ECRITURE")
     */
    public function modifierAction(Request $request, Texte $texte, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $editForm = $this->createForm('App\Form\TexteType', $texte);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid() && $request->isMethod('POST')) {
            $flashBag->addActionFlashBag($texte, 'Modifier');
            if (is_null($texte->getTexteMobile()) && 1 == $texte->getMobile()) {
                $texte->setTexteMobile('');
            }
            $em->flush();

            return $this->redirectToRoute('UcaGest_TexteLister');
        }

        $twigConfig['item'] = $texte;
        $twigConfig['form'] = $editForm->createView();

        return $this->render('UcaBundle/UcaGest/Referentiel/Texte/Formulaire.html.twig', $twigConfig);
    }
}
