<?php

namespace UcaBundle\Controller\UcaGest\Referentiel;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use UcaBundle\Entity\Texte;
use UcaBundle\Datatables\TexteDatatable;
use UcaBundle\Form\TexteType;

/**
 * @Route("UcaGest/Texte")
 * @Security("has_role('ROLE_ADMIN')")
 */
class TexteController extends Controller
{
    /**
     * @Route("/", name="UcaGest_TexteLister")
     * @Isgranted("ROLE_GESTION_TEXTE_LECTURE")
     */
    public function listerAction(Request $request)
    {
        $datatable = $this->get('sg_datatables.factory')->create(TexteDatatable::class);
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;

        if ($request->isXmlHttpRequest()) {
            $responseService = $this->get('sg_datatables.response');
            $responseService->setDatatable($datatable);
            $responseService->getDatatableQueryBuilder();
            return $responseService->getResponse();
        }

        $twigConfig['noAddButton'] = true;
        $twigConfig['codeListe'] = 'Texte';
        return $this->render('@Uca/Common/Liste/Datatable.html.twig', $twigConfig);
    }


    /**
     * @Route("/{id}/Modifier", name="UcaGest_TexteModifier", methods={"GET", "POST"})
     * @Isgranted("ROLE_GESTION_TEXTE_ECRITURE")
     */
    public function modifierAction(Request $request, Texte $texte)
    {
        $em = $this->getDoctrine()->getManager();
        $editForm = $this->createForm('UcaBundle\Form\TexteType', $texte);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid() && $request->isMethod('POST')) {
            $this->get('uca.flashbag')->addActionFlashBag($texte, 'Modifier');
            if (is_null($texte->getTexteMobile()) && $texte->getMobile() == 1) {
                $texte->setTexteMobile('');
            }
            $em->flush();
            return $this->redirectToRoute('UcaGest_TexteLister');
        }

        $twigConfig["item"] = $texte;
        $twigConfig["form"] = $editForm->createView();
        return $this->render('@Uca/UcaGest/Referentiel/Texte/Formulaire.html.twig', $twigConfig);
    }
}
