<?php

namespace App\Tests\Service\Securite;

use App\Entity\Uca\Utilisateur;
use App\Service\Securite\UserChecker;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @internal
 * @coversNothing
 */
class UserCheckerTest extends KernelTestCase
{
    /**
     * @covers \App\Service\Securite\UserChecker::__construct
     */
    public function testConstruct(): void
    {
        $userChecker = new UserChecker(
            static::getContainer()->get(FlashBagInterface::class),
            static::getContainer()->get(RouterInterface::class)
        );

        $this->assertInstanceOf(UserChecker::class, $userChecker);
    }

    /**
     * @covers \App\Service\Securite\UserChecker::checkPreAuth
     */
    public function testCheckPreAuth(): void
    {
        $userChecker = static::getContainer()->get(UserChecker::class);
        $userChecker->checkPreAuth(
            (new Utilisateur())->setRoles(['ROLE_SUPER_ADMIN'])
        );

        $this->assertTrue(true);
    }

    /**
     * @covers \App\Service\Securite\UserChecker::checkPreAuth
     */
    public function testCheckPreAuthUserBloque(): void
    {
        $userChecker = static::getContainer()->get(UserChecker::class);

        $this->expectException(CustomUserMessageAuthenticationException::class);

        $userChecker->checkPreAuth(
            (new Utilisateur())->setRoles(['ROLE_SUPER_ADMIN', 'ROLE_BLOQUE'])
        );
    }

    /**
     * @covers \App\Service\Securite\UserChecker::checkPreAuth
     */
    public function testCheckPreAuthUserBadInstance(): void
    {
        $userChecker = static::getContainer()->get(UserChecker::class);

        $this->assertNull($userChecker->checkPreAuth((new UserTest())));
    }

    /**
     * @covers \App\Service\Securite\UserChecker::checkPostAuth
     */
    public function testCheckPostAuth(): void
    {
        $userChecker = static::getContainer()->get(UserChecker::class);
        $userChecker->checkPostAuth(
            (new Utilisateur())->setRoles(['ROLE_SUPER_ADMIN'])
        );

        $this->assertTrue(true);
    }

    /**
     * @covers \App\Service\Securite\UserChecker::checkPostAuth
     */
    public function testCheckPostAuthUserBloque(): void
    {
        $userChecker = static::getContainer()->get(UserChecker::class);

        $this->expectException(CustomUserMessageAuthenticationException::class);

        $userChecker->checkPostAuth(
            (new Utilisateur())->setRoles(['ROLE_SUPER_ADMIN', 'ROLE_BLOQUE'])
        );
    }

    /**
     * @covers \App\Service\Securite\UserChecker::checkPostAuth
     */
    public function testCheckPostAuthUserBadInstance(): void
    {
        $userChecker = static::getContainer()->get(UserChecker::class);

        $this->assertNull($userChecker->checkPostAuth((new UserTest())));
    }
}

/**
 * Classe utilisée pour pouvoir tester la mauvaise instance qui implémente l'interface UserInterface.
 *
 * @internal
 * @coversNothing
 */
class UserTest implements UserInterface
{
    public function getUserIdentifier()
    {
        return '';
    }

    public function getRoles()
    {
        return [];
    }

    public function getSalt()
    {
        return '';
    }

    public function getPassword()
    {
        return '';
    }

    public function getUsername()
    {
        return '';
    }

    public function eraseCredentials()
    {
    }
}