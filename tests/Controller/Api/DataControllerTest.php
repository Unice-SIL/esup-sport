<?php

namespace App\Tests\Controller\Api;

use App\Entity\Uca\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

/**
 * @internal
 * @coversNothing
 */
class DataControllerTest extends WebTestCase
{
    /**
     * Test de la route DataApi
     *
     * @covers App\Controller\Api\DataController::dataAction
     */
    public function testDataApi(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $user1 = (new Utilisateur())
            ->setRoles([])
            ->setUsername("utest")
            ->setEmail("test@test.fr")
            ->setPassword("password")
            ->setShibboleth(false)
            ->setCgvAcceptees(true)
        ;
        $em->persist($user1);
        $user2 = (new Utilisateur())
            ->setRoles([])
            ->setUsername("helloworld")
            ->setEmail("hello@world.com")
            ->setPassword("password")
            ->setShibboleth(false)
            ->setCgvAcceptees(true)
        ;
        $em->persist($user2);
        $em->flush();

        $router = static::getContainer()->get(RouterInterface::class);
        $route = $router->generate('DataApi');
        $body = [
            "lists" => [
                "users" => [
                    "class" => "App\\Entity\\Uca\\Utilisateur",
                ],
                "usersFiltered" => [
                    "class" => "App\\Entity\\Uca\\Utilisateur",
                    "findBy" => [
                        "repository" => "findOneByIdentifiant",
                        "param" => "utest",
                    ],
                ],
            ],
        ];
        $client->request('POST', $route, $body);
        $response = $client->getResponse();
        $response = json_decode($response->getContent());

        $this->assertIsObject($response);
        $this->assertObjectHasAttribute("users", $response);
        $this->assertIsArray($response->users);
        $this->assertNotEmpty($response->users);
        $this->assertObjectHasAttribute("objectClass", $response->users[0]);
        $this->assertEquals($response->users[0]->objectClass, "App\\Entity\\Uca\\Utilisateur");
        $this->assertObjectHasAttribute("usersFiltered", $response);
        $this->assertIsObject($response->usersFiltered);
        $this->assertObjectHasAttribute("objectClass", $response->usersFiltered);
        $this->assertEquals($response->usersFiltered->objectClass, "App\\Entity\\Uca\\Utilisateur");

        $em->remove($user1);
        $em->remove($user2);
        $em->flush();
    }
}
