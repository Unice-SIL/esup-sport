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
            ->getResult();
    }


    public function listAll() { 
        $em = $this->getEntityManager() ;
        $qb = $this
            ->createQueryBuilder('c')
            ->Join('c.activites','a')
            ->LeftJoin('a.formatsActivite','f')
            ->Select('c ,a');

      /*  if (isset($tab['formatActivite'])) {
            $qb->andWhere('f INsTANCE OF :format')
            ->setParameter('format',
            $em->getClassMetadata
            ('UcaBundle:'.$tab['formatActivite']
        ));}*/

        return $qb
            ->getQuery()
            ->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker')
            ->getResult();
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
}
