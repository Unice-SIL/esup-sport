<?php

namespace App\Tests\Controller\UcaWeb;

use App\Entity\Uca\Commande;
use App\Entity\Uca\CommandeDetail;
use App\Entity\Uca\Creneau;
use App\Entity\Uca\ComportementAutorisation;
use App\Entity\Uca\DhtmlxSerie;
use App\Entity\Uca\FormatAvecCreneau;
use App\Entity\Uca\Groupe;
use App\Entity\Uca\Inscription;
use App\Entity\Uca\MontantTarifProfilUtilisateur;
use App\Entity\Uca\ProfilUtilisateur;
use App\Entity\Uca\Tarif;
use App\Entity\Uca\TypeAutorisation;
use App\Entity\Uca\Utilisateur;
use App\Entity\Uca\UtilisateurCreditHistorique;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;
use App\Entity\Uca\FormatActivite;

/**
 * @internal
 * @coversNothing
 */
class PanierControllerTest extends WebTestCase
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



        $inscription = (new Inscription($creneau, $user_not_empty_panier, ['typeInscription' => 'format']));
        $this->em->persist($inscription);

        $commande = new Commande($user_not_empty_panier);
        $commande->setDatePanier((new \DateTime())->add(new \DateInterval('P1D')))->setMontantTotal("1€")->setNumeroCommande(126875);
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

        $credit = new UtilisateurCreditHistorique($user_not_empty_panier, 8.0, null, 'credit', "ajout manuel de crédits", $commande->getNumeroCommande());
        $this->em->persist($credit);
        $user_not_empty_panier->addCredit($credit);

        $commande->updateMontantTotal();

        $this->em->flush();

        $this->ids['groupe_user_non_admin'] = $goupe_user_non_admin->getId();
        $this->ids['user_empty_panier'] = $user_empty_panier->getId();
        $this->ids['user_not_empty_panier'] = $user_not_empty_panier->getId();
        $this->ids['formatActivite'] = $formatActivite->getId();
        $this->ids['serie'] = $serie->getId();
        $this->ids['creneau'] = $creneau->getId();
        $this->ids['inscription'] = $inscription->getId();
        $this->ids['commande'] = $commande->getId();
        $this->ids['commandeDetail1'] = $commandeDetail1->getId();
        $this->ids['commandeDetail2'] = $commandeDetail2->getId();
        $this->ids['credit'] = $credit->getId();
    }

    protected function tearDown(): void
    {
        $credit = $this->em->getRepository(UtilisateurCreditHistorique::class)->find($this->ids['credit']);
        $this->em->remove($credit);

        $commandeDetail = $this->em->getRepository(CommandeDetail::class)->find($this->ids['commandeDetail1']);
        if ($commandeDetail) {
            $this->em->remove($commandeDetail);
        }

        $commandeDetail = $this->em->getRepository(CommandeDetail::class)->find($this->ids['commandeDetail2']);
        if ($commandeDetail) {
            $this->em->remove($commandeDetail);
        }

        $inscription = $this->em->getRepository(Inscription::class)->find($this->ids['inscription']);
        $this->em->remove($inscription);

        $commande = $this->em->getRepository(Commande::class)->find($this->ids['commande']);
        if ($commande) {
            $this->em->remove($commande);
        }

        $this->em->remove($this->em->getRepository(Creneau::class)->find($this->ids['creneau']));

        $this->em->remove($this->em->getRepository(DhtmlxSerie::class)->find($this->ids['serie']));

        $this->em->remove($this->em->getRepository(FormatActivite::class)->find($this->ids['formatActivite']));

        $this->em->remove($this->em->getRepository(Utilisateur::class)->find($this->ids['user_empty_panier']));
        $this->em->remove($this->em->getRepository(Utilisateur::class)->find($this->ids['user_not_empty_panier']));

        $this->em->remove($this->em->getRepository(Groupe::class)->find($this->ids['groupe_user_non_admin']));

        $this->em->flush();

        static::ensureKernelShutdown();
    }

    /**
     * @covers App\Controller\UcaWeb\PanierController::voirAction
     */
    public function testPanierUnauth()
    {
        $route = $this->router->generate('UcaWeb_Panier');
        $this->client->request('GET', $route);

        $expectedRedirection = $this->router->generate('UcaWeb_ConnexionSelectionProfil');
        $this->assertResponseRedirects($expectedRedirection);
    }

    /**
     * @covers App\Controller\UcaWeb\PanierController::voirAction
     */
    public function testPanierEmpty()
    {
        $route = $this->router->generate('UcaWeb_Panier');
        $user = $this->em->getRepository(Utilisateur::class)->findOneByEmail('user_empty_panier@test.fr');
        $this->client->loginUser($user);
        $this->client->request('GET', $route);

        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse()->getContent();
        $this->assertStringContainsStringIgnoringCase("Votre panier est vide", $response);
    }

    /**
     * @covers App\Controller\UcaWeb\PanierController::voirAction
     */
    public function testPanierNotEmpty()
    {
        $route = $this->router->generate('UcaWeb_Panier');
        $user = $this->em->getRepository(Utilisateur::class)->findOneByEmail('user_not_empty_panier@test.fr');
        $this->client->loginUser($user);
        $this->client->request('GET', $route);

        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse()->getContent();
        $this->assertStringContainsStringIgnoringCase("Total:", $response);
        $this->assertStringContainsStringIgnoringCase("Crédit:", $response);
        $this->assertStringContainsStringIgnoringCase("Montant:", $response);
        $this->assertStringContainsStringIgnoringCase("Vider le panier", $response);
    }

    /**
     * @covers App\Controller\UcaWeb\PanierController::voirAction
     */
    public function testPanierInvalid()
    {
        $route = $this->router->generate('UcaWeb_Panier');
        $user = $this->em->getRepository(Utilisateur::class)->findOneByEmail('user_not_empty_panier@test.fr');
        $token = static::getContainer()->get('security.csrf.token_manager')->getToken('ValiderPaiementPayboxType');
        $this->client->loginUser($user);
        $this->client->request('POST', $route, [
            "ValiderPaiementPayboxType" => [
                "save" => "",
                "_token" => $token->getValue(),
            ],
        ]);
        $this->assertResponseIsSuccessful();
    }

    /**
     * @covers App\Controller\UcaWeb\PanierController::voirAction
     */
    public function testPanierValid()
    {
        $route = $this->router->generate('UcaWeb_Panier');
        $user = $this->em->getRepository(Utilisateur::class)->findOneByEmail('user_not_empty_panier@test.fr');
        $token = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('ValiderPaiementPayboxType');
        $this->client->loginUser($user);
        $this->client->request('POST', $route, [
            "ValiderPaiementPayboxType" => [
                "cgvAcceptees" => true,
                "save" => "",
                "_token" => $token->getValue(),
            ],
        ]);
        $expectedRedirection = $this->router->generate('UcaWeb_PaiementRecapitulatif', ['id' => $this->ids['commande'], 'typePaiement' => 'PAYBOX']);
        $this->assertResponseRedirects($expectedRedirection);
    }

    /**
     * @covers App\Controller\UcaWeb\PanierController::suppressionAction
     * @covers App\Controller\UcaWeb\PanierController::finSuppression
     */
    public function testSuppressionArticle()
    {
        $route = $this->router->generate('UcaWeb_SuppressionArticle', ['id'=>$this->ids['commandeDetail2']]);
        $user = $this->em->getRepository(Utilisateur::class)->findOneByEmail('user_not_empty_panier@test.fr');
        $this->client->loginUser($user);
        $this->client->request('GET', $route);
        $expectedRedirection = $this->router->generate('UcaWeb_Panier');
        $this->assertResponseRedirects($expectedRedirection);
        $commande = $this->em->getRepository(Commande::class)->find($this->ids['commande']);
        $this->assertNotNull($commande);
    }

    /**
     * @covers App\Controller\UcaWeb\PanierController::suppressionToutArticleAction
     * @covers App\Controller\UcaWeb\PanierController::finSuppression
     */
    public function testSuppressionToutArticle()
    {
        $route = $this->router->generate('UcaWeb_SuppressionToutArticle', ['id'=>$this->ids['commande']]);
        $user = $this->em->getRepository(Utilisateur::class)->findOneByEmail('user_not_empty_panier@test.fr');
        $this->client->loginUser($user);
        $this->client->request('GET', $route);
        $expectedRedirection = $this->router->generate('UcaWeb_Panier');
        $this->assertResponseRedirects($expectedRedirection);
        $commande = $this->em->getRepository(Commande::class)->find($this->ids['commande']);
        $this->assertNull($commande);
    }
}
