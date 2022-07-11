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

/**
 * @internal
 * @coversNothing
 */
class ValidationInscriptionEncadrantControllerTest extends WebTestCase
{
    private $client;

    private $em;

    private $inscriptionId;
    
    private $autorisationId;

    protected function setUp(): void
    {
        $this->client = static::createClient();
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

        $creneau = (new Creneau())
            ->setCapacite(1)
            ->addEncadrant($user_gestionnaire)
            ->addEncadrant($user_admin_encadrant)
        ;
        $creneau->setFormatActivite(
            (new FormatAvecCreneau())
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
                ->setEstPayant(false)
                ->setEstEncadre(true)
                ->addAutorisation($typeAutorisation)
        );
        $serie = (new DhtmlxSerie())
            ->setDateDebut(new \DateTime())
            ->setDateFin((new \DateTime())->add(new \DateInterval('P1D')))

        ;
        $this->em->persist($serie);
        $creneau->setSerie($serie);
        $this->em->persist($creneau);

        $inscription = (new Inscription($creneau, $user_gestionnaire, []));
        $this->em->persist($inscription);

        if (!file_exists(__DIR__.'/../../../public/upload/private')) {
            mkdir(__DIR__.'/../../../public/upload/private');
        }
        if (!file_exists(__DIR__.'/../../../public/upload/private/fichiers')) {
            mkdir(__DIR__.'/../../../public/upload/private/fichiers');
        }

        copy(__DIR__.'\\..\\..\\fixtures\\test.pdf', __DIR__.'/../../../public/upload/private/fichiers/test.pdf');

        $autorisation = (new Autorisation($inscription, $typeAutorisation))
            ->setJustificatif("test.pdf")
        ;
        $this->em->persist($autorisation);

        $this->em->flush();
        
        $this->inscriptionId = $inscription->getId();

        $this->autorisationId = $autorisation->getId();
    }

    protected function tearDown(): void
    {
        $container = static::getContainer();

        $autorisation = $container->get(AutorisationRepository::class)->find($this->autorisationId);
        $this->em->remove($autorisation);

        unlink(__DIR__.'/../../../public/upload/private/fichiers/test.pdf');

        $inscription = $container->get(InscriptionRepository::class)->find($this->inscriptionId);
        $inscription->setUtilisateur(null);
        $this->em->remove($inscription);

        $this->em->remove($container->get(UtilisateurRepository::class)->findOneByEmail('user_admin_gestionnaire@test.fr'));
        $this->em->remove($container->get(UtilisateurRepository::class)->findOneByEmail('user_admin_encadrant@test.fr'));
        $this->em->remove($container->get(UtilisateurRepository::class)->findOneByEmail('user_non_admin@test.fr'));

        $this->em->remove($container->get(GroupeRepository::class)->findOneByLibelle('test_controleur_goupe_user_gestionnaire'));
        $this->em->remove($container->get(GroupeRepository::class)->findOneByLibelle('test_controleur_goupe_user_encadrant'));
        $this->em->remove($container->get(GroupeRepository::class)->findOneByLibelle('test_controleur_goupe_user_non_admin'));

        $this->em->flush();

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
                    'draw' => 1,
                    'columns' => [
                        [
                            'data' => 'id',
                            'name' => '',
                            'searchable' => true,
                            'orderable' => true,
                            'search' => [
                                'value' => '',
                                'regex' => false,
                            ],
                        ],
                        [
                            'data' => 'utilisateur.username',
                            'name' => '',
                            'searchable' => true,
                            'orderable' => true,
                            'search' => [
                                'value' => '',
                                'regex' => false,
                            ],
                        ],
                        [
                            'data' => 'utilisateur.nom',
                            'name' => '',
                            'searchable' => true,
                            'orderable' => true,
                            'search' => [
                                'value' => '',
                                'regex' => false,
                            ],
                        ],
                        [
                            'data' => 'utilisateur.prenom',
                            'name' => '',
                            'searchable' => true,
                            'orderable' => true,
                            'search' => [
                                'value' => '',
                                'regex' => false,
                            ],
                        ],
                        [
                            'data' => 'date',
                            'name' => '',
                            'searchable' => true,
                            'orderable' => true,
                            'search' => [
                                'value' => '',
                                'regex' => false,
                            ],
                        ],
                        [
                            'data' => '5',
                            'name' => '',
                            'searchable' => false,
                            'orderable' => false,
                            'search' => [
                                'value' => '',
                                'regex' => false,
                            ],
                        ],
                    ],
                    'order' => [
                        [
                            'column' => 0,
                            'dir' => 'asc',
                        ],
                    ],
                    'start' => 0,
                    'length' => 10,
                    'search' => [
                        'value' => '',
                        'regex' => false
                    ],
                    '_' => 1656600565137
                ],
                [],
                true
            ],
            ['user_admin_encadrant@test.fr','GET','UcaWeb_InscriptionAValiderLister',Response::HTTP_OK,[
                    'type' => 'encadrant',
                    'draw' => 1,
                    'columns' => [
                        [
                            'data' => 'id',
                            'name' => '',
                            'searchable' => true,
                            'orderable' => true,
                            'search' => [
                                'value' => '',
                                'regex' => false,
                            ],
                        ],
                        [
                            'data' => 'utilisateur.username',
                            'name' => '',
                            'searchable' => true,
                            'orderable' => true,
                            'search' => [
                                'value' => '',
                                'regex' => false,
                            ],
                        ],
                        [
                            'data' => 'utilisateur.nom',
                            'name' => '',
                            'searchable' => true,
                            'orderable' => true,
                            'search' => [
                                'value' => '',
                                'regex' => false,
                            ],
                        ],
                        [
                            'data' => 'utilisateur.prenom',
                            'name' => '',
                            'searchable' => true,
                            'orderable' => true,
                            'search' => [
                                'value' => '',
                                'regex' => false,
                            ],
                        ],
                        [
                            'data' => 'date',
                            'name' => '',
                            'searchable' => true,
                            'orderable' => true,
                            'search' => [
                                'value' => '',
                                'regex' => false,
                            ],
                        ],
                        [
                            'data' => '5',
                            'name' => '',
                            'searchable' => false,
                            'orderable' => false,
                            'search' => [
                                'value' => '',
                                'regex' => false,
                            ],
                        ],
                    ],
                    'order' => [
                        [
                            'column' => 0,
                            'dir' => 'asc',
                        ],
                    ],
                    'start' => 0,
                    'length' => 10,
                    'search' => [
                        'value' => '',
                        'regex' => false
                    ],
                    '_' => 1656600565137
                ],
                [],
                true
            ],
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
        $router = static::getContainer()->get(RouterInterface::class);

        if (null != $userEmail) {
            $userTest = static::getContainer()->get(UtilisateurRepository::class)->findOneByEmail($userEmail);
            $this->client->loginUser($userTest);
        }
        $route = $router->generate($routeName, $urlParameters);
        $route = str_replace('id_inscription', $this->inscriptionId, $route);
        $route = str_replace('id_autorisation', $this->autorisationId, $route);

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
