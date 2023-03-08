<?php

namespace App\Tests\Controller\Security;

use App\Entity\Uca\Utilisateur;
use App\Repository\ProfilUtilisateurRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * @internal
 *
 * @coversNothing
 */
class SecurityControllerTest extends WebTestCase
{
    private $client;

    private $router;

    private $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->router = static::getContainer()->get(RouterInterface::class);
        $profil_user = static::getContainer()->get(ProfilUtilisateurRepository::class)->findOneById(4);
        $passwordEncoder = static::getContainer()->get(UserPasswordHasherInterface::class);

        $this->user = (new Utilisateur())
            ->setUsername('SecurityControllerTest')
            ->setNom('Nom')
            ->setPrenom('Prénom')
            ->setEmail('SecurityControllerTest@test.fr')
            ->setPlainPassword('password')
            ->setCgvAcceptees(true)
            ->setEnabled(true)
            ->setConfirmationToken('the_token')
            ->setProfil($profil_user)
            ->setSexe('M')
        ;
        $this->user->setPassword($passwordEncoder->hashPassword($this->user, $this->user->getPlainPassword()));
        $this->user_autre = (new Utilisateur())
            ->setUsername('SecurityControllerTest_autre')
            ->setEmail('SecurityControllerTest_autre@test.fr')
            ->setPassword('password')
            ->setCgvAcceptees(true)
            ->setEnabled(true)
            ->setConfirmationToken('xxx')
        ;

        $this->em->persist($this->user);
        $this->em->persist($this->user_autre);
        $this->em->flush();
    }

    /**
     * Data provider pour le controle des acces au differentes routes.
     */
    public function controlAccessDataProvider()
    {
        return [
            [null, 'GET', 'security_login', Response::HTTP_OK, []],
            ['SecurityControllerTest@test.fr', 'GET', 'security_login', Response::HTTP_FOUND, [], 'UcaWeb_Accueil'],

            [null, 'GET', 'security_change_password', Response::HTTP_NOT_FOUND, ['id' => null, 'token' => null]],
            [null, 'GET', 'security_change_password', Response::HTTP_NOT_FOUND, ['id' => null, 'token' => 'the_token']],
            [null, 'GET', 'security_change_password', Response::HTTP_NOT_FOUND, ['id' => null, 'token' => 'autre_incorrect']],
            [null, 'GET', 'security_change_password', Response::HTTP_FOUND, ['id' => 'user_id', 'token' => null], 'UcaWeb_Accueil'],
            [null, 'GET', 'security_change_password', Response::HTTP_OK, ['id' => 'user_id', 'token' => 'the_token']],
            [null, 'GET', 'security_change_password', Response::HTTP_FOUND, ['id' => 'user_id', 'token' => 'autre_incorrect'], 'UcaWeb_Accueil'],
            [null, 'GET', 'security_change_password', Response::HTTP_FOUND, ['id' => 'autre_user_id', 'token' => null], 'UcaWeb_Accueil'],
            [null, 'GET', 'security_change_password', Response::HTTP_FOUND, ['id' => 'autre_user_id', 'token' => 'the_token'], 'UcaWeb_Accueil'],
            [null, 'GET', 'security_change_password', Response::HTTP_FOUND, ['id' => 'autre_user_id', 'token' => 'autre_incorrect'], 'UcaWeb_Accueil'],

            ['SecurityControllerTest@test.fr', 'GET', 'security_change_password', Response::HTTP_NOT_FOUND, ['id' => null, 'token' => null]],
            ['SecurityControllerTest@test.fr', 'GET', 'security_change_password', Response::HTTP_NOT_FOUND, ['id' => null, 'token' => 'the_token']],
            ['SecurityControllerTest@test.fr', 'GET', 'security_change_password', Response::HTTP_NOT_FOUND, ['id' => null, 'token' => 'autre_incorrect']],
            ['SecurityControllerTest@test.fr', 'GET', 'security_change_password', Response::HTTP_OK, ['id' => 'user_id', 'token' => null]],
            ['SecurityControllerTest@test.fr', 'GET', 'security_change_password', Response::HTTP_FOUND, ['id' => 'user_id', 'token' => 'the_token'], 'UcaWeb_Accueil'],
            ['SecurityControllerTest@test.fr', 'GET', 'security_change_password', Response::HTTP_FOUND, ['id' => 'user_id', 'token' => 'autre_incorrect'], 'UcaWeb_Accueil'],
            ['SecurityControllerTest@test.fr', 'GET', 'security_change_password', Response::HTTP_FOUND, ['id' => 'autre_user_id', 'token' => null], 'UcaWeb_Accueil'],
            ['SecurityControllerTest@test.fr', 'GET', 'security_change_password', Response::HTTP_FOUND, ['id' => 'autre_user_id', 'token' => 'the_token'], 'UcaWeb_Accueil'],
            ['SecurityControllerTest@test.fr', 'GET', 'security_change_password', Response::HTTP_FOUND, ['id' => 'autre_user_id', 'token' => 'autre_incorrect'], 'UcaWeb_Accueil'],

            [null, 'GET', 'security_password_forgotten', Response::HTTP_OK, []],
            ['SecurityControllerTest@test.fr', 'GET', 'security_password_forgotten', Response::HTTP_FOUND, [], 'UcaWeb_Accueil'],

            [null, 'GET', 'security_confirm_account', Response::HTTP_FOUND, ['id' => 'user_id', 'token' => 'the_token'], 'UcaWeb_CGV'],
            [null, 'GET', 'security_confirm_account', Response::HTTP_FOUND, ['id' => 'user_id', 'token' => 'autre_incorrect'], 'security_login'],
            [null, 'GET', 'security_confirm_account', Response::HTTP_FOUND, ['id' => 'autre_user_id', 'token' => 'the_token'], 'security_login'],
            ['SecurityControllerTest@test.fr', 'GET', 'security_confirm_account', Response::HTTP_FOUND, ['id' => 'user_id', 'token' => 'the_token'], 'UcaWeb_Accueil'],
        ];
    }

    /**
     * @dataProvider controlAccessDataProvider
     *
     * @covers \App\Controller\Security\SecurityController::changePassword
     * @covers \App\Controller\Security\SecurityController::confirmAccount
     * @covers \App\Controller\Security\SecurityController::login
     * @covers \App\Controller\Security\SecurityController::passwordForgotten
     *
     * @param mixed      $userEmail
     * @param mixed      $route
     * @param mixed      $httpResponse
     * @param mixed      $method
     * @param null|mixed $id
     * @param null|mixed $urlParameters
     * @param mixed      $expectedRedirectionRoute
     */
    public function testAccesRoutes($userEmail, $method, $route, $httpResponse, $urlParameters, $expectedRedirectionRoute = null): void
    {
        if (null != $userEmail) {
            $userTest = static::getContainer()->get(UtilisateurRepository::class)->findOneByEmail($userEmail);
            $this->client->loginUser($userTest, 'app');
        }

        $route = $this->router->generate($route, $urlParameters);
        $route = str_replace('autre_user_id', $this->user_autre->getId(), $route);
        $route = str_replace('user_id', $this->user->getId(), $route);

        $this->client->request($method, $route);
        $this->assertResponseStatusCodeSame($httpResponse);

        if (null != $expectedRedirectionRoute) {
            $expectedRedirection = $this->router->generate($expectedRedirectionRoute);
            $this->assertResponseRedirects($expectedRedirection);
        }
    }

    /**
     * Data provider pour le formulaire de changement de mot de passe.
     */
    public function formulaireChangePasswordDataProvider()
    {
        return [
            [false, ['plainPassword' => ['first' => 'plop', 'second' => 'test']], Response::HTTP_OK, false],
            [false, ['plainPassword' => ['first' => 'test', 'second' => 'test']], Response::HTTP_FOUND, true],

            [true, ['oldPassword' => 'password', 'plainPassword' => ['first' => 'plop', 'second' => 'test']], Response::HTTP_OK, false],
            [true, ['oldPassword' => 'plop', 'plainPassword' => ['first' => 'test', 'second' => 'test']], Response::HTTP_OK, false],
            [true, ['oldPassword' => 'password', 'plainPassword' => ['first' => 'test', 'second' => 'test']], Response::HTTP_FOUND, true],
        ];
    }

    /**
     * @dataProvider formulaireChangePasswordDataProvider
     *
     * @covers \App\Controller\Security\SecurityController::changePassword
     *
     * @param mixed $connected
     * @param mixed $data
     * @param mixed $httpResponse
     * @param mixed $passwordChanged
     */
    public function testChangePassword($connected, $data, $httpResponse, $passwordChanged)
    {
        $oldPassword = $this->user->getPassword();
        $token = 'the_token';

        if ($connected) {
            $this->client->loginUser($this->user, 'app');
            $token = null;
            $this->user->setConfirmationToken(null);
            $this->em->persist($this->user);
            $this->em->flush();
        }

        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('ucabundle_password_forgotten');
        $this->client->request(
            'POST',
            $this->router->generate('security_change_password', ['id' => $this->user->getId(), 'token' => $token]),
            ['ucabundle_password_forgotten' => array_merge($data, ['_token' => $csrfToken->getValue(), 'submit' => ''])],
        );

        $userUpdated = static::getContainer()->get(UtilisateurRepository::class)->findOneByEmail('SecurityControllerTest@test.fr');

        $this->assertResponseStatusCodeSame($httpResponse);
        if ($passwordChanged) {
            $this->assertNotEquals($oldPassword, $userUpdated->getPassword());
        } else {
            $this->assertEquals($oldPassword, $userUpdated->getPassword());
        }
    }

    /**
     * Data provider pour le formulaire de demande de re-initialisation du mot de passe.
     */
    public function formulairePasswordForgottenDataProvider()
    {
        return [
            [['identifiant' => 'SecurityControllerTest_inconnu'], Response::HTTP_FOUND, false],
            [['identifiant' => 'SecurityControllerTest@test.fr'], Response::HTTP_FOUND, true],
            [['identifiant' => 'SecurityControllerTest'], Response::HTTP_FOUND, true],
        ];
    }

    /**
     * @dataProvider formulairePasswordForgottenDataProvider
     *
     * @covers \App\Controller\Security\SecurityController::passwordForgotten
     *
     * @param mixed $data
     * @param mixed $httpResponse
     * @param mixed $tokenSetted
     */
    public function testPasswordForgotten($data, $httpResponse, $tokenSetted)
    {
        $this->user->setConfirmationToken(null);
        $this->em->persist($this->user);
        $this->em->flush();

        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('ucabundle_password_forgotten');
        $this->client->request(
            'POST',
            $this->router->generate('security_password_forgotten'),
            ['ucabundle_password_forgotten' => array_merge($data, ['_token' => $csrfToken->getValue(), 'save' => ''])],
        );

        $userUpdated = static::getContainer()->get(UtilisateurRepository::class)->findOneByEmail('SecurityControllerTest@test.fr');

        $this->assertResponseStatusCodeSame($httpResponse);
        if ($tokenSetted) {
            $this->assertNotNull($userUpdated->getConfirmationToken());
            $this->assertEmailCount(1);
        } else {
            $this->assertNull($userUpdated->getConfirmationToken());
            $this->assertEmailCount(0);
        }
    }
}
