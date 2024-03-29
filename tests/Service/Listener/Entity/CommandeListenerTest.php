<?php

namespace App\Tests\Service\Listener\Entity;

use App\Entity\Uca\Commande;
use App\Entity\Uca\Utilisateur;
use App\Service\Common\MailService;
use App\Service\Listener\Entity\CommandeListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;

/**
 * @internal
 * @coversNothing
 */
class CommandeListenerTest extends WebTestCase
{
    /**
     * @var CommandeListener
     */
    private $commandeListener;

    protected function setUp(): void
    {
        $this->utilisateur = (new Utilisateur())
            ->setUserName('Inscription')
            ->setEmail('test@test.com')
            ->setPassword('test')
        ;

        $this->commande = new Commande($this->utilisateur);

        $mailerService = static::getContainer()->get(MailService::class);
        $request = static::getContainer()->get(RequestStack::class);
        $router = static::getContainer()->get(RouterInterface::class);
        $this->commandeListener = new CommandeListener($mailerService, $request, $router);
    }

    /**
     * @covers \App\Service\Listener\Entity\CommandeListener::preUpdate
     */
    public function testPreUpdate(): void
    {
        $container = static::getContainer();

        $maxNumCommande = $container->get(EntityManagerInterface::class)->getRepository(Commande::class)->max('numeroCommande');
        $maxNumRecu = $container->get(EntityManagerInterface::class)->getRepository(Commande::class)->max('numeroRecu');

        $a = ['statut' => ['panier', 'apayer'], 'typePaiement' => ['', 'BDS']];

        $event = new PreUpdateEventArgs($this->commande, $container->get(EntityManagerInterface::class), $a);
        $this->assertEquals($this->commande->getNumeroCommande(), null);
        $this->commandeListener->preUpdate($this->commande, $event);
        $this->assertEquals($this->commande->getNumeroCommande(), $maxNumCommande + 1);

        $a = ['statut' => ['annule', 'termine']];
        $event = new PreUpdateEventArgs($this->commande, $container->get(EntityManagerInterface::class), $a);
        $this->assertEquals($this->commande->getNumeroRecu(), null);
        $this->commandeListener->preUpdate($this->commande, $event);
        $this->assertEquals($this->commande->getNumeroRecu(), $maxNumRecu + 1);

        $this->commande = new Commande($this->utilisateur);
        $a = ['statut' => ['panier', 'termine'], 'montantTotal' => [1, 1]];
        $event = new PreUpdateEventArgs($this->commande, $container->get(EntityManagerInterface::class), $a);
        $this->assertEquals($this->commande->getNumeroRecu(), null);
        $this->commandeListener->preUpdate($this->commande, $event);
        $this->assertEquals($this->commande->getNumeroRecu(), $maxNumRecu + 1);

        $this->commande = new Commande($this->utilisateur);
        $a = ['statut' => ['panier', 'termine'], 'montantTotal' => [1, 0]];
        $event = new PreUpdateEventArgs($this->commande, $container->get(EntityManagerInterface::class), $a);
        $this->assertEquals($this->commande->getNumeroCommande(), null);
        $this->commandeListener->preUpdate($this->commande, $event);
        $this->assertEquals($this->commande->getNumeroCommande(), $maxNumCommande + 1);

        $a = ['statut' => ['panier', 'termine'], 'montantTotal' => [1, 0]];
        $event = new PreUpdateEventArgs($this->commande, $container->get(EntityManagerInterface::class), $a);
        $this->commandeListener->preUpdate($this->commande, $event);

        $a = ['statut' => ['termine', 'annule'], 'montantTotal' => [1, 0]];
        $event = new PreUpdateEventArgs($this->commande, $container->get(EntityManagerInterface::class), $a);
        $this->commandeListener->preUpdate($this->commande, $event);
    }
}
