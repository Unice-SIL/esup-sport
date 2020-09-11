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

    /**
     * Constructeur.
     *
     * @param mixed $expediteur
     * @param mixed $templatingService
     */
    public function __construct(\Swift_Mailer $mailer, $expediteur, $templatingService)
    {
        $this->mailer = $mailer;
        $this->expediteur = $expediteur;
        $this->templatingService = $templatingService;
    }

    public function sendMailWithTemplate($subject, $destinataires, $templateName, $templateParams, $cc = null)
    {
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
}
