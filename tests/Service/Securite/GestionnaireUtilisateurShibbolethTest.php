<?php

namespace App\Tests\Service\Securite;

use App\Entity\Uca\Utilisateur;
use App\Exception\ShibbolethException;
use App\Repository\UtilisateurRepository;
use App\Service\Securite\GestionnaireUtilisateurShibboleth;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @internal
 * @coversNothing
 */
class GestionnaireUtilisateurShibbolethTest extends KernelTestCase
{
    /**
     * @var GestionnaireUtilisateurShibboleth
     */
    private $gestionnaire;

    protected function setUp(): void
    {
        $session = static::getContainer()->get('session.factory')->createSession();
        $request = new Request();
        $request->setSession($session);
        static::getContainer()->get(RequestStack::class)->push($request);
        $this->gestionnaire = static::getContainer()->get(GestionnaireUtilisateurShibboleth::class);
    }

    /**
     * @covers \App\Service\Securite\GestionnaireUtilisateurShibboleth::__construct
     */
    public function testConstruct(): void
    {
        $container = static::getContainer();

        $gestionnaire = new GestionnaireUtilisateurShibboleth(
            $container->get(EntityManagerInterface::class),
            $container->get(UtilisateurRepository::class),
            $container->get(RequestStack::class)
        );

        $this->assertInstanceOf(GestionnaireUtilisateurShibboleth::class, $gestionnaire);
    }

    /**
     * @covers \App\Service\Securite\GestionnaireUtilisateurShibboleth::isFirstConnection
     */
    public function testIsFirstConnection(): void
    {
        $this->assertFalse($this->gestionnaire->isFirstConnection());
    }

    /**
     * @covers \App\Service\Securite\GestionnaireUtilisateurShibboleth::refreshUser
     */
    public function testRefreshUser(): void
    {
        $user = new Utilisateur();
        $refreshedUser = $this->gestionnaire->refreshUser($user);

        $this->assertInstanceOf(Utilisateur::class, $refreshedUser);
        $this->assertEquals($user, $refreshedUser);
    }

    public function loadUserDataProvider()
    {
        return [
            [[
                'eppn' => 'test-1234',
                'mail' => 'user1234@test.fr',
                'eduPersonAffiliation' => 'student',
                'uid' => '1234',
                'sn' => 'User1234',
                'givenName' => 'User1234',
                'mifare' => '1234',
                'ptdrouv' => '0',
            ]],
            [[
                'eppn' => 'test-1234',
                'mail' => 'user1234@test.fr',
                'eduPersonAffiliation' => 'student',
                'uid' => '1234',
                'sn' => 'User1234',
                'givenName' => 'User1234',
                'mifare' => '1234',
                'ptdrouv' => '0',
            ]],
            [[
                'eppn' => 'test-4321',
                'mail' => 'user1234@test.fr',
                'eduPersonAffiliation' => 'employee',
                'uid' => '4321',
                'sn' => 'User4321',
                'givenName' => 'User4321',
                'mifare' => '4321',
                'ptdrouv' => '0',
            ]],
        ];
    }

    /**
     * @dataProvider loadUserDataProvider
     *
     * @covers \App\Service\Securite\GestionnaireUtilisateurShibboleth::loadUser
     *
     * @param mixed $credentials
     */
    public function testLoadUser($credentials): void
    {
        $user = $this->gestionnaire->loadUser($credentials);

        $this->assertInstanceOf(Utilisateur::class, $user);
        $this->assertNotNull($user->getId());
        $this->assertTrue($user->getShibboleth());
    }

    public function loadUserExceptionDataProvider()
    {
        return [
            [[
                'eppn' => 'test-4321',
                'mail' => 'user1234@test.fr',
                'eduPersonAffiliation' => 'student;employee;researcher;member',
                'uid' => '4321',
                'sn' => 'User4321',
                'givenName' => 'User4321',
                'mifare' => '4321',
                'ptdrouv' => '0',
            ]],
            [[
                'eppn' => 'test-4321',
                'mail' => 'user1234@test.fr',
                'eduPersonAffiliation' => 'student',
                'uid' => '4321',
                'sn' => 'User4321',
                'givenName' => 'User4321',
                'mifare' => '4321',
                'ptdrouv' => '1',
            ]],
            [[
                'eppn' => 'test-4321',
                'mail' => 'user1234@test.fr',
                'eduPersonAffiliation' => 'plop',
                'uid' => '4321',
                'sn' => 'User4321',
                'givenName' => 'User4321',
                'mifare' => '4321',
                'ptdrouv' => '0',
            ]],
        ];
    }

    /**
     * @dataProvider loadUserExceptionDataProvider
     *
     * @covers \App\Service\Securite\GestionnaireUtilisateurShibboleth::loadUser
     *
     * @param mixed $credentials
     */
    public function testLoadUserException($credentials): void
    {
        $this->expectException(ShibbolethException::class);
        $user = $this->gestionnaire->loadUser($credentials);
    }

    /**
     * @covers \App\Service\Securite\GestionnaireUtilisateurShibboleth::loadUser
     */
    public function testLoadUserExceptionUserDisabled(): void
    {
        $user = (new Utilisateur())
            ->setEnabled(false)
            ->setUsername('test-4321')
            ->setEmail('user1234@test.fr')
            ->setPassword('paassword')
        ;
        static::getContainer()->get(EntityManagerInterface::class)->persist($user);
        static::getContainer()->get(EntityManagerInterface::class)->flush();

        $this->expectException(ShibbolethException::class);

        $credentials = [
            'eppn' => 'test-4321',
            'mail' => 'user1234@test.fr',
            'eduPersonAffiliation' => 'employee',
            'uid' => '4321',
            'sn' => 'User4321',
            'givenName' => 'User4321',
            'mifare' => '4321',
            'ptdrouv' => '1',
        ];

        $user = $this->gestionnaire->loadUser($credentials);
    }

}
