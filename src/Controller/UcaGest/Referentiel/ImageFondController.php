<?php

/*
 * Classe - ImageFondController
 *
 * Consulter, modifier et supprimer les iamges de fond
*/

namespace App\Controller\UcaGest\Referentiel;

use App\Form\ImageFondType;
use App\Entity\Uca\ImageFond;
use App\Service\Common\FlashBag;
use App\Datatables\ImageFondDatatable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sg\DatatablesBundle\Datatable\DatatableFactory;
use Sg\DatatablesBundle\Response\DatatableResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Security("is_granted('ROLE_ADMIN')")
 * @Route("UcaGest/ImageFond")
 */
class ImageFondController extends AbstractController
{
    /**
     * @Route("/", name="UcaGest_ImageFondLister")
     * @Isgranted("ROLE_GESTION_IMAGEFOND_LECTURE")
     */
    public function listerAction(Request $request, DatatableFactory $datatableFactory, DatatableResponse $datatableResponse)
    {
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $datatableFactory->create(ImageFondDatatable::class);
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;
        if ($isAjax) {
            $responseService = $datatableResponse;
            $responseService->setDatatable($datatable);
            $dtQueryBuilder = $responseService->getDatatableQueryBuilder();
            $qb = $dtQueryBuilder->getQb();

            return $responseService->getResponse();
        }
        // Bouton Ajouter
        $usr = $this->getUser();
        $twigConfig['noAddButton'] = true;
        $twigConfig['codeListe'] = 'ImageFond';

        return $this->render('UcaBundle/Common/Liste/Datatable.html.twig', $twigConfig);
    }

    /**
     * @Route("/Supprimer/{id}", name="UcaGest_ImageFondSupprimer")
     * @Isgranted("ROLE_GESTION_IMAGEFOND_ECRITURE")
     */
    public function supprimerAction(Request $request, ImageFond $imageFond, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $em->remove($imageFond);
        $em->flush();
        $flashBag->addActionFlashBag($imageFond, 'Supprimer');

        return $this->redirectToRoute('UcaGest_EtablissementLister');
    }

    /**
     * @Route("/Modifier/{id}", name="UcaGest_ImageFondModifier",requirements={"id"="\d+"}, methods={"GET", "POST"})
     * @Isgranted("ROLE_GESTION_IMAGEFOND_ECRITURE")
     */
    public function modifierAction(Request $request, ImageFond $imageFond, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $form = $this->createForm(ImageFondType::class, $imageFond);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($imageFond);
            $em->flush();
            $flashBag->addActionFlashBag($imageFond, 'Modifier');

            return $this->redirectToRoute('UcaGest_ImageFondLister');
        }
        $twigConfig['item'] = $imageFond;
        $twigConfig['form'] = $form->createView();

        return $this->render('UcaBundle/UcaGest/Referentiel/ImageFond/Formulaire.html.twig', $twigConfig);
    }
}
