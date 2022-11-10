<?php

/*
 * Classe - TypeActiviteRepository
 *
 * Requêtes à la base de données pour l'entité type d'activité
*/

namespace App\Repository;

use App\Entity\Uca\TypeActivite;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class TypeActiviteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeActivite::class);
    }
    
    public function findTypeActiviteByCreneau($id)
    {
        $qb = $this->createQueryBuilder('t')
            ->innerJoin('App\Entity\Uca\ClasseActivite', 'classeAct', 'WITH', 'classeAct.typeActivite = t.id')
            ->innerJoin('App\Entity\Uca\Activite', 'act', 'WITH', 'act.classeActivite = classeAct.id')
            ->innerJoin('App\Entity\Uca\FormatActivite', 'formAct', 'WITH', 'formAct.activite = act.id')
            ->innerJoin('App\Entity\Uca\Creneau', 'cre', 'WITH', 'cre.formatActivite = formAct.id')
            ->where('cre.id = :id')
            ->setParameter('id', $id)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findTypeActiviteByFormatActivite($id)
    {
        $qb = $this->createQueryBuilder('t')
            ->innerJoin('App\Entity\Uca\ClasseActivite', 'classeAct', 'WITH', 'classeAct.typeActivite = t.id')
            ->innerJoin('App\Entity\Uca\Activite', 'act', 'WITH', 'act.classeActivite = classeAct.id')
            ->innerJoin('App\Entity\Uca\FormatActivite', 'formAct', 'WITH', 'formAct.activite = act.id')
            ->where('formAct.id = :id')
            ->setParameter('id', $id)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findTypeActiviteByActivite($id)
    {
        $qb = $this->createQueryBuilder('t')
            ->innerJoin('App\Entity\Uca\ClasseActivite', 'classeAct', 'WITH', 'classeAct.typeActivite = t.id')
            ->innerJoin('App\Entity\Uca\Activite', 'act', 'WITH', 'act.classeActivite = classeAct.id')
            ->where('act.id = :id')
            ->setParameter('id', $id)
        ;

        return $qb->getQuery()->getResult();
    }
}
