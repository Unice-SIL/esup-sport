<?php

/*
 * Classe - ClasseActiviteController
 *
 * Gestion du CRUD pour les classes d'activités
*/

namespace App\Controller\UcaGest\Activite;

use App\Service\Common\FlashBag;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Datatables\ClasseActiviteDatatable;
use App\Entity\Uca\ClasseActivite;
use App\Form\ClasseActiviteType;
use Doctrine\ORM\EntityManagerInterface;
use Sg\DatatablesBundle\Datatable\DatatableFactory;
use Sg\DatatablesBundle\Response\DatatableResponse;

/**
 * @Security("is_granted('ROLE_ADMIN')")
 * @Route("UcaGest/ClasseActivite")
 */
class ClasseActiviteController extends AbstractController
{
    /**
     * @Route("/", name="UcaGest_ClasseActiviteLister")
     * @Isgranted("ROLE_GESTION_CLASSE_ACTIVITE_LECTURE")
     */
    public function listerAction(Request $request, DatatableFactory $datatableFactory, DatatableResponse $datatableResponse)
    {
        /* dump($this->get('security.authorization_checker')); die;
        // dump($this->get('fos_user.security.controller'));die;
        if (!$this->getUser() || !$this->getUser()->hasRole('GESTION_CLASSE_ACTIVITE'))
            throw new \Exception('Access Denied');
        // if (!$this->isGranted('GESTION_CLASSE_ACTIVITE'))
        //     throw new \Exception('Access Denied');
        // $this->denyAccessUnlessGranted('GESTION_CLASSE_ACTIVITE');
        */

        $isAjax = $request->isXmlHttpRequest();
        $datatable = $datatableFactory->create(ClasseActiviteDatatable::class);
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
        if (!$usr->hasRole('ROLE_GESTION_CLASSE_ACTIVITE_ECRITURE')) {
            $twigConfig['noAddButton'] = true;
        }

        $twigConfig['codeListe'] = 'ClasseActivite';

        return $this->render('UcaBundle/Common/Liste/Datatable.html.twig', $twigConfig);
    }

    /**
     * @Route("/Ajouter", name="UcaGest_ClasseActiviteAjouter")
     * @Isgranted("ROLE_GESTION_CLASSE_ACTIVITE_ECRITURE")
     */
    public function ajouterAction(Request $request, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $item = new ClasseActivite($em);
        $form = $this->createForm(ClasseActiviteType::class, $item);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($item);
            $em->flush();
            $flashBag->addActionFlashBag($item, 'Ajouter');

            return $this->redirectToRoute('UcaGest_ClasseActiviteLister');
        }
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();

        return $this->render('UcaBundle/UcaGest/Activite/ClasseActivite/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Modifier/{id}", name="UcaGest_ClasseActiviteModifier")
     * @Isgranted("ROLE_GESTION_CLASSE_ACTIVITE_ECRITURE")
     */
    public function modifierAction(Request $request, ClasseActivite $item, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $form = $this->createForm(ClasseActiviteType::class, $item);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->flush();
            $flashBag->addActionFlashBag($item, 'Modifier');

            return $this->redirectToRoute('UcaGest_ClasseActiviteLister');
        }
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();

        return $this->render('UcaBundle/UcaGest/Activite/ClasseActivite/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Supprimer/{id}", name="UcaGest_ClasseActiviteSupprimer")
     * @Isgranted("ROLE_GESTION_CLASSE_ACTIVITE_ECRITURE")
     */
    public function supprimerAction(Request $request, ClasseActivite $item, FlashBag $flashBag, EntityManagerInterface $em)
    {
        if (!$item->getActivites()->isEmpty()) {
            $flashBag->addActionErrorFlashBag($item, 'Supprimer');

            return $this->redirectToRoute('UcaGest_ClasseActiviteLister');
        }
        $em->remove($item);
        $em->flush();
        $flashBag->addActionFlashBag($item, 'Supprimer');

        return $this->redirectToRoute('UcaGest_ClasseActiviteLister');
    }
}
