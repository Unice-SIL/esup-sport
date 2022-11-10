<?php

/*
 * Classe - LogoPartenaireController
 *
 * Gestion du CRUD pour les logos partenaires
*/

namespace App\Controller\UcaGest\Referentiel;

use App\Form\LogoPartenaireType;
use App\Service\Common\FlashBag;
use App\Entity\Uca\LogoPartenaire;
use Doctrine\ORM\EntityManagerInterface;
use App\Datatables\LogoPartenaireDatatable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sg\DatatablesBundle\Datatable\DatatableFactory;
use Sg\DatatablesBundle\Response\DatatableResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Security("is_granted('ROLE_ADMIN')")
 * @Route("UcaGest/LogoPartenaire")
 */
class LogoPartenaireController extends AbstractController
{
    /**
     * @Route("/", name="UcaGest_LogoPartenaireLister")
     * @Isgranted("ROLE_GESTION_LOGOPARTENAIRE_LECTURE")
     */
    public function listerAction(Request $request, DatatableFactory $datatableFactory, DatatableResponse $datatableResponse)
    {
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $datatableFactory->create(LogoPartenaireDatatable::class);
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
        $twigConfig['codeListe'] = 'LogoPartenaire';

        return $this->render('UcaBundle/UcaGest/Referentiel/LogoPartenaire/Datatable.html.twig', $twigConfig);
    }

    /**
     * @Route("/Supprimer/{id}", name="UcaGest_LogoPartenaireSupprimer")
     * @Isgranted("ROLE_GESTION_LOGOPARTENAIRE_ECRITURE")
     */
    public function supprimerAction(Request $request, LogoPartenaire $logoPartenaire, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $numeroOrdreMaxActuel = $em->getRepository(LogoPartenaire::class)->max('ordre');
        for ($numeroOrdre = $logoPartenaire->getOrdre() + 1; $numeroOrdre <= $numeroOrdreMaxActuel; ++$numeroOrdre) {
            $logoPartenaireAffecteParChangement = $em->getRepository(LogoPartenaire::class)->findOneBy(['ordre' => $numeroOrdre]);
            $logoPartenaireAffecteParChangement->setOrdre($numeroOrdre - 1);
        }

        $em->remove($logoPartenaire);
        $em->flush();
        $flashBag->addActionFlashBag($logoPartenaire, 'Supprimer');

        return $this->redirectToRoute('UcaGest_LogoPartenaireLister');
    }

    /**
     * @Route("/Modifier/{id}", name="UcaGest_LogoPartenaireModifier",requirements={"id"="\d+"}, methods={"GET", "POST"})
     * @Isgranted("ROLE_GESTION_LOGOPARTENAIRE_ECRITURE")
     */
    public function modifierAction(Request $request, LogoPartenaire $logoPartenaire, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $form = $this->createForm(LogoPartenaireType::class, $logoPartenaire);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($logoPartenaire);
            $em->flush();
            $flashBag->addActionFlashBag($logoPartenaire, 'Modifier');

            return $this->redirectToRoute('UcaGest_LogoPartenaireLister');
        }
        $twigConfig['item'] = $logoPartenaire;
        $twigConfig['codeListe'] = 'logopartenaire.modifier';
        $twigConfig['form'] = $form->createView();

        return $this->render('UcaBundle/UcaGest/Referentiel/LogoPartenaire/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Ajouter", name="UcaGest_LogoPartenaireAjouter", methods={"GET", "POST"})
     * @IsGranted("ROLE_GESTION_LOGOPARTENAIRE_ECRITURE")
     */
    public function ajouterAction(Request $request, FlashBag $flashBag, EntityManagerInterface $em)
    {
        $item = new LogoPartenaire($em);
        $form = $this->createForm(LogoPartenaireType::class, $item);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($item);
            $em->flush();
            $flashBag->addActionFlashBag($item, 'Ajouter');

            return $this->redirectToRoute('UcaGest_LogoPartenaireLister');
        }
        $twigConfig['item'] = $item;
        $twigConfig['codeListe'] = 'logopartenaire.ajouter';
        $twigConfig['form'] = $form->createView();

        return $this->render('UcaBundle/UcaGest/Referentiel/LogoPartenaire/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/ModifierOrdre/{id}/{action}", name="UcaGest_LogoPartenaireModifierOrdre", options={"expose"=true}, requirements={"id"="\d+"}, methods={"GET"})
     * @IsGranted("ROLE_GESTION_LOGOPARTENAIRE_ECRITURE")
     *
     * @param mixed $action
     */
    public function modifierOrdreAction(Request $request, LogoPartenaire $logoPartenaire, $action, EntityManagerInterface $em)
    {
        if ('monter' == $action) {
            $delta = -1;
            $modificationPossible = $logoPartenaire->getOrdre() > 1;
        } else {
            $delta = +1;
            $modificationPossible = $logoPartenaire->getOrdre() < $em->getRepository(LogoPartenaire::class)->max('ordre');
        }

        if ($modificationPossible) {
            $oldOrdre = $logoPartenaire->getOrdre();
            $newOrdre = $logoPartenaire->getOrdre() + $delta;
            $logoPartenaireAffecteParChangement = $em->getRepository(LogoPartenaire::class)->findOneBy(['ordre' => $newOrdre]);

            $logoPartenaire->setOrdre($newOrdre);
            $logoPartenaireAffecteParChangement->setOrdre($oldOrdre);

            $em->flush();

            return new Response(200);
        }

        return new Response(204);
    }
}
