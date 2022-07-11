<?php

namespace App\Tests\Controller\UcaWeb;

use App\Entity\Uca\ShnuHighlight;
use App\Entity\Uca\ShnuRubrique;
use App\Repository\TypeRubriqueRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * @internal
 * @coversNothing
 */
class SportHautNiveauControllerTest extends WebTestCase
{
    public function accessDataProvider()
    {
        return [
            [null, 'GET', 'UcaWeb_SportVoir', Response::HTTP_OK],
            [null, 'GET', 'UcaWeb_ConsulterRubrique', Response::HTTP_OK, ['id' => 'id_rubrique'], 1],
            [null, 'GET', 'UcaWeb_ConsulterRubrique', Response::HTTP_OK, ['id' => 'id_rubrique'], 2],
            [null, 'GET', 'UcaWeb_ConsulterRubrique', Response::HTTP_BAD_REQUEST, ['id' => 'id_rubrique'], 3],
            [null, 'GET', 'UcaWeb_ConsulterRubrique', Response::HTTP_OK, ['id' => 'id_rubrique'], 4],
            [null, 'GET', 'UcaWeb_ShnuHighlights', Response::HTTP_OK, ['id' => 'id_highlight']],
        ];
    }

    /**
     * @dataProvider accessDataProvider
     *
     * @covers \App\Controller\UcaWeb\SportHautNiveauController::consulterRubriqueAction
     * @covers \App\Controller\UcaWeb\SportHautNiveauController::listerAction
     * @covers \App\Controller\UcaWeb\SportHautNiveauController::voirHighlightsAction
     *
     * @param mixed      $userEmail
     * @param mixed      $method
     * @param mixed      $routeName
     * @param mixed      $httpResponse
     * @param mixed      $urlParameters
     * @param null|mixed $idType
     */
    public function testAccesRoutes($userEmail, $method, $routeName, $httpResponse, $urlParameters = [], $idType = 1): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        // Creation des donnees
        $highlight = (new ShnuHighlight())
            ->setVideo('test')
        ;
        $em->persist($highlight);
        $rubrique = (new ShnuRubrique())
            ->setTitre('SportHautNiveauController_testAccesRoutes')
        ;
        $rubrique->setType(static::getContainer()->get(TypeRubriqueRepository::class)->findOneById($idType));
        $em->persist($rubrique);
        $em->flush();

        $router = static::getContainer()->get(RouterInterface::class);

        if (null != $userEmail) {
            $userTest = static::getContainer()->get(UtilisateurRepository::class)->findOneByEmail($userEmail);
            $client->loginUser($userTest);
        }
        $route = $router->generate($routeName, $urlParameters);
        $route = str_replace('id_highlight', $highlight->getId(), $route);
        $route = str_replace('id_rubrique', $rubrique->getId(), $route);

        $client->request($method, $route);
        $this->assertResponseStatusCodeSame($httpResponse);

        $em->remove($rubrique);
        $em->remove($highlight);
        $em->flush();
    }
}
