<?php

namespace App\Tests\Controller\UcaWeb;

use App\Entity\Uca\Groupe;
use App\Entity\Uca\Utilisateur;
use App\Entity\Uca\UtilisateurCreditHistorique;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * @internal
 * @coversNothing
 */
class MesCreditsControllerTest extends WebTestCase
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

        $user_credit = (new Utilisateur())
            ->setEmail('user_credit@test.fr')
            ->setUsername('user_credit')
            ->setPassword('password')
            ->setCgvAcceptees(true)
            ->setEnabled(true)
            ->setRoles([])
            ->addGroup($goupe_user_non_admin)
        ;
        $this->em->persist($user_credit);

        $user_no_credit = (new Utilisateur())
            ->setEmail('user_no_credit@test.fr')
            ->setUsername('user_no_credit')
            ->setPassword('password')
            ->setCgvAcceptees(true)
            ->setEnabled(true)
            ->setRoles([])
            ->addGroup($goupe_user_non_admin)
        ;
        $this->em->persist($user_no_credit);

        $credit = new UtilisateurCreditHistorique($user_credit, 8.0, null, 'credit', "Ajout manuel de crédits", null);
        $this->em->persist($credit);
        $user_credit->addCredit($credit);

        $this->em->flush();

        $this->ids['groupe_user_non_admin'] = $goupe_user_non_admin->getId();
        $this->ids['user_credit'] = $user_credit->getId();
        $this->ids['user_no_credit'] = $user_no_credit->getId();
        $this->ids['credit'] = $credit->getId();
    }

    protected function tearDown(): void
    {
        $container = static::getContainer();

        $credit = $this->em->getRepository(UtilisateurCreditHistorique::class)->find($this->ids['credit']);
        $credit->setUtilisateur(null);
        $this->em->remove($credit);

        $this->em->remove($this->em->getRepository(Utilisateur::class)->find($this->ids['user_credit']));
        $this->em->remove($this->em->getRepository(Utilisateur::class)->find($this->ids['user_no_credit']));

        $this->em->remove($this->em->getRepository(Groupe::class)->find($this->ids['groupe_user_non_admin']));

        $this->em->flush();

        static::ensureKernelShutdown();
    }

    public function accessDataProvider()
    {
        return [
            // UcaWeb_MesCredits
            [null,'GET','UcaWeb_MesCredits',Response::HTTP_FOUND],
            ['user_credit@test.fr','GET','UcaWeb_MesCredits',Response::HTTP_OK],
            ['user_no_credit@test.fr','GET','UcaWeb_MesCredits',Response::HTTP_OK],

            // UcaWeb_MesCreditsExport
            [null,'GET','UcaWeb_MesCreditsExport',Response::HTTP_FOUND,['id'=>'id_credit']],
            ['user_no_credit@test.fr','GET','UcaWeb_MesCreditsExport',Response::HTTP_FOUND,['id'=>'id_credit']],
        ];
    }

    public function dataTableDataProvider()
    {
        return [
            ['user_credit@test.fr','GET','UcaWeb_MesCredits',Response::HTTP_OK,[
                    'draw' => 1, 'columns' => [['data' => 'id', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'avoir', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'commandeAssociee', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'date', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'operation', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'typeOperation', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'montant', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => '7', 'name' => '', 'searchable' => false, 'orderable' => false, 'search' => ['value' => '', 'regex' => false]]], 'order' => [['column' => 0, 'dir' => 'asc']], 'start' => 0, 'length' => 10, 'search' => ['value' => '', 'regex' => false], '_' => 1657195188930
                ],
                [],
                true
            ]
        ];
    }

    /**
     * @dataProvider accessDataProvider
     * @dataProvider dataTableDataProvider
     *
     * @covers App\Controller\UcaWeb\MesCreditsController::voirCreditsAction
     * @covers App\Controller\UcaWeb\MesCreditsController::exportCreditAction
     */
    public function testAccesRoutes($userEmail, $method, $routeName, $httpResponse, $urlParameters = [], $body = [], $ajax = false): void
    {
        $router = static::getContainer()->get(RouterInterface::class);

        if (null != $userEmail) {
            $userTest = $this->em->getRepository(Utilisateur::class)->findOneByEmail($userEmail);
            $this->client->loginUser($userTest);
        }
        $route = $router->generate($routeName, $urlParameters);
        $route = str_replace('id_credit', $this->ids['credit'], $route);

        if ($ajax) {
            $this->client->xmlHttpRequest($method, $route, $body);
        } else {
            $this->client->request($method, $route, $body);
        }
        $this->assertResponseStatusCodeSame($httpResponse);
    }

    /**
     * @covers App\Controller\UcaWeb\MesCreditsController::exportCreditAction
     */
    public function testExportOK()
    {
        ob_start();
        $router = static::getContainer()->get(RouterInterface::class);
        $userTest = $this->em->getRepository(Utilisateur::class)->findOneByEmail('user_credit@test.fr');
        $this->client->loginUser($userTest);
        $route = $router->generate('UcaWeb_MesCreditsExport', ['id'=>$this->ids['credit']]);
        $this->client->request('GET', $route);
        $this->client->getResponse()->sendContent();
        $response = ob_get_contents();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertStringStartsWith("%PDF-", $response);
        $this->assertStringEndsWith("\n%%EOF\n", $response);
        ob_end_clean();
    }
}
