<?php

namespace App\Tests\Service\Securite;

use App\Entity\Uca\Utilisateur;
use App\Exception\ShibbolethException;
use App\Repository\UtilisateurRepository;
use App\Service\Securite\GestionnaireUtilisateurShibboleth;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

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
            $container->get(UtilisateurRepository::class)
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
                'eppn' => '1234',
                'mail' => 'user1234@test.fr',
                'eduPersonAffiliation' => 'student',
                'uid' => '1234',
                'sn' => 'User1234',
                'givenName' => 'User1234',
                'mifare' => '1234',
                'ptdrouv' => '0',
            ]],
            [[
                'eppn' => '1234',
                'mail' => 'user1234@test.fr',
                'eduPersonAffiliation' => 'student',
                'uid' => '1234',
                'sn' => 'User1234',
                'givenName' => 'User1234',
                'mifare' => '1234',
                'ptdrouv' => '0',
            ]],
            [[
                'eppn' => '4321',
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
                'eppn' => '4321',
                'mail' => 'user1234@test.fr',
                'eduPersonAffiliation' => 'student;employee;researcher;member',
                'uid' => '4321',
                'sn' => 'User4321',
                'givenName' => 'User4321',
                'mifare' => '4321',
                'ptdrouv' => '0',
            ]],
            [[
                'eppn' => '4321',
                'mail' => 'user1234@test.fr',
                'eduPersonAffiliation' => 'student',
                'uid' => '4321',
                'sn' => 'User4321',
                'givenName' => 'User4321',
                'mifare' => '4321',
                'ptdrouv' => '1',
            ]],
            [[
                'eppn' => '4321',
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
        $user = static::getContainer()->get(UtilisateurRepository::class)->findOneByUsername('4321');
        $user->setEnabled(false);
        static::getContainer()->get(EntityManagerInterface::class)->flush();

        $this->expectException(ShibbolethException::class);

        $credentials = [
            'eppn' => '4321',
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

    public function testSuppressionUtilisateur(): void
    {
        $user = static::getContainer()->get(UtilisateurRepository::class)->findOneByUsername('4321');
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->remove($user);
        $em->flush();

        $this->assertTrue(true);
    }
}
