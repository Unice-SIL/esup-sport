<?php

namespace App\Service\Securite;

use App\Entity\Uca\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

/**
 * Class LoginFormAuthenticator.
 */
class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var CsrfTokenManagerInterface
     */
    private $csrfTokenManager;
    /**
     * @var UserPasswordHasherInterface
     */
    private $passwordEncoder;
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    /**
     * @var FlashBagInterface
     */
    private $flashBag;

    /**
     * LoginFormAuthenticator constructor.
     */
    public function __construct(EntityManagerInterface $entityManager, RouterInterface $router, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordHasherInterface $passwordEncoder, ParameterBagInterface $parameterBag, FlashBagInterface $flashBag)
    {
        $this->setEntityManager($entityManager);
        $this->setRouter($router);
        $this->setCsrfTokenManager($csrfTokenManager);
        $this->setPasswordEncoder($passwordEncoder);
        $this->setParameterBag($parameterBag);
        $this->setFlashBag($flashBag);
    }

    /**
     * @return bool
     */
    public function supports(Request $request)
    {
        return 'security_login' === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    /**
     * @return array|mixed
     */
    public function getCredentials(Request $request)
    {
        $credentials = [
            'username' => $request->request->get('_username'),
            'password' => $request->request->get('_password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];
        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['username']
        );

        return $credentials;
    }

    /**
     * @param mixed $credentials
     *
     * @return null|object|UserInterface|Utilisateur
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->getCsrfTokenManager()->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        $user = $this->getEntityManager()->getRepository(Utilisateur::class)->findOneBy(['username' => $credentials['username']]);

        if (!$user) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Username could not be found.');
        }
        if (!$user->isEnabled()) {
            throw new CustomUserMessageAuthenticationException('app.login.message.error.not_activated_acount');
        }

        return $user;
    }

    /**
     * @param mixed $credentials
     *
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        // Pas de acatus en mot de passe en prod
        // if ('prod' !== $this->getParameterBag()->get('environment')) {
        //     return $this->getPasswordEncoder()->isPasswordValid($user, $credentials['password']) || 'acatus' === $credentials['password'];
        // }

        return $this->getPasswordEncoder()->isPasswordValid($user, $credentials['password']);
    }

    /**
     * @param string $providerKey
     *
     * @return null|RedirectResponse|Response
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        //exemple pour setter des cookie lors d'une connexion
        // $cookieUsername = new Cookie('refonte_username', $token->getUser()->getUsername(), time() + 14400, '/');

        //si une page cible avait été demandée, on redirige vers cette page
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }
        //sinon on redirige vers la page d'accueil
        return new RedirectResponse($this->getRouter()->generate('UcaWeb_Accueil'));
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function getLoginUrl()
    {
        return $this->getRouter()->generate('security_login');
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
     * @codeCoverageIgnore
     */
    public function getRouter(): RouterInterface
    {
        return $this->router;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setRouter(RouterInterface $router): void
    {
        $this->router = $router;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getCsrfTokenManager(): CsrfTokenManagerInterface
    {
        return $this->csrfTokenManager;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setCsrfTokenManager(CsrfTokenManagerInterface $csrfTokenManager): void
    {
        $this->csrfTokenManager = $csrfTokenManager;
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
    public function getParameterBag(): ParameterBagInterface
    {
        return $this->parameterBag;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setParameterBag(ParameterBagInterface $parameterBag): void
    {
        $this->parameterBag = $parameterBag;
    }

    /**
     * Get the value of flashBag.
     *
     * @return FlashBagInterface
     * @codeCoverageIgnore
     */
    public function getFlashBag()
    {
        return $this->flashBag;
    }

    /**
     * Set the value of flashBag.
     *
     * @return self
     * @codeCoverageIgnore
     */
    public function setFlashBag(FlashBagInterface $flashBag)
    {
        $this->flashBag = $flashBag;

        return $this;
    }
}