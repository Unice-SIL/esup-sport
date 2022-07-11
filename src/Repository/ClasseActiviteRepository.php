<?php

/*
 * Classe - ClasseActiviteRepository
 *
 * Contient les requêtes à la base de données pour l'entité classe d'activité
*/

namespace App\Repository;

use App\Entity\Uca\ClasseActivite;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;

class ClasseActiviteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClasseActivite::class);
    }

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
            ->innerJoin('App\Entity\Uca\Activite', 'act', 'WITH', 'act.classeActivite = c.id')
            ->innerJoin('App\Entity\Uca\FormatActivite', 'formAct', 'WITH', 'formAct.activite = act.id')
            ->innerJoin('App\Entity\Uca\Creneau', 'cre', 'WITH', 'cre.formatActivite = formAct.id')
            ->where('cre.id = :id')
            ->setParameter('id', $id)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findClasseActiviteByFormatActivite($id)
    {
        $qb = $this->createQueryBuilder('c')
            ->innerJoin('App\Entity\Uca\Activite', 'act', 'WITH', 'act.classeActivite = c.id')
            ->innerJoin('App\Entity\Uca\FormatActivite', 'formAct', 'WITH', 'formAct.activite = act.id')
            ->where('formAct.id = :id')
            ->setParameter('id', $id)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findClasseActivitePubliee()
    {
        return $this->createQueryBuilder('ca')
            ->leftJoin('ca.activites', 'a')
            ->leftJoin('a.formatsActivite', 'fa')
            ->andWhere('fa.dateFinEffective < :now')
            ->setParameter('now', new DateTime())
            ->orderBy('ca.libelle', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}