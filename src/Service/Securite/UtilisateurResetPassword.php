<?php

namespace App\Service\Securite;

use App\Entity\Uca\Utilisateur;
use App\Service\Common\MailService;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class UtilisateurResetPassword.
 */
class UtilisateurResetPassword
{
    /**
     * Constante pour la génération du mot de passe aléatoire (pour l'email).
     */
    public const availableCharacters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * @var UserPasswordHasherInterface
     */
    private $passwordEncoder;

    /**
     * @var MailService
     */
    private $mailer;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Utilisateur
     */
    private $utilisateur;

    /**
     * @var FlashBagInterface
     */
    private $flashBag;

    /**
     * UtilisateurResetPassword constructor.
     */
    public function __construct(UserPasswordHasherInterface $passwordEncoder, MailService $mailer, TranslatorInterface $translator, EntityManagerInterface $entityManager, FlashBagInterface $flashBag)
    {
        $this->setPasswordEncoder($passwordEncoder);
        $this->setMailer($mailer);
        $this->setTranslator($translator);
        $this->setEntityManager($entityManager);
        $this->setFlashBag($flashBag);
    }

    /**
     * Gestionnaire mettant en place les instructions au préalable avec la réinitialisation d'un mot de passe.
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function sendInstructionResetPassword(string $email): array
    {
        // Récupération de l'utilisateur et on set utilisateur à la classe
        $this->setUtilisateur($this->getEntityManager()->getRepository(Utilisateur::class)->findOneBy(['email' => $email]));

        // L'utilisateur existe et est actif
        if (!empty($this->getUtilisateur()) && true === $this->getUtilisateur()->isEnabled()) {
            // Génération d'un mot de passe temporaire qu'on attribut à l'utilisateur demandant un changement de mot de passe
            $encodePassword = $this->getPasswordEncoder()->hashPassword($this->getUtilisateur(), $this->generateRandomPassword());

            $this->getUtilisateur()->setPassword($encodePassword);

            // Sauvegarde en base de données
            $this->getEntityManager()->flush();

            // L'utilisateur possède une adresse email
            if (!empty($this->getUtilisateur()->getEmail())) {
                // Envoi de l'email à l'utilisateur
                if ('test' !== $_ENV['APP_ENV']) {
                    // @codeCoverageIgnoreStart
                    $sent = $this->getMailer()->sendMailWithTemplate(
                        'app.reset_password.email.subject',
                        [$this->getUtilisateur()->getEmail()],
                        'email/security/reset_password.html.twig',
                        [
                            'title' => 'app.reset_password.email.title',
                            'email' => $this->getUtilisateur()->getEmail(),
                            'password' => $this->getUtilisateur()->getPassword(),
                        ]
                    );
                    // @codeCoverageIgnoreEnd
                }
                $this->getFlashBag()->add('success', $this->getTranslator()->trans('app.reset_password.message.send_email'));

                return [
                    'status' => true,
                    'message' => $this->getTranslator()->trans('app.reset_password.message.send_email'),
                ];
            }
            // L'utilisateur ne possède pas d'adresse email

            // @codeCoverageIgnoreStart
            $this->getFlashBag()->add('error', $this->getTranslator()->trans('app.reset_password.message.not_send_email.no_email_user'));

            return [
                'status' => false,
                'message' => $this->getTranslator()->trans('app.reset_password.message.not_send_email.no_email_user'),
            ];
            // @codeCoverageIgnoreEnd
        }
        // L'utilisateur existe et est inactif
        if (!empty($this->getUtilisateur()) && false === $this->getUtilisateur()->isEnabled()) {
            $this->getFlashBag()->add('error', $this->getTranslator()->trans('app.reset_password.message.not_send_email.inative'));

            return [
                'status' => false,
                'message' => $this->getTranslator()->trans('app.reset_password.message.not_send_email.inative'),
            ];
        }
        // L'utilisateur n'existe pas

        $this->getFlashBag()->add('error', $this->getTranslator()->trans('app.reset_password.message.not_send_email.invalid_username'));

        return [
            'status' => false,
            'message' => $this->getTranslator()->trans('app.reset_password.message.not_send_email.invalid_username'),
        ];
    }

    /**
     * @codeCoverageIgnore
     */
    public function sendInitPassword(Utilisateur $utilisateur)
    {
        if (!empty($utilisateur->getEmail())) {
            // Envoi de l'email à l'utilisateur
            $sent = $this->getMailer()->sendMailWithTemplate(
                'app.reset_password.email.subject',
                [$utilisateur->getEmail()],
                'email/security/init_password.html.twig',
                [
                    'title' => 'app.reset_password.email.title',
                    'email' => $utilisateur->getEmail(),
                    'password' => $utilisateur->getPassword(),
                ]
            );

            if (0 === $sent) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Génération d'un mot de passe aléatoire.
     */
    public function generateRandomPassword()
    {
        // Variable locale
        $result = '';

        // Génération d'un de 16 caractères
        for ($i = 0; $i < 16; ++$i) {
            $result .= self::availableCharacters[rand(0, strlen(self::availableCharacters) - 1)];
        }

        return $result;
    }

    /**
     * Vérification permettant de savoir si l'état de modification d'un mot de passe est valide (pas de modification de l'url, usurpation).
     *
     * @return bool
     */
    public function isValidResetPassword(string $email, string $password)
    {
        // Récupération de l'utilisateur et on set utilisateur à la classe
        $this->setUtilisateur($this->getEntityManager()->getRepository(Utilisateur::class)->findOneBy(['email' => $email]));
        // L'utilisateur n'existe pas
        if (empty($this->getUtilisateur())) {
            return false;
        }
        // On vérifie si le mot de passe de l'utilisateur est identique à celui qui était présent dans l'URL
        return $this->getPasswordEncoder()->isPasswordValid($this->getUtilisateur(), $password);
    }

    /**
     * Gestionnaire réinitialisant le mot de passe.
     *
     * @throws DBALException
     */
    public function resetPassword(string $email, string $password): array
    {
        // Récupération de l'utilisateur et on set utilisateur à la classe
        $this->setUtilisateur($this->getEntityManager()->getRepository(Utilisateur::class)->findOneBy(['email' => $email]));

        // L'utilisateur existe
        if (!empty($this->getUtilisateur())) {
            // Génération du nouveau mot de passe en fonction de la saisie de l'utilisatuer
            $newPassword = $this->getPasswordEncoder()->hashPassword($this->getUtilisateur(), $password);

            // On set le nouveau mot de passe à l'utilisateur (côté BDD refonte)
            $this->getUtilisateur()->setPassword($newPassword);

            // Sauvegarde en base de données (côté BDD refonte)
            $this->getEntityManager()->flush();

            // On set le nouveau mot de passe à l'utilisateur (côté BDD IG)
            $this->getEntityManager()->getConnection()->update('utilisateur', ['password' => $newPassword], ['email' => $email]);

            $this->getFlashBag()->add('success', $this->getTranslator()->trans('app.reset_password.message.reset_password'));

            return [
                'status' => true,
                'message' => $this->getTranslator()->trans('app.reset_password.message.reset_password'),
            ];
        }
        // L'utilisateur n'existe pas

        $this->getFlashBag()->add('error', $this->getTranslator()->trans('app.reset_password.message.not_reset_password'));

        return [
            'status' => false,
            'message' => $this->getTranslator()->trans('app.reset_password.message.not_reset_password'),
        ];
    }

    public function passwordForgotten(string $identifiant): void
    {
        $usr = $this->getEntityManager()->getRepository(Utilisateur::class)->findOneByIdentifiant($identifiant);
        if ((bool) $usr) {
            $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
            $usr->setConfirmationToken($token);
            $this->getEntityManager()->flush();

            if ('test' !== $_ENV['APP_ENV']) {
                // @codeCoverageIgnoreStart
                $this->getMailer()->sendMailWithTemplate(
                    $this->getTranslator()->trans('utilisateur.password_forgotten.mail.subject'),
                    $usr->getEmail(),
                    'UserBundle/Mail/MotDePasseOublie.html.twig',
                    ['user' => $usr]
                );
                // @codeCoverageIgnoreEnd
            }

            $this->getFlashBag()->add('success', 'utilisateur.password_forgotten.mail.sent');
        } else {
            // @codeCoverageIgnoreStart
            $this->getFlashBag()->add('danger', 'utilisateur.not_found');
            // @codeCoverageIgnoreEnd
        }
    }

    public function changePassword(Utilisateur $user): string
    {
        $redirect = (bool) $user->getConfirmationToken() ? 'security_login' : 'UcaWeb_MonCompte';
        $user->setConfirmationToken(null)->setPassword($this->passwordEncoder->hashPassword($user, $user->getPlainPassword()));
        $this->getEntityManager()->flush();

        if ('test' !== $_ENV['APP_ENV']) {
            // @codeCoverageIgnoreStart
            $this->getMailer()->sendMailWithTemplate(
                $this->getTranslator()->trans('utilisateur.change_password.mail.subject'),
                $user->getEmail(),
                'UserBundle/Mail/MotDePasseChange.html.twig',
                ['user' => $user]
            );
            // @codeCoverageIgnoreEnd
        }

        $this->getFlashBag()->add('success', 'utilisateur.change_password.mail.sent');

        return $redirect;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getPasswordEncoder(): UserPasswordHasherInterface
    {
        return $this->passwordEncoder;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setPasswordEncoder(UserPasswordHasherInterface $passwordEncoder): void
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getMailer(): MailService
    {
        return $this->mailer;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setMailer(MailService $mailer): void
    {
        $this->mailer = $mailer;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return Utilisateur
     * @codeCoverageIgnore
     */
    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setUtilisateur(?Utilisateur $utilisateur): void
    {
        $this->utilisateur = $utilisateur;
    }

    /**
     * @return FlashBagInterface
     * @codeCoverageIgnore
     */
    public function getFlashBag()
    {
        return $this->flashBag;
    }

    /**
     * @param FlashBagInterface
     * @codeCoverageIgnore
     */
    public function setFlashBag(FlashBagInterface $flashBag): void
    {
        $this->flashBag = $flashBag;
    }
}
