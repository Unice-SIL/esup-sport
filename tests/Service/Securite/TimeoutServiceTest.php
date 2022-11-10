<?php

namespace App\Tests\Securite\Service;

use App\Entity\Uca\Commande;
use App\Entity\Uca\CommandeDetail;
use App\Entity\Uca\ComportementAutorisation;
use App\Entity\Uca\Creneau;
use App\Entity\Uca\FormatAchatCarte;
use App\Entity\Uca\FormatAvecCreneau;
use App\Entity\Uca\Inscription;
use App\Entity\Uca\Reservabilite;
use App\Entity\Uca\TypeAutorisation;
use App\Entity\Uca\Utilisateur;
use App\Repository\ParametrageRepository;
use App\Service\Common\MailService;
use App\Service\Common\Parametrage;
use App\Service\Securite\TimeoutService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * @internal
 * @coversNothing
 */
class TimeoutServiceTest extends WebTestCase
{
    /**
     * @var TimeoutService
     */
    private $timeoutService;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $container = static::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $logger = $container->get(LoggerInterface::class);
        $mailer = $container->get(MailService::class);

        $this->timeoutService = new TimeoutService($this->em, $logger, $mailer);


        $paramRepo = new ParametrageRepository($container->get(ManagerRegistry::class));
        $param = new Parametrage($paramRepo);

        $event = new RequestEvent($kernel, new Request(), null);
        $param->onKernelRequest($event);
    }

    protected function tearDown(): void
    {
        static::ensureKernelShutdown();
    }

    /**
     * Data provider pour le nettoyage de commande.
     */
    public function nettoyageCommandeDataProvider()
    {
        $this->utilisateur = (new Utilisateur())
            ->setUserName('Inscription')
            ->setEmail('test@test.com')
            ->setPassword('test')
        ;

        $commande1 = (new Commande($this->utilisateur))
            ->setStatut('panier')
            ->setUtilisateur($this->utilisateur)
            ->setDatePanier(new \Datetime('yesterday'))
        ;

        $commande2 = (new Commande($this->utilisateur))
            ->setStatut('apayer')
            ->setUtilisateur($this->utilisateur)
            ->setDateCommande(new \Datetime('2020-06-18'))
            ->setTypePaiement('BDS')
        ;

        $commande3 = (new Commande($this->utilisateur))
            ->setStatut('apayer')
            ->setUtilisateur($this->utilisateur)
            ->setDateCommande(new \Datetime('yesterday'))
            ->setTypePaiement('PAYBOX')
            ->setMoyenPaiement('cb')
        ;

        return [
            [$commande1, $this->utilisateur],
            [$commande2, $this->utilisateur],
            [$commande3, $this->utilisateur],
        ];
    }

    /**
     * @dataProvider nettoyageCommandeDataProvider
     *
     * @covers \App\Service\Securite\TimeoutService::nettoyageCommande
     */
    public function testNettoyageCommande(Commande $commande, Utilisateur $utilisateur): void
    {
        $this->persistObjects([$utilisateur, $commande]);

        $this->timeoutService->nettoyageCommande();
        $this->em->flush();

        $this->assertEquals($commande->getStatut(), 'annule');

        $this->removeObjects([$utilisateur, $commande]);
    }

    /**
     * Data provider pour le nettoyage d'inscription.
     */
    public function nettoyageInscriptionDataProvider()
    {
        $this->utilisateur = (new Utilisateur())
            ->setUserName('Inscription')
            ->setEmail('test@test.com')
            ->setPassword('test')
        ;

        $this->comportementAutorisationFormat = (new ComportementAutorisation())
            ->setdescriptionComportement('desc')
            ->setLibelle('libelle comportement')
            ->setCodeComportement('code')
        ;

        $this->typeAutorisationFormat = (new TypeAutorisation())
            ->setComportement(
                $this->comportementAutorisationFormat
            )
            ->setLibelle('')
        ;

        $date = new \Datetime();

        $this->format =
            (new FormatAchatCarte())
                ->setCarte(
                    $this->typeAutorisationFormat
                )
                ->setCapacite(10)
                ->setLibelle('')
                ->setDescription('')
                ->setDateDebutPublication($date)
                ->setDateFinPublication($date)
                ->setDateDebutInscription($date)
                ->setDateFinInscription($date)
                ->setDateDebutEffective($date)
                ->setDateFinEffective($date)
                ->setImage('')
                ->setEstPayant(false)
                ->setEstEncadre(false)
        ;

        $this->comportementAutorisation = (new ComportementAutorisation())
            ->setdescriptionComportement('desc2')
            ->setLibelle('libelle comportement2')
            ->setCodeComportement('code')
        ;

        $this->typeAutorisation = (new TypeAutorisation())
            ->setComportement(
                $this->comportementAutorisation
            )
            ->setLibelle('')
        ;

        $this->inscription = new Inscription($this->format, $this->utilisateur, []);

        $date = new \DateTime();

        $this->formatActivite =
            (new FormatAvecCreneau())
                ->setLibelle('FormatAvecCreneau')
                ->addAutorisation($this->typeAutorisation)
                ->setCapacite(10)
                ->setDescription('')
                ->setDateDebutPublication($date)
                ->setDateFinPublication($date)
                ->setDateDebutInscription($date)
                ->setDateFinInscription($date)
                ->setDateDebutEffective($date)
                ->setDateFinEffective($date)
                ->setImage('')
                ->setEstPayant(false)
                ->setEstEncadre(false)
        ;
        $this->inscription->setFormatActivite($this->formatActivite);
        $this->creneau = (new Creneau())
            ->setFormatActivite($this->inscription->getFormatActivite())
            ->setCapacite(10)
        ;
        $this->inscription->setCreneau($this->creneau);
        $this->inscription->setStatut('attenteajoutpanier');
        $this->inscription->setDateValidation(new \Datetime('2020-06-18'));

        $this->utilisateur->addInscription($this->inscription);

        $commande = (new Commande($this->utilisateur))
            ->setStatut('panier')
            ->setUtilisateur($this->utilisateur)
            ->setDatePanier(new \Datetime('yesterday'))
        ;

        $commandeDetail = new CommandeDetail($commande, 'inscription', $this->inscription);

        $this->inscription->addCommandeDetail($commandeDetail);

        return [
            [$this->creneau, $this->comportementAutorisation, $this->typeAutorisation, $this->comportementAutorisationFormat, $this->typeAutorisationFormat, $this->format, $this->formatActivite, $this->inscription, $this->utilisateur, $commandeDetail, $commande],
        ];
    }

    /**
     * @dataProvider nettoyageInscriptionDataProvider
     *
     * @covers \App\Service\Securite\TimeoutService::nettoyageInscription
     *
     * @param mixed $creneau
     * @param mixed $comporementAutorisation
     * @param mixed $typeAutorisation
     * @param mixed $comportementAutorisationFormat
     * @param mixed $typeAutorisationFormat
     * @param mixed $format
     * @param mixed $formatActivite
     * @param mixed $inscription
     * @param mixed $utilisateur
     * @param mixed $commandeDetail
     * @param mixed $commande
     */
    public function testNettoyageInscription($creneau, $comporementAutorisation, $typeAutorisation, $comportementAutorisationFormat, $typeAutorisationFormat, $format, $formatActivite, $inscription, $utilisateur, $commandeDetail, $commande): void
    {
        $this->persistObjects([$creneau, $comporementAutorisation, $typeAutorisation, $comportementAutorisationFormat, $typeAutorisationFormat, $format, $formatActivite, $inscription, $utilisateur, $commandeDetail, $commande]);

        $this->timeoutService->nettoyageInscription();
        $this->em->flush();

        $this->assertEquals($inscription->getStatut(), 'annule');

        $this->removeObjects([$creneau, $comporementAutorisation, $typeAutorisation, $comportementAutorisationFormat, $typeAutorisationFormat, $format, $formatActivite, $inscription, $utilisateur, $commandeDetail, $commande]);
    }

    /**
     * Data provider pour l'annulation d'inscription et commande.
     */
    public function annulationInscriptionEtCommandeDataProvider()
    {
        $this->utilisateur = (new Utilisateur())
            ->setUserName('Inscription')
            ->setEmail('test@test.com')
            ->setPassword('test')
        ;

        $this->comportementAutorisationFormat = (new ComportementAutorisation())
            ->setdescriptionComportement('desc')
            ->setLibelle('libelle comportement')
            ->setCodeComportement('code')
        ;

        $this->typeAutorisationFormat = (new TypeAutorisation())
            ->setComportement(
                $this->comportementAutorisationFormat
            )
            ->setLibelle('')
        ;

        $date = new \Datetime();

        $this->format =
            (new FormatAchatCarte())
                ->setCarte(
                    $this->typeAutorisationFormat
                )
                ->setCapacite(10)
                ->setLibelle('')
                ->setDescription('')
                ->setDateDebutPublication($date)
                ->setDateFinPublication($date)
                ->setDateDebutInscription($date)
                ->setDateFinInscription($date)
                ->setDateDebutEffective($date)
                ->setDateFinEffective($date)
                ->setImage('')
                ->setEstPayant(false)
                ->setEstEncadre(false)
        ;

        $this->comportementAutorisation = (new ComportementAutorisation())
            ->setdescriptionComportement('desc2')
            ->setLibelle('libelle comportement2')
            ->setCodeComportement('code')
        ;

        $this->typeAutorisation = (new TypeAutorisation())
            ->setComportement(
                $this->comportementAutorisation
            )
            ->setLibelle('')
        ;

        $this->inscription = new Inscription($this->format, $this->utilisateur, []);

        $date = new \DateTime();

        $this->formatActivite =
            (new FormatAvecCreneau())
                ->setLibelle('FormatAvecCreneau')
                ->addAutorisation($this->typeAutorisation)
                ->setCapacite(10)
                ->setDescription('')
                ->setDateDebutPublication($date)
                ->setDateFinPublication($date)
                ->setDateDebutInscription($date)
                ->setDateFinInscription($date)
                ->setDateDebutEffective($date)
                ->setDateFinEffective($date)
                ->setImage('')
                ->setEstPayant(false)
                ->setEstEncadre(false)
        ;
        $this->inscription->setFormatActivite($this->formatActivite);
        $this->creneau = (new Creneau())
            ->setFormatActivite($this->inscription->getFormatActivite())
            ->setCapacite(10)
        ;
        $this->inscription->setCreneau($this->creneau);
        $this->inscription->setStatut('attenteajoutpanier');
        $this->inscription->setDateValidation(new \Datetime('2020-06-18'));

        $this->utilisateur->addInscription($this->inscription);

        $commande = (new Commande($this->utilisateur))
            ->setStatut('panier')
            ->setUtilisateur($this->utilisateur)
            ->setDatePanier(new \Datetime('yesterday'))
        ;

        $commande2 = (new Commande($this->utilisateur))
            ->setStatut('panier')
            ->setUtilisateur($this->utilisateur)
            ->setDatePanier(new \Datetime('yesterday'))
        ;

        $commandeDetail = new CommandeDetail($commande, 'inscription', $this->inscription);

        $this->inscription->addCommandeDetail($commandeDetail);

        $reservabilite = new Reservabilite();
        $reservabilite->setCapacite(10);

        $this->inscription->setReservabilite($reservabilite);
        $this->inscription->setStatut('attentepartenaire');
        $this->inscription->setListeEmailPartenaires('');
        $this->inscription->setDate(new \Datetime('2020-06-21'));

        $inscription2 = new Inscription($this->format, $this->utilisateur, []);
        $inscription2->setFormatActivite($this->formatActivite);
        $inscription2->setCreneau($this->creneau);
        $inscription2->setStatut('attenteajoutpanier');
        $inscription2->setDateValidation(new \Datetime('2020-06-18'));
        $commandeDetail2 = new CommandeDetail($commande2, 'inscription', $inscription2);
        $inscription2->addCommandeDetail($commandeDetail2);
        $this->utilisateur->addInscription($inscription2);

        return [
            [$this->creneau, $this->comportementAutorisation, $this->typeAutorisation, $this->comportementAutorisationFormat, $this->typeAutorisationFormat, $this->format, $this->formatActivite, $this->utilisateur, $commandeDetail, $commande, $reservabilite, $this->inscription, $commandeDetail2, $commande2, $inscription2],
        ];
    }

    /**
     * @dataProvider annulationInscriptionEtCommandeDataProvider
     *
     * @covers \App\Service\Securite\TimeoutService::annulationInscriptionEtCommande
     * @covers \App\Service\Securite\TimeoutService::nettoyageCommandesInscriptionsPartenaires
     *
     * @param mixed $creneau
     * @param mixed $comportementAutorisation
     * @param mixed $typeAutorisation
     * @param mixed $comportementAutorisationFormat
     * @param mixed $typeAutorisationFormat
     * @param mixed $format
     * @param mixed $formatActivite
     * @param mixed $utilisateur
     * @param mixed $commandeDetail
     * @param mixed $commande
     * @param mixed $reservabilite
     * @param mixed $inscription
     * @param mixed $commandeDetail2
     * @param mixed $commande2
     * @param mixed $inscription2
     */
    public function testNettoyageCommandesInscriptionsPartenaires($creneau, $comportementAutorisation, $typeAutorisation, $comportementAutorisationFormat, $typeAutorisationFormat, $format, $formatActivite, $utilisateur, $commandeDetail, $commande, $reservabilite, $inscription, $commandeDetail2, $commande2, $inscription2): void
    {
        $this->persistObjects([$creneau, $comportementAutorisation, $typeAutorisation, $comportementAutorisationFormat, $typeAutorisationFormat, $format, $formatActivite, $utilisateur, $commandeDetail, $commande, $reservabilite, $inscription, $commandeDetail2, $commande2, $inscription2]);

        $inscription2->setEstPartenaire($inscription->getId());
        $this->persistObjects([$inscription2]);

        $this->timeoutService->nettoyageCommandesInscriptionsPartenaires();

        $this->assertEquals($commandeDetail->getCommande()->getStatut(), 'annule');
        $this->assertEquals($commandeDetail2->getCommande()->getStatut(), 'annule');

        $this->removeObjects([$creneau, $comportementAutorisation, $typeAutorisation, $comportementAutorisationFormat, $typeAutorisationFormat, $format, $formatActivite, $utilisateur, $commandeDetail, $commande, $reservabilite, $inscription, $commandeDetail2, $commande2, $inscription2]);
    }

    /**
     * Data provider pour le nettoyage d'inscription et commande.
     */
    public function nettoyageCommandeEtInscriptionDataProvider()
    {
        $this->utilisateur = (new Utilisateur())
            ->setUserName('Inscription')
            ->setEmail('test@test.com')
            ->setPassword('test')
        ;

        $this->comportementAutorisationFormat = (new ComportementAutorisation())
            ->setdescriptionComportement('desc')
            ->setLibelle('libelle comportement')
            ->setCodeComportement('code')
        ;

        $this->typeAutorisationFormat = (new TypeAutorisation())
            ->setComportement(
                $this->comportementAutorisationFormat
            )
            ->setLibelle('')
        ;

        $date = new \Datetime();

        $this->format =
            (new FormatAchatCarte())
                ->setCarte(
                    $this->typeAutorisationFormat
                )
                ->setCapacite(10)
                ->setLibelle('')
                ->setDescription('')
                ->setDateDebutPublication($date)
                ->setDateFinPublication($date)
                ->setDateDebutInscription($date)
                ->setDateFinInscription($date)
                ->setDateDebutEffective($date)
                ->setDateFinEffective($date)
                ->setImage('')
                ->setEstPayant(false)
                ->setEstEncadre(false)
        ;

        $this->comportementAutorisation = (new ComportementAutorisation())
            ->setdescriptionComportement('desc2')
            ->setLibelle('libelle comportement2')
            ->setCodeComportement('code')
        ;

        $this->typeAutorisation = (new TypeAutorisation())
            ->setComportement(
                $this->comportementAutorisation
            )
            ->setLibelle('')
        ;

        $this->inscription = new Inscription($this->format, $this->utilisateur, []);

        $date = new \DateTime();

        $this->formatActivite =
            (new FormatAvecCreneau())
                ->setLibelle('FormatAvecCreneau')
                ->addAutorisation($this->typeAutorisation)
                ->setCapacite(10)
                ->setDescription('')
                ->setDateDebutPublication($date)
                ->setDateFinPublication($date)
                ->setDateDebutInscription($date)
                ->setDateFinInscription($date)
                ->setDateDebutEffective($date)
                ->setDateFinEffective($date)
                ->setImage('')
                ->setEstPayant(false)
                ->setEstEncadre(false)
        ;
        $this->inscription->setFormatActivite($this->formatActivite);
        $this->creneau = (new Creneau())
            ->setFormatActivite($this->inscription->getFormatActivite())
            ->setCapacite(10)
        ;
        $this->inscription->setCreneau($this->creneau);
        $this->inscription->setStatut('attenteajoutpanier');
        $this->inscription->setDateValidation(new \Datetime('2020-06-18'));

        $this->utilisateur->addInscription($this->inscription);

        $reservabilite = new Reservabilite();
        $reservabilite->setCapacite(10);

        $this->inscription->setReservabilite($reservabilite);
        $this->inscription->setStatut('attentepartenaire');
        $this->inscription->setListeEmailPartenaires('');
        $this->inscription->setDate(new \Datetime('2020-06-21'));

        return [
            [$this->creneau, $this->comportementAutorisation, $this->typeAutorisation, $this->comportementAutorisationFormat, $this->typeAutorisationFormat, $this->format, $this->formatActivite, $this->inscription, $this->utilisateur, $reservabilite],
        ];
    }

    /**
     * @dataProvider nettoyageCommandeEtInscriptionDataProvider
     *
     * @covers \App\Service\Securite\TimeoutService::annulationInscriptionEtCommande
     * @covers \App\Service\Securite\TimeoutService::nettoyageCommandeEtInscription
     *
     * @param mixed $creneau
     * @param mixed $comportementAutorisation
     * @param mixed $typeAutorisation
     * @param mixed $comportementAutorisationFormat
     * @param mixed $typeAutorisationFormat
     * @param mixed $format
     * @param mixed $formatActivite
     * @param mixed $inscription
     * @param mixed $utilisateur
     * @param mixed $reservabilite
     */
    public function testNettoyageCommandeEtInscription($creneau, $comportementAutorisation, $typeAutorisation, $comportementAutorisationFormat, $typeAutorisationFormat, $format, $formatActivite, $inscription, $utilisateur, $reservabilite): void
    {
        $this->persistObjects([$creneau, $comportementAutorisation, $typeAutorisation, $comportementAutorisationFormat, $typeAutorisationFormat, $format, $formatActivite, $inscription, $utilisateur, $reservabilite]);

        $this->assertEquals($inscription->getStatut(), 'attentepartenaire');

        $this->timeoutService->nettoyageCommandeEtInscription();

        $this->assertEquals($inscription->getStatut(), 'annule');

        $this->removeObjects([$creneau, $comportementAutorisation, $typeAutorisation, $comportementAutorisationFormat, $typeAutorisationFormat, $format, $formatActivite, $inscription, $utilisateur, $reservabilite]);
    }

    private function persistObjects($objects)
    {
        foreach ($objects as $event) {
            $this->em->persist($event);
        }
        $this->em->flush();

        return $objects;
    }

    private function removeObjects($objects)
    {
        foreach ($objects as $event) {
            $this->em->remove($event);
        }
        $this->em->flush();

        return $objects;
    }
}
