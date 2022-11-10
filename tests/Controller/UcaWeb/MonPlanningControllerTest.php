<?php

namespace App\Tests\Controller\UcaWeb;

use App\Entity\Uca\Activite;
use App\Entity\Uca\Appel;
use App\Entity\Uca\ClasseActivite;
use App\Entity\Uca\Creneau;
use App\Entity\Uca\DhtmlxEvenement;
use App\Entity\Uca\DhtmlxSerie;
use App\Entity\Uca\FormatActivite;
use App\Entity\Uca\FormatAvecCreneau;
use App\Entity\Uca\FormatSimple;
use App\Entity\Uca\Inscription;
use App\Entity\Uca\Lieu;
use App\Entity\Uca\Reservabilite;
use App\Entity\Uca\TypeActivite;
use App\Entity\Uca\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * @internal
 * @coversNothing
 */
class MonPlanningControllerTest extends WebTestCase
{
    private $client;

    private $router;

    private $em;

    private $ids = [];

    private $tokens = [];

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->router = static::getContainer()->get(RouterInterface::class);
        $this->em = static::getContainer()->get(EntityManagerInterface::class);

        $this->tokens['ucabundle_evenement'] = static::getContainer()->get('security.csrf.token_manager')->getToken('ucabundle_evenement')->getValue();

        $user_encadrant = (new Utilisateur())
            ->setUsername('encadrant')
            ->setPassword('password')
            ->setCgvAcceptees(true)
            ->setEnabled(true)
            ->setEmail('encadrant@test.fr')
            ->setRoles(['ROLE_ENCADRANT'])
        ;
        $this->em->persist($user_encadrant);

        $user_encadrant_gestion = (new Utilisateur())
            ->setUsername('encadrant')
            ->setPassword('password')
            ->setCgvAcceptees(true)
            ->setEnabled(true)
            ->setEmail('encadrant@test.fr')
            ->setRoles(['ROLE_ENCADRANT', 'ROLE_GESTION_FORMAT_ACTIVITE_ECRITURE'])
        ;
        $this->em->persist($user_encadrant_gestion);

        $user = (new Utilisateur())
            ->setUsername('user')
            ->setPassword('password')
            ->setCgvAcceptees(true)
            ->setEnabled(true)
            ->setEmail('user@test.fr')
            ->setRoles([])
            ->setNumeroNfc('9876543210ABCD')
            ->setNom('test')
            ->setPrenom('test')
        ;
        $this->em->persist($user);

        $typeActivite = (new TypeActivite())
            ->setLibelle('Type Activite Test')
        ;
        $this->em->persist($typeActivite);

        $classeActivite = (new ClasseActivite())
            ->setLibelle('Classe Actvite Test')
            ->setTypeActiviteLibelle('Type Activite Test')
            ->setImage('test.jpg')
            ->setTypeActivite($typeActivite)
        ;
        $this->em->persist($classeActivite);

        $activite = (new Activite())
            ->setLibelle('Activite Test')
            ->setImage('test.jpg')
            ->setDescription('Description Activite Test')
            ->setClasseActiviteLibelle('Classe Activite Test')
            ->setClasseActivite($classeActivite)
        ;
        $this->em->persist($activite);

        $formatSimple = (new FormatSimple())
            ->setCapacite(5)
            ->setLibelle('ActivitéTest')
            ->setDescription('Description de l\'activité')
            ->setDateDebutEffective(new \DateTime())
            ->setDateDebutPublication(new \DateTime())
            ->setDateDebutInscription(new \DateTime())
            ->setDateFinEffective(new \DateTime())
            ->setDateFinPublication(new \DateTime())
            ->setDateFinPublication(new \DateTime())
            ->setDateFinInscription(new \DateTime())
            ->setImage('')
            ->setEstPayant(false)
            ->setEstEncadre(false)
            ->setActivite($activite)
        ;
        $this->em->persist($formatSimple);

        $evenement = new DhtmlxEvenement();
        $evenement->setDateDebut(new \DateTime());
        $evenement->setDateFin((new \DateTime())->add(new \DateInterval('P1D')));
        $evenement->setFormatSimple($formatSimple);
        $this->em->persist($evenement);

        $evenement2 = new DhtmlxEvenement();
        $evenement2->setDateDebut(new \DateTime());
        $evenement2->setDateFin((new \DateTime())->add(new \DateInterval('P1D')));
        $this->em->persist($evenement2);

        $evenement3 = new DhtmlxEvenement();
        $evenement3->setDateDebut(new \DateTime());
        $evenement3->setDateFin((new \DateTime())->add(new \DateInterval('P1D')));
        $evenement3->setFormatSimple($formatSimple);
        $this->em->persist($evenement3);

        $serie = (new DhtmlxSerie())
            ->setDateDebut(new \DateTime())
            ->setDateFin((new \DateTime())->add(new \DateInterval('P1D')))
        ;
        $this->em->persist($serie);

        $evenement->setSerie($serie);
        $serie->addEvenement($evenement);

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
            ->setActivite($activite)
        ;
        $this->em->persist($formatActivite);

        $creneau = (new Creneau())
            ->setCapacite(1)
            ->setFormatActivite($formatActivite)
            ->setSerie($serie)
        ;
        $this->em->persist($creneau);
        $serie->setCreneau($creneau);

        $ressourceLieu = (new Lieu());
        $ressourceLieu->setLibelle('Ressource Lieu Test');
        $ressourceLieu->setImage('test.jpg');
        $ressourceLieu->setNbPartenaires(1);
        $ressourceLieu->setNbPartenairesMax(5);
        $this->em->persist($ressourceLieu);

        $reservabilite = (new Reservabilite());
        $reservabilite->setCapacite(10);
        $reservabilite->setEvenement($evenement);
        $reservabilite->setRessource($ressourceLieu);
        $this->em->persist($reservabilite);
        $evenement->setReservabilite($reservabilite);

        $reservabilite2 = (new Reservabilite());
        $reservabilite2->setCapacite(10);
        $reservabilite2->setEvenement($evenement3);
        $reservabilite2->setRessource($ressourceLieu);
        $this->em->persist($reservabilite2);
        $evenement3->setReservabilite($reservabilite2);

        $inscription1 = (new Inscription($reservabilite, $user, ['typeInscription' => 'format']));
        $this->em->persist($inscription1);
        $reservabilite->addInscription($inscription1);
        $user->addInscription($inscription1);

        $inscription2 = (new Inscription($creneau, $user, ['typeInscription' => 'format']));
        $this->em->persist($inscription2);
        $creneau->addInscription($inscription2);
        $user->addInscription($inscription2);

        $inscription3 = (new Inscription($formatSimple, $user, ['typeInscription' => 'format']));
        $this->em->persist($inscription3);
        $formatSimple->addInscription($inscription3);
        $user->addInscription($inscription3);

        $inscription4 = (new Inscription($reservabilite2, $user, ['typeInscription' => 'format']));
        $this->em->persist($inscription4);
        $reservabilite->addInscription($inscription4);
        $user->addInscription($inscription4);

        $appel = (new Appel());
        $appel->setDhtmlxEvenement($evenement3);
        $appel->setPresent(true);
        $appel->setUtilisateur($user);
        $this->em->persist($appel);

        $this->em->flush();

        $this->ids['appel'] = $appel->getId();
        $this->ids['user_encadrant_gestion'] = $user_encadrant_gestion->getId();
        $this->ids['user_encadrant'] = $user_encadrant->getId();
        $this->ids['user'] = $user->getId();
        $this->ids['evenement'] = $evenement->getId();
        $this->ids['evenement2'] = $evenement2->getId();
        $this->ids['evenement3'] = $evenement3->getId();
        $this->ids['serie'] = $serie->getId();
        $this->ids['formatSimple'] = $formatSimple->getId();
        $this->ids['formatActivite'] = $formatActivite->getId();
        $this->ids['activite'] = $activite->getId();
        $this->ids['typeActivite'] = $typeActivite->getId();
        $this->ids['classeActivite'] = $classeActivite->getId();
        $this->ids['creneau'] = $creneau->getId();
        $this->ids['ressource'] = $ressourceLieu->getId();
        $this->ids['reservabilite'] = $reservabilite->getId();
        $this->ids['reservabilite2'] = $reservabilite2->getId();
        $this->ids['inscription1'] = $inscription1->getId();
        $this->ids['inscription2'] = $inscription2->getId();
        $this->ids['inscription3'] = $inscription3->getId();
        $this->ids['inscription4'] = $inscription4->getId();
    }

    protected function tearDown(): void
    {
        $appel = $this->em->getRepository(Appel::class)->find($this->ids['appel']);
        if (null !== $appel) {
            $this->em->remove($appel);
        }

        $this->em->remove($this->em->getRepository(Inscription::class)->find($this->ids['inscription1']));
        $this->em->remove($this->em->getRepository(Inscription::class)->find($this->ids['inscription2']));
        $this->em->remove($this->em->getRepository(Inscription::class)->find($this->ids['inscription3']));
        $this->em->remove($this->em->getRepository(Inscription::class)->find($this->ids['inscription4']));
        $this->em->remove($this->em->getRepository(Reservabilite::class)->find($this->ids['reservabilite']));
        $this->em->remove($this->em->getRepository(Reservabilite::class)->find($this->ids['reservabilite2']));
        $this->em->remove($this->em->getRepository(Lieu::class)->find($this->ids['ressource']));
        $this->em->remove($this->em->getRepository(FormatSimple::class)->find($this->ids['formatSimple']));
        $this->em->remove($this->em->getRepository(DhtmlxEvenement::class)->find($this->ids['evenement']));
        $this->em->remove($this->em->getRepository(DhtmlxEvenement::class)->find($this->ids['evenement2']));
        $this->em->remove($this->em->getRepository(DhtmlxEvenement::class)->find($this->ids['evenement3']));
        $this->em->remove($this->em->getRepository(DhtmlxSerie::class)->find($this->ids['serie']));
        $this->em->remove($this->em->getRepository(Creneau::class)->find($this->ids['creneau']));
        $this->em->remove($this->em->getRepository(FormatActivite::class)->find($this->ids['formatActivite']));
        $this->em->remove($this->em->getRepository(Activite::class)->find($this->ids['activite']));
        $this->em->remove($this->em->getRepository(ClasseActivite::class)->find($this->ids['classeActivite']));
        $this->em->remove($this->em->getRepository(TypeActivite::class)->find($this->ids['typeActivite']));

        $this->em->remove($this->em->getRepository(Utilisateur::class)->find($this->ids['user']));
        $this->em->remove($this->em->getRepository(Utilisateur::class)->find($this->ids['user_encadrant']));
        $this->em->remove($this->em->getRepository(Utilisateur::class)->find($this->ids['user_encadrant_gestion']));

        $this->em->flush();

        static::ensureKernelShutdown();
    }

    public function dataProviderlister()
    {
        return [
            ['user', 'GET', 'UcaWeb_MonPlanning', [], Response::HTTP_OK],
            ['user_encadrant', 'GET', 'UcaWeb_MonPlanning', [], Response::HTTP_OK],
            ['user_encadrant', 'GET', 'UcaWeb_PlanningMore', ['id' => 'id_evenement'], Response::HTTP_FOUND, 'UcaWeb_MonPlanning'],
            ['user_encadrant_gestion', 'GET', 'UcaWeb_PlanningMore', ['id' => 'id_evenement'], Response::HTTP_OK],
            ['user_encadrant_gestion', 'POST', 'UcaWeb_PlanningMore', ['id' => 'id_evenement'], Response::HTTP_OK, null, [], [
                'ucabundle_evenement' => [
                    'appels' => [],
                ],
            ]],
            ['user_encadrant_gestion', 'GET', 'UcaWeb_PlanningMore_listePdf', ['id' => 'id_evenement2'], Response::HTTP_FOUND, 'UcaWeb_MonPlanning'],
            ['user_encadrant_gestion', 'GET', 'UcaWeb_PlanningMore_Desinscrire', ['user' => 'id_user', 'evenement' => 'id_evenement'], Response::HTTP_FOUND, 'UcaWeb_PlanningMore', ['id' => 'id_evenement']],
            ['user_encadrant_gestion', 'GET', 'UcaWeb_PlanningMore_Desinscrire', ['user' => 'id_user', 'evenement' => 'id_evenement3'], Response::HTTP_FOUND, 'UcaWeb_PlanningMore', ['id' => 'id_evenement3']],
            ['user_encadrant_gestion', 'GET', 'UcaWeb_PlanningMore_DesinscrireTout', ['id' => 'id_evenement'], Response::HTTP_FOUND, 'UcaWeb_PlanningMore', ['id' => 'id_evenement']],
            ['user_encadrant_gestion', 'GET', 'UcaWeb_PlanningMore_DesinscrireTout', ['id' => 'id_evenement3'], Response::HTTP_FOUND, 'UcaWeb_PlanningMore', ['id' => 'id_evenement3']],

            ['user_encadrant', 'GET', 'UcaWeb_PlanningMore_NoId', ['id' => 'id_evenement'], Response::HTTP_FOUND, 'UcaWeb_MonPlanning'],
            ['user_encadrant_gestion', 'GET', 'UcaWeb_PlanningMore_NoId', [], Response::HTTP_FOUND, 'UcaWeb_MonPlanning'],
        ];
    }

    /**
     * @dataProvider dataProviderlister
     *
     * @param mixed      $userKey
     * @param mixed      $httpMethod
     * @param mixed      $routeName
     * @param mixed      $routeParams
     * @param mixed      $expectedStatusCode
     * @param null|mixed $expectedRedirectionName
     * @param mixed      $expectedRedirectionParams
     * @param mixed      $body
     */
    public function testLister($userKey, $httpMethod, $routeName, $routeParams, $expectedStatusCode, $expectedRedirectionName = null, $expectedRedirectionParams = [], $body = [])
    {
        $route = $this->router->generate($routeName, $routeParams);
        $route = str_replace('id_evenement2', $this->ids['evenement2'], $route);
        $route = str_replace('id_evenement3', $this->ids['evenement3'], $route);
        $route = str_replace('id_evenement', $this->ids['evenement'], $route);
        $route = str_replace('id_user', $this->ids['user'], $route);

        if (null !== $userKey) {
            $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids[$userKey]));
        }
        if (!empty($body) && isset($body['ucabundle_evenement'])) {
            $body['ucabundle_evenement']['_token'] = $this->tokens['ucabundle_evenement'];
            $body['ucabundle_evenement']['save'] = '';
        }
        $this->client->request($httpMethod, $route, $body);
        if (Response::HTTP_FOUND == $expectedStatusCode) {
            $expectedRedirection = $this->router->generate($expectedRedirectionName, $expectedRedirectionParams);
            $expectedRedirection = str_replace('id_evenement2', $this->ids['evenement2'], $expectedRedirection);
            $expectedRedirection = str_replace('id_evenement3', $this->ids['evenement3'], $expectedRedirection);
            $expectedRedirection = str_replace('id_evenement', $this->ids['evenement'], $expectedRedirection);
            $this->assertResponseRedirects($expectedRedirection);
        } else {
            $this->assertResponseStatusCodeSame($expectedStatusCode);
        }
    }

    public function testListePdfOK()
    {
        ob_start();
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids['user_encadrant_gestion']));
        $route = $this->router->generate('UcaWeb_PlanningMore_listePdf', ['id' => $this->ids['evenement']]);
        $this->client->request('GET', $route);
        $this->client->getResponse()->sendContent();
        $response = ob_get_contents();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertStringStartsWith('%PDF-', $response);
        $this->assertStringEndsWith("\n%%EOF\n", $response);
        ob_end_clean();
    }

    public function testListeExcelOK()
    {
        ob_start();
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids['user_encadrant_gestion']));
        $route = $this->router->generate('UcaWeb_PlanningMore_listeExcel', ['id' => $this->ids['evenement']]);
        $this->client->request('GET', $route);
        $this->client->getResponse()->sendContent();
        $response = ob_get_contents();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertStringStartsWith("PK\x03\x04", $response); // PK\x03\X04 correspond à la signature d'un fichier zip / ooxml (cf wikipedia)
        $this->assertStringContainsString('[Content_Types].xml', $response);
        $this->assertStringContainsString('_rels/.rels', $response);
        $this->assertStringContainsString('docProps/app.xml', $response);
        $this->assertStringContainsString('docProps/core.xml', $response);
        $this->assertStringContainsString('xl/workbook.xml', $response);
        $this->assertStringContainsString('xl/styles.xml', $response);
        $this->assertStringContainsString('xl/sharedStrings.xml', $response);
        $this->assertStringContainsString('xl/_rels/workbook.xml.rels', $response);
        $this->assertStringContainsString('xl/worksheets/sheet1.xml', $response);
        $this->assertStringContainsString('xl/worksheets/_rels/sheet1.xml.rels', $response);
        $this->assertStringContainsString('xl/theme/theme1.xml', $response);
        $this->assertStringContainsString('xl/media/', $response);
        $this->assertStringContainsString('xl/drawings/drawing1.xml', $response);
        $this->assertStringContainsString('xl/drawings/_rels/drawing1.xml.rels', $response);
        ob_end_clean();
    }

    public function testNDEFSansBody()
    {
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids['user']));
        $this->client->request('POST', $this->router->generate('Api_NDEFUser'));
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertIsObject($response);
        $this->assertObjectHasAttribute('user', $response);
        $this->assertEquals('null', $response->user);
    }

    public function testNDEFAvecBody()
    {
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids['user']));
        $this->client->request('POST', $this->router->generate('Api_NDEFUser'), ['id' => 'cd:ab:10:32:54:76:98']);
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertIsObject($response);
        $this->assertObjectHasAttribute('user', $response);
        $this->assertEquals('test test', $response->user);
    }

    public function testGetPlanningMail()
    {
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids['user_encadrant_gestion']));
        $this->client->request('GET', $this->router->generate('UcaWeb_PlanningMore_mail', ['id' => $this->ids['evenement']]));
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertIsObject($response);
        $this->assertObjectHasAttribute('sucess', $response);
        $this->assertFalse($response->sucess);
        $this->assertObjectHasAttribute('form', $response);
        $this->assertIsString($response->form);
    }

    public function testPostPlanningMail()
    {
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids['user_encadrant_gestion']));
        $this->client->request('POST', $this->router->generate('UcaWeb_PlanningMore_mail', ['id' => $this->ids['evenement']]), [
            'ucabundle_mail' => [
                'save' => '',
                'destinataires' => ['user@test.fr'],
                'objet' => 'Test mail planning',
                'mail' => 'Message du mail de gtest pour planning',
            ],
        ]);
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertIsObject($response);
        $this->assertObjectHasAttribute('sucess', $response);
        $this->assertTrue($response->sucess);
        $this->assertObjectNotHasAttribute('form', $response);
        $this->assertEmailCount(1);
    }
}
