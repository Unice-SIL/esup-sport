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
    public static function criteriaByStatut($statut)
    {
        if (is_array($statut)) {
            $expr = Criteria::expr()->in('commande.statut', $statut);
        } else {
            $expr = Criteria::expr()->eq('commande.statut', $statut);
        }

        return Criteria::create()->andWhere($expr);
    }

    public static function criteriaByMoyen($moyen)
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->contains('commande.moyenPaiement', $moyen))
        ;
    }

    public static function criteriaByNumCommmande($numComamnde)
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->eq('commande.numeroCommande', $numComamnde))
        ;
    }

    public static function criteriaByNumRecu($numRecu)
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->eq('commande.numeroRecu', $numRecu))
        ;
    }

    public static function criteriaByMontant($montant)
    {
        if (0 === $montant) {
            $expr = Criteria::expr()->neq('commande.montantTotal', 0);
        // } elseif (null !== $montant && null === $recherche) {
            // $expr = Criteria::expr()->eq('commande.montantTotal', (float) $montant);
        } else {
            $expr = Criteria::expr()->eq('commande.montantTotal', (float) $montant);
            // $expr = Criteria::expr()->contains('commande.montantTotal', '%'.$recherche.'%');
        }

        return Criteria::create()->andWhere($expr);
    }

    public static function criteriaByUtilisateur($nom, $prenom)
    {
        $criteria = Criteria::create();
        $er = $criteria->expr();

        if (null !== $prenom) {
            $criteria->andWhere($er->contains('commande.prenom', '%'.$prenom.'%'));
        }
        if (null !== $nom) {
            $criteria->andWhere($er->contains('commande.nom', '%'.$nom.'%'));
        }

        return $criteria;
    }

    public static function criteriaBetweenDates($startDate, $endDate)
    {
        $criteria = Criteria::create();
        $er = $criteria->expr();

        if (null !== $startDate) {
            $criteria->andWhere($er->gt('commande.datePaiement', $startDate));
        }
        if (null !== $endDate) {
            $criteria->andWhere($er->lt('commande.datePaiement', $endDate));
        }

        return $criteria;
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

    public static function criteriaByRecherche($recherche)
    {
        $criteria = Criteria::create();
        $er = $criteria->expr();
        $criteria->andWhere($er->orx(
            $er->contains('commande.prenom', '%'.$recherche.'%'),
            $er->contains('commande.nom', '%'.$recherche.'%'),
            $er->contains('commande.moyenPaiement', '%'.$recherche.'%'),
            //$er->contains('commande.montantTotal', '%'.$recherche.'%'),
            $er->contains('commande.numeroCommande', '%'.$recherche.'%'),
            $er->contains('commande.numeroRecu', '%'.$recherche.'%'),
            $er->eq('commande.datePaiement', '%'.$recherche.'%'),
            $er->contains('commande.dateCommande', '%'.$recherche.'%'),
            $er->contains('commande.dateAnnulation', '%'.$recherche.'%')
        ));

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

    public function findExtractedCommandes($dateDebut, $dateFin, $nom, $prenom, $statut, $moyen, $montant, $numComamnde, $numRecu, $recherche)
    {
        $qb = $this->createQueryBuilder('commande');
        $qb->addCriteria(self::criteriabyStatut(['termine', 'avoir']));
        if (null === $montant && null === $recherche) {
            $qb->addCriteria(self::criteriaByMontant(0));
        } elseif (null !== $montant && null === $recherche) {
            $qb->addCriteria(self::criteriaByMontant($montant));
        }

        if (null !== $dateDebut || null !== $dateFin) {
            $qb->addCriteria(self::criteriaBetweenDates($dateDebut, $dateFin));
        }
        if (null !== $moyen) {
            $qb->addCriteria(self::criteriaByMoyen($moyen));
        }
        if (null !== $numComamnde) {
            $qb->addCriteria(self::criteriaByNumCommmande($numComamnde));
        }
        if (null !== $numRecu) {
            $qb->addCriteria(self::criteriaByNumRecu($numRecu));
        }
        if (null !== $nom || null !== $prenom) {
            $qb->addCriteria(self::criteriaByUtilisateur($nom, $prenom));
        }
        if (null !== $recherche) {
            $qb->addCriteria(self::criteriaByRecherche($recherche));
        }
        /*
        dump($qb->getParameters());
        dump($qb->getQuery());
        dump($qb->getQuery()->getResult());
        die;*/

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
