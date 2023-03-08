<?php

namespace App\Tests\Service\Securite;

use Twig\Environment;
use App\Entity\Uca\Commande;
use App\Entity\Uca\Utilisateur;
use App\Service\Securite\Paybox;
use App\Service\Common\Parametrage;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ParametrageRepository;
use App\Repository\UtilisateurRepository;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\PayboxBundle\Paybox\System\Base\Request;

/**
 * @internal
 * @coversNothing
 * Aide : https://github.com/lexik/LexikPayboxBundle/blob/master/Tests/Paybox/System/Base/RequestTest.php
 */
class PayboxTest extends WebTestCase
{
    /**
     * @var Request
     */
    private $_paybox;

    /**
     * @var Commande
     */
    private $commande;

    /**
     * @var Paybox
     */
    private $customPaybox;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    private $user;

    /**
     * Fonction qui s'exécute avant chaque test.
     */
    protected function setUp(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $container = static::getContainer();
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $this->em = $container->get(EntityManagerInterface::class);
        $this->user = (new Utilisateur())
            ->setNom('box')
            ->setPrenom('pay')
            ->setUsername('paybox-test')
            ->setSexe('M')
            ->setEmail('paybox@test.fr')
            ->setEnabled(true)
        ;
        $this->user->setPassword($hasher->hashPassword($this->user, $_ENV['ADMIN_PWD']))
        ;
        $this->em->persist($this->user);
        $this->em->flush();

        // Création de la commande
        $this->commande = (new Commande($this->user))
            ->setMontantPaybox('100')
            ->setMontantTotal('100')
            ->setNumeroCommande('123');
        $this->em->persist($this->commande);
        $this->em->flush();

        $formFactory = $this->getMockBuilder('Symfony\Component\Form\FormFactoryInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_paybox = new Request(array(
            'production' => false,
            'currencies' => array(
                '978', // EUR
            ),
            'site'       => 1999888,
            'rank'       => 32,
            'login'      => 2,
            'hmac'       => array(
                'algorithm'      => 'sha512',
                'key'            => '0123456789ABCDEF',
                'signature_name' => 'Sign',
            ),
        ), array(
            'system' => array(
                'primary' => array(
                    'protocol'    => 'https',
                    'host'        => 'tpeweb.paybox.com',
                    'system_path' => '/cgi/MYchoix_pagepaiement.cgi',
                    'test_path'   => '/load.html',
                ),
                'secondary' => array(
                    'protocol'    => 'https',
                    'host'        => 'tpeweb1.paybox.com',
                    'system_path' => '/cgi/MYchoix_pagepaiement.cgi',
                    'test_path'   => '/load.html',
                ),
                'preprod' => array(
                    'protocol'    => 'https',
                    'host'        => 'preprod-tpeweb.paybox.com',
                    'system_path' => '/cgi/MYchoix_pagepaiement.cgi',
                    'test_path'   => '/load.html',
                ),
            )
        ), $formFactory);

        $this->customPaybox = new Paybox(
            $this->_paybox,
            $container->get(RouterInterface::class),
            $container->get(Environment::class),
        );

        $event = new RequestEvent(
            static::getContainer()->get(HttpKernelInterface::class),
            $client->getRequest(),
            null
        );

        $param = new Parametrage($container->get(ParametrageRepository::class));
        $param->onKernelRequest($event);
    }

    /**
     * @covers \App\Service\Securite\Paybox::__construct
     */
    public function testConstructor(): void
    {
        $this->assertInstanceOf(Paybox::class, $this->customPaybox);
    }

    /**
     * @covers \App\Service\Securite\Paybox::setCommande
     */
    public function testSetParameters(): void
    {
        $this->customPaybox->setCommande($this->commande);
        $payboxParams = $this->customPaybox->getParameters();

        $this->assertTrue(isset($payboxParams['PBX_CMD']));
        $this->assertStringContainsString($this->commande->getNumeroCommande(), $payboxParams['PBX_CMD']);

        $this->assertTrue(isset($payboxParams['PBX_DEVISE']));
        $this->assertEquals('978', $payboxParams['PBX_DEVISE']);

        $this->assertTrue(isset($payboxParams['PBX_PORTEUR']));
        $this->assertEquals($this->commande->getUtilisateur()->getEmail(), $payboxParams['PBX_PORTEUR']);

        $this->assertTrue(isset($payboxParams['PBX_RETOUR']));
        $this->assertEquals('Mt:M;Ref:R;Auto:A;Erreur:E;Sign:K', $payboxParams['PBX_RETOUR']);

        $this->assertTrue(isset($payboxParams['PBX_TOTAL']));
        $this->assertEquals(round($this->commande->getMontantAPayer() * 100), $payboxParams['PBX_TOTAL']);
        $this->assertIsFloat($payboxParams['PBX_TOTAL']);

        $this->assertTrue(isset($payboxParams['PBX_TYPEPAIEMENT']));
        $this->assertEquals('CARTE', $payboxParams['PBX_TYPEPAIEMENT']);

        $this->assertTrue(isset($payboxParams['PBX_TYPECARTE']));
        $this->assertEquals('CB', $payboxParams['PBX_TYPECARTE']);

        $this->assertTrue(isset($payboxParams['PBX_DISPLAY']));
        $this->assertIsInt($payboxParams['PBX_DISPLAY']);

        $this->assertTrue(isset($payboxParams['PBX_EFFECTUE']));
        $this->assertStringContainsString('UcaWeb/Paiement/Retour/', $payboxParams['PBX_EFFECTUE']);
        $this->assertStringContainsString($this->commande->getId(), $payboxParams['PBX_EFFECTUE']);
        $this->assertStringContainsString('success', $payboxParams['PBX_EFFECTUE']);

        $this->assertTrue(isset($payboxParams['PBX_REFUSE']));
        $this->assertStringContainsString('UcaWeb/Paiement/Retour/', $payboxParams['PBX_REFUSE']);
        $this->assertStringContainsString($this->commande->getId(), $payboxParams['PBX_REFUSE']);
        $this->assertStringContainsString('denied', $payboxParams['PBX_REFUSE']);

        $this->assertTrue(isset($payboxParams['PBX_ANNULE']));
        $this->assertStringContainsString('UcaWeb/Paiement/Retour/', $payboxParams['PBX_ANNULE']);
        $this->assertStringContainsString($this->commande->getId(), $payboxParams['PBX_ANNULE']);
        $this->assertStringContainsString('canceled', $payboxParams['PBX_ANNULE']);

        $this->assertTrue(isset($payboxParams['PBX_RUF1']));
        $this->assertEquals('POST', $payboxParams['PBX_RUF1']);

        $this->assertTrue(isset($payboxParams['PBX_REPONDRE_A']));
        $this->assertStringContainsString($this->commande->getId(), $payboxParams['PBX_REPONDRE_A']);

        $this->assertEquals(19, count($payboxParams));
    }
}
