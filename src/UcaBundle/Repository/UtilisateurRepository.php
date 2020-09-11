<?php

/*
 * Classe - UtilisateurRepository
 *
 * Requêtes à la base de données pour l'entité utilisateur
*/

namespace UcaBundle\Repository;

class UtilisateurRepository extends \Doctrine\ORM\EntityRepository
{
    public function findtByGroupsName($name)
    {
        return $this->createQueryBuilder('d')
            ->join('d.formatsActivite', 'f')
            ->join('d.groups', 'g')
            ->where('g.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByRole($role)
    {
        $qb = ($this->createQueryBuilder('u'))
            ->select('u.email,u.nom,u.prenom')
            ->join('u.groups', 'g')
            ->where('g.roles LIKE :roles')
            ->setParameter('roles', '%"'.$role.'"%')
    ;

        return $qb->getQuery()->getResult();
    }

    public function findUtilisateurByEvenement($idDhtmlxEvenement)
    {
        $qb = $this->createQueryBuilder('u')
            ->leftJoin('u.appels', 'a')
            ->leftJoin('a.dhtmlxEvenement', 'd')
            ->andWhere('d.id = :idDhtmlxEvenement')
            ->setParameter('idDhtmlxEvenement', $idDhtmlxEvenement)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findEncadrantByActivite($id)
    {
        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.formatsActivite', 'f')
            ->leftJoin('f.activite', 'a')
            ->where('a.id = :id')
            ->setParameter('id', $id)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findByPrenomNom($prenom, $nom)
    {
        $qb = $this->createQueryBuilder('u')
            ->where('u.prenom LIKE :prenom')
            ->setParameter('prenom', $prenom)
            ->andWhere('u.nom LIKE :nom')
            ->setParameter('nom', $nom)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findEncadrantByClasseActivite($id)
    {
        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.formatsActivite', 'f')
            ->leftJoin('f.activite', 'a')
            ->leftJoin('a.classeActivite', 'c')
            ->where('c.id = :id')
            ->setParameter('id', $id)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findEncadrantByTypeActivite($id)
    {
        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.formatsActivite', 'f')
            ->leftJoin('f.activite', 'a')
            ->leftJoin('a.classeActivite', 'c')
            ->leftJoin('c.typeActivite', 't')
            ->where('t.id = :id')
            ->setParameter('id', $id)
        ;

        return $qb->getQuery()->getResult();
    }

    public function getNbreUserInscript($listeUsername)
    {
        $qb = $this->createQueryBuilder('u')
            ->select('GROUP_CONCAT(u.username), COUNT(DISTINCT u.username)')
            ->andWhere('u.username IN (:listeUsername)')
            ->setParameter('listeUsername', $listeUsername)
        ;

        return $qb->getQuery()->getSingleResult();
    }

    public function getNbreUserInscriptActivite($listeUsername, $statut)
    {
        $qb = $this->createQueryBuilder('u')
            ->select('COUNT(DISTINCT u.username)')
            ->andWhere('u.username IN (:listeUsername)')
            ->setParameter('listeUsername', $listeUsername)
            ->innerJoin('UcaBundle\Entity\Inscription', 'i', 'WITH', 'i.utilisateur = u')
            ->andWhere('i.statut = :statut')
            ->setParameter('statut', $statut)
        ;

        return $qb->getQuery()->getSingleResult()[1];
    }
}
