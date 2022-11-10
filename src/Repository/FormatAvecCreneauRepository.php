<?php

/*
 * Classe - FormatAvecCreneauRepository
 *
 * Requêtes à la base de données pour l'entité format avec creneau
*/

namespace App\Repository;

use App\Entity\Uca\FormatAvecCreneau;
use Doctrine\Persistence\ManagerRegistry;

class FormatAvecCreneauRepository extends FormatActiviteRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormatAvecCreneau::class);
    }
}
