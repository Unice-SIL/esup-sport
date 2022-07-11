<?php

namespace App\Tests\Controller\Security;

use App\Controller\Security\ShibbolethController;
use App\Repository\UtilisateurRepository;
use App\Service\Securite\GestionnaireUtilisateurShibboleth;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;

/**
 * @internal
 * @coversNothing
 */
class ShibbolethControllerTest extends WebTestCase
{
    /**
     * test de la méthode appLogout
     *
     * @covers App\Controller\Security\ShibbolethController::appLogoutAction
     */
    public function testAppLogout()
    {
        $client = static::createClient();
        
        $router = static::getContainer()->get(RouterInterface::class);
        $route = $router->generate('UcaWeb_AppLogout');
        $client->request('GET', $route);
        $this->assertResponseRedirects();
    }

    /**
     * test de la méthode shibLogout
     *
     * @covers App\Controller\Security\ShibbolethController::shibLogoutAction
     */
    public function testShibLogout()
    {
        $client = static::createClient();
        
        $router = static::getContainer()->get(RouterInterface::class);
        $route = $router->generate('UcaWeb_ShibLogout');
        $client->request('GET', $route);
        $this->assertResponseIsSuccessful();
    }

    public function shibLoginDataProvider()
    {
        return [
            [true,'UcaWeb_CGV'],
            [false,'UcaWeb_Accueil'],
        ];
    }

    /**
     * test de la méthode shibLoginAction lors d'une prmière connexion
     *
     * @dataProvider shibLoginDataProvider
     *
     * @covers App\Controller\Security\ShibbolethController::shibLoginAction
     */
    public function testShibLogin(bool $firstConnection, string $expectedRouteName)
    {
        $mock = static::createMock(GestionnaireUtilisateurShibboleth::class);
        
        $mock
            ->expects($this->any())
            ->method('isFirstConnection')
            ->withAnyParameters()
            ->willReturn($firstConnection)
        ;

        // Comme le service est privé on ne peut pas le remplacer par le mock et faire une requête avec le client
        // On appel donc la méthode avec le mock en paramètre
        $controller = new ShibbolethController();
        
        // On set le container des services pour que le controller touve ceux dont il a besoin de base (router, twig, etc.)
        $controller->setContainer(static::getContainer());
        
        $response = $controller->shibLoginAction($mock);

        $router = static::getContainer()->get(RouterInterface::class);
        $expectedRoute = $router->generate($expectedRouteName);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals($expectedRoute, $response->getTargetUrl());
    }
}
