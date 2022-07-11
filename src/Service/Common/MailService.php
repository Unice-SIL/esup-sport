<?php

/*
 * classe - MailService
 *
 * Service gérant les mails en utilisant le component Mailer de Symfony
*/

namespace App\Service\Common;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

class MailService
{
    private $mailer;
    private $expediteur;
    private $flashBag;
    private $mailerRoutage;

    /**
     * Constructeur.
     *
     * @param mixed $expediteur
     */
    public function __construct(MailerInterface $mailer, string $mailerSender, FlashBagInterface $flashBag, string $mailerRoutage)
    {
        $this->mailer = $mailer;
        $this->expediteur = $mailerSender;
        $this->flashBag = $flashBag;
        $this->mailerRoutage = $mailerRoutage;
    }

    public function sendMailWithTemplate($subject, $destinataires, $templateName, $templateParams, $cc = null, $exception = false)
    {
        if (!empty($this->mailerRoutage) && !$exception) {
            $templateParams['_emails_to'] = $destinataires;
            $destinataires = $this->mailerRoutage;
        }

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

        // Construction du mail
        $email = (new TemplatedEmail())
            ->subject('[UCA] '.$subject)
            ->from($this->expediteur)
            ->to($destinataires)
            ->htmlTemplate($templateName)
            ->context($templateParams)
        ;

        // On ajoute les adresses en CC si besoin
        if ($cc) {
            $email->cc($cc);
        }

        // Envoi du mail
        try {
            $this->mailer->send($email);

            return true;
        } catch (TransportExceptionInterface $e) {
            // TODO : add error in log file
            // dd($e);

            return 0;
        }
    }

    public function addFlashEmptyMail(): void
    {
        $this->flashBagMessage->addMessageFlashBag('mail.empty', 'danger');
    }
}
