<?php

/*
 * Classe - TypeActiviteRepository
 *
 * Requêtes à la base de données pour l'entité type d'activité
*/

namespace UcaBundle\Repository;

class TypeActiviteRepository extends \Doctrine\ORM\EntityRepository
{
    public function findTypeActiviteByCreneau($id)
    {
        $qb = $this->createQueryBuilder('t')
            ->innerJoin('UcaBundle\Entity\ClasseActivite', 'classeAct', 'WITH', 'classeAct.typeActivite = t.id')
            ->innerJoin('UcaBundle\Entity\Activite', 'act', 'WITH', 'act.classeActivite = classeAct.id')
            ->innerJoin('UcaBundle\Entity\FormatActivite', 'formAct', 'WITH', 'formAct.activite = act.id')
            ->innerJoin('UcaBundle\Entity\Creneau', 'cre', 'WITH', 'cre.formatActivite = formAct.id')
            ->where('cre.id = :id')
            ->setParameter('id', $id)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findTypeActiviteByFormatActivite($id)
    {
        $qb = $this->createQueryBuilder('t')
            ->innerJoin('UcaBundle\Entity\ClasseActivite', 'classeAct', 'WITH', 'classeAct.typeActivite = t.id')
            ->innerJoin('UcaBundle\Entity\Activite', 'act', 'WITH', 'act.classeActivite = classeAct.id')
            ->innerJoin('UcaBundle\Entity\FormatActivite', 'formAct', 'WITH', 'formAct.activite = act.id')
            ->where('formAct.id = :id')
            ->setParameter('id', $id)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findTypeActiviteByActivite($id)
    {
        $qb = $this->createQueryBuilder('t')
            ->innerJoin('UcaBundle\Entity\ClasseActivite', 'classeAct', 'WITH', 'classeAct.typeActivite = t.id')
            ->innerJoin('UcaBundle\Entity\Activite', 'act', 'WITH', 'act.classeActivite = classeAct.id')
            ->where('act.id = :id')
            ->setParameter('id', $id)
        ;

        return $qb->getQuery()->getResult();
    }
}
