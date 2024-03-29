<?php

namespace App\Tests\Service\Securite;

use App\Entity\Uca\Utilisateur;
use App\Repository\UtilisateurRepository;
use App\Service\Securite\RegistrationHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 * @coversNothing
 */
class RegistrationHandlerTest extends KernelTestCase
{
    /**
     * @covers \App\Service\Securite\RegistrationHandler::__construct
     */
    public function testSomething(): void
    {
        $registrationHandler = static::getContainer()->get(RegistrationHandler::class);

        $this->assertInstanceOf(RegistrationHandler::class, $registrationHandler);
    }

    /**
     * @covers \App\Service\Securite\RegistrationHandler::generateRandomPassword
     * @covers \App\Service\Securite\RegistrationHandler::handleRegistration
     * @covers \App\Service\Securite\RegistrationHandler::persistAndFlushUser
     */
    public function testHandleRegistration(): void
    {
        $registrationHandler = static::getContainer()->get(RegistrationHandler::class);

        $response = $registrationHandler->handleRegistration(
            new Request(),
            (new Utilisateur())
                ->setUsername('userTmp')
                ->setEmail('usertmp@test.fr')
                ->setPassword('password')
        );

        $this->assertInstanceOf(RedirectResponse::class, $response);

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $user = static::getContainer()->get(UtilisateurRepository::class)->findOneByUsername('userTmp');
        $em->remove($user);
        $em->flush();
    }

    /**
     * @covers \App\Service\Securite\RegistrationHandler::handleValidationAcount
     */
    public function testHandleValidationAcount(): void
    {
        $registrationHandler = static::getContainer()->get(RegistrationHandler::class);

        $response = $registrationHandler->handleValidationAcount(
            (new Utilisateur())
                ->setUsername('userTmp')
                ->setEmail('usertmp@test.fr')
                ->setPassword('password')
        );

        $this->assertInstanceOf(RedirectResponse::class, $response);

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $user = static::getContainer()->get(UtilisateurRepository::class)->findOneByUsername('userTmp');
        $em->remove($user);
        $em->flush();
    }

    /**
     * @covers \App\Service\Securite\RegistrationHandler::validateAcount
     */
    public function testValidationAcount(): void
    {
        $registrationHandler = static::getContainer()->get(RegistrationHandler::class);
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $user = (new Utilisateur())
            ->setNom('admin')
            ->setPrenom('admin')
            ->setUsername('register')
            ->setSexe('M')
            ->setEmail('admin@test.fr')
            ->setPassword('password')
            ->setEnabled(true)
        ;
        $user->setEnabled(false)->setConfirmationToken('token');
        $em->persist($user);
        $em->flush();

        $registrationHandler->validateAcount($user);

        $this->assertTrue($user->isEnabled());
        $this->assertNull($user->getConfirmationToken());

        $em->remove($user);
        $em->flush();
    }

    /**
     * @covers \App\Service\Securite\RegistrationHandler::isValidateToken
     */
    public function testIsValidateToken(): void
    {
        $registrationHandler = static::getContainer()->get(RegistrationHandler::class);
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $token = '123456789abcdefg';

        $user = (new Utilisateur())
            ->setNom('admin')
            ->setPrenom('admin')
            ->setUsername('register')
            ->setSexe('M')
            ->setEmail('admin@test.fr')
            ->setEnabled(true)
            ->setPassword('password')
            ->setConfirmationToken($token)
        ;
        $em->persist($user);
        $em->flush();

        $this->assertTrue($registrationHandler->isValidateToken($user, $token));

        $em->remove($user);
        $em->flush();
    }

    /**
     * @covers \App\Service\Securite\RegistrationHandler::handleBadTokenValidation
     */
    public function testHandleBadTokenValidation(): void
    {
        $registrationHandler = static::getContainer()->get(RegistrationHandler::class);
        $em = static::getContainer()->get(EntityManagerInterface::class);
        
        $user = (new Utilisateur())
            ->setNom('admin')
            ->setPrenom('admin')
            ->setUsername('register')
            ->setSexe('M')
            ->setEmail('admin@test.fr')
            ->setPassword('password')
            ->setEnabled(true)
        ;
        $em->persist($user);
        $em->flush();

        $response = $registrationHandler->handleBadTokenValidation($user);

        $this->assertInstanceOf(RedirectResponse::class, $response);

        $em->remove($user);
        $em->flush();
    }

    /**
     * @covers \App\Service\Securite\RegistrationHandler::createUser
     */
    public function testCreateUser(): void
    {
        $registrationHandler = static::getContainer()->get(RegistrationHandler::class);

        $user = (new Utilisateur())
            ->setUsername('userTmpCreateUser')
            ->setEmail('usertmp@test.fr')
            ->setPassword('password')
            ->setPlainPassword('password')
        ;

        $registrationHandler->createUser($user);

        $this->assertNotNull($user->getId());

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->remove($user);
        $em->flush();
    }

    /**
     * @covers \App\Service\Securite\RegistrationHandler::confirmAccount
     */
    public function testConfirmAccount(): void
    {
        $registrationHandler = static::getContainer()->get(RegistrationHandler::class);
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $user = (new Utilisateur())
            ->setNom('admin')
            ->setPrenom('admin')
            ->setUsername('register')
            ->setPassword('password')
            ->setSexe('M')
            ->setEmail('admin@test.fr')
            ->setConfirmationToken('token')
            ->setEnabled(false)
        ;
        $em->persist($user);
        $em->flush();

        $registrationHandler->confirmAccount(new Request(), $user);

        $this->assertNull($user->getConfirmationToken());
        $this->assertTrue($user->isEnabled());

        $em->remove($user);
        $em->flush();
    }
}