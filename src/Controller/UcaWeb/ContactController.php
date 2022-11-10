<?php

/*
 * Classe - ContactController
 *
 * Gestion du formulaire de contact
*/

namespace App\Controller\UcaWeb;

use App\Entity\Uca\Contact;
use App\Form\ContactType;
use App\Service\Common\FlashBag;
use App\Service\Common\MailService;
use App\Service\Common\Parametrage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("UcaWeb")
 */
class ContactController extends AbstractController
{
    /**
     * @Route("/Contact", name="UcaWeb_Contact")
     */
    public function contactAction(Request $request, FlashBag $flashBag, MailService $mailer)
    {
        $item = new Contact();
        $form = $this->createForm(ContactType::class, $item);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $mailer->sendMailWithTemplate(
                $item->getObjet(),
                Parametrage::get()->getMailContact(),
                'UcaBundle/Email/Contact/ContactEmail.html.twig',
                ['objet' => $item->getObjet(), 'message' => $item->getMessage(), 'contact_from' => $item->getEmail()],
                $form->getData()->getEmail()
            );

            $flashBag->addMessageFlashBag('contact.demande.success', 'success');

            return $this->redirectToRoute('UcaWeb_Contact');
        }
        $twigConfig['form'] = $form->createView();

        return $this->render('UcaBundle/UcaWeb/Contact/Formulaire.html.twig', $twigConfig);
    }
}
