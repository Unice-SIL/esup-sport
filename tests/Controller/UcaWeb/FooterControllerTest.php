<?php

namespace App\Tests\Controller\UcaWeb;

use App\Entity\Uca\Groupe;
use App\Entity\Uca\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @internal
 * @coversNothing
 */
class FooterControllerTest extends WebTestCase
{
    private $client;
    private $em;
    private $router;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->router = static::getContainer()->get(RouterInterface::class);

        $goupe_user = (new Groupe('goupe_user_non_admin', []))
            ->setLibelle('goupe_user_non_admin')
        ;
        $this->em->persist($goupe_user);

        $user_cgv_acceptees = (new Utilisateur())
            ->setEmail('user_cgv_acceptees@test.fr')
            ->setUsername('user_cgv_acceptees')
            ->setPassword('password')
            ->setCgvAcceptees(true)
            ->setRoles([])
            ->addGroup($goupe_user)
        ;
        $this->em->persist($user_cgv_acceptees);

        $user_cgv_refusees = (new Utilisateur())
            ->setEmail('user_cgv_refusees@test.fr')
            ->setUsername('user_cgv_refusees')
            ->setPassword('password')
            ->setCgvAcceptees(false)
            ->setRoles([])
            ->addGroup($goupe_user)
        ;
        $this->em->persist($user_cgv_refusees);

        $this->em->flush();
    }

    protected function tearDown(): void
    {
        $this->em->remove($this->em->getRepository(Utilisateur::class)->findOneByEmail('user_cgv_acceptees@test.fr'));
        $this->em->remove($this->em->getRepository(Utilisateur::class)->findOneByEmail('user_cgv_refusees@test.fr'));
        $this->em->remove($this->em->getRepository(Groupe::class)->findOneByLibelle('goupe_user_non_admin'));

        $this->em->flush();

        static::ensureKernelShutdown();
    }

    /**
     * @covers \App\Controller\UcaWeb\FooterController::DonneesPersonnellesAction
     */
    public function testRouteDonneesPersonnelles(): void
    {
        $route = $this->router->generate('UcaWeb_DonneesPersonnelles');
        $crawler = $this->client->request('GET', $route);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Données personnelles');
        $this->assertEquals(1, $crawler->filter('h2:contains("Finalités du traitement")')->count());
    }

    public function testClicLienDonneesPersonnelles(): void
    {
        $route = $this->router->generate('UcaGest_Accueil');
        $crawler = $this->client->request('GET', $route);
        $crawler = $this->client->clickLink('Données personnelles');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Données personnelles');
        $this->assertEquals(1, $crawler->filter('h2:contains("Finalités du traitement")')->count());
    }

    /**
     * @covers \App\Controller\UcaWeb\FooterController::CGVAction
     */
    public function testGetRouteCGV(): void
    {
        // User non connecté
        $route = $this->router->generate('UcaWeb_CGV');
        $crawler = $this->client->request('GET', $route);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Conditions générales de vente');
        $this->assertEquals(1, $crawler->filter('strong:contains("Article 1 - Généralités")')->count());

        // User connecté avec CGV acceptée
        $user_accepted = $this->em->getRepository(Utilisateur::class)->findOneByEmail('user_cgv_acceptees@test.fr');
        $this->client->loginUser($user_accepted);
        $route = $this->router->generate('UcaWeb_CGV');
        $crawler = $this->client->request('GET', $route);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Conditions générales de vente');
        $this->assertEquals(1, $crawler->filter('strong:contains("Article 1 - Généralités")')->count());
        $checkboxCgv = $crawler->filter('input:contains("J’ai pris connaissance et j’accepte les conditions générales de vente")');
        $this->assertEquals(0, $checkboxCgv->count());

        // // User connecté avec CGV refusée
        $user_refused = $this->em->getRepository(Utilisateur::class)->findOneByEmail('user_cgv_refusees@test.fr');
        $this->client->loginUser($user_refused);
        $route = $this->router->generate('UcaWeb_CGV');
        $crawler = $this->client->request('GET', $route);
        $checkboxCgv = $crawler->filter('label:contains("J’ai pris connaissance et j’accepte les conditions générales de vente")');
        $this->assertGreaterThan(0, $checkboxCgv->count());
    }

    /**
     * @covers \App\Controller\UcaWeb\FooterController::CGVAction
     */
    public function testPostRouteCGVRefusees()
    {
        $user_refused = $this->em->getRepository(Utilisateur::class)->findOneByEmail('user_cgv_refusees@test.fr');
        $this->client->loginUser($user_refused);
        $route = $this->router->generate('UcaWeb_CGV');
        $this->client->request('POST', $route, ['UtilisateurCgvType' => ['save' => '', '_token' => static::getContainer()->get('security.csrf.token_manager')->getToken('UtilisateurCgvType')->getValue()]]);
        $response = $this->client->getResponse()->getContent();
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsStringIgnoringCase(static::getContainer()->get(TranslatorInterface::class)->trans('cgv.refusees', [], null, 'fr'), $response);
    }

    /**
     * @covers \App\Controller\UcaWeb\FooterController::CGVAction
     */
    public function testPostRouteCGVAcceptees()
    {
        $route = $this->router->generate('UcaWeb_CGV');
        $user_accepted = $this->em->getRepository(Utilisateur::class)->findOneByEmail('user_cgv_refusees@test.fr');
        $this->client->loginUser($user_accepted);
        $this->client->request('POST', $route, ['UtilisateurCgvType' => ['cgvAcceptees' => true, 'save' => '', '_token' => static::getContainer()->get('security.csrf.token_manager')->getToken('UtilisateurCgvType')->getValue()]]);
        $response = $this->client->getResponse()->getContent();
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsStringIgnoringCase(static::getContainer()->get(TranslatorInterface::class)->trans('cgv.acceptees', [], null, 'fr'), $response);
    }

    public function testClicLienCGV(): void
    {
        $route = $this->router->generate('UcaWeb_CGV');
        $crawler = $this->client->request('GET', $route);
        $crawler = $this->client->clickLink('CGV');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Conditions générales de vente');
        $this->assertEquals(1, $crawler->filter('strong:contains("Article 1 - Généralités")')->count());
    }
}
