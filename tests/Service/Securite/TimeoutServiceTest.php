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

        $this->utilisateur = (new Utilisateur())
            ->setUserName('Inscription')
            ->setEmail('test@test.com')
            ->setPassword('test')
        ;

        $paramRepo = new ParametrageRepository($container->get(ManagerRegistry::class));
        $param = new Parametrage($paramRepo);

        $event = new RequestEvent($kernel, new Request(), null);
        $param->onKernelRequest($event);
    }

    /**
     * @covers \App\Service\Securite\TimeoutService::nettoyageCommande
     */
    public function testNettoyageCommande(): void
    {
        $commande = (new Commande($this->utilisateur))
            ->setStatut('panier')
            ->setUtilisateur($this->utilisateur)
            ->setDatePanier(new \Datetime('yesterday'))
        ;

        $this->em->persist($this->utilisateur);
        $this->em->persist($commande);
        $this->em->flush();

        $this->timeoutService->nettoyageCommande();
        $this->em->flush();

        $this->assertEquals($commande->getStatut(), 'annule');

        $this->em->remove($this->utilisateur);
        $this->em->remove($commande);
        $this->em->flush();

        $commande = (new Commande($this->utilisateur))
            ->setStatut('apayer')
            ->setUtilisateur($this->utilisateur)
            ->setDateCommande(new \Datetime('2020-06-18'))
            ->setTypePaiement('BDS')
        ;

        $this->em->persist($this->utilisateur);
        $this->em->persist($commande);
        $this->em->flush();

        $this->timeoutService->nettoyageCommande();
        $this->em->flush();

        $this->assertEquals($commande->getStatut(), 'annule');

        $this->em->remove($this->utilisateur);
        $this->em->remove($commande);
        $this->em->flush();

        $commande = (new Commande($this->utilisateur))
            ->setStatut('apayer')
            ->setUtilisateur($this->utilisateur)
            ->setDateCommande(new \Datetime('yesterday'))
            ->setTypePaiement('PAYBOX')
            ->setMoyenPaiement('cb')
        ;

        $this->em->persist($this->utilisateur);
        $this->em->persist($commande);
        $this->em->flush();

        $this->timeoutService->nettoyageCommande();
        $this->em->flush();

        $this->assertEquals($commande->getStatut(), 'annule');

        $this->em->remove($this->utilisateur);
        $this->em->remove($commande);
        $this->em->flush();
    }

    /**
     * @covers \App\Service\Securite\TimeoutService::nettoyageInscription
     */
    public function testNettoyageInscription(): void
    {
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

        $this->em->persist($this->creneau);
        $this->em->persist($this->comportementAutorisation);
        $this->em->persist($this->typeAutorisation);
        $this->em->persist($this->comportementAutorisationFormat);
        $this->em->persist($this->typeAutorisationFormat);
        $this->em->persist($this->format);
        $this->em->persist($this->formatActivite);
        $this->em->persist($this->inscription);
        $this->em->persist($this->utilisateur);
        $this->em->persist($this->inscription);
        $this->em->persist($commandeDetail);
        $this->em->persist($commande);

        $this->em->flush();

        $this->timeoutService->nettoyageInscription();
        $this->em->flush();

        $this->assertEquals($this->inscription->getStatut(), 'annule');

        $this->em->remove($this->creneau);
        $this->em->remove($this->comportementAutorisation);
        $this->em->remove($this->typeAutorisation);
        $this->em->remove($this->comportementAutorisationFormat);
        $this->em->remove($this->typeAutorisationFormat);
        $this->em->remove($this->format);
        $this->em->remove($this->formatActivite);
        $this->em->remove($this->inscription);
        $this->em->remove($this->utilisateur);
        $this->em->remove($this->inscription);
        $this->em->remove($commandeDetail);
        $this->em->remove($commande);

        $this->em->flush();
    }

    /**
     * @covers \App\Service\Securite\TimeoutService::annulationInscriptionEtCommande
     * @covers \App\Service\Securite\TimeoutService::nettoyageCommandesInscriptionsPartenaires
     */
    public function testNettoyageCommandesInscriptionsPartenaires(): void
    {
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

        $this->em->persist($this->creneau);
        $this->em->persist($this->comportementAutorisation);
        $this->em->persist($this->typeAutorisation);
        $this->em->persist($this->comportementAutorisationFormat);
        $this->em->persist($this->typeAutorisationFormat);
        $this->em->persist($this->format);
        $this->em->persist($this->formatActivite);
        $this->em->persist($this->utilisateur);
        $this->em->persist($commandeDetail);
        $this->em->persist($commande);

        $reservabilite = new Reservabilite();
        $reservabilite->setCapacite(10);

        $this->inscription->setReservabilite($reservabilite);
        $this->inscription->setStatut('attentepartenaire');
        $this->inscription->setListeEmailPartenaires('');
        $this->inscription->setDate(new \Datetime('2020-06-21'));

        $this->em->persist($reservabilite);
        $this->em->persist($this->inscription);

        $this->em->flush();

        $inscription2 = new Inscription($this->format, $this->utilisateur, []);
        $inscription2->setFormatActivite($this->formatActivite);
        $inscription2->setCreneau($this->creneau);
        $inscription2->setStatut('attenteajoutpanier');
        $inscription2->setDateValidation(new \Datetime('2020-06-18'));
        $commandeDetail2 = new CommandeDetail($commande2, 'inscription', $inscription2);
        $inscription2->addCommandeDetail($commandeDetail2);
        $inscription2->setEstPartenaire($this->inscription->getId());
        $this->utilisateur->addInscription($inscription2);

        $this->em->persist($commandeDetail2);
        $this->em->persist($commande2);
        $this->em->persist($inscription2);
        $this->em->flush();

        $this->assertEquals($commandeDetail->getCommande()->getStatut(), 'panier');
        $this->assertEquals($commandeDetail2->getCommande()->getStatut(), 'panier');

        $this->timeoutService->nettoyageCommandesInscriptionsPartenaires();
        $this->em->flush();

        $this->assertEquals($commandeDetail->getCommande()->getStatut(), 'annule');
        $this->assertEquals($commandeDetail2->getCommande()->getStatut(), 'annule');

        $this->em->remove($this->creneau);
        $this->em->remove($this->comportementAutorisation);
        $this->em->remove($this->typeAutorisation);
        $this->em->remove($this->comportementAutorisationFormat);
        $this->em->remove($this->typeAutorisationFormat);
        $this->em->remove($this->format);
        $this->em->remove($this->formatActivite);
        $this->em->remove($this->inscription);
        $this->em->remove($this->utilisateur);
        $this->em->remove($this->inscription);
        $this->em->remove($commandeDetail);
        $this->em->remove($commande);
        $this->em->remove($commandeDetail2);
        $this->em->remove($commande2);
        $this->em->remove($inscription2);

        $this->em->flush();
    }

    /**
     * @covers \App\Service\Securite\TimeoutService::annulationInscriptionEtCommande
     * @covers \App\Service\Securite\TimeoutService::nettoyageCommandeEtInscription
     */
    public function testNettoyageCommandeEtInscription(): void
    {
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

        $this->em->persist($this->creneau);
        $this->em->persist($this->comportementAutorisation);
        $this->em->persist($this->typeAutorisation);
        $this->em->persist($this->comportementAutorisationFormat);
        $this->em->persist($this->typeAutorisationFormat);
        $this->em->persist($this->format);
        $this->em->persist($this->formatActivite);
        $this->em->persist($this->inscription);
        $this->em->persist($this->utilisateur);
        $this->em->persist($reservabilite);

        $this->em->flush();

        $this->assertEquals($this->inscription->getStatut(), 'attentepartenaire');

        $this->timeoutService->nettoyageCommandeEtInscription();

        $this->assertEquals($this->inscription->getStatut(), 'annule');

        $this->em->remove($this->creneau);
        $this->em->remove($this->comportementAutorisation);
        $this->em->remove($this->typeAutorisation);
        $this->em->remove($this->comportementAutorisationFormat);
        $this->em->remove($this->typeAutorisationFormat);
        $this->em->remove($this->format);
        $this->em->remove($this->formatActivite);
        $this->em->remove($this->inscription);
        $this->em->remove($this->utilisateur);
        $this->em->remove($reservabilite);

        $this->em->flush();
    }
}
