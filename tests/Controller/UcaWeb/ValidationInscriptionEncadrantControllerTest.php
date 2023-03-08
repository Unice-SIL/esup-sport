<?php

namespace App\Tests\Controller\UcaWeb;

use App\Entity\Uca\Autorisation;
use App\Entity\Uca\Creneau;
use App\Entity\Uca\ComportementAutorisation;
use App\Entity\Uca\DhtmlxSerie;
use App\Entity\Uca\DhtmlxEvenement;
use App\Entity\Uca\FormatAvecCreneau;
use App\Entity\Uca\Groupe;
use App\Entity\Uca\Inscription;
use App\Entity\Uca\TypeAutorisation;
use App\Entity\Uca\Utilisateur;
use App\Repository\AutorisationRepository;
use App\Repository\GroupeRepository;
use App\Repository\InscriptionRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use App\Entity\Uca\FormatActivite;

/**
 * @internal
 * @coversNothing
 */
class ValidationInscriptionEncadrantControllerTest extends WebTestCase
{
    private $client;

    private $router;

    private $em;

    private $ids = [];

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->router = static::getContainer()->get(RouterInterface::class);
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $goupe_user_gestionnaire = (new Groupe('test_controleur_goupe_user_gestionnaire', ['ROLE_GESTIONNAIRE_VALIDEUR_INSCRIPTION']))
            ->setLibelle('test_controleur_goupe_user_gestionnaire')
        ;
        $this->em->persist($goupe_user_gestionnaire);
        $user_gestionnaire = (new Utilisateur())
            ->setEmail('user_admin_gestionnaire@test.fr')
            ->setUsername('user_gestionnaire')
            ->setPassword('password')
            ->setCgvAcceptees(0)
            ->setRoles([])
            ->addGroup($goupe_user_gestionnaire)
        ;
        $this->em->persist($user_gestionnaire);

        $goupe_user_encadrant = (new Groupe('test_controleur_goupe_user_encadrant', ['ROLE_ENCADRANT']))
            ->setLibelle('test_controleur_goupe_user_encadrant')
        ;
        $this->em->persist($goupe_user_encadrant);
        $user_admin_encadrant = (new Utilisateur())
            ->setEmail('user_admin_encadrant@test.fr')
            ->setUsername('user_admin_encadrant')
            ->setPassword('password')
            ->setCgvAcceptees(0)
            ->setRoles([])
            ->addGroup($goupe_user_encadrant)
        ;
        $this->em->persist($user_admin_encadrant);

        $goupe_user_non_admin = (new Groupe('test_controleur_goupe_user_non_admin', []))
            ->setLibelle('test_controleur_goupe_user_non_admin')
        ;
        $this->em->persist($goupe_user_non_admin);
        $user_non_admin = (new Utilisateur())
            ->setEmail('user_non_admin@test.fr')
            ->setUsername('user_non_admin')
            ->setPassword('password')
            ->setCgvAcceptees(0)
            ->setRoles([])
            ->addGroup($goupe_user_non_admin)
        ;
        $this->em->persist($user_non_admin);

        $comportementAutorisation = (new ComportementAutorisation())->setLibelle('Comportement autorisation')->setCodeComportement('case');
        $this->em->persist($comportementAutorisation);

        $typeAutorisation = (new TypeAutorisation())
            ->setLibelle("Type autorisation")
            ->setComportement($comportementAutorisation)
            ->setComportementLibelle('Comportement autorisation')
            ->setInformationsComplementaires('Infos complémentaires')
        ;
        $this->em->persist($typeAutorisation);

        $formatActivite = (new FormatAvecCreneau())
            ->setLibelle('FormatAvecCreneau')
            ->setCapacite(1)
            ->setDescription("test")
            ->setDateDebutEffective(new \DateTime())
            ->setDateDebutInscription(new \DateTime())
            ->setDateDebutPublication(new \DateTime())
            ->setDateFinEffective((new \DateTime())->add(new \DateInterval('P1D')))
            ->setDateFinInscription((new \DateTime())->add(new \DateInterval('P1D')))
            ->setDateFinPublication((new \DateTime())->add(new \DateInterval('P1D')))
            ->setImage("test")
            ->setStatut(1)
            ->setTarifLibelle("Tarif")
            ->setListeLieux("[]")
            ->setListeAutorisations("[]")
            ->setListeNiveauxSportifs("[]")
            ->setListeProfils("[]")
            ->setListeEncadrants("[]")
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

        $inscription = (new Inscription($creneau, $user_gestionnaire, []));
        $inscription->addEncadrant($user_admin_encadrant);
        $this->em->persist($inscription);

        $user_admin_encadrant->addInscriptionsAValider($inscription);

        if (!file_exists(dirname(__DIR__, 3).'/public/upload/private')) {
            mkdir(dirname(__DIR__, 3).'/public/upload/private');
        }
        if (!file_exists(dirname(__DIR__, 3).'/public/upload/private/fichiers')) {
            mkdir(dirname(__DIR__, 3).'/public/upload/private/fichiers');
        }

        copy(dirname(__DIR__, 2).'/fixtures/test.pdf', dirname(__DIR__, 3).'/public/upload/private/fichiers/test.pdf');

        $autorisation = (new Autorisation($inscription, $typeAutorisation))
            ->setJustificatif("test.pdf")
        ;
        $this->em->persist($autorisation);

        $this->em->flush();

        $this->ids['groupe_user_encadrant'] = $goupe_user_encadrant->getId();
        $this->ids['groupe_user_gestionnaire'] = $goupe_user_gestionnaire->getId();
        $this->ids['groupe_user_non_admin'] = $goupe_user_non_admin->getId();
        $this->ids['user_gestionnaire'] = $user_gestionnaire->getId();
        $this->ids['user_encadrant'] = $user_admin_encadrant->getId();
        $this->ids['user_non_admin'] = $user_non_admin->getId();
        $this->ids['comportementAutorisation'] = $comportementAutorisation->getId();
        $this->ids['typeAutorisation'] = $typeAutorisation->getId();
        $this->ids['formatActivite'] = $formatActivite->getId();
        $this->ids['serie'] = $serie->getId();
        $this->ids['creneau'] = $creneau->getId();
        $this->ids['inscription'] = $inscription->getId();
        $this->ids['autorisation'] = $autorisation->getId();
    }

    protected function tearDown(): void
    {
        $autorisation = $this->em->getRepository(Autorisation::class)->find($this->ids['autorisation']);
        $this->em->remove($autorisation);

        $this->em->remove($this->em->getRepository(TypeAutorisation::class)->find($this->ids['typeAutorisation']));
        $this->em->remove($this->em->getRepository(ComportementAutorisation::class)->find($this->ids['comportementAutorisation']));

        unlink(dirname(__DIR__, 3).'/public/upload/private/fichiers/test.pdf');

        $inscription = $this->em->getRepository(Inscription::class)->find($this->ids['inscription']);
        $this->em->remove($inscription);

        $this->em->remove($this->em->getRepository(Creneau::class)->find($this->ids['creneau']));
        $this->em->remove($this->em->getRepository(DhtmlxSerie::class)->find($this->ids['serie']));
        $this->em->remove($this->em->getRepository(FormatActivite::class)->find($this->ids['formatActivite']));

        $this->em->remove($this->em->getRepository(Utilisateur::class)->find($this->ids['user_gestionnaire']));
        $this->em->remove($this->em->getRepository(Utilisateur::class)->find($this->ids['user_encadrant']));
        $this->em->remove($this->em->getRepository(Utilisateur::class)->find($this->ids['user_non_admin']));

        $this->em->remove($this->em->getRepository(Groupe::class)->find($this->ids['groupe_user_gestionnaire']));
        $this->em->remove($this->em->getRepository(Groupe::class)->find($this->ids['groupe_user_encadrant']));
        $this->em->remove($this->em->getRepository(Groupe::class)->find($this->ids['groupe_user_non_admin']));

        $this->em->flush();

        parent::tearDown();
        static::ensureKernelShutdown();
    }


    public function controlAccessDataProvider()
    {
        return [
            // UcaWeb_InscriptionAValiderLister
            [null,'GET','UcaWeb_InscriptionAValiderLister',Response::HTTP_FOUND],
            ['user_admin_gestionnaire@test.fr','GET','UcaWeb_InscriptionAValiderLister',Response::HTTP_OK,['type'=>'gestionnaire']],
            ['user_admin_gestionnaire@test.fr','GET','UcaWeb_InscriptionAValiderLister',Response::HTTP_FORBIDDEN,['type'=>'encadrant']],
            ['user_admin_gestionnaire@test.fr','GET','UcaWeb_InscriptionAValiderLister',Response::HTTP_OK],
            ['user_admin_encadrant@test.fr','GET','UcaWeb_InscriptionAValiderLister',Response::HTTP_OK,['type'=>'encadrant']],
            ['user_admin_encadrant@test.fr','GET','UcaWeb_InscriptionAValiderLister',Response::HTTP_FORBIDDEN,['type'=>'gestionnaire']],
            ['user_admin_encadrant@test.fr','GET','UcaWeb_InscriptionAValiderLister',Response::HTTP_OK],
            ['user_non_admin@test.fr','GET','UcaWeb_InscriptionAValiderLister',Response::HTTP_FORBIDDEN],

            // UcaWeb_InscriptionAValiderVoir
            [null,'GET','UcaWeb_InscriptionAValiderVoir',Response::HTTP_FOUND,['id'=>'id_inscription']],
            ['user_admin_gestionnaire@test.fr','GET','UcaWeb_InscriptionAValiderVoir',Response::HTTP_OK,['id'=>'id_inscription']],
            ['user_admin_encadrant@test.fr','GET','UcaWeb_InscriptionAValiderVoir',Response::HTTP_OK,['id'=>'id_inscription']],
            ['user_non_admin@test.fr','GET','UcaWeb_InscriptionAValiderVoir',Response::HTTP_FORBIDDEN,['id'=>'id_inscription']],

            // UcaWeb_InscriptionValideeParEncadrant
            // [null,'GET','UcaWeb_InscriptionValideeParEncadrant',Response::HTTP_FOUND,['id'=>'id_inscription']],
            ['user_admin_gestionnaire@test.fr','GET','UcaWeb_InscriptionValideeParEncadrant',Response::HTTP_FORBIDDEN,['id'=>'id_inscription']],
            ['user_admin_encadrant@test.fr','GET','UcaWeb_InscriptionValideeParEncadrant',Response::HTTP_FOUND,['id'=>'id_inscription']],
            ['user_non_admin@test.fr','GET','UcaWeb_InscriptionValideeParEncadrant',Response::HTTP_FORBIDDEN,['id'=>'id_inscription']],

            // UcaWeb_InscriptionRefuseeParEncadrant
            // [null,'GET','UcaWeb_InscriptionRefuseeParEncadrant',Response::HTTP_FOUND,['id'=>'id_inscription']],
            // [null,'POST','UcaWeb_InscriptionRefuseeParEncadrant',Response::HTTP_FOUND,['id'=>'id_inscription'],['motif'=>'test']],
            ['user_admin_gestionnaire@test.fr','GET','UcaWeb_InscriptionRefuseeParEncadrant',Response::HTTP_FORBIDDEN,['id'=>'id_inscription']],
            ['user_admin_gestionnaire@test.fr','POST','UcaWeb_InscriptionRefuseeParEncadrant',Response::HTTP_FORBIDDEN,['id'=>'id_inscription'],['motifRefus'=>'test']],
            ['user_admin_encadrant@test.fr','GET','UcaWeb_InscriptionRefuseeParEncadrant',Response::HTTP_FOUND,['id'=>'id_inscription']],
            ['user_admin_encadrant@test.fr','POST','UcaWeb_InscriptionRefuseeParEncadrant',Response::HTTP_FOUND,['id'=>'id_inscription'],['motifRefus'=>'test']],
            ['user_non_admin@test.fr','GET','UcaWeb_InscriptionRefuseeParEncadrant',Response::HTTP_FORBIDDEN,['id'=>'id_inscription']],
            ['user_non_admin@test.fr','POST','UcaWeb_InscriptionRefuseeParEncadrant',Response::HTTP_FORBIDDEN,['id'=>'id_inscription'],['motifRefus'=>'test']],

            // UcaWeb_InscriptionValideeParGestionnaire
            // [null,'GET','UcaWeb_InscriptionValideeParGestionnaire',Response::HTTP_FOUND,['id'=>'id_inscription']],
            ['user_admin_gestionnaire@test.fr','GET','UcaWeb_InscriptionValideeParGestionnaire',Response::HTTP_FOUND,['id'=>'id_inscription']],
            ['user_admin_encadrant@test.fr','GET','UcaWeb_InscriptionValideeParGestionnaire',Response::HTTP_FORBIDDEN,['id'=>'id_inscription']],
            ['user_non_admin@test.fr','GET','UcaWeb_InscriptionValideeParGestionnaire',Response::HTTP_FORBIDDEN,['id'=>'id_inscription']],

            // UcaWeb_InscriptionRefuseeParGestionnaire
            // [null,'GET','UcaWeb_InscriptionRefuseeParGestionnaire',Response::HTTP_FOUND,['id'=>'id_inscription']],
            // [null,'POST','UcaWeb_InscriptionRefuseeParGestionnaire',Response::HTTP_FOUND,['id'=>'id_inscription'],['motif'=>'test']],
            ['user_admin_gestionnaire@test.fr','GET','UcaWeb_InscriptionRefuseeParGestionnaire',Response::HTTP_FOUND,['id'=>'id_inscription']],
            ['user_admin_gestionnaire@test.fr','POST','UcaWeb_InscriptionRefuseeParGestionnaire',Response::HTTP_FOUND,['id'=>'id_inscription'],['motifRefus'=>'test']],
            ['user_admin_encadrant@test.fr','GET','UcaWeb_InscriptionRefuseeParGestionnaire',Response::HTTP_FORBIDDEN,['id'=>'id_inscription']],
            ['user_admin_encadrant@test.fr','POST','UcaWeb_InscriptionRefuseeParGestionnaire',Response::HTTP_FORBIDDEN,['id'=>'id_inscription'],['motifRefus'=>'test']],
            ['user_non_admin@test.fr','GET','UcaWeb_InscriptionRefuseeParGestionnaire',Response::HTTP_FORBIDDEN,['id'=>'id_inscription']],
            ['user_non_admin@test.fr','POST','UcaWeb_InscriptionRefuseeParGestionnaire',Response::HTTP_FORBIDDEN,['id'=>'id_inscription'],['motifRefus'=>'test']],

            // UcaWeb_TelechargerJustificatif
            [null,'GET','UcaWeb_TelechargerJustificatif',Response::HTTP_FOUND,['id'=>'id_autorisation']],
            ['user_admin_gestionnaire@test.fr','GET','UcaWeb_TelechargerJustificatif',Response::HTTP_OK,['id'=>'id_autorisation']],
            ['user_admin_encadrant@test.fr','GET','UcaWeb_TelechargerJustificatif',Response::HTTP_OK,['id'=>'id_autorisation']],
            ['user_non_admin@test.fr','GET','UcaWeb_TelechargerJustificatif',Response::HTTP_FORBIDDEN,['id'=>'id_autorisation']],
        ];
    }

    public function datatableAccessDataProvider()
    {
        return [
            ['user_admin_gestionnaire@test.fr','GET','UcaWeb_InscriptionAValiderLister',Response::HTTP_OK,[
                    'type' => 'gestionnaire',
                    'draw' => 1, 'columns' => [['data' => 'id', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'utilisateur.username', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'utilisateur.nom', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'utilisateur.prenom', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'date', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => '5', 'name' => '', 'searchable' => false, 'orderable' => false, 'search' => ['value' => '', 'regex' => false]]], 'order' => [['column' => 0, 'dir' => 'asc']], 'start' => 0, 'length' => 10, 'search' => ['value' => '', 'regex' => false], '_' => 1656600565137
                ],
                [],
                true
            ],
            ['user_admin_encadrant@test.fr','GET','UcaWeb_InscriptionAValiderLister',Response::HTTP_OK,[
                    'type' => 'encadrant',
                    'draw' => 1, 'columns' => [['data' => 'id', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'utilisateur.username', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'utilisateur.nom', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'utilisateur.prenom', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'date', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => '5', 'name' => '', 'searchable' => false, 'orderable' => false, 'search' => ['value' => '', 'regex' => false]]], 'order' => [['column' => 0, 'dir' => 'asc']], 'start' => 0, 'length' => 10, 'search' => ['value' => '', 'regex' => false], '_' => 1656600565137
                ],
                [],
                true
            ]
        ];
    }

    /**
     * Test l'accès aux routes
     *
     * @dataProvider controlAccessDataProvider
     * @dataProvider datatableAccessDataProvider
     *
     * @covers App\Controller\UcaWeb\ValidationInscriptionEncadrantController::listerAction
     * @covers App\Controller\UcaWeb\ValidationInscriptionEncadrantController::voirAction
     * @covers App\Controller\UcaWeb\ValidationInscriptionEncadrantController::validationParEncadrantAction
     * @covers App\Controller\UcaWeb\ValidationInscriptionEncadrantController::validationParGestionnaireAction
     * @covers App\Controller\UcaWeb\ValidationInscriptionEncadrantController::refusParEncadrantAction
     * @covers App\Controller\UcaWeb\ValidationInscriptionEncadrantController::refusParGestionnaireAction
     * @covers App\Controller\UcaWeb\ValidationInscriptionEncadrantController::telechargerJustificatifAction
     */
    public function testAccesRoutes($userEmail, $method, $routeName, $httpResponse, $urlParameters = [], $body = [], $ajax = false): void
    {
        if (null != $userEmail) {
            $userTest = $this->em->getRepository(Utilisateur::class)->findOneByEmail($userEmail);
            $this->client->loginUser($userTest, 'app');
        }
        $route = $this->router->generate($routeName, $urlParameters);
        $route = str_replace('id_inscription', $this->ids['inscription'], $route);
        $route = str_replace('id_autorisation', $this->ids['autorisation'], $route);

        if ($ajax) {
            $this->client->xmlHttpRequest($method, $route, $body);
        } else {
            $this->client->request($method, $route, $body);
        }
        $this->assertResponseStatusCodeSame($httpResponse);

        if ($httpResponse === Response::HTTP_OK && $routeName === 'UcaWeb_TelechargerJustificatif') {
            $responseContent = $this->client->getResponse()->getContent();
            $expectedContent = file_get_contents('tests/fixtures/test.pdf');
            $this->assertEquals($expectedContent, $responseContent);
        }
    }
}
