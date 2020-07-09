<?php

namespace UcaBundle\Repository;

use Doctrine\Common\Collections\Criteria;

class ClasseActiviteRepository extends \Doctrine\ORM\EntityRepository
{
    public function findAll()
    {
        return $this
            ->createQueryBuilder('c')
            ->getQuery()
            ->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker')
            ->getResult()
        ;
    }

    public function listAll()
    {
        $em = $this->getEntityManager();
        $qb = $this
            ->createQueryBuilder('c')
            ->Join('c.activites', 'a')
            ->LeftJoin('a.formatsActivite', 'f')
            ->Select('c ,a')
        ;

        /*  if (isset($tab['formatActivite'])) {
              $qb->andWhere('f INsTANCE OF :format')
              ->setParameter('format',
              $em->getClassMetadata
              ('UcaBundle:'.$tab['formatActivite']
          ));}*/

        return $qb
            ->getQuery()
            ->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker')
            ->getResult()
        ;
    }

    /*
    static public function formatActiviteCriteria($criteria,$em=$this->getEntityManager()) :Criteria
    {

        return Criteria::create()
            ->andWhere(Criteria::expr()
                ->gt('f INSTANCE OF',$em->getClassMetadata('UcaBundle:FormatAvecCreneau'))
            )
        );
    }
    */

    public function findClasseActiviteByCreneau($id)
    {
        $qb = $this->createQueryBuilder('c')
            ->innerJoin('UcaBundle\Entity\Activite', 'act', 'WITH', 'act.classeActivite = c.id')
            ->innerJoin('UcaBundle\Entity\FormatActivite', 'formAct', 'WITH', 'formAct.activite = act.id')
            ->innerJoin('UcaBundle\Entity\Creneau', 'cre', 'WITH', 'cre.formatActivite = formAct.id')
            ->where('cre.id = :id')
            ->setParameter('id', $id)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findClasseActiviteByFormatActivite($id)
    {
        $qb = $this->createQueryBuilder('c')
            ->innerJoin('UcaBundle\Entity\Activite', 'act', 'WITH', 'act.classeActivite = c.id')
            ->innerJoin('UcaBundle\Entity\FormatActivite', 'formAct', 'WITH', 'formAct.activite = act.id')
            ->where('formAct.id = :id')
            ->setParameter('id', $id)
        ;

        return $qb->getQuery()->getResult();
    }
}
