<?php

namespace App\Tests;

use App\Entity\Uca\Commande;
use App\Entity\Uca\CommandeDetail;
use App\Entity\Uca\FormatSimple;
use App\Entity\Uca\Inscription;
use App\Entity\Uca\Utilisateur;
use App\Repository\CommandeDetailRepository;
use App\Repository\CommandeRepository;
use App\Repository\FormatSimpleRepository;
use App\Repository\InscriptionRepository;
use App\Repository\UtilisateurRepository;
use App\Service\Securite\LoginFormAuthenticator;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MesCommandesControllerTest extends WebTestCase
{
    private $router;
    private $client;

    private $em;

    private $translator;

    private $ids = [];

    private $tokens = [];

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->router = static::getContainer()->get(RouterInterface::class);
        $this->translator = static::getContainer()->get(TranslatorInterface::class);
        $tokenManager = static::getContainer()->get('security.csrf.token_manager');

        $this->tokens['ValiderPaiementPayboxType'] = $tokenManager->getToken('ValiderPaiementPayboxType')->getValue();

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

        $user_lambda_bis = (new Utilisateur())
            ->setNom('lambda')
            ->setPrenom('user')
            ->setEmail('user_lambda_bis@test.fr')
            ->setUsername('user_lambda_bis')
            ->setPassword('password')
            ->setCgvAcceptees(true)
            ->setRoles([])
        ;
        $this->em->persist($user_lambda_bis);

        $commande = new Commande($user_lambda);
        $this->em->persist($commande);

        $avoir = new Commande($user_lambda);
        $this->em->persist($avoir);

        // Création d'un format
        $formatSimple = (new FormatSimple())
            ->setCapacite(5)
            ->setLibelle('ActivitéTest')
            ->setDescription('Description de l\'activité')
            ->setDateDebutEffective(new DateTime())
            ->setDateDebutPublication(new DateTime())
            ->setDateDebutInscription(new DateTime())
            ->setDateFinEffective(new DateTime())
            ->setDateFinPublication(new DateTime())
            ->setDateFinPublication(new DateTime())
            ->setDateFinInscription(new DateTime())
            ->setImage('')
            ->setEstPayant(false)
            ->setEstEncadre(false)
        ;
        $this->em->persist($formatSimple);

        // Création d'une inscription
        $inscription = new Inscription(
            $formatSimple,
            $user_lambda,
            ['typeInscription' => 'format']
        );
        $this->em->persist($inscription);

        // Création de l'objet commande détail
        $commandeDetail = new CommandeDetail(
            $commande,
            'inscription',
            $inscription
        );
        $this->em->persist($commandeDetail);

        $avoirDetail = new CommandeDetail(
            $commande,
            'inscription',
            $inscription
        );
        $avoirDetail->setAvoir($avoir);
        $avoirDetail->setReferenceAvoir(123456);
        $avoirDetail->setDateAvoir(new DateTime());
        $this->em->persist($avoirDetail);

        $commande->addAvoirCommandeDetail($avoirDetail);

        $this->em->flush();

        $this->ids['avoirDetail'] = $avoirDetail->getId();
        $this->ids['commandeDetail'] = $commandeDetail->getId();
        $this->ids['inscription'] = $inscription->getId();
        $this->ids['formatSimple'] = $formatSimple->getId();
        $this->ids['commande'] = $commande->getId();
        $this->ids['avoir'] = $avoir->getId();
        $this->ids['user_lambda'] = $user_lambda->getId();
        $this->ids['user_lambda_bis'] = $user_lambda_bis->getId();
    }

    protected function tearDown(): void
    {
        $this->em->remove($this->em->getRepository(CommandeDetail::class)->find($this->ids['avoirDetail']));
        $this->em->remove($this->em->getRepository(CommandeDetail::class)->find($this->ids['commandeDetail']));
        $this->em->remove($this->em->getRepository(Inscription::class)->find($this->ids['inscription']));
        $this->em->remove($this->em->getRepository(FormatSimple::class)->find($this->ids['formatSimple']));
        $this->em->remove($this->em->getRepository(Commande::class)->find($this->ids['commande']));
        $this->em->remove($this->em->getRepository(Commande::class)->find($this->ids['avoir']));
        $this->em->remove($this->em->getRepository(Utilisateur::class)->find($this->ids['user_lambda']));
        $this->em->remove($this->em->getRepository(Utilisateur::class)->find($this->ids['user_lambda_bis']));

        $this->em->flush();

        static::ensureKernelShutdown();
    }

    public function listerDataProvider()
    {
        return [
            [],

            // Cas Datatable
            [
                true,
                [
                    'draw' => 1, 'columns' => [['data' => 'id', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'statut', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'datePaiement', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'dateAnnulation', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'dateCommande', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'montantTotal', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'numeroCommande', 'name' => '', 'searchable' => false, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'numeroRecu', 'name' => '', 'searchable' => false, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'montantTotalFormated', 'name' => '', 'searchable' => true, 'orderable' => false, 'search' => ['value' => '', 'regex' => false]], ['data' => 'statutTraduit', 'name' => '', 'searchable' => true, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'paiement', 'name' => '', 'searchable' => false, 'orderable' => true, 'search' => ['value' => '', 'regex' => false]], ['data' => 'date', 'name' => '', 'searchable' => true, 'orderable' => false, 'search' => ['value' => '', 'regex' => false]], ['data' => '12', 'name' => '', 'searchable' => false, 'orderable' => false, 'search' => ['value' => '', 'regex' => false]]], 'order' => [['column' => 3, 'dir' => 'DESC']], 'start' => 0, 'length' => 10, 'search' => ['value' => '', 'regex' => false], '_' => 1659619619363
                ]
            ]
        ];
    }

    public function voirDataProvider()
    {
        return [
            [],

            // Cas Datatable (datatable n'a plus l'air d'etre utilisé sur cette page)
            [
                true,
                [
                    'draw' => 1,
                ]
            ]
        ];
    }

    /**
     * @dataProvider listerDataProvider
     */
    public function testLister($isAjax = false, $urlParams = [])
    {
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids['user_lambda']));
        $route = $this->router->generate('UcaWeb_MesCommandes', $urlParams);

        if ($isAjax) {
            $this->client->xmlHttpRequest('GET', $route);
        } else {
            $this->client->request('GET', $route);
        }
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testVoirRedirection()
    {
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids['user_lambda_bis']));
        $route = $this->router->generate('UcaWeb_MesCommandesVoir', ['id'=>$this->ids['commande']]);
        $this->client->request('GET', $route);
        $expectedRedirection = $this->router->generate('UcaWeb_MesCommandes');
        $this->assertResponseRedirects($expectedRedirection);
    }

    /**
     * @dataProvider voirDataProvider
     */
    public function testGetVoir($isAjax = false, $urlParams = [])
    {
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids['user_lambda']));
        $route = $this->router->generate('UcaWeb_MesCommandesVoir', array_merge(['id'=>$this->ids['commande']], $urlParams));
        if ($isAjax) {
            $this->client->xmlHttpRequest('GET', $route);
        } else {
            $this->client->request('GET', $route);
        }
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testPostVoirFormValid()
    {
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids['user_lambda']));
        $route = $this->router->generate('UcaWeb_MesCommandesVoir', ['id'=>$this->ids['commande']]);
        $this->client->request('POST', $route, [
            'ValiderPaiementPayboxType' => [
                'cgvAcceptees' => true,
                'save' => '',
                '_token' => $this->tokens['ValiderPaiementPayboxType']
            ]
        ]);
        $expectedRedirection = $this->router->generate('UcaWeb_PaiementRecapitulatif', ['id'=>$this->ids['commande'],'typePaiement'=>'PAYBOX']);
        $this->assertResponseRedirects($expectedRedirection);
    }

    public function testPostVoirFormInvalid()
    {
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids['user_lambda']));
        $route = $this->router->generate('UcaWeb_MesCommandesVoir', ['id'=>$this->ids['commande']]);
        $this->client->request('POST', $route, [
            'ValiderPaiementPayboxType' => [
                'save' => '',
                '_token' => $this->tokens['ValiderPaiementPayboxType']
            ]
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertStringContainsStringIgnoringCase($this->translator->trans('mentions.conditions.nonvalide', [], null, 'fr'), $this->client->getResponse()->getContent());
    }

    public function testRouteMesCommandesAnnuler(): void
    {
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids['user_lambda']));
        $route = $this->router->generate('UcaWeb_MesCommandesAnnuler', ['id'=>$this->ids['commande']]);
        $this->client->request('GET', $route);
        $expectedRedirection = $this->router->generate('UcaWeb_MesCommandes');
        $this->assertResponseRedirects($expectedRedirection);
    }

    public function testRouteMesCommandesAnnulerPasBonUser(): void
    {
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids['user_lambda_bis']));
        $route = $this->router->generate('UcaWeb_MesCommandesAnnuler', ['id'=>$this->ids['commande']]);
        $this->client->request('GET', $route);
        $expectedRedirection = $this->router->generate('UcaWeb_MesCommandes');
        $this->assertResponseRedirects($expectedRedirection);
    }

    public function testExportPasBonUser()
    {
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids['user_lambda_bis']));
        $route = $this->router->generate('UcaWeb_MesCommandesExport', ['id'=>$this->ids['commande']]);
        $this->client->request('GET', $route);
        $expectedRedirection = $this->router->generate('UcaWeb_MesCommandes');
        $this->assertResponseRedirects($expectedRedirection);
    }

    public function testExportBonUser()
    {
        ob_start();
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids['user_lambda']));
        $route = $this->router->generate('UcaWeb_MesCommandesExport', ['id'=>$this->ids['commande']]);
        $this->client->request('GET', $route);
        $this->client->getResponse()->sendContent();
        $response = ob_get_contents();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertStringStartsWith("%PDF-", $response);
        $this->assertStringEndsWith("\n%%EOF\n", $response);
        ob_end_clean();
    }

    public function testExportAvoirPasBonUser()
    {
        $avoir = $this->em->getRepository(CommandeDetail::class)->find($this->ids['avoirDetail']);
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids['user_lambda_bis']));
        $route = $this->router->generate('UcaWeb_MesAvoirsExport', ['id'=>$this->ids['commande'],'refAvoir'=>$avoir->getReferenceAvoir()]);
        $this->client->request('GET', $route);
        $expectedRedirection = $this->router->generate('UcaWeb_MesCredits');
        $this->assertResponseRedirects($expectedRedirection);
    }

    public function testExportAvoirBonUser()
    {
        ob_start();
        $avoir = $this->em->getRepository(CommandeDetail::class)->find($this->ids['avoirDetail']);
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids['user_lambda']));
        $route = $this->router->generate('UcaWeb_MesAvoirsExport', ['id'=>$this->ids['commande'],'refAvoir'=>$avoir->getReferenceAvoir()]);
        $this->client->request('GET', $route);
        $this->client->getResponse()->sendContent();
        $response = ob_get_contents();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertStringStartsWith("%PDF-", $response);
        $this->assertStringEndsWith("\n%%EOF\n", $response);
        ob_end_clean();
    }
}
