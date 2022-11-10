<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

/**
 * @internal
 * @coversNothing
 */
class AccueilControllerTest extends WebTestCase
{
    /**
     * @covers \App\Controller\AccueilController::accueilAction
     */
    public function testAccueilAction(): void
    {
        $client = static::createClient();
        $router = static::getContainer()->get(RouterInterface::class);
        $client->request('GET', $router->generate('UcaGest_Accueil'));

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
    }
}