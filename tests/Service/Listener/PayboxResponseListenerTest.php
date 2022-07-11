<?php

namespace App\Tests\Service\Listener;

use App\Entity\Uca\Commande;
use App\Repository\CommandeRepository;
use App\Repository\UtilisateurRepository;
use App\Service\Common\MailService;
use App\Service\Listener\PayboxResponseListener;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\PayboxBundle\Event\PayboxResponseEvent;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 * @coversNothing
 */
class PayboxResponseListenerTest extends WebTestCase
{
    /**
     * @var PayboxResponseListener
     */
    private $payboxListener;

    protected function setUp(): void
    {
        $container = static::getContainer();

        $this->payboxListener = new PayboxResponseListener(
            $container->get(EntityManagerInterface::class),
            $container->get(LoggerInterface::class),
            $container->get(MailService::class),
            $container->get(CommandeRepository::class)
        );
    }

    /**
     * @covers \App\Service\Listener\PayboxResponseListener::__construct
     */
    public function testConstructor(): void
    {
        $this->assertInstanceOf(PayboxResponseListener::class, $this->payboxListener);
    }

    /**
     * @covers \App\Service\Listener\PayboxResponseListener::onPayboxIpnResponse
     */
    public function testOnPayboxIpnResponseNotVerified(): void
    {
        $_SERVER['REQUEST_URI'] = 'test';
        $reponse = new PayboxResponseEvent([], false);

        $this->payboxListener->onPayboxIpnResponse($reponse);

        $this->assertTrue(true);
    }

    /**
     * @covers \App\Service\Listener\PayboxResponseListener::onPayboxIpnResponse
     */
    public function testOnPayboxIpnResponseWithError(): void
    {
        $_SERVER['REQUEST_URI'] = 'test';
        $reponse = new PayboxResponseEvent(['Erreur' => 'error'], false);

        $this->payboxListener->onPayboxIpnResponse($reponse);

        $this->assertTrue(true);
    }

    /**
     * @covers \App\Service\Listener\PayboxResponseListener::onPayboxIpnResponse
     */
    public function testOnPayboxIpnResponse(): void
    {
        $_SERVER['REQUEST_URI'] = 'test';

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $user = static::getContainer()->get(UtilisateurRepository::class)->findOneByUsername('admin');

        // Création de la commande
        $commande = (new Commande($user))
            ->setMontantPaybox('100')
            ->setMontantTotal('100')
            ->setNumeroCommande('123')
        ;

        $em->persist($commande);
        $em->flush();

        $_GET['id'] = $commande->getId();

        $reponse = new PayboxResponseEvent([
            'Erreur' => 0,
            'Ref' => $commande->getNumeroCommande(),
            'Mt' => ((int) $commande->getMontantPaybox() * 100),
        ], true);

        $this->payboxListener->onPayboxIpnResponse($reponse);

        $this->assertEquals('termine', $commande->getStatut());
        $this->assertEquals('PAYBOX', $commande->getTypePaiement());
        $this->assertEquals('cb', $commande->getMoyenPaiement());

        $em->remove($commande);
        $em->flush();
    }

    /**
     * @covers \App\Service\Listener\PayboxResponseListener::onPayboxIpnResponse
     */
    public function testOnPayboxIpnResponseMontantInvalide(): void
    {
        $_SERVER['REQUEST_URI'] = 'test';

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $user = static::getContainer()->get(UtilisateurRepository::class)->findOneByUsername('admin');

        // Création de la commande
        $commande = (new Commande($user))
            ->setMontantPaybox('100')
            ->setMontantTotal('100')
            ->setNumeroCommande('123')
        ;

        $em->persist($commande);
        $em->flush();

        $_GET['id'] = $commande->getId();

        $reponse = new PayboxResponseEvent([
            'Erreur' => 0,
            'Ref' => $commande->getNumeroCommande(),
            'Mt' => 0,
        ], true);

        $this->payboxListener->onPayboxIpnResponse($reponse);

        $this->assertEquals('panier', $commande->getStatut());
        $this->assertNull($commande->getTypePaiement());
        $this->assertNull($commande->getMoyenPaiement());

        $em->remove($commande);
        $em->flush();
    }

    /**
     * @covers \App\Service\Listener\PayboxResponseListener::onPayboxIpnResponse
     */
    public function testOnPayboxIpnResponseCommandeNotFound(): void
    {
        $_SERVER['REQUEST_URI'] = 'test';

        $_GET['id'] = '1';

        $reponse = new PayboxResponseEvent([
            'Erreur' => 0,
            'Ref' => '123',
            'Mt' => 0,
        ], true);

        $this->payboxListener->onPayboxIpnResponse($reponse);

        $this->assertTrue(true);
    }
}