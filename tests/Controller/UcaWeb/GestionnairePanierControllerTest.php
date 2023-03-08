<?php

namespace App\Tests\Controller\UcaWeb;

use App\Entity\Uca\Commande;
use App\Entity\Uca\CommandeDetail;
use App\Entity\Uca\Creneau;
use App\Entity\Uca\DhtmlxSerie;
use App\Entity\Uca\FormatActivite;
use App\Entity\Uca\FormatAvecCreneau;
use App\Entity\Uca\Groupe;
use App\Entity\Uca\Inscription;
use App\Entity\Uca\Utilisateur;
use App\Entity\Uca\UtilisateurCreditHistorique;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * @internal
 *
 * @coversNothing
 */
class GestionnairePanierControllerTest extends WebTestCase
{
    private $client;

    private $router;

    private $em;

    private $ids = [];

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->router = static::getContainer()->get(RouterInterface::class);

        // Création Utilisateur
        $goupe_user_non_admin = (new Groupe('test_controleur_goupe_user_non_admin', []))
            ->setLibelle('test_controleur_goupe_user_non_admin')
        ;
        $this->em->persist($goupe_user_non_admin);

        $groupe_user_admin = (new Groupe('test_controleur_groupe_user_admin', ['ROLE_ADMIN']))
            ->setLibelle('test_controleur_goupe_user_admin')
        ;
        $this->em->persist($groupe_user_admin);

        $groupe_user_gestionnaire_panier = (new Groupe('test_controleur_groupe_user_gestionnaire_panier', ['ROLE_GESTION_PAIEMENT_COMMANDE']))
            ->setLibelle('test_controleur_goupe_user_gestionnaire_panier')
        ;
        $this->em->persist($groupe_user_gestionnaire_panier);

        $user_empty_panier = (new Utilisateur())
            ->setEmail('user_empty_panier@test.fr')
            ->setUsername('user_empty_panier')
            ->setPassword('password')
            ->setCgvAcceptees(true)
            ->setEnabled(true)
            ->setRoles([])
            ->addGroup($goupe_user_non_admin)
        ;
        $this->em->persist($user_empty_panier);

        $user_not_empty_panier = (new Utilisateur())
            ->setEmail('user_not_empty_panier@test.fr')
            ->setUsername('user_not_empty_panier')
            ->setPassword('password')
            ->setCgvAcceptees(true)
            ->setEnabled(true)
            ->setRoles([])
            ->addGroup($goupe_user_non_admin)
        ;
        $this->em->persist($user_not_empty_panier);

        $user_admin = (new Utilisateur())
            ->setEmail('user_admin@test.fr')
            ->setUsername('user_admin')
            ->setPassword('password')
            ->setCgvAcceptees(true)
            ->setEnabled(true)
            ->setRoles([])
            ->addGroup($groupe_user_admin)
        ;
        $this->em->persist($user_admin);

        $user_gestionnaire = (new Utilisateur())
            ->setEmail('user_gestionnaire@test.fr')
            ->setUsername('user_gestionnaire')
            ->setPassword('password')
            ->setCgvAcceptees(true)
            ->setEnabled(true)
            ->setRoles([])
            ->addGroup($groupe_user_gestionnaire_panier)
        ;
        $this->em->persist($user_gestionnaire);

        $user_admin_gestionnaire = (new Utilisateur())
            ->setEmail('user_admin_gestionnaire@test.fr')
            ->setUsername('user_admin_gestionnaire')
            ->setPassword('password')
            ->setCgvAcceptees(true)
            ->setEnabled(true)
            ->setRoles([])
            ->addGroup($groupe_user_gestionnaire_panier)
            ->addGroup($groupe_user_admin)
        ;
        $this->em->persist($user_admin_gestionnaire);

        $formatActivite = (new FormatAvecCreneau())
            ->setLibelle('FormatAvecCreneau')
            ->setCapacite(1)
            ->setDescription('test')
            ->setDateDebutEffective(new \DateTime())
            ->setDateDebutInscription(new \DateTime())
            ->setDateDebutPublication(new \DateTime())
            ->setDateFinEffective((new \DateTime())->add(new \DateInterval('P1D')))
            ->setDateFinInscription((new \DateTime())->add(new \DateInterval('P1D')))
            ->setDateFinPublication((new \DateTime())->add(new \DateInterval('P1D')))
            ->setImage('test')
            ->setStatut(1)
            ->setTarifLibelle('Tarif')
            ->setListeLieux('[]')
            ->setListeAutorisations('[]')
            ->setListeNiveauxSportifs('[]')
            ->setListeProfils('[]')
            ->setListeEncadrants('[]')
            ->setPromouvoir(false)
            ->setEstPayant(true)
            ->setEstEncadre(false)
        ;
        $this->em->persist($formatActivite);

        $serie = (new DhtmlxSerie())
            ->setDateDebut(new \DateTime())
            ->setDateFin((new \DateTime())->add(new \DateInterval('P1D')))

        ;
        $this->em->persist($serie);

        $creneau = (new Creneau())
            ->setCapacite(1)
            ->setFormatActivite($formatActivite)
            ->setSerie($serie)
        ;
        $this->em->persist($creneau);

        $inscription = (new Inscription($creneau, $user_not_empty_panier, ['typeInscription' => 'format']));
        $this->em->persist($inscription);

        $commande = new Commande($user_not_empty_panier);
        $commande->setDatePanier((new \DateTime())->add(new \DateInterval('P1D')))->setMontantTotal('1€')->setNumeroCommande(126875);
        $this->em->persist($commande);
        $user_not_empty_panier->addCommande($commande);

        $commandeDetail1 = new CommandeDetail($commande, 'inscription', $inscription);
        $commandeDetail1->setMontant(10);
        $commandeDetail1->setTva(2);
        $this->em->persist($commandeDetail1);

        $commandeDetail2 = new CommandeDetail($commande, 'inscription', $inscription);
        $commandeDetail2->setMontant(2);
        $commandeDetail2->setTva(2);
        $this->em->persist($commandeDetail2);

        $credit = new UtilisateurCreditHistorique($user_not_empty_panier, 8.0, null, 'credit', 'ajout manuel de crédits', $commande->getNumeroCommande());
        $this->em->persist($credit);
        $user_not_empty_panier->addCredit($credit);

        $commande->updateMontantTotal();

        $this->em->flush();

        $this->ids['groupe_user_non_admin'] = $goupe_user_non_admin->getId();
        $this->ids['groupe_user_admin'] = $groupe_user_admin->getId();
        $this->ids['groupe_gestionnaire'] = $groupe_user_gestionnaire_panier->getId();
        $this->ids['user_empty_panier'] = $user_empty_panier->getId();
        $this->ids['user_not_empty_panier'] = $user_not_empty_panier->getId();
        $this->ids['user_gestionnaire'] = $user_gestionnaire->getId();
        $this->ids['user_admin'] = $user_admin->getId();
        $this->ids['user_admin_gestionnaire'] = $user_admin_gestionnaire->getId();
        $this->ids['formatActivite'] = $formatActivite->getId();
        $this->ids['serie'] = $serie->getId();
        $this->ids['creneau'] = $creneau->getId();
        $this->ids['inscription'] = $inscription->getId();
        $this->ids['commande'] = $commande->getId();
        $this->ids['commandeDetail1'] = $commandeDetail1->getId();
        $this->ids['commandeDetail2'] = $commandeDetail2->getId();
        $this->ids['credit'] = $credit->getId();
    }

    public function accessDataProvider()
    {
        return [
            // UcaWeb_CommandeEnAttenteLister
            [null, 'GET', 'UcaWeb_CommandeEnAttenteLister', Response::HTTP_FOUND],
            ['user_admin@test.fr', 'GET', 'UcaWeb_CommandeEnAttenteLister', Response::HTTP_FORBIDDEN],
            ['user_gestionnaire@test.fr', 'GET', 'UcaWeb_CommandeEnAttenteLister', Response::HTTP_OK],

            // UcaWeb_CommandeEnAttenteVoir
            [null, 'GET', 'UcaWeb_CommandeEnAttenteVoir', Response::HTTP_FOUND, ['id' => 'id_commande']],
            ['user_admin@test.fr', 'GET', 'UcaWeb_CommandeEnAttenteVoir', Response::HTTP_FORBIDDEN, ['id' => 'id_commande']],
            ['user_gestionnaire@test.fr', 'GET', 'UcaWeb_CommandeEnAttenteVoir', Response::HTTP_OK, ['id' => 'id_commande']],

            // UcaWeb_CommandeEnAttenteSupprimer
            // [null,'GET','UcaWeb_CommandeEnAttenteSupprimer',Response::HTTP_FOUND,['id' => 'id_commande']],
            ['user_admin@test.fr', 'GET', 'UcaWeb_CommandeEnAttenteSupprimer', Response::HTTP_FORBIDDEN, ['id' => 'id_commande']],
            ['user_gestionnaire@test.fr', 'GET', 'UcaWeb_CommandeEnAttenteSupprimer', Response::HTTP_FOUND, ['id' => 'id_commande']],

            // UcaWeb_ArticleSupprimer
            [null, 'GET', 'UcaWeb_ArticleSupprimer', Response::HTTP_FOUND, ['id' => 'id_commande_detail']],
            ['user_admin@test.fr', 'GET', 'UcaWeb_ArticleSupprimer', Response::HTTP_FORBIDDEN, ['id' => 'id_commande_detail']],
            ['user_gestionnaire@test.fr', 'GET', 'UcaWeb_ArticleSupprimer', Response::HTTP_FOUND, ['id' => 'id_commande_detail']],
        ];
    }

    public function dataTableDataProvider()
    {
        return [
            ['user_gestionnaire@test.fr', 'GET', 'UcaWeb_CommandeEnAttenteLister', Response::HTTP_OK, [
                'draw' => 1, 'columns' => [['data' => 'id', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'statut', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'dateCommande', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'montantTotal', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'id', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'numeroCommande', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'utilisateur.nom', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'utilisateur.prenom', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'dateCommande', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'montantTotalFormated', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => '10', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]]], 'order' => [['column' => 0, 'dir' => 'asc']], 'start' => 0, 'length' => 10, 'search' => ['value' => '', 'regex' => false], '_' => 1657191110725,
            ],
                [],
                true,
            ],
            ['user_gestionnaire@test.fr', 'GET', 'UcaWeb_CommandeEnAttenteVoir', Response::HTTP_OK, [
                'id' => 'id_commande',
                'draw' => 1, 'columns' => [['data' => 'id', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'statut', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'dateCommande', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'montantTotal', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'id', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'numeroCommande', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'utilisateur.nom', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'utilisateur.prenom', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'dateCommande', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'montantTotalFormated', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => '10', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]]], 'order' => [['column' => 0, 'dir' => 'asc']], 'start' => 0, 'length' => 10, 'search' => ['value' => '', 'regex' => false], '_' => 1657191110725,
            ],
                [],
                true,
            ],
        ];
    }

    /**
     * @dataProvider accessDataProvider
     * @dataProvider dataTableDataProvider
     *
     * @covers \App\Controller\UcaWeb\GestionnairePanierController::listerAction
     * @covers \App\Controller\UcaWeb\GestionnairePanierController::supprimerAction
     * @covers \App\Controller\UcaWeb\GestionnairePanierController::supprimerArticleAction
     * @covers \App\Controller\UcaWeb\GestionnairePanierController::voirAction
     *
     * @param mixed $userEmail
     * @param mixed $method
     * @param mixed $routeName
     * @param mixed $httpResponse
     * @param mixed $urlParameters
     * @param mixed $body
     * @param mixed $ajax
     */
    public function testAccesRoutes($userEmail, $method, $routeName, $httpResponse, $urlParameters = [], $body = [], $ajax = false): void
    {
        $router = static::getContainer()->get(RouterInterface::class);

        if (null != $userEmail) {
            $userTest = $this->em->getRepository(Utilisateur::class)->findOneByEmail($userEmail);
            $this->client->loginUser($userTest, 'app');
        }
        $route = $router->generate($routeName, $urlParameters);
        $route = str_replace('id_commande_detail', $this->ids['commandeDetail2'], $route);
        $route = str_replace('id_commande', $this->ids['commande'], $route);

        if ($ajax) {
            $this->client->xmlHttpRequest($method, $route, $body);
        } else {
            $this->client->request($method, $route, $body);
        }
        $this->assertResponseStatusCodeSame($httpResponse);
    }

    /**
     * @covers \App\Controller\UcaWeb\GestionnairePanierController::supprimerArticleAction
     */
    public function testArticleSupprimer()
    {
        $route = $this->router->generate('UcaWeb_ArticleSupprimer', ['id' => $this->ids['commandeDetail2']]);
        $user = $this->em->getRepository(Utilisateur::class)->find($this->ids['user_gestionnaire']);
        $this->client->loginUser($user, 'app');
        $this->client->request('GET', $route);
        $expectedRedirection = $this->router->generate('UcaWeb_CommandeEnAttenteVoir', ['id' => $this->ids['commande']]);
        $this->assertResponseRedirects($expectedRedirection);
        $commande = $this->em->getRepository(Commande::class)->find($this->ids['commande']);
        $this->assertNotNull($commande);
    }

    /**
     * @covers \App\Controller\UcaWeb\GestionnairePanierController::supprimerAction
     */
    public function testCommandeEnAttenteSupprimer()
    {
        $route = $this->router->generate('UcaWeb_CommandeEnAttenteSupprimer', ['id' => $this->ids['commande']]);
        $user = $this->em->getRepository(Utilisateur::class)->find($this->ids['user_gestionnaire']);
        $this->client->loginUser($user, 'app');
        $this->client->request('GET', $route);
        $expectedRedirection = $this->router->generate('UcaWeb_CommandeEnAttenteLister');
        $this->assertResponseRedirects($expectedRedirection);
        $commande = $this->em->getRepository(Commande::class)->find($this->ids['commande']);
        $this->assertNotNull($commande);
        $this->assertEqualsIgnoringCase('annule', $commande->getStatut());
    }
}
