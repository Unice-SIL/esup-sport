<?php

namespace App\Service\Securite;

use App\Entity\Uca\Utilisateur;
use App\Events\RegistrationEvents;
use App\Service\Common\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Gestionnaire d'inscription.
 *
 * enclenchement du processus d'activation d'un compte
 */
class RegistrationHandler
{
    /**
     * Constante, longueur de la chaine de caractères du tokenValidation.
     *
     * @var int TOKEN_LENGTH
     */
    public const TOKEN_LENGTH = 16;

    /**
     * Constante utilisée pour générer le tokenValidation (pour l'email).
     *
     * @var string availableCharacters
     */
    public const availableCharacters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * @var EntityManagerInterface Entity Manager
     */
    private $em;

    /**
     * Gestionnaire du formulaire d'authentification.
     *
     * @var loginFormAuthenticator
     */
    private $authenticator;

    /**
     * Dispatcher d'événements.
     *
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Composant de router Symfony.
     *
     * @var RouterInterface
     */
    private $router;

    /**
     * @var UserPasswordHasherInterface
     */
    private $passwordEncoder;

    /**
     * @var FlashBagInterface
     */
    private $flashBag;

    /**
     * @var MailService
     */
    private $mailer;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(EntityManagerInterface $em, LoginFormAuthenticator $authenticator, EventDispatcherInterface $eventDispatcher, RouterInterface $router, UserPasswordHasherInterface $passwordEncoder, FlashBagInterface $flashBag, MailService $mailer, TranslatorInterface $translator, TokenStorageInterface $tokenStorage)
    {
        $this->setEm($em);
        $this->setAuthenticator($authenticator);
        $this->setEventDispatcher($eventDispatcher);
        $this->setRouter($router);
        $this->passwordEncoder = $passwordEncoder;
        $this->flashBag = $flashBag;
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->tokenStorage = $tokenStorage;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * prise en main du processus d'inscription en fonction du mode de validation du compte.
     *
     * @var request
     * @var Utilisateur
     */
    public function handleRegistration(Request $request, Utilisateur $user): Response
    {
        //on enregistre le nouvel utilisateur
        $this->persistAndFlushUser($user);
        //on set le token de validation de l'user
        $user->setConfirmationToken($this->generateRandomPassword());
        $this->persistAndFlushUser($user);

        //On déclenche l'event, envoi du mail de validation de compte
        $this->dispatchEvent($user, RegistrationEvents::USER_REGISTERED_NEEDS_VALIDATION);

        //on redirige vers la page de connexion
        return new RedirectResponse($this->getRouter()->generate('security_login'));
    }

    /**
     * Processus d'activation d'un compte utlisateur, apres envoi du mail de demande d'activation de compte user.
     */
    public function handleValidationAcount(Utilisateur $user): RedirectResponse
    {
        //on active le compte utilisateur
        $this->validateAcount($user);

        return new RedirectResponse($this->getRouter()->generate('security_login'));
    }

    /**
     * validation de compte utilisateur.
     */
    public function validateAcount(Utilisateur $user): void
    {
        $user->setEnabled(true)->setConfirmationToken(null);
        $this->persistAndFlushUser($user);

        $this->dispatchEvent($user, RegistrationEvents::USER_ACOUNT_VALIDATED);
    }

    /**
     * Dispatcher générique qui dispatch un evenement donné.
     *
     * @param string $eventName nom de l'event à déclencher
     * @codeCoverageIgnore
     */
    public function dispatchEvent(Utilisateur $user, string $eventName): void
    {
        $event = new GenericEvent($user);
        //On déclenche l'event, envoi du mail de validation de compte
        $this->getEventDispatcher()->dispatch($event, $eventName);
    }

    /**
     * @param Utilisateur $user  utilisateur concerné par cette activation
     * @param string      $token token de validation associé à l'user
     */
    public function isValidateToken(Utilisateur $user, string $token): bool
    {
        return $user->getConfirmationToken() === $token && self::TOKEN_LENGTH === strlen($token);
    }

    /**
     * Fonction qui gère le message d"erreur du toek et redirige l'utilisateur.
     *
     * @var Utilisateur
     *
     * @param mixed $user
     */
    public function handleBadTokenValidation($user): RedirectResponse
    {
        //On déclenche l'event gérant la génération du message flash d'erreur
        $this->dispatchEvent($user, RegistrationEvents::BAD_VALIDATION_TOKEN);

        return new RedirectResponse($this->getRouter()->generate('UcaWeb_Accueil'));
    }

    /**
     * Get $em Entity Manager.
     *
     * @codeCoverageIgnore
     */
    public function getEm(): EntityManagerInterface
    {
        return $this->em;
    }

    /**
     * Set $em Entity Manager.
     *
     * @param EntityManagerInterface $em $em Entity Manager
     * @codeCoverageIgnore
     */
    public function setEm(EntityManagerInterface $em): self
    {
        $this->em = $em;

        return $this;
    }

    /**
     * Get $authenticator.
     *
     * @codeCoverageIgnore
     */
    public function getAuthenticator(): LoginFormAuthenticator
    {
        return $this->authenticator;
    }

    /**
     * Set $authenticator.
     *
     * @param loginFormAuthenticator $authenticator $authenticator
     * @codeCoverageIgnore
     */
    public function setAuthenticator(loginFormAuthenticator $authenticator): self
    {
        $this->authenticator = $authenticator;

        return $this;
    }

    /**
     * Get the value of eventDispatcher.
     *
     * @codeCoverageIgnore
     */
    public function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }

    /**
     * Set the value of eventDispatcher.
     *
     * @codeCoverageIgnore
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): self
    {
        $this->eventDispatcher = $eventDispatcher;

        return $this;
    }

    /**
     * Get $router.
     *
     * @codeCoverageIgnore
     */
    public function getRouter(): RouterInterface
    {
        return $this->router;
    }

    /**
     * Set $router.
     *
     * @param RouterInterface $router $router
     * @codeCoverageIgnore
     */
    public function setRouter(RouterInterface $router): self
    {
        $this->router = $router;

        return $this;
    }

    /**
     * Création d'un utilisateur depuis la partie Gestion.
     */
    public function createUser(Utilisateur $user): void
    {
        $plainPassword = $user->getPlainPassword();
        $user
            ->setPassword($this->passwordEncoder->hashPassword($user, $plainPassword))
            ->setConfirmationToken(rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '='))
            ->setEnabled(false)
        ;

        $this->em->persist($user);
        $this->em->flush();

        if ('test' !== $_ENV['APP_ENV']) {
            // @codeCoverageIgnoreStart
            $this->mailer->sendMailWithTemplate(
                $this->translator->trans('registration.email.subject', ['%username%' => $user->getUsername()]),
                $user->getEmail(),
                'UserBundle/Mail/EnregistrementUtilisateur.html.twig',
                ['user' => $user, 'plainPassword' => $plainPassword]
            );

            $this->flashBag->add('success', 'utilisateur.registration.success');
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Fonction qui permet de d'activer le compte utilisateur et le connecter.
     */
    public function confirmAccount(Request $request, Utilisateur $user): void
    {
        $user->setConfirmationToken(null)->setEnabled(true);
        $this->em->flush();

        $token = new UsernamePasswordToken($user, $user->getPassword(), 'main', $user->getRoles());
        $this->tokenStorage->setToken($token);
        $event = new InteractiveLoginEvent($request, $token);
        $this->eventDispatcher->dispatch($event);

        $this->flashBag->add('success', 'registration.confirm.success');
    }

    /**
     * genere un tokenValidation aléatoire à assigner à l'utilisateur.
     *
     * @return string token genéré
     */
    private function generateRandomPassword(): string
    {
        // Variable locale
        $result = '';

        // Génération d'un de 16 caractères
        for ($i = 0; $i < self::TOKEN_LENGTH; ++$i) {
            $result .= self::availableCharacters[rand(0, strlen(self::availableCharacters) - 1)];
        }

        return $result;
    }

    /**
     * Permet d'enregistrer ou de modifier un utlisateur en BDD.
     *
     * @codeCoverageIgnore
     */
    private function persistAndFlushUser(Utilisateur $user): void
    {
        $this->getEm()->persist($user);
        $this->getEm()->flush();
    }
}