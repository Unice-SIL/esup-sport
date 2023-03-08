<?php

namespace App\Tests\Controller\Security;

use App\Entity\Uca\Utilisateur;
use App\Repository\ProfilUtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * @internal
 * @coversNothing
 */
class RegistrationControllerTest extends WebTestCase
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
            ['GET', 'registration_validate_acount', Response::HTTP_NOT_FOUND, ['id' => null, 'token' => null]],
            ['GET', 'registration_validate_acount', Response::HTTP_NOT_FOUND, ['id' => null, 'token' => 'the_token']],
            ['GET', 'registration_validate_acount', Response::HTTP_NOT_FOUND, ['id' => null, 'token' => 'autre_incorrect']],
            ['GET', 'registration_validate_acount', Response::HTTP_NOT_FOUND, ['id' => 'user_id', 'token' => null]],
            ['GET', 'registration_validate_acount', Response::HTTP_FOUND, ['id' => 'user_id', 'token' => 'the_token'], 'security_login'],
            ['GET', 'registration_validate_acount', Response::HTTP_FOUND, ['id' => 'user_id', 'token' => 'autre_incorrect'], 'security_login'],
            ['GET', 'registration_validate_acount', Response::HTTP_NOT_FOUND, ['id' => 'autre_user_id', 'token' => null]],
            ['GET', 'registration_validate_acount', Response::HTTP_FOUND, ['id' => 'autre_user_id', 'token' => 'the_token'], 'security_login'],
            ['GET', 'registration_validate_acount', Response::HTTP_FOUND, ['id' => 'autre_user_id', 'token' => 'autre_incorrect'], 'security_login'],
        ];
    }

    /**
     * @dataProvider controlAccessDataProvider
     *
     * @covers \App\Controller\Security\SecurityController::changePassword
     *
     * @param mixed      $route
     * @param mixed      $httpResponse
     * @param mixed      $method
     * @param null|mixed $id
     * @param null|mixed $urlParameters
     * @param mixed      $expectedRedirectionRoute
     */
    public function testAccesRoutes($method, $route, $httpResponse, $urlParameters, $expectedRedirectionRoute = null): void
    {
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
}
