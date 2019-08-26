<?php

namespace UcaBundle\Controller\UcaGest\Parametrage;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use UcaBundle\Service\Common\FlashBag;
use UcaBundle\Entity\Parametrage;
use UcaBundle\Form\ParametrageType;


/**
 * @Route("UcaGest/Parametrage")
 * @Isgranted("ROLE_GESTION_PARAMETRAGE")
 */
class ParametrageController extends Controller
{
    /**
     * @Route("/", name="UcaGest_Parametrage")
     * @Isgranted("ROLE_GESTION_PARAMETRAGE")
     */
    public function listerAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $item = $this->getDoctrine()->getRepository(Parametrage::class)->findOneById(1);
        $twigConfig['item'] = $item;
        return $this->render('@Uca/UcaGest/Parametrage/Parametrage.html.twig', $twigConfig);
    }
    
    /** 
     * @Route("/Modifier/{id}", name="UcaGest_ParametrageModifier",requirements={"id"="\d+"}) 
     * @Method({"GET", "POST"})
     * @Isgranted("ROLE_GESTION_PARAMETRAGE")
    */
    public function modifierAction(Request $request, Parametrage $item)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->get('form.factory')->create(ParametrageType::class, $item);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($item);
            $em->flush();
            $this->get('uca.flashbag')->addActionFlashBag($item, 'Modifier');
            return $this->redirectToRoute('UcaGest_Parametrage');
        }
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();
        return $this->render('@Uca/UcaGest/Parametrage/Formulaire.html.twig', $twigConfig);
    }
}
