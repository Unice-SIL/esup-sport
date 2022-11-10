<?php

/*
 * classe - PayBox
 *
 * Service mettant en place la solution de paiement paybox
*/

namespace App\Service\Securite;

use App\Entity\Uca\Commande;
use App\Service\Common\Parametrage;
use Lexik\Bundle\PayboxBundle\Paybox\System\Base\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class PayBox
{
    private $router;
    private $paybox;
    private $twig;

    public function __construct(Request $paybox, RouterInterface $router, Environment $twig)
    {
        $this->paybox = $paybox;
        $this->router = $router;
        $this->twig = $twig;
    }

    public function setCommande(Commande $commande)
    {
        $this->paybox->setParameters([
            'PBX_CMD' => $commande->getNumeroCommande().'-'.(new \DateTime())->format('YmdHis'),
            // 'PBX_CMD'          => $commande->getNumeroCommande(),
            'PBX_DEVISE' => '978',
            'PBX_PORTEUR' => $commande->getUtilisateur()->getEmail(),
            'PBX_RETOUR' => 'Mt:M;Ref:R;Auto:A;Erreur:E',
            'PBX_TOTAL' => round($commande->getMontantAPayer() * 100),
            'PBX_TYPEPAIEMENT' => 'CARTE',
            'PBX_TYPECARTE' => 'CB',
            'PBX_DISPLAY' => Parametrage::get()->getTimerPaybox() * 60,
            'PBX_EFFECTUE' => $this->router->generate('UcaWeb_PaiementRetourPaybox', ['id' => $commande->getId(), 'status' => 'success'], UrlGeneratorInterface::ABSOLUTE_URL),
            'PBX_REFUSE' => $this->router->generate('UcaWeb_PaiementRetourPaybox', ['id' => $commande->getId(), 'status' => 'denied'], UrlGeneratorInterface::ABSOLUTE_URL),
            'PBX_ANNULE' => $this->router->generate('UcaWeb_PaiementRetourPaybox', ['id' => $commande->getId(), 'status' => 'canceled'], UrlGeneratorInterface::ABSOLUTE_URL),
            'PBX_RUF1' => 'POST',
            'PBX_REPONDRE_A' => $this->router->generate('lexik_paybox_ipn', ['time' => time(), 'id' => $commande->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        // $commande->setHmac($this->paybox->getParameters()['PBX_HMAC']);
    }

    /**
     * @codeCoverageIgnore
     */
    public function getParameters()
    {
        return $this->paybox->getParameters();
    }

    /**
     * @codeCoverageIgnore
     */
    public function getUrl()
    {
        return $this->paybox->getUrl();
    }

    /**
     * @codeCoverageIgnore
     */
    public function getForm()
    {
        return $this->paybox->getForm()->createView();
    }
}
