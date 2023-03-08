<?php

namespace App\Tests\Service\Securite;

use App\Entity\Uca\Utilisateur;
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
    protected function setUp(): void
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $user = (new Utilisateur())
            ->setEmail('UtilisateurResetPasswordTest@test.fr')
            ->setUsername('UtilisateurResetPasswordTest')
            ->setPassword('password')
            ->setCgvAcceptees(0)
            ->setRoles([])
            ->setEnabled(true)
        ;
        $em->persist($user);
        $em->flush();
    }

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

        $return = $service->sendInstructionResetPassword('UtilisateurResetPasswordTest@test.fr');

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
        $user = static::getContainer()->get(UtilisateurRepository::class)->findOneByUsername('UtilisateurResetPasswordTest');
        $user->setEnabled(false);
        (static::getContainer()->get(EntityManagerInterface::class))->flush();

        $return = $service->sendInstructionResetPassword('UtilisateurResetPasswordTest@test.fr');

        $this->assertIsArray($return);
        $this->assertArrayHasKey('status', $return);
        $this->assertArrayHasKey('message', $return);
        $this->assertFalse($return['status']);

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
        $isValid = $service->isValidResetPassword('UtilisateurResetPasswordTest@test.fr', 'Admin123*');

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
        $retour = $service->resetPassword('UtilisateurResetPasswordTest@test.fr', 'Admin123*');

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
        $service->passwordForgotten('UtilisateurResetPasswordTest@test.fr', 'Admin123*');

        $user = static::getContainer()->get(UtilisateurRepository::class)->findOneByUsername('UtilisateurResetPasswordTest');
        $this->assertNotNull($user->getConfirmationToken());
        $this->assertEmailCount(1);
    }

    // Cas non possible a tester car il n'y a que des actions dans le flashbag
    // /**
    //  * @covers \App\Service\Securite\UtilisateurResetPassword::passwordForgotten
    //  */
    // public function testPasswordForgottenUnknowUser(): void
    // {
    //     $service = static::getContainer()->get(UtilisateurResetPassword::class);
    //     $service->passwordForgotten('user@test.fr', 'Admin123*');

    //     $this->assertTrue(true);
    // }

    /**
     * @covers \App\Service\Securite\UtilisateurResetPassword::changePassword
     */
    public function testChangePassword(): void
    {
        $service = static::getContainer()->get(UtilisateurResetPassword::class);
        $user = static::getContainer()->get(UtilisateurRepository::class)->findOneByUsername('UtilisateurResetPasswordTest');
        $user->setPlainPassword('Admin123*');
        $redirect = $service->changePassword($user);

        $this->assertIsString($redirect);
    }
}
