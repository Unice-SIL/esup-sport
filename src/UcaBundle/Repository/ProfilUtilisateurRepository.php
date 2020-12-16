<?php

/*
 * Classe - ProfilUtilisateur repository
 *
 * Contient les requêtes à la base de données pour l'entité profil utilisateur
*/

namespace UcaBundle\Repository;

class ProfilUtilisateurRepository extends \Doctrine\ORM\EntityRepository
{
    public function findMaxInscription($id)
    {
        return $this->createQueryBuilder('profil')
            ->select('profil.nbMaxInscriptions')
            ->where('profil.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
