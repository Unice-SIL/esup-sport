<?php

namespace App\Tests\Controller\Security;

use App\Entity\Uca\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * @internal
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

        $this->user = (new Utilisateur())
            ->setUsername('test')
            ->setEmail('SecurityControllerTest@test.fr')
            ->setPassword('password')
            ->setCgvAcceptees(true)
            ->setEnabled(true)
            ->setConfirmationToken('1234')
        ;

        $this->em->persist($this->user);
        $this->em->flush();
    }

    protected function tearDown(): void
    {
        $this->em->remove($this->user);
        $this->em->flush();

        static::ensureKernelShutdown();
    }

    /**
     * Data provider pour le controle des acces au differentes routes.
     */
    public function controlAccessDataProvider()
    {
        return [
            [null, 'GET', 'security_login', Response::HTTP_OK],
            ['SecurityControllerTest@test.fr', 'GET', 'security_login', Response::HTTP_FOUND],
        ];
    }

    /**
     * @dataProvider controlAccessDataProvider
     *
     * @covers \App\Controller\Security\SecurityController::login
     *
     * @param mixed      $userEmail
     * @param mixed      $route
     * @param mixed      $httpResponse
     * @param mixed      $method
     * @param null|mixed $id
     * @param null|mixed $urlParameters
     */
    public function testAccesRoutes($userEmail, $method, $route, $httpResponse, $urlParameters = []): void
    {
        if (null != $userEmail) {
            $userTest = static::getContainer()->get(UtilisateurRepository::class)->findOneByEmail($userEmail);
            $this->client->loginUser($userTest);
        }
        // $route = str_replace('id_rubrique', $this->rubriqueShnuId + 1, $router->generate($route, $urlParameters));
        $route = $this->router->generate($route, $urlParameters);
        $this->client->request($method, $route);
        $this->assertResponseStatusCodeSame($httpResponse);
    }

    // /**
    //  * @covers \App\Controller\Security\SecurityController::passwordForgotten
    //  */
    // public function testGetPasswordForgotten()
    // {
    //     $route = $this->router->generate('security_password_forgotten');
    //     $this->client->request('GET', $route);

    //     $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    // }

    // /**
    //  * @covers \App\Controller\Security\SecurityController::confirmAccount
    //  */
    // public function testGetConfirmAccountBonToken()
    // {
    //     $route = $this->router->generate('security_confirm_account', ['id' => $this->user->getId(), 'token' => '1234']);
    //     $this->client->request('GET', $route);

    //     $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
    // }

    // /**
    //  * @covers \App\Controller\Security\SecurityController::confirmAccount
    //  */
    // public function testConfirmAccountMauvaisToken()
    // {
    //     $route = $this->router->generate('security_confirm_account', ['id' => $this->user->getId(), 'token' => '0']);
    //     $this->client->request('GET', $route, ['token' => '1234']);

    //     $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
    // }
}
