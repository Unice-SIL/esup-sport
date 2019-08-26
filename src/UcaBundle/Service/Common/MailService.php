<?php

namespace UcaBundle\Service\Common;

class MailService
{

	private $mailer;
    private $expediteur;
    private $templatingService;

	/**
	 * Constructeur
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
            ->setSubject('[UCA] ' . $subject)
            ->setFrom($this->expediteur)
            ->setCC($cc)
            ->setTo($destinataires)
            ->setBody(
                $this->templatingService->render(
                    $templateName,
                    $templateParams
                ),
                'text/html'
            );
        
		return $this->mailer->send($message);
	}

}
