<?php

namespace App\Tests\Controller\Api;

use App\Entity\Uca\Commande;
use App\Entity\Uca\CommandeDetail;
use App\Entity\Uca\Creneau;
use App\Entity\Uca\DhtmlxEvenement;
use App\Entity\Uca\DhtmlxSerie;
use App\Entity\Uca\FormatActivite;
use App\Entity\Uca\FormatAvecCreneau;
use App\Entity\Uca\Groupe;
use App\Entity\Uca\Inscription;
use App\Entity\Uca\Lieu;
use App\Entity\Uca\Reservabilite;
use App\Entity\Uca\Ressource;
use App\Entity\Uca\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

/**
 * @internal
 *
 * @coversNothing
 */
class DhtmlxControllerTest extends WebTestCase
{
    private $client;

    private $router;
    private $em;

    private $ids = [];

    private $repetitions;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $this->router = static::getContainer()->get(RouterInterface::class);

        $this->em = static::getContainer()->get(EntityManagerInterface::class);

        $serieBase1 = (new DhtmlxSerie())
            ->setDateDebut(new \DateTime())
            ->setDateFin((new \DateTime())->add(new \DateInterval('P1D')))
        ;
        $this->em->persist($serieBase1);

        $serieBase2 = (new DhtmlxSerie())
            ->setDateDebut(new \DateTime())
            ->setDateFin((new \DateTime())->add(new \DateInterval('P1D')))
        ;
        $this->em->persist($serieBase2);

        $serieBase3 = (new DhtmlxSerie())
            ->setDateDebut(new \DateTime())
            ->setDateFin((new \DateTime())->add(new \DateInterval('P1D')))
        ;
        $this->em->persist($serieBase3);

        $evenement1 = (new DhtmlxEvenement())
            ->setSerie($serieBase1)
            ->setDependanceSerie(true)
            ->setDateDebut(new \DateTime())
            ->setDateFin((new \DateTime())->add(new \DateInterval('P1D')))
        ;
        $this->em->persist($evenement1);

        $evenement2 = (new DhtmlxEvenement())
            ->setSerie($serieBase2)
            ->setDependanceSerie(true)
            ->setDateDebut(new \DateTime())
            ->setDateFin((new \DateTime())->add(new \DateInterval('P1D')))
        ;
        $this->em->persist($evenement2);

        $evenement3 = (new DhtmlxEvenement())
            ->setSerie($serieBase2)
            ->setDependanceSerie(true)
            ->setDateDebut(new \DateTime())
            ->setDateFin((new \DateTime())->add(new \DateInterval('P1D')))
        ;
        $this->em->persist($evenement3);

        $evenement4 = (new DhtmlxEvenement())
            ->setSerie($serieBase3)
            ->setDependanceSerie(true)
            ->setDateDebut(new \DateTime())
            ->setDateFin((new \DateTime())->add(new \DateInterval('P1D')))
        ;
        $this->em->persist($evenement4);

        $goupe_user_non_admin = (new Groupe('test_controleur_goupe_user_non_admin', []))
            ->setLibelle('test_controleur_goupe_user_non_admin')
        ;
        $this->em->persist($goupe_user_non_admin);

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

        $formatactivite = (new FormatAvecCreneau())
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
        $this->em->persist($formatactivite);

        $creneau = (new Creneau())
            ->setCapacite(1)
            ->setFormatActivite($formatactivite)
        ;
        $this->em->persist($creneau);

        $serieCreneau = (new DhtmlxSerie())
            ->setDateDebut(new \DateTime())
            ->setDateFin((new \DateTime())->add(new \DateInterval('P1D')))
            ->setCreneau($creneau)
        ;
        $this->em->persist($serieCreneau);
        $creneau->setSerie($serieCreneau);

        $inscriptionAttente = (new Inscription($creneau, $user_not_empty_panier, ['typeInscription' => 'format']));
        $this->em->persist($inscriptionAttente);

        $commande = new Commande($user_not_empty_panier);
        $commande->setDatePanier((new \DateTime())->add(new \DateInterval('P1D')))->setMontantTotal('1€')->setNumeroCommande(126875);
        $this->em->persist($commande);
        $user_not_empty_panier->addCommande($commande);

        $commandeDetail = new CommandeDetail($commande, 'inscription', $inscriptionAttente);
        $commandeDetail->setMontant(10);
        $commandeDetail->setTva(2);
        $this->em->persist($commandeDetail);

        $ressourceLieu = (new Lieu());
        $ressourceLieu->setLibelle('Ressource Lieu Test');
        $ressourceLieu->setImage('test.jpg');
        $ressourceLieu->setNbPartenaires(1);
        $ressourceLieu->setNbPartenairesMax(5);
        $this->em->persist($ressourceLieu);

        $reservabilite1 = (new Reservabilite());
        $reservabilite1->setCapacite(10);
        $reservabilite1->setEvenement($evenement4);
        $reservabilite1->setRessource($ressourceLieu);

        $this->em->persist($reservabilite1);

        $evenement4->setReservabilite($reservabilite1);
        $evenement3->setReservabilite($reservabilite1);

        $this->em->flush();

        $this->ids['evenement1'] = $evenement1->getId();
        $this->ids['evenement2'] = $evenement2->getId();
        $this->ids['evenement3'] = $evenement3->getId();
        $this->ids['evenement4'] = $evenement4->getId();
        $this->ids['serieBase1'] = $serieBase1->getId();
        $this->ids['serieBase2'] = $serieBase2->getId();
        $this->ids['serieBase3'] = $serieBase3->getId();
        $this->ids['groupe_user_non_admin'] = $goupe_user_non_admin->getId();
        $this->ids['user_non_empty_panier'] = $user_not_empty_panier->getId();
        $this->ids['formatActviteAvecCreneau'] = $formatactivite->getId();
        $this->ids['creneau'] = $creneau->getId();
        $this->ids['serieCreneau'] = $serieCreneau->getId();
        $this->ids['inscriptionAttente'] = $inscriptionAttente->getId();
        $this->ids['commande'] = $commande->getId();
        $this->ids['commandeDetail'] = $commandeDetail->getId();
        $this->ids['ressource'] = $ressourceLieu->getId();
        $this->ids['reservabilite'] = $reservabilite1->getId();

        $this->repetitions = false;
    }

    /**
     * @covers \App\Controller\Api\DhtmlxController::isSeuleOccurrenceDependance
     */
    public function testDhtmlxNbOccurrenceDependanceIsLessThanOne()
    {
        $this->client->xmlHttpRequest('POST', $this->router->generate('DhtmlxNbOccurrenceDependance'), ['serieId' => $this->ids['serieCreneau']]);
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertTrue(true === $response);
    }

    /**
     * @covers \App\Controller\Api\DhtmlxController::isSeuleOccurrenceDependance
     */
    public function testDhtmlxNbOccurrenceDependanceIsOne()
    {
        $this->client->xmlHttpRequest('POST', $this->router->generate('DhtmlxNbOccurrenceDependance'), ['serieId' => $this->ids['serieBase1']]);
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertTrue(true === $response);
    }

    /**
     * @covers \App\Controller\Api\DhtmlxController::isSeuleOccurrenceDependance
     */
    public function testDhtmlxNbOccurrenceDependanceIsGreaterThanOne()
    {
        $this->client->xmlHttpRequest('POST', $this->router->generate('DhtmlxNbOccurrenceDependance'), ['serieId' => $this->ids['serieBase2']]);
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertTrue(false === $response);
    }

    /**
     * @covers \App\Controller\Api\DhtmlxController::isInscritForSerie
     */
    public function testDhtmlxSerieInscritInscriptionEnAttente()
    {
        $this->client->xmlHttpRequest('POST', $this->router->generate('DhtmlxSerieInscrit'), ['id' => $this->ids['serieCreneau'], 'statut' => 'attente']);
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertTrue(true === $response);
    }

    /**
     * @covers \App\Controller\Api\DhtmlxController::isInscritForSerie
     */
    public function testDhtmlxSerieInscritPasInscriptionValide()
    {
        $this->client->xmlHttpRequest('POST', $this->router->generate('DhtmlxSerieInscrit'), ['id' => $this->ids['serieCreneau'], 'statut' => 'valide']);
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertTrue(false === $response);
    }

    /**
     * @covers \App\Controller\Api\DhtmlxController::annulerInscription
     */
    public function testDhtmlxAnnulerInscription()
    {
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids['user_non_empty_panier']), 'app');
        $this->client->xmlHttpRequest('POST', $this->router->generate('DhtmlxAnnulerInscription'), ['id' => $this->ids['serieCreneau']]);
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertTrue(200 === $response);
        $inscription = $this->em->getRepository(Inscription::class)->find($this->ids['inscriptionAttente']);
        $this->assertNotNull($inscription);
        $this->assertEqualsIgnoringCase('annule', $inscription->getStatut());
        $commande = $this->em->getRepository(Commande::class)->find($this->ids['commande']);
        $this->assertNotNull($commande);
        $commandeDetail = $this->em->getRepository(CommandeDetail::class)->find($this->ids['commandeDetail']);
        $this->assertNull($commandeDetail);
    }

    /**
     * @covers \App\Controller\Api\DhtmlxController::getEventAction
     */
    public function testGetDhtmlxApiCaseRessource()
    {
        $this->client->xmlHttpRequest('GET', $this->router->generate('DhtmlxApi'), ['activite' => $this->ids['ressource'], 'type' => 'ressource']);
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertIsObject($response);
        $this->assertObjectHasAttribute('evenements', $response);
        $this->assertObjectHasAttribute('series', $response);
        $this->assertObjectHasAttribute('preinscription', $response);
        $this->assertIsArray($response->evenements);
        $this->assertIsArray($response->series);
        $this->assertNull($response->preinscription);
    }

    /**
     * @covers \App\Controller\Api\DhtmlxController::getEventAction
     */
    public function testGetDhtmlxApiCaseFormatActivite()
    {
        $this->client->xmlHttpRequest('GET', $this->router->generate('DhtmlxApi'), ['activite' => $this->ids['formatActviteAvecCreneau'], 'type' => 'FormatActivite']);
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertIsObject($response);
        $this->assertObjectHasAttribute('evenements', $response);
        $this->assertObjectHasAttribute('series', $response);
        $this->assertObjectHasAttribute('preinscription', $response);
        $this->assertNull($response->evenements);
        $this->assertIsArray($response->series);
        $this->assertNull($response->preinscription);
    }

    /**
     * @covers \App\Controller\Api\DhtmlxController::getEventAction
     */
    public function testGetDhtmlxApiCaseEncadrantOrUser()
    {
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids['user_non_empty_panier']), 'app');
        $this->client->xmlHttpRequest('GET', $this->router->generate('DhtmlxApi'), ['type' => 'user']);
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertIsObject($response);
        $this->assertObjectHasAttribute('evenements', $response);
        $this->assertObjectHasAttribute('series', $response);
        $this->assertObjectHasAttribute('preinscription', $response);
        $this->assertIsArray($response->evenements);
        $this->assertIsArray($response->series);
        $this->assertIsArray($response->preinscription);
    }

    /**
     * @covers \App\Controller\Api\DhtmlxController::getEventAction
     */
    public function testGetDhtmlxApiCaseEncadrantOrUserWithoutLogin()
    {
        $this->client->xmlHttpRequest('GET', $this->router->generate('DhtmlxApi'), ['type' => 'user']);
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertNull($response);
    }

    /**
     * @covers \App\Controller\Api\DhtmlxController::DhtmlxApiPostAction
     */
    public function testPostDhtmlxApiDeleteAction()
    {
        $this->client->xmlHttpRequest('POST', $this->router->generate('DhtmlxApi'), ['evenement' => [
            'evenementType' => 'ressource',
            'action' => 'delete',
            'id' => $this->ids['evenement4'],
        ]]);
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertIsObject($response);
        $this->assertNull($response->id);
    }

    /**
     * @covers \App\Controller\Api\DhtmlxController::DhtmlxApiPostAction
     */
    public function testPostDhtmlxApiExtendAction()
    {
        $this->repetitions = true;
        $this->client->xmlHttpRequest('POST', $this->router->generate('DhtmlxApi'), ['evenement' => [
            'evenementType' => 'serie',
            'action' => 'extend',
            'id' => $this->ids['evenement1'],
            'nbRepetition' => 3,
            'dateDebutRepetition' => (new \DateTime())->add(new \DateInterval('P5D'))->format('Y-M-D H:i:s'),
            'dateDebut' => (new \DateTime())->add(new \DateInterval('PT5M'))->format('Y-M-D H:i:s'),
            'dateFin' => (new \DateTime())->add(new \DateInterval('PT10M'))->format('Y-M-D H:i:s'),
        ]]);
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertIsObject($response);
        $this->assertObjectHasAttribute('evenements', $response);
        $this->assertObjectHasAttribute('notCreated', $response);
        $this->assertEquals($this->ids['evenement4'] + 3, $response->evenements[2]->id);
    }
}
