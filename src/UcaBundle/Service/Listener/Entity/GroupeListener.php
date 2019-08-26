<?php

namespace UcaBundle\Service\Listener\Entity;

use UcaBundle\Entity\Groupe;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;

class GroupeListener
{
    private $translator;

    public function __construct($translator) {
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
        $listeRoles = "";
        foreach ($groupe->getRoles() as $role){
            if(!empty($listeRoles)){
                $listeRoles .= ", ";
            }
            $listeRoles .= $this->translator->trans('security.roles.' . $role, [], 'messages', 'fr');
        }
        $groupe->setListeRoles($listeRoles);
    }
}
