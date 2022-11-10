<?php

namespace App\Tests\Service\Securite;

use App\Entity\Uca\Utilisateur;
use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use App\Service\Securite\LoginFormAuthenticator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

/**
 * @internal
 */
class LoginFormAuthenticatorTest extends WebTestCase
{
    // /**
    //  * @var LoginFormAuthenticator
    //  */
    // private $loginFormAuthenticator;

    // protected function setUp(): void
    // {
    // }

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
        $client = static::createClient();
        $loginFormAuthenticator = static::getContainer()->get(LoginFormAuthenticator::class);
        $client->request('POST', 'security_login', ['_username' => 'username', '_password' => 'password', '_csrf_token' => 'csrf_token']);

        $request = $client->getRequest();

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
            'username' => 'admin',
            'password' => $_ENV['ADMIN_PWD'],
            'csrf_token' => null,
        ];

        $loginFormAuthenticator = static::getContainer()->get(LoginFormAuthenticator::class);
        $user = static::getContainer()->get(UtilisateurRepository::class)->findOneByUsername('admin');

        $this->assertTrue($loginFormAuthenticator->checkCredentials($credentials, $user));
    }

    /**
     * @covers \App\Service\Securite\LoginFormAuthenticator::onAuthenticationSuccess
     */
    public function testOnAuthenticationSuccessWithRequestedRoute(): void
    {
        $client = static::createClient();
        $loginFormAuthenticator = static::getContainer()->get(LoginFormAuthenticator::class);
        $client->request('GET', static::getContainer()->get(RouterInterface::class)->generate('UcaWeb_MonPlanning'));

        $request = $client->getRequest();

        $token = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $redirection = $loginFormAuthenticator->onAuthenticationSuccess($request, $token, 'main');

        $this->assertInstanceOf(RedirectResponse::class, $redirection);
        $this->assertStringContainsString('/UcaWeb/MonPlanning/', $redirection->getTargetUrl());
    }

    /**
     * @covers \App\Service\Securite\LoginFormAuthenticator::onAuthenticationSuccess
     */
    public function testOnAuthenticationSuccessWithWrongRequestedRoute(): void
    {
        $client = static::createClient();
        $loginFormAuthenticator = static::getContainer()->get(LoginFormAuthenticator::class);
        $client->request('GET', static::getContainer()->get(RouterInterface::class)->generate('UcaWeb_Accueil'));

        $request = $client->getRequest();

        $token = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $redirection = $loginFormAuthenticator->onAuthenticationSuccess($request, $token, 'main');

        $this->assertInstanceOf(RedirectResponse::class, $redirection);
        $this->assertEquals('/fr/', $redirection->getTargetUrl());
    }

    /**
     * @covers \App\Service\Securite\LoginFormAuthenticator::getUser
     */
    public function testGetUser(): void
    {
        $client = static::createClient();
        $usersRepository = static::getContainer()->get(UtilisateurRepository::class);
        $testUserAdmin = $usersRepository->find(1);

        $client->loginUser($testUserAdmin);

        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('authenticate');

        $parametersRequest = [
            '_username' => 'admin',
            '_password' => 'Admin123*',
            '_csrf_token' => $csrfToken->getValue()
        ];
        $client->request('POST', 'security_login', $parametersRequest);
        $request = $client->getRequest();

        $container = static::getContainer();
        $loginFormAuthenticator = $container->get(LoginFormAuthenticator::class);

        $credentials = $loginFormAuthenticator->getCredentials($request);
        $userProvider = $this
            ->getMockBuilder('Symfony\Component\Security\Core\User\UserProviderInterface')
            ->getMock();

        $user = $loginFormAuthenticator->getUser($credentials, $userProvider);
        $this->assertInstanceOf(Utilisateur::class, $user);
    }

    /**
     * @covers \App\Service\Securite\LoginFormAuthenticator::getUser
     */
    public function testGetUserTokenIsNotValid(): void
    {
        $this->expectException(InvalidCsrfTokenException::class);

        $client = static::createClient();
        $client->request('POST', 'security_login', ['_username' => 'username', '_password' => 'password', '_csrf_token' => 'csrf_token']);
        $request = $client->getRequest();

        $container = static::getContainer();
        $loginFormAuthenticator = $container->get(LoginFormAuthenticator::class);

        // $user = $container->get(UtilisateurRepository::class)->findOneByUsername('admin');

        $credentials = $loginFormAuthenticator->getCredentials($request);
        $userProvider = $this
            ->getMockBuilder('Symfony\Component\Security\Core\User\UserProviderInterface')
            ->getMock();

        $user = $loginFormAuthenticator->getUser($credentials, $userProvider);
    }

    /**
     * @covers \App\Service\Securite\LoginFormAuthenticator::getUser
     */
    public function testGetUserNotFound(): void
    {
        $this->expectException(CustomUserMessageAuthenticationException::class);
        $client = static::createClient();
        $usersRepository = static::getContainer()->get(UtilisateurRepository::class);
        $testUserAdmin = $usersRepository->find(1);

        $client->loginUser($testUserAdmin);

        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('authenticate');

        $parametersRequest = [
            '_username' => 'WrongPseudoForTest',
            '_password' => 'Admin123*',
            '_csrf_token' => $csrfToken->getValue()
        ];
        $client->request('POST', 'security_login', $parametersRequest);
        $request = $client->getRequest();

        $container = static::getContainer();
        $loginFormAuthenticator = $container->get(LoginFormAuthenticator::class);

        $credentials = $loginFormAuthenticator->getCredentials($request);
        $userProvider = $this
            ->getMockBuilder('Symfony\Component\Security\Core\User\UserProviderInterface')
            ->getMock();

        $user = $loginFormAuthenticator->getUser($credentials, $userProvider);
    }

    /**
     * @covers \App\Service\Securite\LoginFormAuthenticator::getUser
     */
    public function testGetUserNotEnable(): void
    {
        $this->expectException(CustomUserMessageAuthenticationException::class);
        $client = static::createClient();
        $usersRepository = static::getContainer()->get(UtilisateurRepository::class);
        $testUserAdmin = $usersRepository->find(1);
        $testUserAdmin->setEnabled(false);

        $client->loginUser($testUserAdmin);

        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('authenticate');

        $parametersRequest = [
            '_username' => 'admin',
            '_password' => 'Admin123*',
            '_csrf_token' => $csrfToken->getValue()
        ];
        $client->request('POST', 'security_login', $parametersRequest);
        $request = $client->getRequest();

        $container = static::getContainer();
        $loginFormAuthenticator = $container->get(LoginFormAuthenticator::class);

        $credentials = $loginFormAuthenticator->getCredentials($request);
        $userProvider = $this
            ->getMockBuilder('Symfony\Component\Security\Core\User\UserProviderInterface')
            ->getMock();

        $user = $loginFormAuthenticator->getUser($credentials, $userProvider);
        $testUserAdmin->setEnabled(true);
    }
}
