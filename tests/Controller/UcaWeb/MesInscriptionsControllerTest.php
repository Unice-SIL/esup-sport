<?php

namespace App\Tests\Controller\UcaWeb;

use App\Entity\Uca\Activite;
use App\Entity\Uca\ClasseActivite;
use App\Entity\Uca\Commande;
use App\Entity\Uca\FormatSimple;
use App\Entity\Uca\Groupe;
use App\Entity\Uca\Inscription;
use App\Entity\Uca\Lieu;
use App\Entity\Uca\TypeActivite;
use App\Entity\Uca\Utilisateur;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * @internal
 * @coversNothing
 */
class MesInscriptionsControllerTest extends WebTestCase
{
    /**
     * @var KernelBrowser
     */
    private $client;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var RouterInterface
     */
    private $router;

    private $ids = [];

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->router = static::getContainer()->get(RouterInterface::class);

        $groupe_user_gestion_inscription = (new Groupe('groupe_user_gestion_inscription', ['ROLE_GESTION_INSCRIPTION']))
            ->setLibelle('groupe_user_gestion_inscription')
        ;
        $this->em->persist($groupe_user_gestion_inscription);

        $groupe_user_non_gestion = (new Groupe('goupe_user_non_gestion', []))
            ->setLibelle('goupe_user_non_gestion')
        ;
        $this->em->persist($groupe_user_non_gestion);

        $user_gestion_inscription = (new Utilisateur())
            ->setNom('inscription')
            ->setPrenom('gestion')
            ->setEmail('user_gestion_inscription@test.fr')
            ->setUsername('user_gestion_inscription')
            ->setPassword('password')
            ->setCgvAcceptees(true)
            ->setRoles([])
            ->addGroup($groupe_user_gestion_inscription)
        ;
        $this->em->persist($user_gestion_inscription);

        $user_non_gestion = (new Utilisateur())
            ->setNom('inscription')
            ->setPrenom('non_gestion')
            ->setEmail('user_non_gestionn@test.fr')
            ->setUsername('user_non_gestion')
            ->setPassword('password')
            ->setCgvAcceptees(true)
            ->setRoles([])
            ->addGroup($groupe_user_non_gestion)
        ;
        $this->em->persist($user_non_gestion);

        $user_lambda = (new Utilisateur())
            ->setNom('lambda')
            ->setPrenom('user')
            ->setEmail('user_lambda@test.fr')
            ->setUsername('user_lambda')
            ->setPassword('password')
            ->setCgvAcceptees(true)
            ->setRoles([])
        ;
        $this->em->persist($user_lambda);

        $user_suppression_massive_pas_gestion = (new Utilisateur())
            ->setNom('lambda')
            ->setPrenom('user')
            ->setEmail('user_lambda@test.fr')
            ->setUsername('user_lambda')
            ->setPassword('password')
            ->setCgvAcceptees(true)
            ->setRoles(['ROLE_GESTION_SUPPRESSION_MASSIVE'])
        ;
        $this->em->persist($user_suppression_massive_pas_gestion);

        $typeActivite = (new TypeActivite())
            ->setLibelle('Type Activite Test')
        ;
        $this->em->persist($typeActivite);

        $classeActivite = (new ClasseActivite())
            ->setLibelle('Classe Actvite Test')
            ->setTypeActiviteLibelle('Type Activite Test')
            ->setImage('')
            ->setTypeActivite($typeActivite)
        ;
        $this->em->persist($classeActivite);

        $activite = (new Activite())
            ->setLibelle('Activite Test')
            ->setImage('')
            ->setDescription('Description Activite Test')
            ->setClasseActiviteLibelle('Classe Activite Test')
            ->setClasseActivite($classeActivite)
        ;
        $this->em->persist($activite);

        $lieu = (new Lieu())
            ->setLibelle('Lieu Desinscription Test')
            ->setImage('test')
            ->setNbPartenaires(10)
            ->setNbPartenairesMax(10)
        ;
        $this->em->persist($lieu);

        $dateNow = new DateTime('now');

        $formatSimple = (new FormatSimple())
            ->setLibelle('format simple')
            ->setDescription('format simple - description')
            ->setDateDebutPublication($dateNow->modify('+1 day'))
            ->setDateFinPublication($dateNow->modify('+10 day'))
            ->setDateDebutInscription($dateNow)
            ->setDateFinInscription($dateNow->modify('+10 day'))
            ->setImage('')
            ->setCapacite(48)
            ->setEstPayant(false)
            ->setEstEncadre(false)
            ->setDateDebutEffective($dateNow)
            ->setDateFinEffective($dateNow)
            ->setActivite($activite)
            ->addLieu($lieu)
        ;
        $this->em->persist($formatSimple);

        $inscription = new Inscription($formatSimple, $user_non_gestion, ['typeInscription' => 'format']);
        $this->em->persist($inscription);

        $inscriptionValide = new Inscription($formatSimple, $user_non_gestion, ['typeInscription' => 'format']);
        $inscriptionValide->setStatut('valide');
        $this->em->persist($inscriptionValide);

        $inscriptionAttentePaiement = new Inscription($formatSimple, $user_non_gestion, ['typeInscription' => 'format']);
        $inscriptionAttentePaiement->setStatut('attentepaiement');
        $this->em->persist($inscriptionAttentePaiement);

        $inscriptionAttenteValidationGestionnaire = new Inscription($formatSimple, $user_non_gestion, ['typeInscription' => 'format']);
        $inscriptionAttenteValidationGestionnaire->setStatut('attentevalidationgestionnaire');
        $this->em->persist($inscriptionAttenteValidationGestionnaire);

        $inscriptionAttenteValidationEncadrant = new Inscription($formatSimple, $user_non_gestion, ['typeInscription' => 'format']);
        $inscriptionAttenteValidationEncadrant->setStatut('attentevalidationencadrant');
        $this->em->persist($inscriptionAttenteValidationEncadrant);

        $this->em->flush();

        $this->ids['lieu'] = $lieu->getId();
        $this->ids['inscription'] = $inscription->getId();
        $this->ids['inscriptionValide'] = $inscriptionValide->getId();
        $this->ids['inscriptionAttentePaiement'] = $inscriptionAttentePaiement->getId();
        $this->ids['inscriptionAttenteValidationGestionnaire'] = $inscriptionAttenteValidationGestionnaire->getId();
        $this->ids['inscriptionAttenteValidationEncadrant'] = $inscriptionAttenteValidationEncadrant->getId();
        $this->ids['formatSimple'] = $formatSimple->getId();
        $this->ids['activite'] = $activite->getId();
        $this->ids['classeActivite'] = $classeActivite->getId();
        $this->ids['typeActivite'] = $typeActivite->getId();
        $this->ids['user_gestion_inscription'] = $user_gestion_inscription->getId();
        $this->ids['user_non_gestion'] = $user_non_gestion->getId();
        $this->ids['user_lambda'] = $user_lambda->getId();
        $this->ids['user_suppression_massive_pas_gestion'] = $user_suppression_massive_pas_gestion->getId();
        $this->ids['groupe_user_gestion_inscription'] = $groupe_user_gestion_inscription->getId();
        $this->ids['groupe_user_non_gestion'] = $groupe_user_non_gestion->getId();
    }

    protected function tearDown(): void
    {
        $inscription = $this->em->getRepository(Inscription::class)->find($this->ids['inscription']);
        $commandes_details = $inscription->getCommandeDetails();
        foreach ($commandes_details as $cd) {
            $this->em->remove($cd);
        }
        $commande = $this->em->getRepository(Commande::class)->findOneByInscription($inscription->getId());
        if (null !== $commande) {
            $this->em->remove($commande);
        }
        $this->em->remove($inscription);
        $this->em->remove($this->em->getRepository(Inscription::class)->find($this->ids['inscriptionValide']));
        $this->em->remove($this->em->getRepository(Inscription::class)->find($this->ids['inscriptionAttentePaiement']));
        $this->em->remove($this->em->getRepository(Inscription::class)->find($this->ids['inscriptionAttenteValidationGestionnaire']));
        $this->em->remove($this->em->getRepository(Inscription::class)->find($this->ids['inscriptionAttenteValidationEncadrant']));
        $this->em->remove($this->em->getRepository(FormatSimple::class)->find($this->ids['formatSimple']));
        $this->em->remove($this->em->getRepository(Lieu::class)->find($this->ids['lieu']));
        $this->em->remove($this->em->getRepository(Activite::class)->find($this->ids['activite']));
        $this->em->remove($this->em->getRepository(ClasseActivite::class)->find($this->ids['classeActivite']));
        $this->em->remove($this->em->getRepository(TypeActivite::class)->find($this->ids['typeActivite']));

        $this->em->remove($this->em->getRepository(Utilisateur::class)->find($this->ids['user_gestion_inscription']));
        $this->em->remove($this->em->getRepository(Utilisateur::class)->find($this->ids['user_non_gestion']));
        $this->em->remove($this->em->getRepository(Utilisateur::class)->find($this->ids['user_lambda']));
        $this->em->remove($this->em->getRepository(Utilisateur::class)->find($this->ids['user_suppression_massive_pas_gestion']));
        $this->em->remove($this->em->getRepository(Groupe::class)->find($this->ids['groupe_user_gestion_inscription']));
        $this->em->remove($this->em->getRepository(Groupe::class)->find($this->ids['groupe_user_non_gestion']));

        $this->em->flush();

        static::ensureKernelShutdown();
    }

    /**
     * @dataProvider getInscriptionAnnulerDataProvider
     *
     * @param mixed $userKey
     * @param mixed $expectedRedirectionName
     */
    public function testRouteMesInscriptionsAnnuler($userKey, $expectedRedirectionName): void
    {
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids[$userKey]));
        $route = $this->router->generate('UcaWeb_MesInscriptionsAnnuler', ['id' => $this->ids['inscription']]);
        $this->client->request('GET', $route);

        $expectedRedirection = $this->router->generate($expectedRedirectionName);
        $this->assertResponseRedirects($expectedRedirection);
    }

    /**
     * @dataProvider getInscriptionAjoutPanierDataProvider
     *
     * @param mixed $userKey
     * @param mixed $expectedRedirectionName
     */
    public function testRouteMesInscriptionsAjoutPanier($userKey, $expectedRedirectionName): void
    {
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids[$userKey]));
        $route = $this->router->generate('UcaWeb_MesInscriptionsAjoutPanier', ['id' => $this->ids['inscription']]);
        $this->client->request('GET', $route);

        $expectedRedirection = $this->router->generate($expectedRedirectionName);
        $this->assertResponseRedirects($expectedRedirection);
    }

    /**
     * @dataProvider getInscriptionAnnulerDataProvider
     *
     * @param mixed $userKey
     * @param mixed $expectedRedirectionName
     */
    public function testRouteMesInscriptionSeDesinscrire($userKey, $expectedRedirectionName): void
    {
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids[$userKey]));
        $route = $this->router->generate('UcaWeb_MesInscriptionsSeDesinscrire', ['id' => $this->ids['inscription']]);
        $this->client->request('GET', $route);

        $expectedRedirection = $this->router->generate($expectedRedirectionName);
        $this->assertResponseRedirects($expectedRedirection);
    }

    /**
     * @dataProvider listerDataProvider
     * @dataProvider dataTableDataProvider
     *
     * @param mixed $userKey
     * @param mixed $routeName
     * @param mixed $expectedStatusCode
     * @param mixed $routeParams
     * @param mixed $isAjax
     */
    public function testLister($userKey, $routeName, $expectedStatusCode, $routeParams = [], $isAjax = false)
    {
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids[$userKey]));
        $route = $this->router->generate($routeName, $routeParams);
        if ($isAjax) {
            $this->client->xmlHttpRequest('GET', $route);
        } else {
            $this->client->request('GET', $route);
        }
        $this->assertResponseStatusCodeSame($expectedStatusCode);
    }

    public function testListerRedirection()
    {
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids['user_lambda']));
        $route = $this->router->generate('UcaGest_GestionInscription');
        $this->client->request('GET', $route);
        $expectedRedirection = $this->router->generate('UcaWeb_MesInscriptions');
        $this->assertResponseRedirects($expectedRedirection);
    }

    public function testDesinscriptionMassivePasBonRole()
    {
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids['user_suppression_massive_pas_gestion']));
        $route = $this->router->generate('UcaGest_GestionInscription_DesincriptionMassive', [
            'nom' => 'null',
            'prenom' => 'null',
            'statut' => 0,
            'idTypeActivite' => 0,
            'idClasseActivite' => 0,
            'idActivite' => 0,
            'idFormatActivite' => 0,
            'idCreneau' => 0,
            'idEncadrant' => 0,
            'idEtablissement' => 0,
            'idLieu' => 0,
        ]);
        $this->client->request('GET', $route);
        $expectedRedirection = $this->router->generate('UcaWeb_MesInscriptions');
        $this->assertResponseRedirects($expectedRedirection);
    }

    /**
     * @dataProvider desinscriptionMassiveFiltresDataProvider
     *
     * @param mixed $filtres
     */
    public function testDesinscriptionMassiveNoAjax($filtres)
    {
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids['user_gestion_inscription']));
        $route = $this->router->generate('UcaGest_GestionInscription_DesincriptionMassive', $filtres);
        $this->client->request('GET', $route);
        $expectedRedirection = $this->router->generate('UcaGest_GestionInscription');
        $this->assertResponseRedirects($expectedRedirection);
    }

    public function testDesinscriptionMassiveAjaxNoFiltre()
    {
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids['user_gestion_inscription']));
        $route = $this->router->generate('UcaGest_GestionInscription_DesincriptionMassive', [
            'nom' => 'null',
            'prenom' => 'null',
            'statut' => 0,
            'idTypeActivite' => 0,
            'idClasseActivite' => 0,
            'idActivite' => 0,
            'idFormatActivite' => 0,
            'idCreneau' => 0,
            'idEncadrant' => 0,
            'idEtablissement' => 0,
            'idLieu' => 0,
        ]);
        $this->client->xmlHttpRequest('GET', $route);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertIsObject($response);
        $this->assertObjectHasAttribute('filtre', $response);
        $this->assertFalse($response->filtre);
        $this->assertObjectNotHasAttribute('valide', $response);
        $this->assertObjectNotHasAttribute('attentepaiement', $response);
        $this->assertObjectNotHasAttribute('attentevalidationgestionnaire', $response);
        $this->assertObjectNotHasAttribute('attentevalidationencadrant', $response);
    }

    public function testDesinscriptionMassiveAjaxWithFiltre()
    {
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids['user_gestion_inscription']));
        $route = $this->router->generate('UcaGest_GestionInscription_DesincriptionMassive', [
            'nom' => 'null',
            'prenom' => 'null',
            'statut' => 0,
            'idTypeActivite' => 0,
            'idClasseActivite' => 0,
            'idActivite' => 0,
            'idFormatActivite' => 0,
            'idCreneau' => 0,
            'idEncadrant' => 0,
            'idEtablissement' => 0,
            'idLieu' => $this->ids['lieu'],
        ]);
        $this->client->xmlHttpRequest('GET', $route);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertIsObject($response);
        $this->assertObjectHasAttribute('filtre', $response);
        $this->assertTrue($response->filtre);
        $this->assertObjectHasAttribute('valide', $response);
        $this->assertIsInt($response->valide);
        $this->assertObjectHasAttribute('attentepaiement', $response);
        $this->assertIsInt($response->attentepaiement);
        $this->assertObjectHasAttribute('attentevalidationgestionnaire', $response);
        $this->assertIsInt($response->attentevalidationgestionnaire);
        $this->assertObjectHasAttribute('attentevalidationencadrant', $response);
        $this->assertIsInt($response->attentevalidationencadrant);
    }

    /**
     * Data provider.
     */
    public function getInscriptionAnnulerDataProvider()
    {
        return [
            ['user_non_gestion', 'UcaWeb_MesInscriptions'],
            ['user_gestion_inscription', 'UcaGest_GestionInscription'],
            ['user_lambda', 'UcaWeb_MesInscriptions'],
        ];
    }

    public function getInscriptionAjoutPanierDataProvider()
    {
        return [
            ['user_non_gestion', 'UcaWeb_Panier'],
            ['user_gestion_inscription', 'UcaGest_GestionInscription'],
        ];
    }

    public function listerDataProvider()
    {
        return [
            ['user_non_gestion', 'UcaWeb_MesInscriptions', Response::HTTP_OK],
            ['user_gestion_inscription', 'UcaGest_GestionInscription', Response::HTTP_OK],
        ];
    }

    public function dataTableDataProvider()
    {
        return [
            [
                'user_non_gestion', 'UcaWeb_MesInscriptions', Response::HTTP_OK, [
                    'draw' => 1, 'columns' => [['data' => 'id', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'statut', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'utilisateur.id', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'creneau.formatActivite.libelle', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'creneau.serie.evenements.dateDebut', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'creneau.serie.evenements.dateFin', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'formatActivite.activite.libelle', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'formatActivite.libelle', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'reservabilite.evenement.dateDebut', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'reservabilite.serie.dateDebut', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'reservabilite.evenement.dateFin', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'reservabilite.serie.dateFin', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'reservabilite.ressource.libelle', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'Activite', 'name' => '', 'searchable' => false, 'orderable' => false, 'search' => ['value' => '', 'regex' => false]], ['data' => 'date', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'statutTraduit', 'name' => '', 'searchable' => false, 'orderable' => false, 'search' => ['value' => '', 'regex' => false]], ['data' => 'creneauActivite', 'name' => '', 'searchable' => false, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'reservabiliteActivite', 'name' => '', 'searchable' => false, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => '18', 'name' => '', 'searchable' => false, 'orderable' => false, 'search' => ['value' => '', 'regex' => false]]], 'order' => [['column' => 0, 'dir' => 'asc']], 'start' => 0, 'length' => 10, 'search' => ['value' => '', 'regex' => false], '_' => 1659610361354,
                ],
                true,
            ],
            [
                'user_gestion_inscription', 'UcaGest_GestionInscription', Response::HTTP_OK, [
                    'draw' => 1, 'columns' => [['data' => 'id', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'statut', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'utilisateur.id', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'creneau.formatActivite.libelle', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'creneau.listeEncadrants', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'creneau.serie.evenements.dateDebut', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'creneau.serie.evenements.dateFin', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'formatActivite.activite.libelle', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'formatActivite.libelle', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'formatActivite.lieu.etablissement.libelle', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'formatActivite', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'reservabilite.evenement.dateDebut', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'reservabilite.evenement.dateFin', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'reservabilite.ressource.libelle', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'utilisateur.nom', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'utilisateur.prenom', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'Activite', 'name' => '', 'searchable' => true, 'orderable' => false, 'search' => ['value' => '', 'regex' => false]], ['data' => 'date', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'statutTraduit', 'name' => '', 'searchable' => true, 'orderable' => false, 'search' => ['value' => '', 'regex' => false]], ['data' => 'creneauActivite', 'name' => '', 'searchable' => false, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'reservabiliteActivite', 'name' => '', 'searchable' => false, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'formatActivite.listeEncadrants', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'formatActivite.listLieux', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => '23', 'name' => '', 'searchable' => false, 'orderable' => false, 'search' => ['value' => '', 'regex' => false]]], 'order' => [['column' => 0, 'dir' => 'asc']], 'start' => 0, 'length' => 10, 'search' => ['value' => '', 'regex' => false], '_' => 1659611758666,
                ],
                true,
            ],
        ];
    }

    public function desinscriptionMassiveFiltresDataProvider()
    {
        return [
            [[
                'nom' => 'null',
                'prenom' => 'null',
                'statut' => 0,
                'idTypeActivite' => 0,
                'idClasseActivite' => 0,
                'idActivite' => 0,
                'idFormatActivite' => 0,
                'idCreneau' => 0,
                'idEncadrant' => 0,
                'idEtablissement' => 0,
                'idLieu' => 0,
            ]],
            [[
                'nom' => 'null',
                'prenom' => 'null',
                'statut' => 0,
                'idTypeActivite' => 0,
                'idClasseActivite' => 0,
                'idActivite' => 0,
                'idFormatActivite' => 0,
                'idCreneau' => 'allCreneaux',
                'idEncadrant' => 0,
                'idEtablissement' => 0,
                'idLieu' => 0,
            ]],
            [[
                'nom' => 'null',
                'prenom' => 'null',
                'statut' => 0,
                'idTypeActivite' => 0,
                'idClasseActivite' => 0,
                'idActivite' => 0,
                'idFormatActivite' => 0,
                'idCreneau' => 1,
                'idEncadrant' => 0,
                'idEtablissement' => 0,
                'idLieu' => 0,
            ]],
            [[
                'nom' => 'null',
                'prenom' => 'null',
                'statut' => 0,
                'idTypeActivite' => 0,
                'idClasseActivite' => 0,
                'idActivite' => 0,
                'idFormatActivite' => 0,
                'idCreneau' => 0,
                'idEncadrant' => 1,
                'idEtablissement' => 0,
                'idLieu' => 0,
            ]],
            [[
                'nom' => 'null',
                'prenom' => 'null',
                'statut' => 0,
                'idTypeActivite' => 0,
                'idClasseActivite' => 0,
                'idActivite' => 0,
                'idFormatActivite' => 0,
                'idCreneau' => 0,
                'idEncadrant' => 0,
                'idEtablissement' => 1,
                'idLieu' => 0,
            ]],
            [[
                'nom' => 'null',
                'prenom' => 'null',
                'statut' => 0,
                'idTypeActivite' => 0,
                'idClasseActivite' => 0,
                'idActivite' => 0,
                'idFormatActivite' => 0,
                'idCreneau' => 0,
                'idEncadrant' => 0,
                'idEtablissement' => 0,
                'idLieu' => 1,
            ]],
        ];
    }
}
