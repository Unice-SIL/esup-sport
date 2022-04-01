<?php

/*
 * classe - MailService
 *
 * Service gérant les mails en utilisant la libraire swiftmailer
*/

namespace UcaBundle\Service\Common;

class MailService
{
    private $mailer;
    private $expediteur;
    private $templatingService;
    private $flashBagMessage;

    /**
     * Constructeur.
     *
     * @param mixed $expediteur
     * @param mixed $templatingService
     */
    public function __construct(\Swift_Mailer $mailer, $expediteur, $templatingService, FlashBag $flashBagMessage)
    {
        $this->mailer = $mailer;
        $this->expediteur = $expediteur;
        $this->templatingService = $templatingService;
        $this->flashBagMessage = $flashBagMessage;
    }

    public function sendMailWithTemplate($subject, $destinataires, $templateName, $templateParams, $cc = null)
    {
        if (empty($destinataires)) {
            $this->addFlashEmptyMail();

            return;
        }

        if (is_array($destinataires)) {
            foreach ($destinataires as $destinataire) {
                if (null === $destinataire || '' === $destinataire) {
                    $this->addFlashEmptyMail();

                    return;
                }
            }
        } elseif (is_string($destinataires) && (null === $destinataires || '' === $destinataires)) {
            $this->addFlashEmptyMail();

            return;
        }

        $message = \Swift_Message::newInstance()
            ->setSubject('[UCA] '.$subject)
            ->setFrom($this->expediteur)
            ->setCC($cc)
            ->setTo($destinataires)
            ->setBody(
                $this->templatingService->render(
                    $templateName,
                    $templateParams
                ),
                'text/html'
            )
        ;

        return $this->mailer->send($message);
    }

    public function addFlashEmptyMail(): void
    {
        $this->flashBagMessage->addMessageFlashBag('mail.empty', 'danger');
    }
}