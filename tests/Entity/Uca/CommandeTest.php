<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\Commande;
use App\Entity\Uca\CommandeDetail;
use App\Entity\Uca\ComportementAutorisation;
use App\Entity\Uca\FormatSimple;
use App\Entity\Uca\Inscription;
use App\Entity\Uca\TypeAutorisation;
use App\Entity\Uca\Utilisateur;
use App\Repository\UtilisateurRepository;
use DateInterval;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @internal
 * @coversNothing
 */
class CommandeTest extends WebTestCase
{
    private Commande $commande;
    private Utilisateur $user;

    // protected function setUp(): void
    // {
    //     $this->user = static::getContainer()->get(UtilisateurRepository::class)->findOneByUsername('admin');
    //     $this->commande = new Commande($this->user);

    //     $formatSimple = (new FormatSimple())
    //         ->setDateDebutEffective(new DateTime())
    //         ->setDateFinEffective(new DateTime())
    //     ;

    //     $inscription = (new Inscription(
    //         $formatSimple,
    //         new Utilisateur(),
    //         ['typeInscription' => 'format']
    //     ));

    //     $inscription->initAutorisations();

    //     $commandeDetail = (new CommandeDetail(
    //         $this->commande,
    //         'inscription',
    //         $inscription
    //     ))->setMontant('10');
    // }

    /**
     * @covers \App\Entity\Uca\Commande::__construct
     */
    public function testConstruct(): void
    {
        $this->createCommande();
        $this->assertInstanceOf(Commande::class, $this->commande);
        $this->assertEquals('panier', $this->commande->getStatut());

        // $kernel = self::bootKernel();

        // $this->assertSame('test', $kernel->getEnvironment());
        //$routerService = static::getContainer()->get('router');
        //$myCustomService = static::getContainer()->get(CustomService::class);
    }

    /**
     * @covers \App\Entity\Uca\Commande::updateMontantTotal
     */
    public function testUpdateMontantTotal(): void
    {
        $this->createCommande();
        $this->commande->updateMontantTotal();

        $this->assertEquals('10', $this->commande->getMontantTotal());
        $this->assertEquals(0.0, $this->commande->getTva());
    }

    /**
     * @covers \App\Entity\Uca\Commande::sauvegardeInformations
     */
    public function testSauvegardeInformations(): void
    {
        $this->createCommande();
        $this->commande->sauvegardeInformations();

        $this->assertEquals('Utilisateur', $this->commande->getPrenom());
        $this->assertEquals('Admin', $this->commande->getNom());
    }

    /**
     * @covers \App\Entity\Uca\Commande::changeStatut
     */
    public function testChangeStatutPanier(): void
    {
        $this->createCommande();
        $this->commande->changeStatut('panier', ['typePaiement' => 'BDS', 'moyenPaiement' => 'CB']);

        $this->assertEquals('panier', $this->commande->getStatut());
    }

    /**
     * @covers \App\Entity\Uca\Commande::changeStatut
     */
    public function testChangeStatutApayer(): void
    {
        $this->createCommande();
        $this->commande->changeStatut('apayer', ['typePaiement' => 'BDS', 'moyenPaiement' => 'CB']);

        $this->assertEquals('apayer', $this->commande->getStatut());
    }

    /**
     * @covers \App\Entity\Uca\Commande::changeStatut
     */
    public function testChangeStatutAnnule(): void
    {
        $this->createCommande();
        $this->commande->changeStatut('annule', ['typePaiement' => 'BDS', 'moyenPaiement' => 'CB']);

        $this->assertEquals('annule', $this->commande->getStatut());
    }

    /**
     * @covers \App\Entity\Uca\Commande::changeStatut
     */
    public function testChangeStatutAvoir(): void
    {
        $this->createCommande();
        $this->commande->changeStatut('avoir', ['typePaiement' => 'BDS', 'moyenPaiement' => 'CB']);

        $this->assertEquals('avoir', $this->commande->getStatut());
    }

    /**
     * @covers \App\Entity\Uca\Commande::changeStatut
     */
    public function testChangeStatutFactureAnnulee(): void
    {
        $this->createCommande();
        $this->commande->changeStatut('factureAnnulee', ['typePaiement' => 'BDS', 'moyenPaiement' => 'CB']);

        $this->assertEquals('factureAnnulee', $this->commande->getStatut());
    }

    /**
     * @covers \App\Entity\Uca\Commande::changeStatut
     */
    public function testChangeStatutTermine(): void
    {
        $this->createCommande();
        $this->commande->getCommandeDetails()->first()->setMontant(0);
        $this->commande->changeStatut('termine', ['typePaiement' => 'BDS', 'moyenPaiement' => 'CB']);

        $this->assertEquals('termine', $this->commande->getStatut());
    }

    /**
     * @covers \App\Entity\Uca\Commande::traitementPostGenerationAvoir
     */
    public function testTraitementPostGenerationAvoir(): void
    {
        $this->createCommande();
        $this->addAvoir();
        $this->commande->traitementPostGenerationAvoir();

        $this->assertTrue(true);
    }

    /**
     * @covers \App\Entity\Uca\Commande::traitementPostPaiement
     */
    public function testTraitementPostPaiement(): void
    {
        $this->createCommande();
        $this->commande->traitementPostPaiement();

        $this->assertTrue(true);
    }

    /**
     * @covers \App\Entity\Uca\Commande::traitementPostAnnulation
     */
    public function testTraitementPostAnnulation(): void
    {
        $this->createCommande();
        $this->commande->setCreditUtilise(10);
        $this->commande->traitementPostAnnulation(['em' => static::getContainer()->get(EntityManagerInterface::class)]);

        $this->assertTrue(true);
    }

    /**
     * @covers \App\Entity\Uca\Commande::setHmac
     */
    public function testSetHmac(): void
    {
        $this->createCommande();
        $this->commande->setHmac('hmac');

        $this->assertEquals('hmac', $this->commande->getCommandeDetails()->first()->getHmac());
    }

    /**
     * @covers \App\Entity\Uca\Commande::getHmac
     */
    public function testGetHmacNull(): void
    {
        $this->createCommande();
        $this->commande->setMoyenPaiement('CB')->setTypePaiement('BDS')->setHmac('hmac');
        $this->assertNull($this->commande->getHmac());
    }

    /**
     * @covers \App\Entity\Uca\Commande::getHmac
     */
    public function testGetHmacPaybox(): void
    {
        $this->createCommande();
        $this->commande->setMoyenPaiement('CB')->setTypePaiement('PAYBOX')->setHmac('hmac');
        $this->assertEquals('hmac', $this->commande->getHmac());
    }

    /**
     * @covers \App\Entity\Uca\Commande::getTimeout
     */
    public function testGetTimeoutPanier(): void
    {
        $this->dispatchKernelEvent();
        $this->createCommande();

        $this->commande->setStatut('panier');

        $this->assertInstanceOf(DateInterval::class, $this->commande->getTimeout());
    }

    /**
     * @covers \App\Entity\Uca\Commande::getTimeout
     */
    public function testGetTimeoutAPayerPaybox(): void
    {
        $this->dispatchKernelEvent();
        $this->createCommande();

        $this->commande->setTypePaiement('PAYBOX')->setStatut('apayer')->setDateCommande(new DateTime());

        $this->assertInstanceOf(DateInterval::class, $this->commande->getTimeout());
    }

    /**
     * @covers \App\Entity\Uca\Commande::getTimeout
     */
    public function testGetTimeoutAPayerBDS(): void
    {
        $this->dispatchKernelEvent();
        $this->createCommande();

        $this->commande->setTypePaiement('BDS')->setStatut('apayer')->setDateCommande(new DateTime());

        $this->assertInstanceOf(DateInterval::class, $this->commande->getTimeout());
    }

    /**
     * @covers \App\Entity\Uca\Commande::getTimeout
     */
    public function testGetTimeoutNull(): void
    {
        $this->createCommande();
        $this->commande->setStatut('annule');

        $this->assertNull($this->commande->getTimeout());
    }

    /**
     * @covers \App\Entity\Uca\Commande::getTimeout
     */
    public function testGetTimeoutNullDate(): void
    {
        $this->dispatchKernelEvent();
        $this->createCommande();

        $this->commande->setTypePaiement('BDS')->setStatut('apayer');

        $this->assertNull($this->commande->getTimeout());
    }

    /**
     * @covers \App\Entity\Uca\Commande::hasAvoir
     */
    public function testHasAvoirFalse(): void
    {
        $this->createCommande();
        $this->assertFalse($this->commande->hasAvoir());
    }

    /**
     * @covers \App\Entity\Uca\Commande::hasAvoir
     */
    public function testHasAvoirTrue(): void
    {
        $this->createCommande();
        $this->addAvoir();
        $this->assertTrue($this->commande->hasAvoir());
    }

    /**
     * @covers \App\Entity\Uca\Commande::eligibleAvoir
     */
    public function testEligibleAvoir(): void
    {
        $this->createCommande();
        $this->assertInstanceOf(Commande::class, $this->commande->eligibleAvoir());
    }

    /**
     * @covers \App\Entity\Uca\Commande::eligibleAvoir
     */
    public function testEligibleFalse(): void
    {
        $commande = new Commande(static::getContainer()->get(UtilisateurRepository::class)->findOneByUsername('admin'));
        $this->assertFalse($commande->eligibleAvoir());
    }

    /**
     * @covers \App\Entity\Uca\Commande::hasFormatAchatCarte
     */
    public function testHasFormatAchatCarte(): void
    {
        $this->createCommande();

        $typeAutorisation = (new TypeAutorisation())
            ->setLibelle('Type autorisation')->setComportementLibelle('Achat de Carte')
            ->setComportement((new ComportementAutorisation())->setLibelle('Achat de Carte'))
        ;

        $commandeDetail = (new CommandeDetail(
            $this->commande,
            'autorisation',
            $typeAutorisation,
            $this->commande->getCommandeDetails()->first()
        ))->setMontant('10');

        $this->assertIsArray($this->commande->hasFormatAchatCarte());
    }

    /**
     * @covers \App\Entity\Uca\Commande::hasFormatAchatCarte
     */
    public function testHasFormatAchatCarteFalse(): void
    {
        $this->createCommande();
        $this->assertFalse($this->commande->hasFormatAchatCarte());
    }

    /**
     * @covers \App\Entity\Uca\Commande::getCommmandeDetailsByAvoir
     */
    public function testGetCommmandeDetailsByAvoirNone(): void
    {
        $this->createCommande();
        $cmdDetails = $this->commande->getCommmandeDetailsByAvoir('1234');

        $this->assertInstanceOf(ArrayCollection::class, $cmdDetails);
        $this->assertEquals(0, $cmdDetails->count());
    }

    /**
     * @covers \App\Entity\Uca\Commande::getCommmandeDetailsByAvoir
     */
    public function testGetCommmandeDetailsByAvoir(): void
    {
        $this->createCommande();
        $this->addAvoir();
        $cmdDetails = $this->commande->getCommmandeDetailsByAvoir('1234');

        $this->assertInstanceOf(ArrayCollection::class, $cmdDetails);
        $this->assertEquals(1, $cmdDetails->count());
    }

    /**
     * @covers \App\Entity\Uca\Commande::getTotalAvoir
     */
    public function testGetTotalAvoir(): void
    {
        $this->createCommande();
        $this->addAvoir();

        $this->assertEquals(10, $this->commande->getTotalAvoir('1234'));
    }

    /**
     * @covers \App\Entity\Uca\Commande::getTvaAvoir
     */
    public function testGetTvaAvoir(): void
    {
        $this->createCommande();
        $this->addAvoir();

        $this->assertEquals(0.0, $this->commande->getTvaAvoir('1234'));
    }

    /**
     * @covers \App\Entity\Uca\Commande::getDateAvoir
     */
    public function testGetDateAvoir(): void
    {
        $this->createCommande();
        $this->addAvoir();
        $this->assertInstanceOf(DateTime::class, $this->commande->getDateAvoir());
    }

    /**
     * @covers \App\Entity\Uca\Commande::getMontantAPayer
     */
    public function testGetMontantAPayer(): void
    {
        $this->createCommande();
        $this->commande->setMontantTotal('10')->setCreditUtilise('5');

        $this->assertEquals(5.0, $this->commande->getMontantAPayer());
    }

    /**
     * Fonction qui permet de créer un objet Commande.
     */
    private function createCommande(): void
    {
        $this->user = static::getContainer()->get(UtilisateurRepository::class)->findOneByUsername('admin');
        $this->commande = new Commande($this->user);

        $formatSimple = (new FormatSimple())
            ->setDateDebutEffective(new DateTime())
            ->setDateFinEffective(new DateTime())
        ;

        $inscription = (new Inscription(
            $formatSimple,
            new Utilisateur(),
            ['typeInscription' => 'format']
        ));

        $inscription->initAutorisations();

        $commandeDetail = (new CommandeDetail(
            $this->commande,
            'inscription',
            $inscription
        ))->setMontant('10');
    }

    /**
     * Fonction qui permet de simuler l'envoi d'une requête Kernel pour pouvoir initaliser l'écoute du Listener Parametrage.
     */
    private function dispatchKernelEvent(): void
    {
        $client = $this->createClient();

        $client->request('GET', '/');

        $dispatcher = new EventDispatcher();
        $event = new RequestEvent(
            static::getContainer()->get(HttpKernelInterface::class),
            $client->getRequest(),
            null
        );

        $dispatcher->dispatch($event, 'kernel.event_listener');
    }

    /**
     * Fonction qui permet d'ajouter un avoir à la commande.
     */
    private function addAvoir(): void
    {
        $formatSimple = (new FormatSimple())
            ->setDateDebutEffective(new DateTime())
            ->setDateFinEffective(new DateTime())
        ;

        $inscription = (new Inscription(
            $formatSimple,
            new Utilisateur(),
            ['typeInscription' => 'format']
        ));

        $commandeDetail = (new CommandeDetail(
            $this->commande,
            'inscription',
            $inscription
        ))->setMontant('10')->setReferenceAvoir('1234')->setTva('0.0')->setDateAvoir(new DateTime());

        $this->commande->addAvoirCommandeDetail($commandeDetail);
    }
}