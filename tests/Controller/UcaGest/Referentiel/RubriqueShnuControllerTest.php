<?php

namespace App\Tests\Controller\UcaGest\Referentiel;

use App\Entity\Uca\Groupe;
use App\Entity\Uca\ShnuRubrique;
use App\Entity\Uca\Utilisateur;
use App\Repository\GroupeRepository;
use App\Repository\ShnuRubriqueRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * @internal
 * @coversNothing
 */
class RubriqueShnuControllerTest extends WebTestCase
{
    private $client;
    private $rubriqueShnuId;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $goupe_user_admin_complet = (new Groupe('test_controleur_goupe_user_admin_complet', ['ROLE_GESTION_SHNU_RUBRIQUE_ECRITURE']))
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

        $goupe_user_admin_lecture = (new Groupe('test_controleur_goupe_user_admin_lecture', ['ROLE_GESTION_SHNU_RUBRIQUE_LECTURE']))
            ->setLibelle('test_controleur_goupe_user_admin_lecture')
        ;
        $em->persist($goupe_user_admin_lecture);
        $user_admin_lecture = (new Utilisateur())
            ->setEmail('user_admin_lecture@test.fr')
            ->setUsername('user_admin_lecture')
            ->setPassword('password')
            ->setCgvAcceptees(0)
            ->setRoles([])
            ->addGroup($goupe_user_admin_lecture)
        ;
        $em->persist($user_admin_lecture);

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

        $rubriqueShnu = (new ShnuRubrique())
            ->setTitre('test_controleur_rubrique_1')
        ;
        $em->persist($rubriqueShnu);
        $em->flush();

        $rubriqueShnu2 = (new ShnuRubrique())
            ->setTitre('test_controleur_rubrique_2')
        ;
        $em->persist($rubriqueShnu2);
        $em->flush();

        $rubriqueShnu3 = (new ShnuRubrique())
            ->setTitre('test_controleur_rubrique_3')
        ;
        $em->persist($rubriqueShnu3);
        $em->flush();

        $this->rubriqueShnuId = $rubriqueShnu->getId();
    }

    protected function tearDown(): void
    {
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $em->remove($container->get(UtilisateurRepository::class)->findOneByEmail('user_admin_complet@test.fr'));
        $em->remove($container->get(UtilisateurRepository::class)->findOneByEmail('user_admin_lecture@test.fr'));
        $em->remove($container->get(UtilisateurRepository::class)->findOneByEmail('user_non_admin@test.fr'));

        $em->remove($container->get(GroupeRepository::class)->findOneByLibelle('test_controleur_goupe_user_admin_complet'));
        $em->remove($container->get(GroupeRepository::class)->findOneByLibelle('test_controleur_goupe_user_admin_lecture'));
        $em->remove($container->get(GroupeRepository::class)->findOneByLibelle('test_controleur_goupe_user_non_admin'));

        $rubrique = $container->get(ShnuRubriqueRepository::class)->findOneById($this->rubriqueShnuId);
        if (null != $rubrique) {
            $em->remove($rubrique);
        }
        $rubrique2 = $container->get(ShnuRubriqueRepository::class)->findOneById($this->rubriqueShnuId + 1);
        if (null != $rubrique2) {
            $em->remove($rubrique2);
        }
        $rubrique3 = $container->get(ShnuRubriqueRepository::class)->findOneById($this->rubriqueShnuId + 2);
        if (null != $rubrique3) {
            $em->remove($rubrique3);
        }

        $em->flush();

        static::ensureKernelShutdown();
    }

    /**
     * Data provider pour le controle des acces au differentes routes.
     */
    public function controlAccessDataProvider()
    {
        return [
            // Index
            [null, 'GET', 'UcaGest_Shnu_RubriqueLister', Response::HTTP_FOUND],
            ['user_admin_complet@test.fr', 'GET', 'UcaGest_Shnu_RubriqueLister', Response::HTTP_OK],
            ['user_admin_lecture@test.fr', 'GET', 'UcaGest_Shnu_RubriqueLister', Response::HTTP_OK],
            ['user_non_admin@test.fr', 'GET', 'UcaGest_Shnu_RubriqueLister', Response::HTTP_FORBIDDEN],
            // Ajouter
            [null, 'GET', 'UcaGest_ShnuRubriqueAjouter', Response::HTTP_FOUND],
            ['user_admin_complet@test.fr', 'GET', 'UcaGest_ShnuRubriqueAjouter', Response::HTTP_OK],
            ['user_admin_lecture@test.fr', 'GET', 'UcaGest_ShnuRubriqueAjouter', Response::HTTP_FORBIDDEN],
            ['user_non_admin@test.fr', 'GET', 'UcaGest_ShnuRubriqueAjouter', Response::HTTP_FORBIDDEN],
            // Modifier
            [null, 'GET', 'UcaGest_ShnuRubriqueModifier', Response::HTTP_FOUND, ['id' => 'id_rubrique']],
            ['user_admin_complet@test.fr', 'GET', 'UcaGest_ShnuRubriqueModifier', Response::HTTP_OK, ['id' => 'id_rubrique']],
            ['user_admin_lecture@test.fr', 'GET', 'UcaGest_ShnuRubriqueModifier', Response::HTTP_FORBIDDEN, ['id' => 'id_rubrique']],
            ['user_non_admin@test.fr', 'GET', 'UcaGest_ShnuRubriqueModifier', Response::HTTP_FORBIDDEN, ['id' => 'id_rubrique']],
            // Monter ordre
            [null, 'GET', 'UcaGest_ShnuRubriqueModifierOrdre', Response::HTTP_FOUND, ['id' => 'id_rubrique', 'action' => 'monter']],
            ['user_admin_complet@test.fr', 'GET', 'UcaGest_ShnuRubriqueModifierOrdre', Response::HTTP_OK, ['id' => 'id_rubrique', 'action' => 'monter']],
            ['user_admin_lecture@test.fr', 'GET', 'UcaGest_ShnuRubriqueModifierOrdre', Response::HTTP_FORBIDDEN, ['id' => 'id_rubrique', 'action' => 'monter']],
            ['user_non_admin@test.fr', 'GET', 'UcaGest_ShnuRubriqueModifierOrdre', Response::HTTP_FORBIDDEN, ['id' => 'id_rubrique', 'action' => 'monter']],
            // Descendre ordre
            [null, 'GET', 'UcaGest_ShnuRubriqueModifierOrdre', Response::HTTP_FOUND, ['id' => 'id_rubrique', 'action' => 'descendre']],
            ['user_admin_complet@test.fr', 'GET', 'UcaGest_ShnuRubriqueModifierOrdre', Response::HTTP_OK, ['id' => 'id_rubrique', 'action' => 'descendre']],
            ['user_admin_lecture@test.fr', 'GET', 'UcaGest_ShnuRubriqueModifierOrdre', Response::HTTP_FORBIDDEN, ['id' => 'id_rubrique', 'action' => 'descendre']],
            ['user_non_admin@test.fr', 'GET', 'UcaGest_ShnuRubriqueModifierOrdre', Response::HTTP_FORBIDDEN, ['id' => 'id_rubrique', 'action' => 'descendre']],
            // Supprimer
            [null, 'GET', 'UcaGest_ShnuRubriqueSupprimer', Response::HTTP_FOUND, ['id' => 'id_rubrique']],
            ['user_admin_complet@test.fr', 'GET', 'UcaGest_ShnuRubriqueSupprimer', Response::HTTP_FOUND, ['id' => 'id_rubrique']],
            ['user_admin_lecture@test.fr', 'GET', 'UcaGest_ShnuRubriqueSupprimer', Response::HTTP_FORBIDDEN, ['id' => 'id_rubrique']],
            ['user_non_admin@test.fr', 'GET', 'UcaGest_ShnuRubriqueSupprimer', Response::HTTP_FORBIDDEN, ['id' => 'id_rubrique']],
        ];
    }

    /**
     * @dataProvider controlAccessDataProvider
     *
     * @covers \App\Controller\UcaGest\Referentiel\RubriqueShnuController::ajouterRubriqueAction
     * @covers \App\Controller\UcaGest\Referentiel\RubriqueShnuController::listerAction
     * @covers \App\Controller\UcaGest\Referentiel\RubriqueShnuController::modifierAction
     * @covers \App\Controller\UcaGest\Referentiel\RubriqueShnuController::modifierOrdreAction
     * @covers \App\Controller\UcaGest\Referentiel\RubriqueShnuController::supprimerAction
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
        $router = static::getContainer()->get(RouterInterface::class);

        if (null != $userEmail) {
            $userTest = static::getContainer()->get(UtilisateurRepository::class)->findOneByEmail($userEmail);
            $this->client->loginUser($userTest);
        }
        $route = str_replace('id_rubrique', $this->rubriqueShnuId, $router->generate($route, $urlParameters));
        $this->client->request($method, $route);
        $this->assertResponseStatusCodeSame($httpResponse);
    }

    /**
     * Data provider pour le formulaire de creation.
     */
    public function formulaireCreationDataProvider()
    {
        $f_pdf = new UploadedFile(__DIR__.'../../../../fixtures/test.pdf', 'test.pdf');
        $f_jpg = new UploadedFile(__DIR__.'../../../../fixtures/logo_atimic.jpg', 'logo_atimic.jpg');

        return [
            // Cas vide
            [['titre' => null, 'type' => '', 'texte' => null, 'lien' => null], ['imageFile' => null], Response::HTTP_OK],
            [['titre' => '', 'type' => '', 'texte' => '', 'lien' => ''], ['imageFile' => null], Response::HTTP_OK],
            // Cas partiels
            [['titre' => '', 'type' => '1', 'texte' => '', 'lien' => ''], $f_jpg, Response::HTTP_OK],
            [['titre' => 'the titre', 'type' => '', 'texte' => '', 'lien' => ''], $f_jpg, Response::HTTP_OK],
            [['titre' => 'the titre', 'type' => '1', 'texte' => '', 'lien' => ''], null, Response::HTTP_OK],
            // Format image incorrect
            [['titre' => 'the titre', 'type' => '1', 'texte' => 'plop', 'lien' => 'google.fr'], $f_pdf, Response::HTTP_OK],
            // Cas valides
            [['titre' => 'the titre', 'type' => '1', 'texte' => '', 'lien' => ''], $f_jpg, Response::HTTP_FOUND],
            [['titre' => 'the titre', 'type' => '1', 'texte' => 'plop', 'lien' => 'http://google.fr'], $f_jpg, Response::HTTP_FOUND],
        ];
    }

    /**
     * @dataProvider formulaireCreationDataProvider
     *
     * @covers \App\Controller\UcaGest\Referentiel\RubriqueShnuController::ajouterRubriqueAction
     *
     * @param mixed $data
     * @param mixed $file
     * @param mixed $httpResponse
     */
    public function testAjouterRubrique($data, $file, $httpResponse)
    {
        $nbrRubriquesAvant = count(static::getContainer()->get(ShnuRubriqueRepository::class)->findAll());

        $router = static::getContainer()->get(RouterInterface::class);

        $userTest = static::getContainer()->get(UtilisateurRepository::class)->findOneByEmail('user_admin_complet@test.fr');
        $this->client->loginUser($userTest);

        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('ucabundle_rubriqueshnu');
        $this->client->request(
            'POST',
            $router->generate('UcaGest_ShnuRubriqueAjouter'),
            ['ucabundle_rubriqueshnu' => array_merge($data, ['_token' => $csrfToken->getValue(), 'save' => ''])],
            ['ucabundle_rubriqueshnu' => ['imageFile' => ['file' => $file]]],
        );

        $this->assertResponseStatusCodeSame($httpResponse);
        $nbrRubriquesApres = count(static::getContainer()->get(ShnuRubriqueRepository::class)->findAll());

        if (Response::HTTP_FOUND == $httpResponse) {
            $this->assertEquals($nbrRubriquesAvant + 1, $nbrRubriquesApres);
        } else {
            $this->assertEquals($nbrRubriquesAvant, $nbrRubriquesApres);
        }

        if (null != $file && Response::HTTP_FOUND == $httpResponse) {
            $rubriqueASupprimer = static::getContainer()->get(ShnuRubriqueRepository::class)->findBy([], ['id' => 'DESC'], 1, 0)[0];
            copy(__DIR__.'../../../../fixtures/image/'.$rubriqueASupprimer->getImage(), $file->getPathname());
            $em = static::getContainer()->get(EntityManagerInterface::class);
            $em->remove($rubriqueASupprimer);
            $em->flush();
        }
    }

    /**
     * @covers \App\Controller\UcaGest\Referentiel\RubriqueShnuController::supprimerAction
     */
    public function testSupprimerRubriqueSansIdentifiant()
    {
        $rubriqueRepo = static::getContainer()->get(ShnuRubriqueRepository::class);
        $nbrAvant = count($rubriqueRepo->findAll());

        $router = static::getContainer()->get(RouterInterface::class);

        $userTest = static::getContainer()->get(UtilisateurRepository::class)->findOneByEmail('user_admin_complet@test.fr');
        $this->client->loginUser($userTest);

        $route = $router->generate('UcaGest_ShnuRubriqueSupprimer', ['id' => null]);
        $this->client->request('GET', $route);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $nbrApres = count($rubriqueRepo->findAll());
        $this->assertEquals($nbrAvant, $nbrApres);
    }

    /**
     * @covers \App\Controller\UcaGest\Referentiel\RubriqueShnuController::supprimerAction
     */
    public function testSupprimerRubriqueIdentifiantInconnu()
    {
        $rubriqueRepo = static::getContainer()->get(ShnuRubriqueRepository::class);
        $nbrAvant = count($rubriqueRepo->findAll());

        $router = static::getContainer()->get(RouterInterface::class);

        $userTest = static::getContainer()->get(UtilisateurRepository::class)->findOneByEmail('user_admin_complet@test.fr');
        $this->client->loginUser($userTest);

        $route = $router->generate('UcaGest_ShnuRubriqueSupprimer', ['id' => 0]);
        $this->client->request('GET', $route);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $nbrApres = count($rubriqueRepo->findAll());
        $this->assertEquals($nbrAvant, $nbrApres);
    }

    /**
     * @covers \App\Controller\UcaGest\Referentiel\RubriqueShnuController::supprimerAction
     */
    public function testSupprimerRubriqueOrdreMin()
    {
        $rubriqueRepo = static::getContainer()->get(ShnuRubriqueRepository::class);
        $nbrAvant = count($rubriqueRepo->findAll());
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $rubriqueOrdreMin = $rubriqueRepo->findOneByOrdre(1);
        if (null == $rubriqueOrdreMin || $rubriqueOrdreMin->getId() == $this->rubriqueShnuId) {
            $rubriqueOrdreMin = null;
        } else {
            $em->remove($rubriqueOrdreMin);
            $r = $rubriqueRepo->findOneById($this->rubriqueShnuId);
            $r->setOrdre(1);

            $em->flush();
        }
        $id_rubriqueOrdre2 = $rubriqueRepo->findOneByOrdre(2)->getId();

        $this->assertNotNull($rubriqueRepo->findOneByOrdre($nbrAvant));

        $router = static::getContainer()->get(RouterInterface::class);

        $userTest = static::getContainer()->get(UtilisateurRepository::class)->findOneByEmail('user_admin_complet@test.fr');
        $this->client->loginUser($userTest);

        $route = $router->generate('UcaGest_ShnuRubriqueSupprimer', ['id' => $this->rubriqueShnuId]);
        $this->client->request('GET', $route);
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $nbrApres = count($rubriqueRepo->findAll());
        $this->assertEquals($nbrAvant - 1, $nbrApres);
        $this->assertNull($rubriqueRepo->findOneByOrdre($nbrAvant));
        $this->assertNull($rubriqueRepo->findOneById($this->rubriqueShnuId));
        $newRubriqueOrdre1 = $rubriqueRepo->findOneByOrdre(1);
        $this->assertNotEquals($newRubriqueOrdre1->getId(), $this->rubriqueShnuId);
        $this->assertEquals($newRubriqueOrdre1->getId(), $id_rubriqueOrdre2);

        if (null != $rubriqueOrdreMin) {
            $em->persist($rubriqueOrdreMin);

            $em->flush();
        }
    }

    /**
     * @covers \App\Controller\UcaGest\Referentiel\RubriqueShnuController::supprimerAction
     */
    public function testSupprimerRubriqueOrdreMax()
    {
        $rubriqueRepo = static::getContainer()->get(ShnuRubriqueRepository::class);
        $nbrAvant = count($rubriqueRepo->findAll());

        $this->assertNotNull($rubriqueRepo->findOneByOrdre($nbrAvant));

        $router = static::getContainer()->get(RouterInterface::class);

        $userTest = static::getContainer()->get(UtilisateurRepository::class)->findOneByEmail('user_admin_complet@test.fr');
        $this->client->loginUser($userTest);

        $route = $router->generate('UcaGest_ShnuRubriqueSupprimer', ['id' => $this->rubriqueShnuId + 2]);
        $this->client->request('GET', $route);
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $nbrApres = count($rubriqueRepo->findAll());
        $this->assertEquals($nbrAvant - 1, $nbrApres);
        $this->assertNull($rubriqueRepo->findOneByOrdre($nbrAvant));
        $this->assertNull($rubriqueRepo->findOneById($this->rubriqueShnuId + 2));
    }

    /**
     * @covers \App\Controller\UcaGest\Referentiel\RubriqueShnuController::supprimerAction
     */
    public function testSupprimerRubriqueOrdreMilieu()
    {
        $rubriqueRepo = static::getContainer()->get(ShnuRubriqueRepository::class);
        $nbrAvant = count($rubriqueRepo->findAll());

        $rubriqueOrdreAvant = $rubriqueRepo->findOneById($this->rubriqueShnuId)->getOrdre();
        $rubriqueOrdreApres = $rubriqueRepo->findOneById($this->rubriqueShnuId + 2)->getOrdre();

        $this->assertNotNull($rubriqueRepo->findOneByOrdre($nbrAvant));

        $router = static::getContainer()->get(RouterInterface::class);

        $userTest = static::getContainer()->get(UtilisateurRepository::class)->findOneByEmail('user_admin_complet@test.fr');
        $this->client->loginUser($userTest);

        $route = $router->generate('UcaGest_ShnuRubriqueSupprimer', ['id' => $this->rubriqueShnuId + 1]);
        $this->client->request('GET', $route);
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $nbrApres = count($rubriqueRepo->findAll());
        $this->assertEquals($nbrAvant - 1, $nbrApres);
        $this->assertNull($rubriqueRepo->findOneByOrdre($nbrAvant));
        $this->assertNull($rubriqueRepo->findOneById($this->rubriqueShnuId + 1));

        $this->assertEquals($rubriqueRepo->findOneById($this->rubriqueShnuId)->getOrdre(), $rubriqueOrdreAvant);
        $this->assertEquals($rubriqueRepo->findOneById($this->rubriqueShnuId + 2)->getOrdre(), $rubriqueOrdreApres - 1);
    }

    /**
     * Data provider pour le formulaire de creation.
     */
    public function formulaireEditionDataProvider()
    {
        $f_pdf = new UploadedFile(__DIR__.'../../../../fixtures/test.pdf', 'test.pdf');
        $f_jpg = new UploadedFile(__DIR__.'../../../../fixtures/logo_atimic.jpg', 'logo_atimic.jpg');

        return [
            // Cas vide
            [['titre' => null, 'type' => '', 'texte' => null, 'lien' => null], ['imageFile' => null], Response::HTTP_OK],
            [['titre' => '', 'type' => '', 'texte' => '', 'lien' => ''], ['imageFile' => null], Response::HTTP_OK],
            // Cas partiels
            [['titre' => '', 'type' => '1', 'texte' => '', 'lien' => ''], $f_jpg, Response::HTTP_OK],
            [['titre' => 'the titre', 'type' => '', 'texte' => '', 'lien' => ''], $f_jpg, Response::HTTP_OK],
            [['titre' => 'the titre', 'type' => '1', 'texte' => '', 'lien' => ''], null, Response::HTTP_OK],
            // Format image incorrect
            [['titre' => 'the titre', 'type' => '1', 'texte' => 'plop', 'lien' => 'google.fr'], $f_pdf, Response::HTTP_OK],
            // Cas valides
            [['titre' => 'the titre', 'type' => '1', 'texte' => '', 'lien' => ''], $f_jpg, Response::HTTP_FOUND],
            [['titre' => 'the titre', 'type' => '1', 'texte' => 'plop', 'lien' => 'http://google.fr'], $f_jpg, Response::HTTP_FOUND],
        ];
    }

    /**
     * @dataProvider formulaireEditionDataProvider
     *
     * @covers \App\Controller\UcaGest\Referentiel\RubriqueShnuController::modifierAction
     *
     * @param mixed $data
     * @param mixed $file
     * @param mixed $httpResponse
     */
    public function testModifierRubrique($data, $file, $httpResponse)
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $nbrRubriquesAvant = count(static::getContainer()->get(ShnuRubriqueRepository::class)->findAll());
        $oldVersionRubrique = static::getContainer()->get(ShnuRubriqueRepository::class)->findOneById($this->rubriqueShnuId);
        $em->detach($oldVersionRubrique);

        $router = static::getContainer()->get(RouterInterface::class);

        $userTest = static::getContainer()->get(UtilisateurRepository::class)->findOneByEmail('user_admin_complet@test.fr');
        $this->client->loginUser($userTest);

        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('ucabundle_rubriqueshnu');
        $this->client->request(
            'POST',
            $router->generate('UcaGest_ShnuRubriqueModifier', ['id' => $this->rubriqueShnuId]),
            ['ucabundle_rubriqueshnu' => array_merge($data, ['_token' => $csrfToken->getValue(), 'save' => ''])],
            ['ucabundle_rubriqueshnu' => ['imageFile' => ['file' => $file]]],
        );

        $this->assertResponseStatusCodeSame($httpResponse);
        $nbrRubriquesApres = count(static::getContainer()->get(ShnuRubriqueRepository::class)->findAll());

        $this->assertEquals($nbrRubriquesAvant, $nbrRubriquesApres);

        if (Response::HTTP_FOUND == $httpResponse) {
            $rubriqueModifie = static::getContainer()->get(ShnuRubriqueRepository::class)->findOneById($this->rubriqueShnuId);
            $this->assertEquals($rubriqueModifie->getOrdre(), $oldVersionRubrique->getOrdre());
            $this->assertEquals($rubriqueModifie->getTitre(), $data['titre']);
            $this->assertEquals($rubriqueModifie->getType()->getId(), $data['type']);
            $this->assertEquals($rubriqueModifie->getTexte(), $data['texte']);
            $this->assertEquals($rubriqueModifie->getLien(), $data['lien']);

            if (null != $file) {
                copy(__DIR__.'../../../../fixtures/image/'.$rubriqueModifie->getImage(), $file->getPathname());
            }
        }
    }

    /**
     * @covers \App\Controller\UcaGest\Referentiel\RubriqueShnuController::modifierOrdreAction
     */
    public function testModifierOrdreRubriqueSansIdentifiant()
    {
        $router = static::getContainer()->get(RouterInterface::class);

        $userTest = static::getContainer()->get(UtilisateurRepository::class)->findOneByEmail('user_admin_complet@test.fr');
        $this->client->loginUser($userTest);

        $route = $router->generate('UcaGest_ShnuRubriqueModifierOrdre', ['id' => null, 'action' => 'monter']);
        $this->client->request('GET', $route);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * @covers \App\Controller\UcaGest\Referentiel\RubriqueShnuController::modifierOrdreAction
     */
    public function testModifierOrdreIdentifiantInconnu()
    {
        $router = static::getContainer()->get(RouterInterface::class);

        $userTest = static::getContainer()->get(UtilisateurRepository::class)->findOneByEmail('user_admin_complet@test.fr');
        $this->client->loginUser($userTest);

        $route = $router->generate('UcaGest_ShnuRubriqueModifierOrdre', ['id' => 0, 'action' => 'monter']);
        $this->client->request('GET', $route);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * @covers \App\Controller\UcaGest\Referentiel\RubriqueShnuController::modifierOrdreAction
     */
    public function testModifierOrdreMinDescendre()
    {
        $rubriqueRepo = static::getContainer()->get(ShnuRubriqueRepository::class);
        $idOrdre1 = $rubriqueRepo->findOneByOrdre(1)->getId();
        $idOrdre2 = $rubriqueRepo->findOneByOrdre(2)->getId();

        $userTest = static::getContainer()->get(UtilisateurRepository::class)->findOneByEmail('user_admin_complet@test.fr');
        $this->client->loginUser($userTest);

        $router = static::getContainer()->get(RouterInterface::class);
        $route = $router->generate('UcaGest_ShnuRubriqueModifierOrdre', ['id' => $idOrdre1, 'action' => 'descendre']);
        $this->client->request('GET', $route);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertEquals($rubriqueRepo->findOneByOrdre(1)->getId(), $idOrdre2);
        $this->assertEquals($rubriqueRepo->findOneByOrdre(2)->getId(), $idOrdre1);
    }

    /**
     * @covers \App\Controller\UcaGest\Referentiel\RubriqueShnuController::modifierOrdreAction
     */
    public function testModifierOrdreMinMonter()
    {
        $rubriqueRepo = static::getContainer()->get(ShnuRubriqueRepository::class);
        $idOrdre1 = $rubriqueRepo->findOneByOrdre(1)->getId();
        $idOrdre2 = $rubriqueRepo->findOneByOrdre(2)->getId();

        $userTest = static::getContainer()->get(UtilisateurRepository::class)->findOneByEmail('user_admin_complet@test.fr');
        $this->client->loginUser($userTest);

        $router = static::getContainer()->get(RouterInterface::class);
        $route = $router->generate('UcaGest_ShnuRubriqueModifierOrdre', ['id' => $idOrdre1, 'action' => 'monter']);
        $this->client->request('GET', $route);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertEquals($rubriqueRepo->findOneByOrdre(1)->getId(), $idOrdre1);
        $this->assertEquals($rubriqueRepo->findOneByOrdre(2)->getId(), $idOrdre2);
    }

    /**
     * @covers \App\Controller\UcaGest\Referentiel\RubriqueShnuController::modifierOrdreAction
     */
    public function testModifierOrdreMaxDescendre()
    {
        $rubriqueRepo = static::getContainer()->get(ShnuRubriqueRepository::class);
        $ordreMax = $rubriqueRepo->max('ordre');

        $userTest = static::getContainer()->get(UtilisateurRepository::class)->findOneByEmail('user_admin_complet@test.fr');
        $this->client->loginUser($userTest);

        $this->assertEquals($rubriqueRepo->findOneById($this->rubriqueShnuId + 1)->getOrdre(), $ordreMax - 1);
        $this->assertEquals($rubriqueRepo->findOneById($this->rubriqueShnuId + 2)->getOrdre(), $ordreMax);

        $router = static::getContainer()->get(RouterInterface::class);
        $route = $router->generate('UcaGest_ShnuRubriqueModifierOrdre', ['id' => $this->rubriqueShnuId + 2, 'action' => 'descendre']);
        $this->client->request('GET', $route);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertEquals($rubriqueRepo->findOneById($this->rubriqueShnuId + 1)->getOrdre(), $ordreMax - 1);
        $this->assertEquals($rubriqueRepo->findOneById($this->rubriqueShnuId + 2)->getOrdre(), $ordreMax);
    }

    /**
     * @covers \App\Controller\UcaGest\Referentiel\RubriqueShnuController::modifierOrdreAction
     */
    public function testModifierOrdreMaxMonter()
    {
        $rubriqueRepo = static::getContainer()->get(ShnuRubriqueRepository::class);
        $ordreMax = $rubriqueRepo->max('ordre');

        $userTest = static::getContainer()->get(UtilisateurRepository::class)->findOneByEmail('user_admin_complet@test.fr');
        $this->client->loginUser($userTest);

        $this->assertEquals($rubriqueRepo->findOneById($this->rubriqueShnuId + 1)->getOrdre(), $ordreMax - 1);
        $this->assertEquals($rubriqueRepo->findOneById($this->rubriqueShnuId + 2)->getOrdre(), $ordreMax);

        $router = static::getContainer()->get(RouterInterface::class);
        $route = $router->generate('UcaGest_ShnuRubriqueModifierOrdre', ['id' => $this->rubriqueShnuId + 2, 'action' => 'monter']);
        $this->client->request('GET', $route);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertEquals($rubriqueRepo->findOneById($this->rubriqueShnuId + 1)->getOrdre(), $ordreMax);
        $this->assertEquals($rubriqueRepo->findOneById($this->rubriqueShnuId + 2)->getOrdre(), $ordreMax - 1);
    }

    /**
     * @covers \App\Controller\UcaGest\Referentiel\RubriqueShnuController::modifierOrdreAction
     */
    public function testModifierOrdreMilieuDescendre()
    {
        $rubriqueRepo = static::getContainer()->get(ShnuRubriqueRepository::class);
        $ordreMax = $rubriqueRepo->max('ordre');

        $userTest = static::getContainer()->get(UtilisateurRepository::class)->findOneByEmail('user_admin_complet@test.fr');
        $this->client->loginUser($userTest);

        $this->assertEquals($rubriqueRepo->findOneById($this->rubriqueShnuId)->getOrdre(), $ordreMax - 2);
        $this->assertEquals($rubriqueRepo->findOneById($this->rubriqueShnuId + 1)->getOrdre(), $ordreMax - 1);
        $this->assertEquals($rubriqueRepo->findOneById($this->rubriqueShnuId + 2)->getOrdre(), $ordreMax);

        $router = static::getContainer()->get(RouterInterface::class);
        $route = $router->generate('UcaGest_ShnuRubriqueModifierOrdre', ['id' => $this->rubriqueShnuId + 1, 'action' => 'descendre']);
        $this->client->request('GET', $route);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertEquals($rubriqueRepo->findOneById($this->rubriqueShnuId)->getOrdre(), $ordreMax - 2);
        $this->assertEquals($rubriqueRepo->findOneById($this->rubriqueShnuId + 1)->getOrdre(), $ordreMax);
        $this->assertEquals($rubriqueRepo->findOneById($this->rubriqueShnuId + 2)->getOrdre(), $ordreMax - 1);
    }

    /**
     * @covers \App\Controller\UcaGest\Referentiel\RubriqueShnuController::modifierOrdreAction
     */
    public function testModifierOrdreMilieuMonter()
    {
        $rubriqueRepo = static::getContainer()->get(ShnuRubriqueRepository::class);
        $ordreMax = $rubriqueRepo->max('ordre');

        $userTest = static::getContainer()->get(UtilisateurRepository::class)->findOneByEmail('user_admin_complet@test.fr');
        $this->client->loginUser($userTest);

        $this->assertEquals($rubriqueRepo->findOneById($this->rubriqueShnuId)->getOrdre(), $ordreMax - 2);
        $this->assertEquals($rubriqueRepo->findOneById($this->rubriqueShnuId + 1)->getOrdre(), $ordreMax - 1);
        $this->assertEquals($rubriqueRepo->findOneById($this->rubriqueShnuId + 2)->getOrdre(), $ordreMax);

        $router = static::getContainer()->get(RouterInterface::class);
        $route = $router->generate('UcaGest_ShnuRubriqueModifierOrdre', ['id' => $this->rubriqueShnuId + 1, 'action' => 'monter']);
        $this->client->request('GET', $route);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertEquals($rubriqueRepo->findOneById($this->rubriqueShnuId)->getOrdre(), $ordreMax - 1);
        $this->assertEquals($rubriqueRepo->findOneById($this->rubriqueShnuId + 1)->getOrdre(), $ordreMax - 2);
        $this->assertEquals($rubriqueRepo->findOneById($this->rubriqueShnuId + 2)->getOrdre(), $ordreMax);
    }
}
