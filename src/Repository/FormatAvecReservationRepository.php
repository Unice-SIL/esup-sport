<?php

/*
 * Classe - FormatAvecReservationRepository
 *
 * Requêtes à la base de données pour l'entité format avec creneau
*/

namespace App\Repository;

use App\Entity\Uca\FormatAvecReservation;
use Doctrine\Persistence\ManagerRegistry;

class FormatAvecReservationRepository extends FormatActiviteRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormatAvecReservation::class);
    }
}
