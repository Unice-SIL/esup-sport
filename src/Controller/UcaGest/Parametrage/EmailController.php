<?php

/*
 * Classe - EmailController
 *
 * Gestion du CRUD pour les logos parametrables
*/

namespace App\Controller\UcaGest\Parametrage;

use App\Form\EmailType;
use App\Service\Common\FlashBag;
use App\Entity\Uca\Email;
use Doctrine\ORM\EntityManagerInterface;
use App\Datatables\EmailDatatable;
use App\Entity\Uca\Commande;
use App\Repository\InscriptionRepository;
use App\Service\Common\ListeVariables;
use App\Service\Common\MailService;
use App\Service\Common\Parametrage;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sg\DatatablesBundle\Datatable\DatatableFactory;
use Sg\DatatablesBundle\Response\DatatableResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Routing\RouterInterface;

/**
 * @Security("is_granted('ROLE_ADMIN')")
 * @Route("UcaGest/Email")
 */
class EmailController extends AbstractController
{
    private $liste;
    public function __construct(ListeVariables $liste){
        $this->liste = $liste->getListe();
    }

    /**
     * @Route("/", name="UcaGest_EmailLister")
     * @Isgranted("ROLE_GESTION_PARAMETRAGE")
     */
    public function listerAction(Request $request, DatatableFactory $datatableFactory, DatatableResponse $datatableResponse)
    {
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $datatableFactory->create(EmailDatatable::class);
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
        $twigConfig['codeListe'] = 'Email';
        $twigConfig['noAddButton'] = true;

        return $this->render('UcaBundle/Common/Liste/Datatable.html.twig', $twigConfig);
    }

    /**
     * @Route("/Modifier/{id}", name="UcaGest_EmailModifier",requirements={"id"="\d+"}, methods={"GET", "POST"})
     * @Isgranted("ROLE_GESTION_PARAMETRAGE")
     */
    public function modifierAction(Request $request, Email $email, FlashBag $flashBag, EntityManagerInterface $em, MailService $mailer, InscriptionRepository $rere, RouterInterface $router)
    {
        $form = $this->createForm(EmailType::class, $email, ['placeholder' => $this->liste[$email->getNom()]]);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($email);
            $em->flush();
            $flashBag->addActionFlashBag($email, 'Modifier');
            return $this->redirectToRoute('UcaGest_EmailLister');
        }
        $twigConfig['item'] = $email;
        $twigConfig['codeListe'] = 'Email.modifier';
        $twigConfig['form'] = $form->createView();

        return $this->render('UcaBundle/UcaGest/Parametrage/Email/Formulaire.html.twig', $twigConfig);
    }
}
