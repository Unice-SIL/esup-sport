<?php

/*
 * Classe - AnnotationRepository
 *
 * Contient les requêtes à la base de données pour l'entité annotation
*/

namespace App\Repository;

use App\Entity\Uca\Annotation;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class AnnotationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Annotation::class);
    }

    public function test()
    {
        $qb = $this->createQueryBuilder('a');
        $qb->andWhere('a.annotation = :annotation');
        $qb->setParameter('annotation', 'Gedmo\Mapping\Annotation\Translatable');

        $res = $qb->getQuery()->getResult();

        $casewhenId = 'CASE ';
        $casewhenVal = 'CASE ';
        foreach ($res as $k => $v) {
            $qb->leftJoin($v->getEntity(), 't'.$k, Join::WITH, "a.entity = '".$v->getEntity()."'");
            $casewhenId .= 'WHEN t'.$k.'.id IS NOT NULL THEN t'.$k.'.id ';
            $casewhenVal .= 'WHEN t'.$k.'.id IS NOT NULL THEN t'.$k.'.'.$v->getField().' ';
        }
        $casewhenId .= 'ELSE -1 END';
        $casewhenVal .= 'ELSE \'\' END';

        $qb->leftJoin('Gedmo\Translatable\Entity\Translation', 'tt', Join::WITH, 'a.entity = tt.objectClass and a.field = tt.field and tt.foreignKey = '.$casewhenId);

        $qb->select('a.entity, a.field, a.annotation');
        $qb->addSelect($casewhenId.' AS id');
        $qb->addSelect($casewhenVal.' AS valfr');
        $qb->addSelect('tt.content AS valen');

        return $qb->getQuery()->getResult();
    }
}
