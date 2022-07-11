<?php

namespace App\Tests\Controller\UcaWeb;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 * @coversNothing
 */
class EvenementControllerTest extends WebTestCase
{
    public function accessDataProvider()
    {
        return [
            // UcaWeb_Acceuil
            ['GET','UcaWeb_Evenement',Response::HTTP_OK],
            ['GET','UcaWeb_Evenement',Response::HTTP_OK,['page'=>-1]],
            ['GET','UcaWeb_Evenement',Response::HTTP_OK,['page'=>0]],
            ['GET','UcaWeb_Evenement',Response::HTTP_OK,['page'=>100]],
        ];
    }

    /**
     * @dataProvider accessDataProvider
     *
     * @covers App\Controller\UcaWeb\EvenementController::listAction
     */
    public function testAccesRoutes($method, $routeName, $httpResponse, $urlParameters = [], $body = [], $ajax = false): void
    {
        $client = static::createClient();
        $router = static::getContainer()->get(RouterInterface::class);

        $route = $router->generate($routeName, $urlParameters);

        if ($ajax) {
            $client->xmlHttpRequest($method, $route, $body);
        } else {
            $client->request($method, $route, $body);
        }
        $this->assertResponseStatusCodeSame($httpResponse);
    }
}
