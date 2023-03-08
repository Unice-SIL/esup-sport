<?php

namespace App\Tests\Controller\UcaGest\Parametrage;

use App\Entity\Uca\Groupe;
use App\Entity\Uca\LogoParametrable;
use App\Entity\Uca\Utilisateur;
use App\Repository\GroupeRepository;
use App\Repository\LogoParametrableRepository;
use App\Repository\UtilisateurRepository;
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
class LogoParametrableControllerTest extends WebTestCase
{
    private $client;
    private $logoId;

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

        $logo1 = (new LogoParametrable())
            ->setEmplacement('test_controleur_logo_parametrable_1')
            ->setImage('')
            ->setActif(true)
        ;
        $em->persist($logo1);
        $em->flush();

        $logo2 = (new LogoParametrable())
            ->setEmplacement('test_controleur_logo_parametrable_2')
            ->setImage('')
            ->setActif(true)
        ;
        $em->persist($logo2);
        $em->flush();

        $logo3 = (new LogoParametrable())
            ->setEmplacement('test_controleur_logo_parametrable_3')
            ->setImage('')
            ->setActif(true)
        ;
        $em->persist($logo3);
        $em->flush();

        $this->logoId = $logo1->getId();
    }

    protected function tearDown(): void
    {
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $em->remove($container->get(UtilisateurRepository::class)->findOneByEmail('user_admin_complet@test.fr'));
        $em->remove($container->get(UtilisateurRepository::class)->findOneByEmail('user_non_admin@test.fr'));

        $em->remove($container->get(GroupeRepository::class)->findOneByLibelle('test_controleur_goupe_user_admin_complet'));
        $em->remove($container->get(GroupeRepository::class)->findOneByLibelle('test_controleur_goupe_user_non_admin'));

        $logo = $container->get(LogoParametrableRepository::class)->findOneById($this->logoId);
        if (null != $logo) {
            $em->remove($logo);
        }
        $logo2 = $container->get(LogoParametrableRepository::class)->findOneById($this->logoId + 1);
        if (null != $logo2) {
            $em->remove($logo2);
        }
        $logo3 = $container->get(LogoParametrableRepository::class)->findOneById($this->logoId + 2);
        if (null != $logo3) {
            $em->remove($logo3);
        }

        $em->flush();

        parent::tearDown();
        static::ensureKernelShutdown();
    }

    /**
     * Data provider pour le controle des acces au differentes routes.
     */
    public function controlAccessDataProvider()
    {
        return [
            // Index
            [null, 'GET', 'UcaGest_LogoParametrableLister', Response::HTTP_FOUND],
            ['user_admin_complet@test.fr', 'GET', 'UcaGest_LogoParametrableLister', Response::HTTP_OK],
            ['user_non_admin@test.fr', 'GET', 'UcaGest_LogoParametrableLister', Response::HTTP_FORBIDDEN],
            // Modifier
            [null, 'GET', 'UcaGest_LogoParametrableModifier', Response::HTTP_FOUND, ['id' => 'id_logo']],
            ['user_admin_complet@test.fr', 'GET', 'UcaGest_LogoParametrableModifier', Response::HTTP_OK, ['id' => 'id_logo']],
            ['user_non_admin@test.fr', 'GET', 'UcaGest_LogoParametrableModifier', Response::HTTP_FORBIDDEN, ['id' => 'id_logo']],
        ];
    }

    public function datatableProvider()
    {
        return [
            // Index
            [null, 'GET', 'UcaGest_LogoParametrableLister', Response::HTTP_FOUND,
                ["draw"=>1, "columns"=>[["data"=>"id", "name"=>"", "searchable"=>"true", "orderable"=>"true", "search"=>["value"=>"", "regex"=>"false"]], ["data"=>"image", "name"=>"", "searchable"=>"true", "orderable"=>"false", "search"=>["value"=>"", "regex"=>"false"]], ["data"=>"emplacement", "name"=>"", "searchable"=>"true", "orderable"=>"true", "search"=>["value"=>"", "regex"=>"false"]], ["data"=>"3", "name"=>"", "searchable"=>"false", "orderable"=>"false", "search"=>["value"=>"", "regex"=>"false"]]], "order"=>[["column"=>"0", "dir"=>"asc"]], "start"=>"0", "length"=>"10", "search"=>["value"=>"", "regex"=>"false"], "_"=>"1673950875619"],
                true
            ],
            ['user_admin_complet@test.fr', 'GET', 'UcaGest_LogoParametrableLister', Response::HTTP_OK,
                ["draw"=>1, "columns"=>[["data"=>"id", "name"=>"", "searchable"=>"true", "orderable"=>"true", "search"=>["value"=>"", "regex"=>"false"]], ["data"=>"image", "name"=>"", "searchable"=>"true", "orderable"=>"false", "search"=>["value"=>"", "regex"=>"false"]], ["data"=>"emplacement", "name"=>"", "searchable"=>"true", "orderable"=>"true", "search"=>["value"=>"", "regex"=>"false"]], ["data"=>"3", "name"=>"", "searchable"=>"false", "orderable"=>"false", "search"=>["value"=>"", "regex"=>"false"]]], "order"=>[["column"=>"0", "dir"=>"asc"]], "start"=>"0", "length"=>"10", "search"=>["value"=>"", "regex"=>"false"], "_"=>"1673950875619"],
                true
            ],
            ['user_non_admin@test.fr', 'GET', 'UcaGest_LogoParametrableLister', Response::HTTP_FORBIDDEN,
                ["draw"=>1, "columns"=>[["data"=>"id", "name"=>"", "searchable"=>"true", "orderable"=>"true", "search"=>["value"=>"", "regex"=>"false"]], ["data"=>"image", "name"=>"", "searchable"=>"true", "orderable"=>"false", "search"=>["value"=>"", "regex"=>"false"]], ["data"=>"emplacement", "name"=>"", "searchable"=>"true", "orderable"=>"true", "search"=>["value"=>"", "regex"=>"false"]], ["data"=>"3", "name"=>"", "searchable"=>"false", "orderable"=>"false", "search"=>["value"=>"", "regex"=>"false"]]], "order"=>[["column"=>"0", "dir"=>"asc"]], "start"=>"0", "length"=>"10", "search"=>["value"=>"", "regex"=>"false"], "_"=>"1673950875619"],
                true
            ],
        ];
    }

    /**
     * @dataProvider controlAccessDataProvider
     * @dataProvider datatableProvider
     *
     * @covers \App\Controller\UcaGest\Parametrage\LogoParametrableController::listerAction
     * @covers \App\Controller\UcaGest\Parametrage\LogoParametrableController::modifierAction
     *
     * @param mixed      $userEmail
     * @param mixed      $route
     * @param mixed      $httpResponse
     * @param mixed      $method
     * @param null|mixed $id
     * @param null|mixed $urlParameters
     */
    public function testAccesRoutes($userEmail, $method, $route, $httpResponse, $urlParameters = [], $isAjax = false): void
    {
        $router = static::getContainer()->get(RouterInterface::class);

        if (null != $userEmail) {
            $userTest = static::getContainer()->get(UtilisateurRepository::class)->findOneByEmail($userEmail);
            $this->client->loginUser($userTest, 'app');
        }
        $route = str_replace('id_logo', $this->logoId + 1, $router->generate($route, $urlParameters));
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
        $f_pdf = new UploadedFile(dirname(__DIR__, 3).'/fixtures/test.pdf', 'test.pdf');
        $f_jpg = new UploadedFile(dirname(__DIR__, 3).'/fixtures/logo_atimic.jpg', 'logo_atimic.jpg');

        return [
            // Cas vide

            // Cas partiels

            // Format image incorrect
            [$f_pdf, Response::HTTP_OK],
            // Cas valides
            [$f_jpg, Response::HTTP_FOUND],
            [null, Response::HTTP_FOUND],
            [['imageFile' => null], Response::HTTP_OK],
        ];
    }

    /**
     * @dataProvider formulaireEditionDataProvider
     *
     * @covers \App\Controller\UcaGest\Parametrage\LogoParametrableController::modifierAction
     *
     * @param mixed $file
     * @param mixed $httpResponse
     */
    public function testModifierLogoParametrable($file, $httpResponse)
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $nbrLogoParametrablesAvant = count(static::getContainer()->get(LogoParametrableRepository::class)->findAll());
        $oldVersionLogoParametrable = static::getContainer()->get(LogoParametrableRepository::class)->findOneById($this->logoId);
        $em->detach($oldVersionLogoParametrable);

        $router = static::getContainer()->get(RouterInterface::class);

        $userTest = static::getContainer()->get(UtilisateurRepository::class)->findOneByEmail('user_admin_complet@test.fr');
        $this->client->loginUser($userTest, 'app');

        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('ucabundle_logoparametrable');
        $this->client->request(
            'POST',
            $router->generate('UcaGest_LogoParametrableModifier', ['id' => $this->logoId]),
            ['ucabundle_logoparametrable' => ['_token' => $csrfToken->getValue(), 'save' => '']],
            ['ucabundle_logoparametrable' => ['imageFile' => ['file' => $file]]],
        );

        $this->assertResponseStatusCodeSame($httpResponse);
        $nbrLogoParametrablesApres = count(static::getContainer()->get(LogoParametrableRepository::class)->findAll());

        $this->assertEquals($nbrLogoParametrablesAvant, $nbrLogoParametrablesApres);

        if (Response::HTTP_FOUND == $httpResponse) {
            $logoModifie = static::getContainer()->get(LogoParametrableRepository::class)->findOneById($this->logoId);

            if (null != $file) {
                copy(dirname(__DIR__, 3).'/fixtures/image/'.$logoModifie->getImage(), $file->getPathname());
            }
        }
    }
}
