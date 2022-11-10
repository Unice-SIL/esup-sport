<?php

/*
 * Classe - CommandeDetailRepository
 *
 * Contient les requêtes à la base de données pour l'entité commande détails
*/

namespace App\Repository;

use App\Entity\Uca\CommandeDetail;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;

class CommandeDetailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommandeDetail::class);
    }

    public static function criteriaByAutorisation($autorisation)
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->eq('typeAutorisation', $autorisation))
        ;
    }

    public static function criteriaEstPayant()
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->gt('cmdDetail.montant', 0))
        ;
    }

    /**
     * Fonction qui permet de récupérer des détails de commande en fonction de dates et de montant.
     *
     * @param [type] $dateDebut
     * @param [type] $dateFin
     * @param [type] $montantPaye
     */
    public function findCommandeDetails($dateDebut, $dateFin, $montantPaye)
    {
        $qb = $this->createQueryBuilder('cd');
        $qb->leftJoin('App\Entity\Uca\Commande', 'c', 'WITH', 'c.id = cd.commande');
        $qb->where('c.datePaiement IS NOT NULL');

        if (null != $dateDebut and null != $dateFin) {
            $qb->add(
                'where',
                $qb->expr()->between(
                    'c.datePaiement',
                    ':from',
                    ':to'
                )
            )
                ->setParameters(['from' => $dateDebut, 'to' => $dateFin])
                    ;
        } elseif (null != $dateDebut and null == $dateFin) {
            $qb->where('c.datePaiement >= :dateDebut')
                ->setParameter('dateDebut', $dateDebut)
            ;
        } elseif (null == $dateDebut and null != $dateFin) {
            $qb->where('c.datePaiement <= :dateFin')
                ->setParameter('dateFin', $dateFin)
            ;
        }

        if ($montantPaye) {
            $qb->andWhere('cd.montant > 0');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Fonction qui permet de récupérer des détails de commandes pour des anciennes commandes
     * Cette fonction est utilisé dans un but de reprise de données.
     */
    public function findCommandeDetailPourAncienneCommandeGratuite()
    {
        $qb = $this->createQueryBuilder('cd');
        $qb->leftJoin('App\Entity\Uca\Commande', 'c', 'WITH', 'c.id = cd.commande');
        $qb->where("c.statut = 'termine'");
        $qb->andWhere('cd.libelle IS NULL OR cd.description IS NULL OR cd.typeArticle IS NULL');

        return $qb->getQuery()->getResult();
    }

    /**
     * Fonction qui retourne la valeur maximum pour un champ donné.
     *
     * @param [type] $field
     */
    public function max($field)
    {
        $qb = $this->createQueryBuilder('c');
        $qb->select('MAX(c.'.$field.')');
        $res = $qb->getQuery()->getSingleScalarResult();

        return empty($res) ? 0 : $res;
    }

    public function findExtractedCommandeDetails($dateDebut, $dateFin, $nom, $prenom, $statut, $moyen, $montant, $numComamnde, $numRecu, $recherche, $estPayant = false)
    {
        $qb = $this->createQueryBuilder('cmdDetail');
        $qb->leftJoin('App\Entity\Uca\Commande', 'commande', 'WITH', 'commande.id = cmdDetail.commande');
        $repo = CommandeRepository::class;

        if (null !== $montant && null === $recherche) {
            $qb->addCriteria($repo::criteriaByMontant($montant));
        }

        if ($estPayant) {
            $qb->addCriteria($repo::criteriabyStatut(['termine', 'avoir'], 'commande.'));
            $qb->addCriteria(self::criteriaEstPayant());
        }
        if (null !== $dateDebut || null !== $dateFin) {
            $qb->addCriteria($repo::criteriaBetweenDates($dateDebut, $dateFin));
        }
        if (null !== $moyen) {
            $qb->addCriteria($repo::criteriaByMoyen($moyen));
        }
        if (null !== $numComamnde) {
            $qb->addCriteria($repo::criteriaByNumCommmande($numComamnde));
        }
        if (null !== $numRecu) {
            $qb->addCriteria($repo::criteriaByNumRecu($numRecu));
        }
        if (null !== $nom || null !== $prenom) {
            $qb->addCriteria($repo::criteriaByUtilisateur($nom, $prenom));
        }
        if (null !== $recherche) {
            $qb->addCriteria($repo::criteriaByRecherche($recherche));
        }

        /*dump($qb->getParameters());
        dump($qb->getQuery());
        dump($qb->getQuery()->getResult());
        die;*/

        return $qb->getQuery()->getResult();
    }

    /**
     * Fonction qui permet de récupérer les détails de commandes qui concerne les cartes passées
     * La fonction est utilisé dans la commande Symfony DeleteOldCardCommand.
     */
    public function findCommandeDetailAncienneCarte()
    {
        $qb = $this->createQueryBuilder('cd')
            ->leftJoin('cd.typeAutorisation', 'ta')
            ->leftJoin('cd.commande', 'c')
            ->leftJoin('c.utilisateur', 'u')
            ->andWhere('ta.comportement = 4')
            ->andWhere('cd.dateCarteFinValidite IS NULL OR cd.dateCarteFinValidite < :now')
            ->setParameter('now', new DateTime())
            ->andWhere("c.statut = 'termine'")
            ->andWhere('ta MEMBER OF u.autorisations')
            ->groupBy('c.utilisateur, cd.typeAutorisation')
            ->orderBy('cd.dateAjoutPanier', 'DESC')
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * Fonction qui permet de récupérer les détails d'une commande à partir d'un utilisateur et d'une autorisation.
     *
     * @param [type] $utilisateur
     * @param [type] $autorisation
     */
    public function findCommandeDetailWithAutorisationByUser($utilisateur, $autorisation)
    {
        $qb = $this->createQueryBuilder('cd')
            ->leftJoin('cd.commande', 'c')
            ->andWhere("c.utilisateur = :utilisateur AND c.statut = 'termine'")
            ->setParameter('utilisateur', $utilisateur)
            ->andWhere('cd.typeAutorisation = :autorisation')
            ->setParameter('autorisation', $autorisation)
            ->orderBy('cd.dateAjoutPanier', 'DESC')
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * Fonction qui permet de récupérer les détails de commande qui concerne des cartes qui n'ont pas de date de validité
     * La fonction est notamment utilisé dans la commande de mise à jour de ces dates.
     */
    public function findCommandeDetailCarteSansDate()
    {
        $qb = $this->createQueryBuilder('cd')
            ->leftJoin('cd.typeAutorisation', 'ta')
            ->leftJoin('cd.commande', 'c')
            ->leftJoin('c.utilisateur', 'u')
            ->andWhere('ta.comportement = 4')
            ->andWhere('cd.dateCarteFinValidite IS NULL')
            ->andWhere("c.statut = 'termine'")
            ->andWhere('ta MEMBER OF u.autorisations')
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * Fonction qui permet de récupérer les détails de commandes concerant une autorisation qui est invalide.
     */
    public function findCommandeDetailWithAutorisationInvalid()
    {
        $qb = $this->createQueryBuilder('cd')
            ->andWhere('(cd.typeAutorisation IS NOT NULL AND cd.dateCarteFinValidite < :now) OR (cd.typeAutorisation IS NOT NULL AND cd.dateCarteFinValidite IS NULL)')
            ->setParameter('now', new DateTime())
        ;

        return $qb->getQuery()->getResult();
    }
}
