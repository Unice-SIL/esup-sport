<?php

/*
 * classe - CommandeListener
 *
 * Service intervant lors des modification en base de données de l'entité commande
*/

namespace App\Service\Listener\Entity;

use App\Entity\Uca\Commande;
use App\Service\Common\MailService;
use App\Service\Common\Parametrage;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class CommandeListener
{
    private $mailer;
    private $request;
    private $router;

    /**
     * @codeCoverageIgnore
     */
    public function __construct(MailService $mailer, RequestStack $request, RouterInterface $router)
    {
        $this->mailer = $mailer;
        $this->request = $request;
        $this->router = $router;
    }

    public function preUpdate(Commande $commande, PreUpdateEventArgs $event)
    {
        $em = $event->getEntityManager();
        if ($event->hasChangedField('statut')) {
            if ('apayer' != $event->getOldValue('statut') && 'apayer' == $event->getNewValue('statut')) {
                if (null === $commande->getNumeroCommande()) {
                    $numero = $em->getRepository(Commande::class)->max('numeroCommande') + 1;
                    $commande->setNumeroCommande($numero);
                }

                if ($event->hasChangedField('typePaiement') && 'BDS' != $event->getOldValue('typePaiement') && 'BDS' == $event->getNewValue('typePaiement') && $commande->getUtilisateur()->getEmail()) {
                    if ('test' !== $_ENV['APP_ENV']) {
                        //@codeCoverageIgnoreStart
                        $this->mailer->sendMailWithTemplate(
                            null,
                            $commande->getUtilisateur()->getEmail(),
                            'CommandeARegler',
                            ['numeroCommande' => $commande->getNumeroCommande(), 'timerBds' => Parametrage::getTimerBds()]
                        );
                        //@codeCoverageIgnoreEnd
                    }
                }
            }
            if ('annule' == $event->getOldValue('statut') && 'termine' == $event->getNewValue('statut')) {
                $numero = $em->getRepository(Commande::class)->max('numeroRecu') + 1;
                $commande->setNumeroRecu($numero);
            } elseif ('termine' != $event->getOldValue('statut') && 'termine' == $event->getNewValue('statut')) {
                // condition supplémentaire pour les avoirs ?
                if ($event->hasChangedField('montantTotal') && 0 != $event->getNewValue('montantTotal')) {
                    $numero = $em->getRepository(Commande::class)->max('numeroRecu') + 1;
                    $commande->setNumeroRecu($numero);
                } elseif ('avoir' != $event->getNewValue('statut') && null === $commande->getNumeroCommande()) {
                    $numero = $em->getRepository(Commande::class)->max('numeroCommande') + 1;
                    $commande->setNumeroCommande($numero);
                }

                $lienFacture = $this->router->generate('UcaWeb_MesCommandesExport', ['id' => $commande->getId()]);
                if ($commande->getUtilisateur()->getEmail()) {
                    if ('test' !== $_ENV['APP_ENV']) {
                        //@codeCoverageIgnoreStart
                        $this->mailer->sendMailWithTemplate(
                            null,
                            $commande->getUtilisateur()->getEmail(),
                            'ValidationCommande',
                            ['numeroCommande' => $commande->getNumeroCommande(), 'lienFacture' => $this->request->getMainRequest()->getScheme().'://'.$this->request->getMainRequest()->getHttpHost().$lienFacture]
                        );
                        //@codeCoverageIgnoreEnd
                    }
                }
            }
            if ('annule' != $event->getOldValue('statut') && 'annule' == $event->getNewValue('statut') && $commande->getUtilisateur()->getEmail()) {
                if ('test' !== $_ENV['APP_ENV']) {
                    //@codeCoverageIgnoreStart
                    $this->mailer->sendMailWithTemplate(
                        null,
                        $commande->getUtilisateur()->getEmail(),
                        'AnulationCommande',
                        ['numeroCommande' => $commande->getNumeroCommande()]
                    );
                    //@codeCoverageIgnoreEnd
                }
            }
        }
    }
}
