<?php

/*
 * Classe - CommandeRepository
 *
 * Contient les requtes à la base de données pour l'entité commande
*/

namespace UcaBundle\Repository;

use Doctrine\Common\Collections\Criteria;
use UcaBundle\Service\Common\Parametrage;

class CommandeRepository extends \Doctrine\ORM\EntityRepository
{
    public static function criteriaByStatut($statutListe)
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->in('statut', $statutListe))
        ;
    }

    public static function criteriaANettoyer()
    {
        $eb = Criteria::expr();
        $criteria = Criteria::create();
        $criteria->where(
            $eb->orX(
                $eb->andX(
                    $eb->eq('statut', 'apayer'),
                    $eb->eq('typePaiement', 'PAYBOX'),
                    $eb->eq('moyenPaiement', 'cb'),
                    $eb->lt('dateCommande', Parametrage::getDateDebutCbLimite())
                ),
                $eb->andX(
                    $eb->eq('statut', 'apayer'),
                    $eb->eq('typePaiement', 'BDS'),
                    $eb->lt('dateCommande', Parametrage::getDateDebutBdsLimite())
                ),
                $eb->andX(
                    $eb->eq('statut', 'panier'),
                    $eb->lt('datePanier', Parametrage::getDateDebutPanierLimite())
                )
            )
        );

        return $criteria;
    }

    public function max($field)
    {
        $qb = $this->createQueryBuilder('c');
        $qb->select('MAX(c.'.$field.')');
        $res = $qb->getQuery()->getSingleScalarResult();

        return empty($res) ? 0 : $res;
    }

    public function aNettoyer()
    {
        return $this->matching(self::criteriaANettoyer());
    }

    //Méthode qui permet de récupérer toutes les factures payées et non gratuites avec ou non des parametres de recherches
    public function findAllFacture($datePaiement, $recherche)
    {
        $qb = $this->createQueryBuilder('c')
            ->where("c.statut = 'termine' and c.montantTotal <> '0.00'")
        ;
        if (null != $datePaiement) {
            $qb->andWhere('c.datePaiement BETWEEN :debut AND :fin')
                ->setParameters([
                    'debut' => \DateTime::createFromFormat('Y-m-d', $datePaiement)->setTime(0, 0, 0),
                    'fin' => \DateTime::createFromFormat('Y-m-d', $datePaiement)->setTime(23, 59, 59),
                ])
            ;
        }

        if (null != $recherche) {
            $qb->andWhere('c.prenom = :recherche OR c.nom = :recherche')
                ->setParameter('recherche', $recherche)
            ;
        }

        return $qb->getQuery()->getResult();
    }

    public function findAllCommandes($date = null, $recherche = null, $dateDebut = null, $dateFin = null)
    {
        $parametres = [];
        $request = "c.statut = 'termine' and c.montantTotal <> '0.00'";
        if (null != $date and 'null' != $date) {
            $parametres['date_paiement_min'] = new \DateTime($date.' 00:00:00');
            $parametres['date_paiement_max'] = new \DateTime($date.' 23:59:59');
            $request .= ' and c.datePaiement >= :date_paiement_min and c.datePaiement <= :date_paiement_max';
        } else {
            if (null != $dateDebut and 'null' != $dateDebut) {
                $parametres['dateDebut'] = new \DateTime($dateDebut.' 00:00:00');
                $request .= ' and c.datePaiement >= :dateDebut';
            }
            if (null != $dateFin and 'null' != $dateFin) {
                $parametres['dateFin'] = new \DateTime($dateFin.' 23:59:59');
                $request .= ' and c.datePaiement <= :dateFin';
            }
        }
        if (null != $recherche and 'null' != $recherche) {
            $parametres['nom'] = $recherche;
            $parametres['prenom'] = $recherche;
            $request .= ' and (c.nom = :nom or c.prenom = :prenom)';
        }

        $qb = $this->createQueryBuilder('c')
            ->where($request)
            ->setParameters($parametres)
        ;

        return $qb->getQuery()->execute();
    }

    public function findCommandeByTypeAutorisationAndUser($typeAutorisation, $user)
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.commandeDetails', 'cd')
            ->andWhere('c.utilisateur = :user')
            ->setParameter('user', $user)
            ->andWhere('cd.typeAutorisation = :typeAutorisation')
            ->setParameter('typeAutorisation', $typeAutorisation)
        ;

        return $qb->getQuery()->getResult();
    }
}
