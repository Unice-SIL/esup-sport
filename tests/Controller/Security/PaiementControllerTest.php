<?php

namespace App\Tests\Controller\Security;

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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @internal
 * @coversNothing
 */
class PaiementControllerTest extends WebTestCase
{
    private $client;

    private $router;

    private $translator;

    private $em;

    private $inscription1Id;

    private $inscription2Id;

    private $commande1Id;

    private $commande2Id;

    private $commande3Id;

    private $commande4Id;

    private $commandeDetail11Id;

    private $commandeDetail12Id;

    private $commandeDetail21Id;

    private $commandeDetail22Id;

    private $credit1Id;

    private $credit2Id;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->router = static::getContainer()->get(RouterInterface::class);
        $this->translator = static::getContainer()->get(TranslatorInterface::class);

        // Création Utilisateur
        $goupe_user_non_admin = (new Groupe('test_controleur_groupe_user_non_admin', []))
            ->setLibelle('test_controleur_groupe_user_non_admin')
        ;
        $this->em->persist($goupe_user_non_admin);

        $groupe_gestionnaire = (new Groupe(
            'test_controleur_groupe_gestionnaire',
            [
                'ROLE_GESTION_PAIEMENT_COMMANDE',
                'ROLE_GESTION_COMMANDES',
            ]
        ))
            ->setLibelle('test_controleur_groupe_gestionnaire')
        ;
        $this->em->persist($groupe_gestionnaire);

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

        $user_not_empty_panier_with_credit = (new Utilisateur())
            ->setEmail('user_not_empty_panier_with_credit@test.fr')
            ->setUsername('user_not_empty_panier_with_credit')
            ->setPassword('password')
            ->setCgvAcceptees(true)
            ->setEnabled(true)
            ->setRoles([])
            ->addGroup($goupe_user_non_admin)
        ;
        $this->em->persist($user_not_empty_panier_with_credit);

        $user_not_empty_panier_with_not_enough_credit = (new Utilisateur())
            ->setEmail('user_not_empty_panier_with_not_enough_credit@test.fr')
            ->setUsername('user_not_empty_panier_with_not_enough_credit')
            ->setPassword('password')
            ->setCgvAcceptees(true)
            ->setEnabled(true)
            ->setRoles([])
            ->addGroup($goupe_user_non_admin)
        ;
        $this->em->persist($user_not_empty_panier_with_not_enough_credit);

        $user_gestionnaire = (new Utilisateur())
            ->setEmail('user_gestionnaire@test.fr')
            ->setUsername('user_gestionnaire')
            ->setPassword('password')
            ->setCgvAcceptees(true)
            ->setEnabled(true)
            ->setRoles([])
            ->addGroup($groupe_gestionnaire)
        ;
        $this->em->persist($user_gestionnaire);


        $creneau = (new Creneau())
            ->setCapacite(1)
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
                ->setEstPayant(true)
                ->setEstEncadre(false)
        );
        $serie = (new DhtmlxSerie())
            ->setDateDebut(new \DateTime())
            ->setDateFin((new \DateTime())->add(new \DateInterval('P1D')))

        ;
        $this->em->persist($serie);
        $creneau->setSerie($serie);
        $this->em->persist($creneau);

        

        $inscription1 = (new Inscription($creneau, $user_not_empty_panier_with_credit, ['typeInscription' => 'format']));
        $this->em->persist($inscription1);

        $commande1 = new Commande($user_not_empty_panier_with_credit);
        $commande1->setDatePanier((new \DateTime())->add(new \DateInterval('P1D')))->setMontantTotal("1€")->setNumeroCommande(126875);
        $this->em->persist($commande1);
        $user_not_empty_panier_with_credit->addCommande($commande1);

        $commandeDetail11 = new CommandeDetail($commande1, 'inscription', $inscription1);
        $commandeDetail11->setMontant(10);
        $commandeDetail11->setTva(2);
        $this->em->persist($commandeDetail11);

        $commandeDetail12 = new CommandeDetail($commande1, 'inscription', $inscription1);
        $commandeDetail12->setMontant(2);
        $commandeDetail12->setTva(2);
        $this->em->persist($commandeDetail12);

        $credit1 = new UtilisateurCreditHistorique($user_not_empty_panier_with_credit, 100.0, null, 'credit', "ajout manuel de crédits", $commande1->getNumeroCommande());
        $this->em->persist($credit1);
        $user_not_empty_panier_with_credit->addCredit($credit1);

        $commande1->updateMontantTotal();

        $inscription2 = (new Inscription($creneau, $user_not_empty_panier_with_not_enough_credit, ['typeInscription' => 'format']));
        $this->em->persist($inscription2);

        $commande2 = new Commande($user_not_empty_panier_with_not_enough_credit);
        $commande2->setDatePanier((new \DateTime())->add(new \DateInterval('P1D')))->setMontantTotal("1€")->setNumeroCommande(126885);
        $this->em->persist($commande2);
        $user_not_empty_panier_with_not_enough_credit->addCommande($commande2);

        $commandeDetail21 = new CommandeDetail($commande2, 'inscription', $inscription2);
        $commandeDetail21->setMontant(10);
        $commandeDetail21->setTva(2);
        $this->em->persist($commandeDetail21);

        $commandeDetail22 = new CommandeDetail($commande2, 'inscription', $inscription2);
        $commandeDetail22->setMontant(2);
        $commandeDetail22->setTva(2);
        $this->em->persist($commandeDetail22);

        $credit2 = new UtilisateurCreditHistorique($user_not_empty_panier_with_not_enough_credit, 8.0, null, 'credit', "ajout manuel de crédits", $commande2->getNumeroCommande());
        $this->em->persist($credit2);
        $user_not_empty_panier_with_not_enough_credit->addCredit($credit2);

        $commande2->updateMontantTotal();

        $commande3 = new Commande($user_empty_panier);
        $commande3->setStatut('termine');
        $this->em->persist($commande3);

        $commande4 = new Commande($user_empty_panier);
        $this->em->persist($commande4);

        $this->em->flush();

        $this->inscription1Id = $inscription1->getId();

        $this->inscription2Id = $inscription2->getId();

        $this->commande1Id = $commande1->getId();

        $this->commande2Id = $commande2->getId();

        $this->commande3Id = $commande3->getId();

        $this->commande4Id = $commande4->getId();

        $this->commandeDetail11Id = $commandeDetail11->getId();

        $this->commandeDetail12Id = $commandeDetail12->getId();

        $this->commandeDetail21Id = $commandeDetail21->getId();

        $this->commandeDetail22Id = $commandeDetail22->getId();

        $this->credit1Id = $credit1->getId();

        $this->credit2Id = $credit2->getId();
    }

    protected function tearDown(): void
    {
        $container = static::getContainer();

        $credit = $this->em->getRepository(UtilisateurCreditHistorique::class)->find($this->credit1Id);
        $credit->setUtilisateur(null);
        $this->em->remove($credit);

        $credit = $this->em->getRepository(UtilisateurCreditHistorique::class)->find($this->credit2Id);
        $credit->setUtilisateur(null);
        $this->em->remove($credit);
        
        
        $commandeDetail = $this->em->getRepository(CommandeDetail::class)->find($this->commandeDetail11Id);
        if ($commandeDetail) {
            $commandeDetail->setCommande(null);
            $this->em->remove($commandeDetail);
        }

        $commandeDetail = $this->em->getRepository(CommandeDetail::class)->find($this->commandeDetail12Id);
        if ($commandeDetail) {
            $commandeDetail->setCommande(null);
            $this->em->remove($commandeDetail);
        }

        $commandeDetail = $this->em->getRepository(CommandeDetail::class)->find($this->commandeDetail21Id);
        if ($commandeDetail) {
            $commandeDetail->setCommande(null);
            $this->em->remove($commandeDetail);
        }

        $commandeDetail = $this->em->getRepository(CommandeDetail::class)->find($this->commandeDetail22Id);
        if ($commandeDetail) {
            $commandeDetail->setCommande(null);
            $this->em->remove($commandeDetail);
        }
        
        $inscription = $this->em->getRepository(Inscription::class)->find($this->inscription1Id);
        $inscription->setUtilisateur(null);
        $this->em->remove($inscription);

        $inscription = $this->em->getRepository(Inscription::class)->find($this->inscription2Id);
        $inscription->setUtilisateur(null);
        $this->em->remove($inscription);

        $commande = $this->em->getRepository(Commande::class)->find($this->commande1Id);
        if ($commande) {
            $commande->setUtilisateur(null);
            $this->em->remove($commande);
        }

        $commande = $this->em->getRepository(Commande::class)->find($this->commande2Id);
        if ($commande) {
            $commande->setUtilisateur(null);
            $this->em->remove($commande);
        }

        $commande = $this->em->getRepository(Commande::class)->find($this->commande3Id);
        if ($commande) {
            $commande->setUtilisateur(null);
            $this->em->remove($commande);
        }

        $commande = $this->em->getRepository(Commande::class)->find($this->commande4Id);
        if ($commande) {
            $commande->setUtilisateur(null);
            $this->em->remove($commande);
        }

        $this->em->remove($this->em->getRepository(Utilisateur::class)->findOneByEmail('user_empty_panier@test.fr'));
        $this->em->remove($this->em->getRepository(Utilisateur::class)->findOneByEmail('user_not_empty_panier_with_not_enough_credit@test.fr'));
        $this->em->remove($this->em->getRepository(Utilisateur::class)->findOneByEmail('user_not_empty_panier_with_credit@test.fr'));
        $this->em->remove($this->em->getRepository(Utilisateur::class)->findOneByEmail('user_gestionnaire@test.fr'));
        $this->em->remove($this->em->getRepository(Groupe::class)->findOneByLibelle('test_controleur_groupe_user_non_admin'));
        $this->em->remove($this->em->getRepository(Groupe::class)->findOneByLibelle('test_controleur_groupe_gestionnaire'));

        $this->em->flush();

        static::ensureKernelShutdown();
    }

    /**
     * @covers App\Controller\Security\PaiementController::paiementRecapitulatifAction
     */
    public function testRecapitulatifWithEnoughCredit()
    {
        $route = $this->router->generate('UcaWeb_PaiementRecapitulatif', ['id'=>$this->commande1Id]);
        $this->client->request('GET', $route);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = $this->client->getResponse()->getContent();
        $this->assertStringContainsStringIgnoringCase(
            $this->translator->trans(
                'paiement.validation.success',
                [
                    '%montant%' => $this->em->getRepository(Commande::class)->find($this->commande1Id)->getMontantTotal(),
                ],
                null,
                'fr'
            ),
            $response
        );
    }

    /**
     * @covers App\Controller\Security\PaiementController::paiementRecapitulatifAction
     */
    public function testRecapitulatifWithNotEnoughCredit()
    {
        $route = $this->router->generate('UcaWeb_PaiementRecapitulatif', ['id'=>$this->commande2Id]);
        $this->client->request('GET', $route);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = $this->client->getResponse()->getContent();
        $this->assertStringContainsStringIgnoringCase(
            $this->translator->trans(
                'paiement.recap',
                [],
                null,
                'fr'
            ),
            $response
        );
    }

    /**
     * @covers App\Controller\Security\PaiementController::paiementRetourPayboxAction
     */
    public function testRetourPaybox()
    {
        $route = $this->router->generate(
            'UcaWeb_PaiementRetourPaybox',
            [
                'status' => 'success',
                'Erreur' => '00000',
                'Ref' => $this->em->getRepository(Commande::class)->find($this->commande1Id)->getNumeroCommande(),
                'Mt' => $this->em->getRepository(Commande::class)->find($this->commande1Id)->getMontantTotal() * 100,
            ]
        );
        $this->client->request('GET', $route);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = $this->client->getResponse()->getContent();
        $this->assertStringContainsStringIgnoringCase(
            $this->translator->trans(
                'paiement.validation.success',
                [
                    '%montant%' => $this->em->getRepository(Commande::class)->find($this->commande1Id)->getMontantTotal(),
                ],
                null,
                'fr'
            ),
            $response
        );
    }

    /**
     * @covers App\Controller\Security\PaiementController::paiementValidationChequeAction
     */
    public function testValidationCheque()
    {
        $route = $this->router->generate(
            'UcaWeb_PaiementValidationCheque',
            [
                'id' => $this->commande1Id,
                'source' => 'gestioncaisse',
            ]
        );
        $this->client->request('GET', $route);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = $this->client->getResponse()->getContent();
        $this->assertStringContainsStringIgnoringCase(
            $this->translator->trans(
                'paiement.validation.success',
                [
                    '%montant%' => $this->em->getRepository(Commande::class)->find($this->commande1Id)->getMontantTotal(),
                ],
                null,
                'fr'
            ),
            $response
        );
    }

    /**
     * @covers App\Controller\Security\PaiementController::paiementValidationAction
     */
    public function testGetValidationBDSCheque()
    {
        $route = $this->router->generate(
            'UcaWeb_PaiementValidation',
            [
                'id' => $this->commande1Id,
                'source' => 'mescommandes',
                'typePaiement' => 'BDS',
                'moyenPaiement' => 'cheque',
            ]
        );
        $user = $this->em->getRepository(Utilisateur::class)->findOneByEmail('user_gestionnaire@test.fr');
        $this->client->loginUser($user);
        $this->client->request('GET', $route);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertIsObject($response);
        $this->assertObjectHasAttribute('formValid', $response);
        $this->assertFalse($response->formValid);
        $this->assertObjectHasAttribute('form', $response);
        $this->assertObjectNotHasAttribute('redirection', $response);
    }

    /**
     * @covers App\Controller\Security\PaiementController::paiementValidationAction
     */
    public function testPostValidationBDSCheque()
    {
        $route = $this->router->generate(
            'UcaWeb_PaiementValidation',
            [
                'id' => $this->commande1Id,
                'source' => 'mescommandes',
                'typePaiement' => 'BDS',
                'moyenPaiement' => 'cheque',
            ]
        );
        $user = $this->em->getRepository(Utilisateur::class)->findOneByEmail('user_gestionnaire@test.fr');
        //$token = static::getContainer()->get('security.csrf.token_manager')->getToken('ucabundle_numeroCheque')->getValue(); // Actuellement pas de csrf_protection sur ce form
        $this->client->loginUser($user);
        $this->client->request(
            'POST',
            $route,
            [
                'ucabundle_numeroCheque' => [
                    //'_token' => $token,
                    'save' => '',
                    'numeroCheque' => '12345678900123456',
                ],
            ]
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertIsObject($response);
        $this->assertObjectHasAttribute('formValid', $response);
        $this->assertTrue($response->formValid);
        $this->assertObjectNotHasAttribute('form', $response);
        $this->assertObjectHasAttribute('redirection', $response);
        $redirectionExpected = $this->router->generate('UcaWeb_PaiementValidationCheque', ['id'=>$this->commande1Id,'source'=>'gestioncaisse']);
        $this->assertEquals($redirectionExpected, $response->redirection);
    }

    /**
     * @covers App\Controller\Security\PaiementController::paiementValidationAction
     */
    public function testGetValidationEspece()
    {
        $route = $this->router->generate(
            'UcaWeb_PaiementValidation',
            [
                'id' => $this->commande1Id,
                'source' => 'mescommandes',
                'typePaiement' => 'BDS',
                'moyenPaiement' => 'espece',
            ]
        );
        $user = $this->em->getRepository(Utilisateur::class)->findOneByEmail('user_gestionnaire@test.fr');
        $this->client->loginUser($user);
        $this->client->request('GET', $route);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertStringContainsStringIgnoringCase($this->translator->trans('paiement.confirmation.success', [], null, 'fr'), $this->client->getResponse()->getContent());
    }

    /**
     * @covers App\Controller\Security\PaiementController::paiementValidationAction
     */
    public function testGetValidationCommandeTermineGestionnaire()
    {
        $route = $this->router->generate(
            'UcaWeb_PaiementValidation',
            [
                'id' => $this->commande3Id,
                'source' => 'mescommandes',
                'typePaiement' => 'BDS',
                'moyenPaiement' => 'espece',
            ]
        );
        $user = $this->em->getRepository(Utilisateur::class)->findOneByEmail('user_gestionnaire@test.fr');
        $this->client->loginUser($user);
        $this->client->request('GET', $route);
        $redirectionExpected = $this->router->generate('UcaGest_ReportingCommandeDetails', ['id' => $this->commande3Id]);
        $this->assertResponseRedirects($redirectionExpected);
    }

    /**
     * @covers App\Controller\Security\PaiementController::paiementValidationAction
     */
    public function testGetValidationCommandeTermineUtilisateur()
    {
        $route = $this->router->generate(
            'UcaWeb_PaiementValidation',
            [
                'id' => $this->commande3Id,
                'source' => 'mescommandes',
                'typePaiement' => 'BDS',
                'moyenPaiement' => 'espece',
            ]
        );
        $user = $this->em->getRepository(Utilisateur::class)->findOneByEmail('user_empty_panier@test.fr');
        $this->client->loginUser($user);
        $this->client->request('GET', $route);
        $redirectionExpected = $this->router->generate('UcaWeb_MesCommandesVoir', ['id' => $this->commande3Id]);
        $this->assertResponseRedirects($redirectionExpected);
    }

    /**
     * @covers App\Controller\Security\PaiementController::paiementValidationAction
     */
    public function testGetValidationCommandeVide()
    {
        $route = $this->router->generate(
            'UcaWeb_PaiementValidation',
            [
                'id' => $this->commande4Id,
                'source' => 'mescommandes',
                'typePaiement' => 'BDS',
                'moyenPaiement' => 'espece',
            ]
        );
        $user = $this->em->getRepository(Utilisateur::class)->findOneByEmail('user_gestionnaire@test.fr');
        $this->client->loginUser($user);
        $this->client->request('GET', $route);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertStringContainsStringIgnoringCase($this->translator->trans('paiement.confirmation.canceled', [], null, 'fr'), $this->client->getResponse()->getContent());
    }
}
