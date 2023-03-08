<?php

/*
 * Classe - EmailRepository
 *
 * Requêtes à la base de données pour l'entité Email
*/

namespace App\Repository;

use App\Entity\Uca\Email;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class EmailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Email::class);
    }
}
