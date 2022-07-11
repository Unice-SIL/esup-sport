<?php

/*
 * classe - GroupeListener
 *
 * Service intervant lors des modification en base de données de l'entité Groupe
*/

namespace App\Service\Listener\Entity;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use App\Entity\Uca\Groupe;
use Symfony\Contracts\Translation\TranslatorInterface;

class GroupeListener
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function postPersist(Groupe $groupe, LifecycleEventArgs $event)
    {
        $groupe->setName($groupe->getId());

        $em = $event->getEntityManager();
        $uow = $em->getUnitOfWork();
        $class = $em->getClassMetadata(Groupe::class);
        $uow->computeChangeSet($class, $groupe);
    }

    public function preFlush(Groupe $groupe, PreFlushEventArgs $event)
    {
        $listeRoles = '';
        foreach ($groupe->getRoles() as $role) {
            if (!empty($listeRoles)) {
                $listeRoles .= ', ';
            }
            $listeRoles .= $this->translator->trans('security.roles.'.$role, [], 'messages', 'fr');
        }
        $groupe->setListeRoles($listeRoles);
    }
}
