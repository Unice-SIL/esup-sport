<?php

namespace App\Tests\Service\Securite;

use App\Entity\Uca\Utilisateur;
use App\Service\Securite\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;

/**
 * @internal
 *
 * @coversNothing
 */
class LoginFormAuthenticatorTest extends WebTestCase
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    private $client;

    private $user;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $this->user = (new Utilisateur())
            ->setNom('in')
            ->setPrenom('log')
            ->setUsername('login-test')
            ->setSexe('M')
            ->setEmail('login@test.fr')
            ->setEnabled(true)
        ;
        $this->user->setPassword($hasher->hashPassword($this->user, $_ENV['ADMIN_PWD']))
        ;
        $this->em->persist($this->user);
        $this->em->flush();
    }

    /**
     * @covers \App\Service\Securite\LoginFormAuthenticator::__construct
     */
    public function testConstruct(): void
    {
        $loginFormAuthenticator = static::getContainer()->get(LoginFormAuthenticator::class);
        $this->assertInstanceOf(LoginFormAuthenticator::class, $loginFormAuthenticator);
    }

    /**
     * @covers \App\Service\Securite\LoginFormAuthenticator::supports
     */
    public function testSupports(): void
    {
        $loginFormAuthenticator = static::getContainer()->get(LoginFormAuthenticator::class);
        $request = Request::create('security_login', 'POST');
        $request->attributes->set('_route', 'security_login');

        $this->assertTrue($loginFormAuthenticator->supports($request));
    }

    /**
     * @covers \App\Service\Securite\LoginFormAuthenticator::getCredentials
     */
    public function testGetCredentials(): void
    {
        $loginFormAuthenticator = static::getContainer()->get(LoginFormAuthenticator::class);
        $this->client->request('POST', 'security_login', ['_username' => 'username', '_password' => 'password', '_csrf_token' => 'csrf_token']);

        $request = $this->client->getRequest();

        $credentials = $loginFormAuthenticator->getCredentials($request);

        $this->assertIsArray($credentials);
        $this->assertArrayHasKey('username', $credentials);
        $this->assertArrayHasKey('password', $credentials);
        $this->assertArrayHasKey('csrf_token', $credentials);
    }

    /**
     * @covers \App\Service\Securite\LoginFormAuthenticator::checkCredentials
     */
    public function testCheckCredentials(): void
    {
        $credentials = [
            'username' => 'login-test',
            'password' => $_ENV['ADMIN_PWD'],
            'csrf_token' => null,
        ];

        $loginFormAuthenticator = static::getContainer()->get(LoginFormAuthenticator::class);

        $this->assertTrue($loginFormAuthenticator->checkCredentials($credentials, $this->user));
    }

    /**
     * @covers \App\Service\Securite\LoginFormAuthenticator::onAuthenticationSuccess
     */
    public function testOnAuthenticationSuccessWithRequestedRoute(): void
    {
        $loginFormAuthenticator = static::getContainer()->get(LoginFormAuthenticator::class);
        $this->client->request('GET', static::getContainer()->get(RouterInterface::class)->generate('UcaWeb_MonPlanning'));

        $request = $this->client->getRequest();

        $token = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $redirection = $loginFormAuthenticator->onAuthenticationSuccess($request, $token, 'main');

        $this->assertInstanceOf(RedirectResponse::class, $redirection);
        $this->assertStringContainsString('/UcaWeb/MonPlanning/', $redirection->getTargetUrl());
    }

    /**
     * @covers \App\Service\Securite\LoginFormAuthenticator::onAuthenticationSuccess
     */
    public function testOnAuthenticationSuccessWithWrongRequestedRoute(): void
    {
        $loginFormAuthenticator = static::getContainer()->get(LoginFormAuthenticator::class);
        $this->client->request('GET', static::getContainer()->get(RouterInterface::class)->generate('UcaWeb_Accueil'));

        $request = $this->client->getRequest();

        $token = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $redirection = $loginFormAuthenticator->onAuthenticationSuccess($request, $token, 'main');

        $this->assertInstanceOf(RedirectResponse::class, $redirection);
        $this->assertEquals('/fr/', $redirection->getTargetUrl());
    }

    /**
     * @covers \App\Service\Securite\LoginFormAuthenticator::getUser
     */
    public function testGetUser(): void
    {
        $this->client->loginUser($this->user, 'app');

        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('authenticate');

        $parametersRequest = [
            '_username' => 'login-test',
            '_password' => 'Admin123*',
            '_csrf_token' => $csrfToken->getValue(),
        ];
        $this->client->request('POST', 'security_login', $parametersRequest);
        $request = $this->client->getRequest();

        $container = static::getContainer();
        $loginFormAuthenticator = $container->get(LoginFormAuthenticator::class);

        $credentials = $loginFormAuthenticator->getCredentials($request);
        $userProvider = $this
            ->getMockBuilder('Symfony\Component\Security\Core\User\UserProviderInterface')
            ->getMock()
        ;

        $user = $loginFormAuthenticator->getUser($credentials, $userProvider);
        $this->assertInstanceOf(Utilisateur::class, $user);
    }

    /**
     * @covers \App\Service\Securite\LoginFormAuthenticator::getUser
     */
    public function testGetUserTokenIsNotValid(): void
    {
        $this->expectException(InvalidCsrfTokenException::class);

        $this->client->request('POST', 'security_login', ['_username' => 'username', '_password' => 'password', '_csrf_token' => 'csrf_token']);
        $request = $this->client->getRequest();

        $container = static::getContainer();
        $loginFormAuthenticator = $container->get(LoginFormAuthenticator::class);

        // $user = $container->get(UtilisateurRepository::class)->findOneByUsername('admin');

        $credentials = $loginFormAuthenticator->getCredentials($request);
        $userProvider = $this
            ->getMockBuilder('Symfony\Component\Security\Core\User\UserProviderInterface')
            ->getMock()
        ;

        $user = $loginFormAuthenticator->getUser($credentials, $userProvider);
    }

    /**
     * @covers \App\Service\Securite\LoginFormAuthenticator::getUser
     */
    public function testGetUserNotFound(): void
    {
        $this->expectException(CustomUserMessageAuthenticationException::class);

        $this->client->loginUser($this->user, 'app');

        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('authenticate');

        $parametersRequest = [
            '_username' => 'WrongPseudoForTest',
            '_password' => 'Admin123*',
            '_csrf_token' => $csrfToken->getValue(),
        ];
        $this->client->request('POST', 'security_login', $parametersRequest);
        $request = $this->client->getRequest();

        $container = static::getContainer();
        $loginFormAuthenticator = $container->get(LoginFormAuthenticator::class);

        $credentials = $loginFormAuthenticator->getCredentials($request);
        $userProvider = $this
            ->getMockBuilder('Symfony\Component\Security\Core\User\UserProviderInterface')
            ->getMock()
        ;

        $user = $loginFormAuthenticator->getUser($credentials, $userProvider);
    }

    /**
     * @covers \App\Service\Securite\LoginFormAuthenticator::getUser
     */
    public function testGetUserNotEnable(): void
    {
        $this->expectException(CustomUserMessageAuthenticationException::class);
        $this->user->setEnabled(false);
        $this->em->flush();
        $this->client->loginUser($this->user, 'app');

        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('authenticate');

        $parametersRequest = [
            '_username' => 'login-test',
            '_password' => 'Admin123*',
            '_csrf_token' => $csrfToken->getValue(),
        ];
        $this->client->request('POST', 'security_login', $parametersRequest);
        $request = $this->client->getRequest();

        $container = static::getContainer();
        $loginFormAuthenticator = $container->get(LoginFormAuthenticator::class);

        $credentials = $loginFormAuthenticator->getCredentials($request);
        $userProvider = $this
            ->getMockBuilder('Symfony\Component\Security\Core\User\UserProviderInterface')
            ->getMock()
        ;
        $user = $loginFormAuthenticator->getUser($credentials, $userProvider);
    }
}
