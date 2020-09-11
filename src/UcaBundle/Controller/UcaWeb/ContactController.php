<?php

/*
 * Classe - ContactController
 *
 * Gestion du formulaire de contact
*/

namespace UcaBundle\Controller\UcaWeb;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Entity\Contact;
use UcaBundle\Form\ContactType;
use UcaBundle\Service\Common\Parametrage;

/**
 * @Route("UcaWeb")
 */
class ContactController extends Controller
{
    /**
     * @Route("/Contact", name="UcaWeb_Contact")
     */
    public function contactAction(Request $request)
    {
        $item = new Contact();
        $form = $this->get('form.factory')->create(ContactType::class, $item);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $mailer = $this->container->get('mailService');
            $mailer->sendMailWithTemplate(
                $item->getObjet(),
                Parametrage::get()->getMailContact(),
                '@Uca/Email/Contact/ContactEmail.html.twig',
                ['objet' => $item->getObjet(), 'message' => $item->getMessage(), 'email' => $item->getEmail()],
                $form->getData()->getEmail()
            );

            $this->get('uca.flashbag')->addMessageFlashBag('contact.demande.success', 'success');

            return $this->redirectToRoute('UcaWeb_Contact');
        }
        $twigConfig['form'] = $form->createView();

        return $this->render('@Uca/UcaWeb/Contact/Formulaire.html.twig', $twigConfig);
    }
}
