<?php

namespace App\Tests\Service\Securite;

use App\Repository\UtilisateurRepository;
use App\Service\Securite\UtilisateurResetPassword;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 * @coversNothing
 */
class UtilisateurResetPasswordTest extends KernelTestCase
{
    /**
     * @covers \App\Service\Securite\UtilisateurResetPassword::__construct
     */
    public function testConstruct(): void
    {
        $service = static::getContainer()->get(UtilisateurResetPassword::class);

        $this->assertInstanceOf(UtilisateurResetPassword::class, $service);
    }

    /**
     * @covers \App\Service\Securite\UtilisateurResetPassword::sendInstructionResetPassword
     */
    public function testSendInstructionResetPassword(): void
    {
        $service = static::getContainer()->get(UtilisateurResetPassword::class);

        $return = $service->sendInstructionResetPassword('admin@uca.fr');

        $this->assertIsArray($return);
        $this->assertArrayHasKey('status', $return);
        $this->assertArrayHasKey('message', $return);
        $this->assertTrue($return['status']);
    }

    /**
     * @covers \App\Service\Securite\UtilisateurResetPassword::sendInstructionResetPassword
     */
    public function testSendInstructionResetPasswordUserNotFound(): void
    {
        $service = static::getContainer()->get(UtilisateurResetPassword::class);

        $return = $service->sendInstructionResetPassword('notfound');

        $this->assertIsArray($return);
        $this->assertArrayHasKey('status', $return);
        $this->assertArrayHasKey('message', $return);
        $this->assertFalse($return['status']);
    }

    /**
     * @covers \App\Service\Securite\UtilisateurResetPassword::sendInstructionResetPassword
     */
    public function testSendInstructionResetPasswordUserDisabled(): void
    {
        $service = static::getContainer()->get(UtilisateurResetPassword::class);
        $user = static::getContainer()->get(UtilisateurRepository::class)->findOneByUsername('admin');
        $user->setEnabled(false);
        (static::getContainer()->get(EntityManagerInterface::class))->flush();

        $return = $service->sendInstructionResetPassword('admin@uca.fr');

        $this->assertIsArray($return);
        $this->assertArrayHasKey('status', $return);
        $this->assertArrayHasKey('message', $return);
        $this->assertFalse($return['status']);

        $user->setEnabled(true);
        (static::getContainer()->get(EntityManagerInterface::class))->flush();
    }

    /**
     * @covers \App\Service\Securite\UtilisateurResetPassword::generateRandomPassword
     */
    public function testGenerateRandomPassword(): void
    {
        $service = static::getContainer()->get(UtilisateurResetPassword::class);
        $password = $service->generateRandomPassword();

        $this->assertIsString($password);
        $this->assertEquals(16, strlen($password));
    }

    /**
     * @covers \App\Service\Securite\UtilisateurResetPassword::isValidResetPassword
     */
    public function testIsValidResetPassword(): void
    {
        $service = static::getContainer()->get(UtilisateurResetPassword::class);
        $isValid = $service->isValidResetPassword('admin@uca.fr', 'Admin123*');

        $this->assertIsBool($isValid);
        $this->assertFalse($isValid);
    }

    /**
     * @covers \App\Service\Securite\UtilisateurResetPassword::isValidResetPassword
     */
    public function testIsValidResetPasswordUnknowUser(): void
    {
        $service = static::getContainer()->get(UtilisateurResetPassword::class);
        $isValid = $service->isValidResetPassword('user@test.fr', 'Admin123*');

        $this->assertIsBool($isValid);
        $this->assertFalse($isValid);
    }

    /**
     * @covers \App\Service\Securite\UtilisateurResetPassword::resetPassword
     */
    public function testResetPassword(): void
    {
        $service = static::getContainer()->get(UtilisateurResetPassword::class);
        $retour = $service->resetPassword('admin@uca.fr', 'Admin123*');

        $this->assertIsArray($retour);
        $this->assertArrayHasKey('status', $retour);
        $this->assertArrayHasKey('message', $retour);
        $this->assertEquals(true, $retour['status']);
    }

    /**
     * @covers \App\Service\Securite\UtilisateurResetPassword::resetPassword
     */
    public function testResetPasswordUnknowUser(): void
    {
        $service = static::getContainer()->get(UtilisateurResetPassword::class);
        $retour = $service->resetPassword('user@test.fr', 'Admin123*');

        $this->assertIsArray($retour);
        $this->assertArrayHasKey('status', $retour);
        $this->assertArrayHasKey('message', $retour);
        $this->assertEquals(false, $retour['status']);
    }

    /**
     * @covers \App\Service\Securite\UtilisateurResetPassword::passwordForgotten
     */
    public function testPasswordForgotten(): void
    {
        $service = static::getContainer()->get(UtilisateurResetPassword::class);
        $service->passwordForgotten('admin@uca.fr', 'Admin123*');

        $user = static::getContainer()->get(UtilisateurRepository::class)->findOneByUsername('admin');
        $this->assertNotNull($user->getConfirmationToken());
    }

    /**
     * @covers \App\Service\Securite\UtilisateurResetPassword::passwordForgotten
     */
    public function testPasswordForgottenUnknowUser(): void
    {
        $service = static::getContainer()->get(UtilisateurResetPassword::class);
        $service->passwordForgotten('user@test.fr', 'Admin123*');

        $this->assertTrue(true);
    }

    /**
     * @covers \App\Service\Securite\UtilisateurResetPassword::changePassword
     */
    public function testChangePassword(): void
    {
        $service = static::getContainer()->get(UtilisateurResetPassword::class);
        $user = static::getContainer()->get(UtilisateurRepository::class)->findOneByUsername('admin');
        $user->setPlainPassword('Admin123*');
        $redirect = $service->changePassword($user);

        $this->assertIsString($redirect);
    }
}