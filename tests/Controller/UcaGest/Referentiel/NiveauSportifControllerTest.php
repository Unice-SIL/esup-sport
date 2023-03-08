<?php

namespace App\Tests\Controller\UcaGest\Referentiel;

use App\Entity\Uca\Groupe;
use App\Entity\Uca\NiveauSportif;
use App\Entity\Uca\ShnuRubrique;
use App\Entity\Uca\Utilisateur;
use App\Repository\GroupeRepository;
use App\Repository\NiveauSportifRepository;
use App\Repository\ShnuRubriqueRepository;
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
class NiveauSportifControllerTest extends WebTestCase
{
    private $client;
    private $niveauId;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $goupe_user_admin_complet = (new Groupe('test_controleur_goupe_user_admin_complet', ['ROLE_GESTION_NIVEAUSPORTIF_ECRITURE']))
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

        $goupe_user_admin_lecture = (new Groupe('test_controleur_goupe_user_admin_lecture', ['ROLE_GESTION_NIVEAUSPORTIF_LECTURE']))
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

        $niveau1 = (new NiveauSportif())
            ->setLibelle('test_controleur_ns_1')
        ;
        $em->persist($niveau1);
        $em->flush();

        $niveau2 = (new NiveauSportif())
            ->setLibelle('test_controleur_ns_2')
        ;
        $em->persist($niveau2);
        $em->flush();

        $niveau3 = (new NiveauSportif())
            ->setLibelle('test_controleur_ns_3')
        ;
        $em->persist($niveau3);
        $em->flush();

        $this->niveauId = $niveau1->getId();
    }

    /**
     * Data provider pour le controle des acces au differentes routes.
     */
    public function controlAccessDataProvider()
    {
        return [
            // Index
            [null, 'GET', 'UcaGest_NiveauSportifLister', Response::HTTP_FOUND],
            ['user_admin_complet@test.fr', 'GET', 'UcaGest_NiveauSportifLister', Response::HTTP_OK],
            ['user_admin_lecture@test.fr', 'GET', 'UcaGest_NiveauSportifLister', Response::HTTP_OK],
            ['user_non_admin@test.fr', 'GET', 'UcaGest_NiveauSportifLister', Response::HTTP_FORBIDDEN],
            // Ajouter
            [null, 'GET', 'UcaGest_NiveauSportifAjouter', Response::HTTP_FOUND],
            ['user_admin_complet@test.fr', 'GET', 'UcaGest_NiveauSportifAjouter', Response::HTTP_OK],
            ['user_admin_lecture@test.fr', 'GET', 'UcaGest_NiveauSportifAjouter', Response::HTTP_FORBIDDEN],
            ['user_non_admin@test.fr', 'GET', 'UcaGest_NiveauSportifAjouter', Response::HTTP_FORBIDDEN],
            // Modifier
            [null, 'GET', 'UcaGest_NiveauSportifModifier', Response::HTTP_FOUND, ['id' => 'id_niveau_sportif']],
            ['user_admin_complet@test.fr', 'GET', 'UcaGest_NiveauSportifModifier', Response::HTTP_OK, ['id' => 'id_niveau_sportif']],
            ['user_admin_lecture@test.fr', 'GET', 'UcaGest_NiveauSportifModifier', Response::HTTP_FORBIDDEN, ['id' => 'id_niveau_sportif']],
            ['user_non_admin@test.fr', 'GET', 'UcaGest_NiveauSportifModifier', Response::HTTP_FORBIDDEN, ['id' => 'id_niveau_sportif']],
            // Supprimer
            [null, 'GET', 'UcaGest_NiveauSportifSupprimer', Response::HTTP_FOUND, ['id' => 'id_niveau_sportif']],
            ['user_admin_complet@test.fr', 'GET', 'UcaGest_NiveauSportifSupprimer', Response::HTTP_FOUND, ['id' => 'id_niveau_sportif']],
            ['user_admin_lecture@test.fr', 'GET', 'UcaGest_NiveauSportifSupprimer', Response::HTTP_FORBIDDEN, ['id' => 'id_niveau_sportif']],
            ['user_non_admin@test.fr', 'GET', 'UcaGest_NiveauSportifSupprimer', Response::HTTP_FORBIDDEN, ['id' => 'id_niveau_sportif']],
        ];
    }

    /**
     * @dataProvider controlAccessDataProvider
     *
     * @covers \App\Controller\UcaGest\Referentiel\NiveauSportifController::ajouterAction
     * @covers \App\Controller\UcaGest\Referentiel\NiveauSportifController::listerAction
     * @covers \App\Controller\UcaGest\Referentiel\NiveauSportifController::modifierAction
     * @covers \App\Controller\UcaGest\Referentiel\NiveauSportifController::supprimerAction
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
            $this->client->loginUser($userTest, 'app');
        }
        $route = str_replace('id_niveau_sportif', $this->niveauId + 1, $router->generate($route, $urlParameters));
        $this->client->request($method, $route);
        $this->assertResponseStatusCodeSame($httpResponse);
    }

    /**
     * Data provider pour le formulaire de creation.
     */
    public function formulaireCreationDataProvider()
    {
        return [
            // Cas vide
            [['libelle' => null], Response::HTTP_OK],
            [['libelle' => ''], Response::HTTP_OK],
            // Cas valides
            [['libelle' => 'the titre'], Response::HTTP_FOUND],
        ];
    }

    /**
     * @dataProvider formulaireCreationDataProvider
     *
     * @covers \App\Controller\UcaGest\Referentiel\NiveauSportifController::ajouterAction
     *
     * @param mixed $data
     * @param mixed $httpResponse
     */
    public function testAjouterNiveau($data, $httpResponse)
    {
        $nbrNiveauxAvant = count(static::getContainer()->get(NiveauSportifRepository::class)->findAll());

        $router = static::getContainer()->get(RouterInterface::class);

        $userTest = static::getContainer()->get(UtilisateurRepository::class)->findOneByEmail('user_admin_complet@test.fr');
        $this->client->loginUser($userTest, 'app');

        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('ucabundle_niveausportif');
        $this->client->request(
            'POST',
            $router->generate('UcaGest_NiveauSportifAjouter'),
            ['ucabundle_niveausportif' => array_merge($data, ['_token' => $csrfToken->getValue(), 'save' => ''])],
        );

        $this->assertResponseStatusCodeSame($httpResponse);
        $nbrNiveauxApres = count(static::getContainer()->get(NiveauSportifRepository::class)->findAll());

        if (Response::HTTP_FOUND == $httpResponse) {
            $this->assertEquals($nbrNiveauxAvant + 1, $nbrNiveauxApres);
        } else {
            $this->assertEquals($nbrNiveauxAvant, $nbrNiveauxApres);
        }

        if (Response::HTTP_FOUND == $httpResponse) {
            $niveauSportifASupprimer = static::getContainer()->get(NiveauSportifRepository::class)->findBy([], ['id' => 'DESC'], 1, 0)[0];
            $em = static::getContainer()->get(EntityManagerInterface::class);
            $em->remove($niveauSportifASupprimer);
            $em->flush();
        }
    }

    /**
     * @covers \App\Controller\UcaGest\Referentiel\NiveauSportifController::supprimerAction
     */
    public function testSupprimerNiveauSansIdentifiant()
    {
        $niveauRepo = static::getContainer()->get(NiveauSportifRepository::class);
        $nbrAvant = count($niveauRepo->findAll());

        $router = static::getContainer()->get(RouterInterface::class);

        $userTest = static::getContainer()->get(UtilisateurRepository::class)->findOneByEmail('user_admin_complet@test.fr');
        $this->client->loginUser($userTest, 'app');

        $route = $router->generate('UcaGest_NiveauSportifSupprimer', ['id' => null]);
        $this->client->request('GET', $route);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $nbrApres = count($niveauRepo->findAll());
        $this->assertEquals($nbrAvant, $nbrApres);
    }

    /**
     * @covers \App\Controller\UcaGest\Referentiel\NiveauSportifController::supprimerAction
     */
    public function testSupprimerRubriqueIdentifiantInconnu()
    {
        $niveauRepo = static::getContainer()->get(NiveauSportifRepository::class);
        $nbrAvant = count($niveauRepo->findAll());

        $router = static::getContainer()->get(RouterInterface::class);

        $userTest = static::getContainer()->get(UtilisateurRepository::class)->findOneByEmail('user_admin_complet@test.fr');
        $this->client->loginUser($userTest, 'app');

        $route = $router->generate('UcaGest_NiveauSportifSupprimer', ['id' => 0]);
        $this->client->request('GET', $route);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $nbrApres = count($niveauRepo->findAll());
        $this->assertEquals($nbrAvant, $nbrApres);
    }

    /**
     * Data provider pour le formulaire de creation.
     */
    public function formulaireEditionDataProvider()
    {
        return [
            // Cas vide
            [['libelle' => null], Response::HTTP_OK],
            [['libelle' => ''], Response::HTTP_OK],
            // Cas valides
            [['libelle' => 'the titre'], Response::HTTP_FOUND],
        ];
    }

    /**
     * @dataProvider formulaireEditionDataProvider
     *
     * @covers \App\Controller\UcaGest\Referentiel\NiveauSportifController::modifierAction
     *
     * @param mixed $data
     * @param mixed $httpResponse
     */
    public function testModifierNiveau($data, $httpResponse)
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $nbrNiveauxAvant = count(static::getContainer()->get(NiveauSportifRepository::class)->findAll());
        $oldVersionRubrique = static::getContainer()->get(NiveauSportifRepository::class)->findOneById($this->niveauId);
        $em->detach($oldVersionRubrique);

        $router = static::getContainer()->get(RouterInterface::class);

        $userTest = static::getContainer()->get(UtilisateurRepository::class)->findOneByEmail('user_admin_complet@test.fr');
        $this->client->loginUser($userTest, 'app');

        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('ucabundle_niveausportif');
        $this->client->request(
            'POST',
            $router->generate('UcaGest_NiveauSportifModifier', ['id' => $this->niveauId]),
            ['ucabundle_niveausportif' => array_merge($data, ['_token' => $csrfToken->getValue(), 'save' => ''])],
        );

        $this->assertResponseStatusCodeSame($httpResponse);
        $nbrNiveauxApres = count(static::getContainer()->get(NiveauSportifRepository::class)->findAll());

        $this->assertEquals($nbrNiveauxAvant, $nbrNiveauxApres);

        if (Response::HTTP_FOUND == $httpResponse) {
            $rubriqueModifie = static::getContainer()->get(NiveauSportifRepository::class)->findOneById($this->niveauId);
            $this->assertEquals($rubriqueModifie->getLibelle(), $data['libelle']);
        }
    }
}
