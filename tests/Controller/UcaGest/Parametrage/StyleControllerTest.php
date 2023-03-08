<?php

namespace App\Tests\Controller\UcaGest\Parametrage;

use App\Entity\Uca\Groupe;
use App\Entity\Uca\ProfilUtilisateur;
use App\Entity\Uca\Style;
use App\Entity\Uca\Utilisateur;
use App\Repository\GroupeRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * @internal
 * @coversNothing
 */
class StyleControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $profilUser = (new ProfilUtilisateur())
            ->setLibelle('Test StyleController')
            ->setNbMaxInscriptions(100)
            ->setNbMaxInscriptionsRessource(100)
            ->setPreinscription(false)
        ;
        $em->persist($profilUser);

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
            ->setProfil($profilUser)
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
            ->setProfil($profilUser)
        ;
        $em->persist($user_non_admin);

        $stylePreview = $em->getRepository(Style::class)->findOneBy(['preview' => true]);
        $stylePreview->setPrimaryColor('#FF0000');
        $stylePreview->setSecondaryColor('#0000FF');
        $stylePreview->setSuccessColor("#FFFFFF");
        $stylePreview->setWarningColor("#FF00FF");
        $stylePreview->setDangerColor("#FFF0FF");
        $stylePreview->setPrimaryHover(0.1);
        $stylePreview->setSecondaryHover(-0.1);
        $stylePreview->setSuccessHover(0.2);
        $stylePreview->setWarningHover(0.3);
        $stylePreview->setDangerHover(0.4);
        $stylePreview->setPrimaryShadow(1.0);
        $stylePreview->setSecondaryShadow(-1.0);
        $stylePreview->setSuccessShadow(-0.2);
        $stylePreview->setWarningShadow(-0.3);
        $stylePreview->setDangerShadow(-0.4);
        $stylePreview->setNavbarBackgroundColor('#BB0000');
        $stylePreview->setNavbarForegroundColor('#00FF00');

        $style = $em->getRepository(Style::class)->findOneBy(['preview' => false]);
        $style->setPrimaryColor('#00FF00');
        $style->setSecondaryColor('#0F0F0F');
        $style->setSuccessColor("#000000");
        $style->setWarningColor("#00F000");
        $style->setDangerColor("#000F00");
        $style->setPrimaryHover(0.2);
        $style->setSecondaryHover(-0.2);
        $style->setSuccessHover(-0.2);
        $style->setWarningHover(-0.3);
        $style->setDangerHover(-0.4);
        $style->setPrimaryShadow(0.5);
        $style->setSecondaryShadow(-0.5);
        $style->setSuccessShadow(0.2);
        $style->setWarningShadow(0.3);
        $style->setDangerShadow(0.4);
        $style->setNavbarBackgroundColor('#00BB00');
        $style->setNavbarForegroundColor('#00FFF0');

        $em->flush();
    }

    /**
     * Data provider pour le controle des acces au differentes routes.
     */
    public function controlAccessDataProvider()
    {
        return [
            // Index
            [null, 'GET', 'UcaGest_StyleIndex', Response::HTTP_FOUND],
            ['user_admin_complet@test.fr', 'GET', 'UcaGest_StyleIndex', Response::HTTP_OK],
            ['user_non_admin@test.fr', 'GET', 'UcaGest_StyleIndex', Response::HTTP_FORBIDDEN],
            // Modifier
            [null, 'GET', 'UcaGest_StyleModifier', Response::HTTP_FOUND],
            ['user_admin_complet@test.fr', 'GET', 'UcaGest_StyleModifier', Response::HTTP_OK],
            ['user_non_admin@test.fr', 'GET', 'UcaGest_StyleModifier', Response::HTTP_FORBIDDEN],
            // Preview
            [null, 'GET', 'UcaGest_StylePreview', Response::HTTP_FOUND],
            ['user_admin_complet@test.fr', 'GET', 'UcaGest_StylePreview', Response::HTTP_OK],
            ['user_non_admin@test.fr', 'GET', 'UcaGest_StylePreview', Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     * @dataProvider controlAccessDataProvider
     *
     * @covers \App\Controller\UcaGest\Parametrage\StyleController::indexAction
     * @covers \App\Controller\UcaGest\Parametrage\StyleController::previewAction
     * @covers \App\Controller\UcaGest\Parametrage\StyleController::modifierAction
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
        $route = $router->generate($route, $urlParameters);
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
            [['primaryColor' => null, 'primaryHover' => null, 'primaryShadow' => null, 'secondaryColor' => null, 'secondaryHover' => null, 'secondaryShadow' => null, 'navbarBackgroundColor' => null, 'navbarForegroundColor' => null, 'successColor' => null, 'successHover' => null, 'successShadow' => null, 'warningColor' => null, 'warningHover' => null, 'warningShadow' => null, 'dangerColor' => null, 'dangerHover' => null, 'dangerShadow' => null], Response::HTTP_OK],
            [['primaryColor' => '', 'primaryHover' => 0, 'primaryShadow' => 0, 'secondaryColor' => '', 'secondaryHover' => 0, 'secondaryShadow' => 0, 'navbarBackgroundColor' => '', 'navbarForegroundColor' => '', 'successColor' => '', 'successHover' => 0, 'successShadow' => 0, 'warningColor' => '', 'warningHover' => 0, 'warningShadow' => 0, 'dangerColor' => '', 'dangerHover' => 0, 'dangerShadow' => 0], Response::HTTP_OK],
            // Cas partiels
            [['primaryColor' => '#ffffff', 'primaryHover' => '', 'primaryShadow' => '', 'secondaryColor' => '', 'secondaryHover' => '', 'secondaryShadow' => '', 'navbarBackgroundColor' => '', 'navbarForegroundColor' => '', 'successColor' => '', 'successHover' => '', 'successShadow' => '', 'warningColor' => '', 'warningHover' => '', 'warningShadow' => '', 'dangerColor' => '', 'dangerHover' => '', 'dangerShadow' => ''], Response::HTTP_OK],
            [['primaryColor' => '', 'primaryHover' => 0, 'primaryShadow' => '', 'secondaryColor' => '', 'secondaryHover' => '', 'secondaryShadow' => '', 'navbarBackgroundColor' => '', 'navbarForegroundColor' => '', 'successColor' => '', 'successHover' => '', 'successShadow' => '', 'warningColor' => '', 'warningHover' => '', 'warningShadow' => '', 'dangerColor' => '', 'dangerHover' => '', 'dangerShadow' => ''], Response::HTTP_OK],
            [['primaryColor' => '', 'primaryHover' => '', 'primaryShadow' => 0, 'secondaryColor' => '', 'secondaryHover' => '', 'secondaryShadow' => '', 'navbarBackgroundColor' => '', 'navbarForegroundColor' => '', 'successColor' => '', 'successHover' => '', 'successShadow' => '', 'warningColor' => '', 'warningHover' => '', 'warningShadow' => '', 'dangerColor' => '', 'dangerHover' => '', 'dangerShadow' => ''], Response::HTTP_OK],
            [['primaryColor' => '#ffffff', 'primaryHover' => 0, 'primaryShadow' => '', 'secondaryColor' => '', 'secondaryHover' => '', 'secondaryShadow' => '', 'navbarBackgroundColor' => '', 'navbarForegroundColor' => '', 'successColor' => '', 'successHover' => '', 'successShadow' => '', 'warningColor' => '', 'warningHover' => '', 'warningShadow' => '', 'dangerColor' => '', 'dangerHover' => '', 'dangerShadow' => ''], Response::HTTP_OK],
            [['primaryColor' => '#ffffff', 'primaryHover' => '', 'primaryShadow' => 0, 'secondaryColor' => '', 'secondaryHover' => '', 'secondaryShadow' => '', 'navbarBackgroundColor' => '', 'navbarForegroundColor' => '', 'successColor' => '', 'successHover' => '', 'successShadow' => '', 'warningColor' => '', 'warningHover' => '', 'warningShadow' => '', 'dangerColor' => '', 'dangerHover' => '', 'dangerShadow' => ''], Response::HTTP_OK],
            [['primaryColor' => '#ffffff', 'primaryHover' => 0, 'primaryShadow' => 0, 'secondaryColor' => '', 'secondaryHover' => '', 'secondaryShadow' => '', 'navbarBackgroundColor' => '', 'navbarForegroundColor' => '', 'successColor' => '', 'successHover' => '', 'successShadow' => '', 'warningColor' => '', 'warningHover' => '', 'warningShadow' => '', 'dangerColor' => '', 'dangerHover' => '', 'dangerShadow' => ''], Response::HTTP_OK],
            [['primaryColor' => '', 'primaryHover' => '', 'primaryShadow' => '', 'secondaryColor' => '#ffffff', 'secondaryHover' => '', 'secondaryShadow' => '', 'navbarBackgroundColor' => '', 'navbarForegroundColor' => '', 'successColor' => '', 'successHover' => '', 'successShadow' => '', 'warningColor' => '', 'warningHover' => '', 'warningShadow' => '', 'dangerColor' => '', 'dangerHover' => '', 'dangerShadow' => ''], Response::HTTP_OK],
            [['primaryColor' => '', 'primaryHover' => '', 'primaryShadow' => '', 'secondaryColor' => '', 'secondaryHover' => 0, 'secondaryShadow' => '', 'navbarBackgroundColor' => '', 'navbarForegroundColor' => '', 'successColor' => '', 'successHover' => '', 'successShadow' => '', 'warningColor' => '', 'warningHover' => '', 'warningShadow' => '', 'dangerColor' => '', 'dangerHover' => '', 'dangerShadow' => ''], Response::HTTP_OK],
            [['primaryColor' => '', 'primaryHover' => '', 'primaryShadow' => '', 'secondaryColor' => '', 'secondaryHover' => '', 'secondaryShadow' => 0, 'navbarBackgroundColor' => '', 'navbarForegroundColor' => '', 'successColor' => '', 'successHover' => '', 'successShadow' => '', 'warningColor' => '', 'warningHover' => '', 'warningShadow' => '', 'dangerColor' => '', 'dangerHover' => '', 'dangerShadow' => ''], Response::HTTP_OK],
            [['primaryColor' => '', 'primaryHover' => '', 'primaryShadow' => '', 'secondaryColor' => '', 'secondaryHover' => '', 'secondaryShadow' => '', 'navbarBackgroundColor' => '', 'navbarForegroundColor' => '', 'successColor' => '#00ff00', 'successHover' => '', 'successShadow' => '', 'warningColor' => '', 'warningHover' => '', 'warningShadow' => '', 'dangerColor' => '', 'dangerHover' => '', 'dangerShadow' => ''], Response::HTTP_OK],
            [['primaryColor' => '', 'primaryHover' => '', 'primaryShadow' => '', 'secondaryColor' => '', 'secondaryHover' => '', 'secondaryShadow' => '', 'navbarBackgroundColor' => '', 'navbarForegroundColor' => '', 'successColor' => '', 'successHover' => 0, 'successShadow' => '', 'warningColor' => '', 'warningHover' => '', 'warningShadow' => '', 'dangerColor' => '', 'dangerHover' => '', 'dangerShadow' => ''], Response::HTTP_OK],
            [['primaryColor' => '', 'primaryHover' => '', 'primaryShadow' => '', 'secondaryColor' => '', 'secondaryHover' => '', 'secondaryShadow' => '', 'navbarBackgroundColor' => '', 'navbarForegroundColor' => '', 'successColor' => '', 'successHover' => '', 'successShadow' => 0, 'warningColor' => '', 'warningHover' => '', 'warningShadow' => '', 'dangerColor' => '', 'dangerHover' => '', 'dangerShadow' => ''], Response::HTTP_OK],
            [['primaryColor' => '', 'primaryHover' => '', 'primaryShadow' => '', 'secondaryColor' => '#ffffff', 'secondaryHover' => 0, 'secondaryShadow' => '', 'navbarBackgroundColor' => '', 'navbarForegroundColor' => '', 'successColor' => '', 'successHover' => '', 'successShadow' => '', 'warningColor' => '', 'warningHover' => '', 'warningShadow' => '', 'dangerColor' => '', 'dangerHover' => '', 'dangerShadow' => ''], Response::HTTP_OK],
            [['primaryColor' => '', 'primaryHover' => '', 'primaryShadow' => '', 'secondaryColor' => '#ffffff', 'secondaryHover' => '', 'secondaryShadow' => 0, 'navbarBackgroundColor' => '', 'navbarForegroundColor' => '', 'successColor' => '', 'successHover' => '', 'successShadow' => '', 'warningColor' => '', 'warningHover' => '', 'warningShadow' => '', 'dangerColor' => '', 'dangerHover' => '', 'dangerShadow' => ''], Response::HTTP_OK],
            [['primaryColor' => '', 'primaryHover' => '', 'primaryShadow' => '', 'secondaryColor' => '#ffffff', 'secondaryHover' => 0, 'secondaryShadow' => 0, 'navbarBackgroundColor' => '', 'navbarForegroundColor' => '', 'successColor' => '', 'successHover' => '', 'successShadow' => '', 'warningColor' => '', 'warningHover' => '', 'warningShadow' => '', 'dangerColor' => '', 'dangerHover' => '', 'dangerShadow' => ''], Response::HTTP_OK],
            [['primaryColor' => '#ffffff', 'primaryHover' => 0, 'primaryShadow' => 0, 'secondaryColor' => '#000000', 'secondaryHover' => '', 'secondaryShadow' => '', 'navbarBackgroundColor' => '', 'navbarForegroundColor' => '', 'successColor' => '', 'successHover' => '', 'successShadow' => '', 'warningColor' => '', 'warningHover' => '', 'warningShadow' => '', 'dangerColor' => '', 'dangerHover' => '', 'dangerShadow' => ''], Response::HTTP_OK],
            [['primaryColor' => '#ffffff', 'primaryHover' => 0, 'primaryShadow' => 0, 'secondaryColor' => '', 'secondaryHover' => 0, 'secondaryShadow' => '', 'navbarBackgroundColor' => '', 'navbarForegroundColor' => '', 'successColor' => '', 'successHover' => '', 'successShadow' => '', 'warningColor' => '', 'warningHover' => '', 'warningShadow' => '', 'dangerColor' => '', 'dangerHover' => '', 'dangerShadow' => ''], Response::HTTP_OK],
            [['primaryColor' => '#ffffff', 'primaryHover' => 0, 'primaryShadow' => 0, 'secondaryColor' => '', 'secondaryHover' => '', 'secondaryShadow' => 0, 'navbarBackgroundColor' => '', 'navbarForegroundColor' => '', 'successColor' => '', 'successHover' => '', 'successShadow' => '', 'warningColor' => '', 'warningHover' => '', 'warningShadow' => '', 'dangerColor' => '', 'dangerHover' => '', 'dangerShadow' => ''], Response::HTTP_OK],
            [['primaryColor' => '#ffffff', 'primaryHover' => 0, 'primaryShadow' => 0, 'secondaryColor' => '#000000', 'secondaryHover' => 0, 'secondaryShadow' => '', 'navbarBackgroundColor' => '', 'navbarForegroundColor' => '', 'successColor' => '', 'successHover' => '', 'successShadow' => '', 'warningColor' => '', 'warningHover' => '', 'warningShadow' => '', 'dangerColor' => '', 'dangerHover' => '', 'dangerShadow' => ''], Response::HTTP_OK],
            [['primaryColor' => '#ffffff', 'primaryHover' => 0, 'primaryShadow' => 0, 'secondaryColor' => '#000000', 'secondaryHover' => '', 'secondaryShadow' => 0, 'navbarBackgroundColor' => '', 'navbarForegroundColor' => '', 'successColor' => '', 'successHover' => '', 'successShadow' => '', 'warningColor' => '', 'warningHover' => '', 'warningShadow' => '', 'dangerColor' => '', 'dangerHover' => '', 'dangerShadow' => ''], Response::HTTP_OK],
            [['primaryColor' => '', 'primaryHover' => '', 'primaryShadow' => '', 'secondaryColor' => '', 'secondaryHover' => '', 'secondaryShadow' => '', 'navbarBackgroundColor' => '#ffffff', 'navbarForegroundColor' => '', 'successColor' => '', 'successHover' => '', 'successShadow' => '', 'warningColor' => '', 'warningHover' => '', 'warningShadow' => '', 'dangerColor' => '', 'dangerHover' => '', 'dangerShadow' => ''], Response::HTTP_OK],
            [['primaryColor' => '', 'primaryHover' => '', 'primaryShadow' => '', 'secondaryColor' => '', 'secondaryHover' => '', 'secondaryShadow' => '', 'navbarBackgroundColor' => '', 'navbarForegroundColor' => '#000000', 'successColor' => '', 'successHover' => '', 'successShadow' => '', 'warningColor' => '', 'warningHover' => '', 'warningShadow' => '', 'dangerColor' => '', 'dangerHover' => '', 'dangerShadow' => ''], Response::HTTP_OK],
            [['primaryColor' => '', 'primaryHover' => '', 'primaryShadow' => '', 'secondaryColor' => '', 'secondaryHover' => '', 'secondaryShadow' => '', 'navbarBackgroundColor' => '#ffffff', 'navbarForegroundColor' => '#000000', 'successColor' => '', 'successHover' => '', 'successShadow' => '', 'warningColor' => '', 'warningHover' => '', 'warningShadow' => '', 'dangerColor' => '', 'dangerHover' => '', 'dangerShadow' => ''], Response::HTTP_OK],
            [['primaryColor' => '#ffffff', 'primaryHover' => 0, 'primaryShadow' => 0, 'secondaryColor' => '#000000', 'secondaryHover' => 0, 'secondaryShadow' => 0, 'navbarBackgroundColor' => '', 'navbarForegroundColor' => '', 'successColor' => '', 'successHover' => '', 'successShadow' => '', 'warningColor' => '', 'warningHover' => '', 'warningShadow' => '', 'dangerColor' => '', 'dangerHover' => '', 'dangerShadow' => ''], Response::HTTP_OK],
            [['primaryColor' => '#ffffff', 'primaryHover' => 0, 'primaryShadow' => 0, 'secondaryColor' => '#000000', 'secondaryHover' => 0, 'secondaryShadow' => 0, 'navbarBackgroundColor' => '#ffffff', 'navbarForegroundColor' => '', 'successColor' => '', 'successHover' => '', 'successShadow' => '', 'warningColor' => '', 'warningHover' => '', 'warningShadow' => '', 'dangerColor' => '', 'dangerHover' => '', 'dangerShadow' => ''], Response::HTTP_OK],
            [['primaryColor' => '#ffffff', 'primaryHover' => 0, 'primaryShadow' => 0, 'secondaryColor' => '#000000', 'secondaryHover' => 0, 'secondaryShadow' => 0, 'navbarBackgroundColor' => '', 'navbarForegroundColor' => '#000000', 'successColor' => '', 'successHover' => '', 'successShadow' => '', 'warningColor' => '', 'warningHover' => '', 'warningShadow' => '', 'dangerColor' => '', 'dangerHover' => '', 'dangerShadow' => ''], Response::HTTP_OK],
            // Cas valides
            [['primaryColor' => '#ffffff', 'primaryHover' => 0, 'primaryShadow' => 0, 'secondaryColor' => '#000000', 'secondaryHover' => 0, 'secondaryShadow' => 0, 'navbarBackgroundColor' => '#ffffff', 'navbarForegroundColor' => '#000000', 'successColor' => '#00ff00', 'successHover' => 0, 'successShadow' => 0, 'warningColor' => '#ffff00', 'warningHover' => 0, 'warningShadow' => 0, 'dangerColor' => '#ff0000', 'dangerHover' => 0, 'dangerShadow' => 0], Response::HTTP_FOUND],
        ];
    }

    /**
     * @dataProvider formulaireEditionDataProvider
     *
     * @covers \App\Controller\UcaGest\Parametrage\StyleController::modifierAction
     *
     * @param mixed $data
     * @param mixed $httpResponse
     */
    public function testModifierStyle($data, $httpResponse)
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $router = static::getContainer()->get(RouterInterface::class);

        $userTest = static::getContainer()->get(UtilisateurRepository::class)->findOneByEmail('user_admin_complet@test.fr');
        $this->client->loginUser($userTest, 'app');

        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('ucabundle_style');
        $this->client->request(
            'POST',
            $router->generate('UcaGest_StyleModifier'),
            ['ucabundle_style' => array_merge($data, ['_token' => $csrfToken->getValue(), 'save' => '']) ],
        );

        $this->assertResponseStatusCodeSame($httpResponse);

        if ($httpResponse === Response::HTTP_FOUND) {
            $stylePreview = $em->getRepository(Style::class)->findOneBy(['preview' => true]);
            $style = $em->getRepository(Style::class)->findOneBy(['preview' => false]);

            $this->assertEquals($data['primaryColor'], $stylePreview->getPrimaryColor());
            $this->assertEquals($data['primaryHover'], $stylePreview->getPrimaryHover());
            $this->assertEquals($data['primaryShadow'], $stylePreview->getPrimaryShadow());
            $this->assertEquals($data['secondaryColor'], $stylePreview->getSecondaryColor());
            $this->assertEquals($data['secondaryHover'], $stylePreview->getSecondaryHover());
            $this->assertEquals($data['secondaryShadow'], $stylePreview->getSecondaryShadow());
            $this->assertEquals($data['successColor'], $stylePreview->getSuccessColor());
            $this->assertEquals($data['successHover'], $stylePreview->getSuccessHover());
            $this->assertEquals($data['successShadow'], $stylePreview->getSuccessShadow());
            $this->assertEquals($data['warningColor'], $stylePreview->getWarningColor());
            $this->assertEquals($data['warningHover'], $stylePreview->getWarningHover());
            $this->assertEquals($data['warningShadow'], $stylePreview->getWarningShadow());
            $this->assertEquals($data['dangerColor'], $stylePreview->getDangerColor());
            $this->assertEquals($data['dangerHover'], $stylePreview->getDangerHover());
            $this->assertEquals($data['dangerShadow'], $stylePreview->getDangerShadow());
            $this->assertEquals($data['navbarBackgroundColor'], $stylePreview->getNavbarBackgroundColor());
            $this->assertEquals($data['navbarForegroundColor'], $stylePreview->getNavbarForegroundColor());

            $this->assertNotEquals($data['primaryColor'], $style->getPrimaryColor());
            $this->assertNotEquals($data['primaryHover'], $style->getPrimaryHover());
            $this->assertNotEquals($data['primaryShadow'], $style->getPrimaryShadow());
            $this->assertNotEquals($data['secondaryColor'], $style->getSecondaryColor());
            $this->assertNotEquals($data['secondaryHover'], $style->getSecondaryHover());
            $this->assertNotEquals($data['secondaryShadow'], $style->getSecondaryShadow());
            $this->assertNotEquals($data['successColor'], $style->getSuccessColor());
            $this->assertNotEquals($data['successHover'], $style->getSuccessHover());
            $this->assertNotEquals($data['successShadow'], $style->getSuccessShadow());
            $this->assertNotEquals($data['warningColor'], $style->getWarningColor());
            $this->assertNotEquals($data['warningHover'], $style->getWarningHover());
            $this->assertNotEquals($data['warningShadow'], $style->getWarningShadow());
            $this->assertNotEquals($data['dangerColor'], $style->getDangerColor());
            $this->assertNotEquals($data['dangerHover'], $style->getDangerHover());
            $this->assertNotEquals($data['dangerShadow'], $style->getDangerShadow());
            $this->assertNotEquals($data['navbarBackgroundColor'], $style->getNavbarBackgroundColor());
            $this->assertNotEquals($data['navbarForegroundColor'], $style->getNavbarForegroundColor());
        }
    }

    /**
     * @covers \App\Controller\UcaGest\Parametrage\StyleController::previewAction
     */
    public function testPostPreview()
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $router = static::getContainer()->get(RouterInterface::class);

        $userTest = static::getContainer()->get(UtilisateurRepository::class)->findOneByEmail('user_admin_complet@test.fr');
        $this->client->loginUser($userTest, 'app');

        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('ucabundle_style_preview');
        $this->client->request(
            'POST',
            $router->generate('UcaGest_StylePreview'),
            ['ucabundle_style_preview' => ['_token' => $csrfToken->getValue(), 'save' => ''] ],
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $stylePreview = $em->getRepository(Style::class)->findOneBy(['preview' => true]);
        $style = $em->getRepository(Style::class)->findOneBy(['preview' => false]);

        $this->assertEquals($stylePreview->getPrimaryColor(), $style->getPrimaryColor());
        $this->assertEquals($stylePreview->getPrimaryHover(), $style->getPrimaryHover());
        $this->assertEquals($stylePreview->getPrimaryShadow(), $style->getPrimaryShadow());
        $this->assertEquals($stylePreview->getSecondaryColor(), $style->getSecondaryColor());
        $this->assertEquals($stylePreview->getSecondaryHover(), $style->getSecondaryHover());
        $this->assertEquals($stylePreview->getSecondaryShadow(), $style->getSecondaryShadow());
        $this->assertEquals($stylePreview->getSuccessColor(), $style->getSuccessColor());
        $this->assertEquals($stylePreview->getSuccessHover(), $style->getSuccessHover());
        $this->assertEquals($stylePreview->getSuccessShadow(), $style->getSuccessShadow());
        $this->assertEquals($stylePreview->getWarningColor(), $style->getWarningColor());
        $this->assertEquals($stylePreview->getWarningHover(), $style->getWarningHover());
        $this->assertEquals($stylePreview->getWarningShadow(), $style->getWarningShadow());
        $this->assertEquals($stylePreview->getDangerColor(), $style->getDangerColor());
        $this->assertEquals($stylePreview->getDangerHover(), $style->getDangerHover());
        $this->assertEquals($stylePreview->getDangerShadow(), $style->getDangerShadow());
        $this->assertEquals($stylePreview->getNavbarBackgroundColor(), $style->getNavbarBackgroundColor());
        $this->assertEquals($stylePreview->getNavbarForegroundColor(), $style->getNavbarForegroundColor());
    }
}
