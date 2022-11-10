<?php

namespace App\Tests\Controller\UcaWeb;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * @internal
 * @coversNothing
 */
class AccueilControllerTest extends WebTestCase
{
    public function accessDataProvider()
    {
        return [
            // UcaWeb_Acceuil
            ['GET','UcaWeb_Accueil',Response::HTTP_OK],

            // UcaWeb_ConnexionSelectionProfil
            ['GET','UcaWeb_ConnexionSelectionProfil',Response::HTTP_OK],

        ];
    }

    /**
     * @dataProvider accessDataProvider
     *
     * @covers App\Controller\UcaWeb\AccueilController::accueilAction
     * @covers App\Controller\UcaWeb\AccueilController::selectionProfilAction
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
