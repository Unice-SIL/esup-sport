<?php

/*
 * classe - MailService
 *
 * Service gérant les mails en utilisant le component Mailer de Symfony
*/

namespace App\Service\Common;

use App\Entity\Uca\Email;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Twig\Environment;

class MailService
{
    private $mailer;
    private $expediteur;
    private $flashBag;
    private $mailerRoutage;
    private $liste;
    private $em;
    private $twig;

    /**
     * Constructeur.
     *
     * @param mixed $expediteur
     */
    public function __construct(MailerInterface $mailer, string $mailerSender, FlashBagInterface $flashBag, string $mailerRoutage, EntityManagerInterface $em, ListeVariables $liste, Environment $twig)
    {
        $this->mailer = $mailer;
        $this->expediteur = $mailerSender;
        $this->flashBag = $flashBag;
        $this->mailerRoutage = $mailerRoutage;
        $this->liste = $liste;
        $this->em = $em;
        $this->twig = $twig;
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

        if (!is_array($destinataires)) {
            $destinataires = [$destinataires];
        }

        if ($template = $this->em->getRepository(Email::class)->findOneByNom($templateName)) {
            $templateBody = str_replace(['[[', ']]'], ['{{', '}}'], $template->getCorps());
        }

        if ($subject === null && $template) {
            $subject = str_replace(['[[', ']]'], ['{{', '}}'], $template->getSubject());
            $subject = $this->twig->createTemplate($subject);
            $subject = $subject->render($templateParams);
        }
        // Construction du mail
        $email = (new TemplatedEmail())
            ->subject(Parametrage::get()->getPrefixMail().' '.$subject)
            ->from($this->expediteur)
            ->to(...$destinataires)
            ->htmlTemplate($template ? 'UcaBundle/Email/MainTemplate/MainTemplateCKEditor.html.twig' : $templateName)
            ->context($template ? ['corps' => $templateBody,
            'placeholders' => $templateParams] : $templateParams)
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
        $this->flashBag->add('danger', 'mail.empty');
    }
}
