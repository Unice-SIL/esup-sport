<?php

namespace App\Tests\Controller\UcaGest\Parametrage;

use App\Entity\Uca\Groupe;
use App\Entity\Uca\LogoParametrable;
use App\Entity\Uca\PeriodeFermeture;
use App\Entity\Uca\Utilisateur;
use App\Repository\GroupeRepository;
use App\Repository\LogoParametrableRepository;
use App\Repository\PeriodeFermetureRepository;
use App\Repository\UtilisateurRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * @internal
 *
 * @coversNothing
 */
class PeriodeFermetureControllerTest extends WebTestCase
{
    private $client;
    private $periodeId;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $goupe_user_admin_complet = (new Groupe('test_controleur_goupe_user_admin_complet', ['ROLE_GESTION_PARAMETRAGE']))
            ->setLibelle('test_controleur_goupe_user_admin_complet')
        ;
        $em->persist($goupe_user_admin_complet);
        $user_admin_complet = (new Utilisateur())
            ->setEmail('user_admin_complet@test.fr')
            ->setUsername('user_admin_complet')
            ->setPassword('password')
            ->setCgvAcceptees(0)
            ->setRoles([])
            ->addGroup($goupe_user_admin_complet)
        ;
        $em->persist($user_admin_complet);

        $goupe_user_non_admin = (new Groupe('test_controleur_goupe_user_non_admin', []))
            ->setLibelle('test_controleur_goupe_user_non_admin')
        ;
        $em->persist($goupe_user_non_admin);
        $user_non_admin = (new Utilisateur())
            ->setEmail('user_non_admin@test.fr')
            ->setUsername('user_non_admin')
            ->setPassword('password')
            ->setCgvAcceptees(0)
            ->setRoles([])
            ->addGroup($goupe_user_non_admin)
        ;
        $em->persist($user_non_admin);

        $periode1 = (new PeriodeFermeture())
            ->setDescription('test controller 1')
            ->setDateDeb(new DateTime('2022-12-17'))
            ->setDateFin(new DateTime('2023-01-02'))
        ;
        $em->persist($periode1);
        $em->flush();

        $periode2 = (new PeriodeFermeture())
            ->setDescription('test controller 2')
            ->setDateDeb(new DateTime('2022-12-17'))
            ->setDateFin(new DateTime('2023-01-02'))
        ;
        $em->persist($periode2);
        $em->flush();

        $periode3 = (new PeriodeFermeture())
            ->setDescription('test controller 3')
            ->setDateDeb(new DateTime('2022-12-17'))
            ->setDateFin(new DateTime('2023-01-02'))
        ;
        $em->persist($periode3);
        $em->flush();

        $this->periodeId = $periode1->getId();
    }


    /**
     * Data provider pour le controle des acces au differentes routes.
     */
    public function controlAccessDataProvider()
    {
        return [
            // Index
            [null, 'GET', 'UcaGest_PeriodeFermetureLister', Response::HTTP_FOUND],
            ['user_admin_complet@test.fr', 'GET', 'UcaGest_PeriodeFermetureLister', Response::HTTP_OK],
            ['user_non_admin@test.fr', 'GET', 'UcaGest_PeriodeFermetureLister', Response::HTTP_FORBIDDEN],
            // Ajouter
            [null, 'GET', 'UcaGest_PeriodeFermetureAjouter', Response::HTTP_FOUND],
            ['user_admin_complet@test.fr', 'GET', 'UcaGest_PeriodeFermetureAjouter', Response::HTTP_OK],
            ['user_non_admin@test.fr', 'GET', 'UcaGest_PeriodeFermetureAjouter', Response::HTTP_FORBIDDEN],
            // Modifier
            [null, 'GET', 'UcaGest_PeriodeFermetureModifier', Response::HTTP_FOUND, ['id' => 'id_periode']],
            ['user_admin_complet@test.fr', 'GET', 'UcaGest_PeriodeFermetureModifier', Response::HTTP_OK, ['id' => 'id_periode']],
            ['user_non_admin@test.fr', 'GET', 'UcaGest_PeriodeFermetureModifier', Response::HTTP_FORBIDDEN, ['id' => 'id_periode']],
            // Supprimer
            [null, 'GET', 'UcaGest_PeriodeFermetureSupprimer', Response::HTTP_FOUND, ['id' => 'id_periode']],
            ['user_admin_complet@test.fr', 'GET', 'UcaGest_PeriodeFermetureSupprimer', Response::HTTP_FOUND, ['id' => 'id_periode']],
            ['user_non_admin@test.fr', 'GET', 'UcaGest_PeriodeFermetureSupprimer', Response::HTTP_FORBIDDEN, ['id' => 'id_periode']],
        ];
    }

    public function dataTableProvider()
    {
        return [
            [null, 'GET', 'UcaGest_PeriodeFermetureLister', Response::HTTP_FOUND,
                ["draw"=>1, "columns"=>[["data"=>"id", "name"=>"", "searchable"=>"true", "orderable"=>"true", "search"=>["value"=>"", "regex"=>"false"]], ["data"=>"dateDeb", "name"=>"", "searchable"=>"true", "orderable"=>"true", "search"=>["value"=>"", "regex"=>"false"]], ["data"=>"dateFin", "name"=>"", "searchable"=>"true", "orderable"=>"true", "search"=>["value"=>"", "regex"=>"false"]], ["data"=>"description", "name"=>"", "searchable"=>"true", "orderable"=>"true", "search"=>["value"=>"", "regex"=>"false"]], ["data"=>"4", "name"=>"", "searchable"=>"false", "orderable"=>"false", "search"=>["value"=>"", "regex"=>"false"]]], "order"=>[["column"=>"0", "dir"=>"asc"]], "start"=>"0", "length"=>"10", "search"=>["value"=>"", "regex"=>"false"], "_"=>"1673950875619"],
                true
            ],
            ['user_admin_complet@test.fr', 'GET', 'UcaGest_PeriodeFermetureLister', Response::HTTP_OK,
                ["draw"=>1, "columns"=>[["data"=>"id", "name"=>"", "searchable"=>"true", "orderable"=>"true", "search"=>["value"=>"", "regex"=>"false"]], ["data"=>"dateDeb", "name"=>"", "searchable"=>"true", "orderable"=>"true", "search"=>["value"=>"", "regex"=>"false"]], ["data"=>"dateFin", "name"=>"", "searchable"=>"true", "orderable"=>"true", "search"=>["value"=>"", "regex"=>"false"]], ["data"=>"description", "name"=>"", "searchable"=>"true", "orderable"=>"true", "search"=>["value"=>"", "regex"=>"false"]], ["data"=>"4", "name"=>"", "searchable"=>"false", "orderable"=>"false", "search"=>["value"=>"", "regex"=>"false"]]], "order"=>[["column"=>"0", "dir"=>"asc"]], "start"=>"0", "length"=>"10", "search"=>["value"=>"", "regex"=>"false"], "_"=>"1673950875619"],
                true
            ],
            ['user_non_admin@test.fr', 'GET', 'UcaGest_PeriodeFermetureLister', Response::HTTP_FORBIDDEN,
                ["draw"=>1, "columns"=>[["data"=>"id", "name"=>"", "searchable"=>"true", "orderable"=>"true", "search"=>["value"=>"", "regex"=>"false"]], ["data"=>"dateDeb", "name"=>"", "searchable"=>"true", "orderable"=>"true", "search"=>["value"=>"", "regex"=>"false"]], ["data"=>"dateFin", "name"=>"", "searchable"=>"true", "orderable"=>"true", "search"=>["value"=>"", "regex"=>"false"]], ["data"=>"description", "name"=>"", "searchable"=>"true", "orderable"=>"true", "search"=>["value"=>"", "regex"=>"false"]], ["data"=>"4", "name"=>"", "searchable"=>"false", "orderable"=>"false", "search"=>["value"=>"", "regex"=>"false"]]], "order"=>[["column"=>"0", "dir"=>"asc"]], "start"=>"0", "length"=>"10", "search"=>["value"=>"", "regex"=>"false"], "_"=>"1673950875619"],
                true
            ],
        ];
    }

    /**
     * @dataProvider controlAccessDataProvider
     * @dataProvider dataTableProvider
     *
     * @covers \App\Controller\UcaGest\Parametrage\PeriodeFermetureController::listerAction
     * @covers \App\Controller\UcaGest\Parametrage\PeriodeFermetureController::ajouterAction
     * @covers \App\Controller\UcaGest\Parametrage\PeriodeFermetureController::modifierAction
     * @covers \App\Controller\UcaGest\Parametrage\PeriodeFermetureController::supprimerAction
     *
     * @param mixed      $userEmail
     * @param mixed      $route
     * @param mixed      $httpResponse
     * @param mixed      $method
     * @param null|mixed $id
     * @param null|mixed $urlParameters
     * @param bool $isAjax
     */
    public function testAccesRoutes($userEmail, $method, $route, $httpResponse, $urlParameters = [], $isAjax = false): void
    {
        $router = static::getContainer()->get(RouterInterface::class);

        if (null != $userEmail) {
            $userTest = static::getContainer()->get(UtilisateurRepository::class)->findOneByEmail($userEmail);
            $this->client->loginUser($userTest, 'app');
        }
        $route = str_replace('id_periode', $this->periodeId + 1, $router->generate($route, $urlParameters));
        if ($isAjax) {
            $this->client->xmlHttpRequest($method, $route);
        } else {
            $this->client->request($method, $route);
        }
        $this->assertResponseStatusCodeSame($httpResponse);
    }

    /**
     * Data provider pour le formulaire de creation.
     */
    public function formulaireEditionDataProvider()
    {
        return [
            // Cas vide
            [['description' => null, 'dateDeb' => null, 'dateFin' => null], Response::HTTP_OK],
            [['description' => '', 'dateDeb' => '', 'dateFin' => ''], Response::HTTP_OK],
            // Cas partiels
            [['description' => 'test', 'dateDeb' => '', 'dateFin' => ''], Response::HTTP_OK],
            [['description' => '', 'dateDeb' => '01/01/2023', 'dateFin' => ''], Response::HTTP_OK],
            [['description' => '', 'dateDeb' => '', 'dateFin' => '02/01/2023'], Response::HTTP_OK],
            [['description' => 'test', 'dateDeb' => '01/01/2023', 'dateFin' => ''], Response::HTTP_OK],
            [['description' => 'test', 'dateDeb' => '', 'dateFin' => '02/01/2023'], Response::HTTP_OK],
            [['description' => '', 'dateDeb' => '01/01/2023', 'dateFin' => '02/01/2023'], Response::HTTP_OK],
            // Cas valides
            [['description' => 'test', 'dateDeb' => '01/01/2023', 'dateFin' => '02/01/2023'], Response::HTTP_FOUND],
        ];
    }

    /**
     * @dataProvider formulaireEditionDataProvider
     *
     * @covers \App\Controller\UcaGest\Parametrage\PeriodeFermetureController::modifierAction
     *
     * @param mixed $data
     * @param mixed $httpResponse
     */
    public function testModifierPeriodeFermeture($data, $httpResponse)
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $nbrPeriodeFermeturesAvant = count(static::getContainer()->get(PeriodeFermetureRepository::class)->findAll());
        $oldVersionPeriodeFermeture = static::getContainer()->get(PeriodeFermetureRepository::class)->findOneById($this->periodeId);
        $em->detach($oldVersionPeriodeFermeture);

        $router = static::getContainer()->get(RouterInterface::class);

        $userTest = static::getContainer()->get(UtilisateurRepository::class)->findOneByEmail('user_admin_complet@test.fr');
        $this->client->loginUser($userTest, 'app');

        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('ucabundle_periodefermeture');
        $this->client->request(
            'POST',
            $router->generate('UcaGest_PeriodeFermetureModifier', ['id' => $this->periodeId]),
            ['ucabundle_periodefermeture' => array_merge($data, ['_token' => $csrfToken->getValue(), 'save' => '']) ],
        );

        $this->assertResponseStatusCodeSame($httpResponse);
        $nbrPeriodeFermeturesApres = count(static::getContainer()->get(PeriodeFermetureRepository::class)->findAll());

        $this->assertEquals($nbrPeriodeFermeturesAvant, $nbrPeriodeFermeturesApres);
    }

    /**
     * @dataProvider formulaireEditionDataProvider
     *
     * @covers \App\Controller\UcaGest\Parametrage\PeriodeFermetureController::ajouterAction
     *
     * @param mixed $data
     * @param mixed $httpResponse
     */
    public function testAjouterPeriodeFermeture($data, $httpResponse)
    {
        $nbrPeriodeFermeturesAvant = count(static::getContainer()->get(PeriodeFermetureRepository::class)->findAll());

        $router = static::getContainer()->get(RouterInterface::class);

        $userTest = static::getContainer()->get(UtilisateurRepository::class)->findOneByEmail('user_admin_complet@test.fr');
        $this->client->loginUser($userTest, 'app');

        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('ucabundle_periodefermeture');
        $this->client->request(
            'POST',
            $router->generate('UcaGest_PeriodeFermetureAjouter'),
            ['ucabundle_periodefermeture' => array_merge($data, ['_token' => $csrfToken->getValue(), 'save' => '']) ],
        );

        $this->assertResponseStatusCodeSame($httpResponse);
        $nbrPeriodeFermeturesApres = count(static::getContainer()->get(PeriodeFermetureRepository::class)->findAll());

        if ($httpResponse === Response::HTTP_FOUND) {
            $nbrPeriodeFermeturesAvant++;
        }
        $this->assertEquals($nbrPeriodeFermeturesAvant, $nbrPeriodeFermeturesApres);
    }

    /**
     * @covers \App\Controller\UcaGest\Parametrage\PeriodeFermetureController::supprimerAction
     */
    public function testSupprimerPeriodeFermeture()
    {
        $nbrPeriodeFermeturesAvant = count(static::getContainer()->get(PeriodeFermetureRepository::class)->findAll());

        $router = static::getContainer()->get(RouterInterface::class);

        $userTest = static::getContainer()->get(UtilisateurRepository::class)->findOneByEmail('user_admin_complet@test.fr');
        $this->client->loginUser($userTest, 'app');

        $this->client->request(
            'GET',
            $router->generate('UcaGest_PeriodeFermetureSupprimer', ['id' => $this->periodeId]),
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $nbrPeriodeFermeturesApres = count(static::getContainer()->get(PeriodeFermetureRepository::class)->findAll());

        $this->assertEquals($nbrPeriodeFermeturesAvant - 1, $nbrPeriodeFermeturesApres);
    }
}
