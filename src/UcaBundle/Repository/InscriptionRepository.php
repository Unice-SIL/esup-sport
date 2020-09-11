<?php

/*
 * Classe - InscriptionRepository
 *
 * Requêtes à la base de données pour l'entité inscription
*/

namespace UcaBundle\Repository;

use Doctrine\Common\Collections\Criteria;
use UcaBundle\Service\Common\Parametrage;

class InscriptionRepository extends \Doctrine\ORM\EntityRepository
{
    public static function criteriaANettoyer()
    {
        $eb = Criteria::expr();
        $criteria = Criteria::create();
        $criteria->where(
            $eb->orX(
                $eb->andX(
                    $eb->eq('statut', 'attenteajoutpanier'),
                    $eb->lt('dateValidation', Parametrage::getDateDebutPanierApresValidationLimite())
                )
            )
        );

        return $criteria;
    }

    public function aNettoyer()
    {
        return $this->matching(self::criteriaANettoyer());
    }

    public function findUtilisateurPourDesinscriptionCreneau($creneau, $user)
    {
        $qb = $this->createQueryBuilder('i')
            ->where('i.creneau = :creneau')
            ->setParameter('creneau', $creneau)
            ->andWhere('i.utilisateur = :user')
            ->setParameter('user', $user)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findUtilisateurPourDesinscriptionFormat($format, $user)
    {
        $qb = $this->createQueryBuilder('i')
            ->where('i.formatActivite = :format')
            ->setParameter('format', $format)
            ->andWhere('i.utilisateur = :user')
            ->setParameter('user', $user)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findInscriptionCreneauxBascule($listeActivite)
    {
        $qb = $this->createQueryBuilder('i')
            ->innerJoin('UcaBundle\Entity\FormatActivite', 'formAct', 'WITH', 'i.formatActivite = formAct.id')
            ->where('i.creneau IS NOT NULL')
            ->andWhere('i.statut NOT IN (:listeStatut)')
            ->andWhere('formAct.activite IN (:listeActivite)')
            ->setParameter('listeStatut', ['desinscrit', 'annule', 'ancienneinscription', 'desinscriptionadministrative'])
            ->setParameter('listeActivite', $listeActivite)
        ;

        return $qb->getQuery()->getResult();
    }

    public function inscriptionParCreneauStatut($creneau, $statut)
    {
        $qb = $this->createQueryBuilder('i')
            ->where('i.creneau = :creneau')
            ->andWhere('i.statut LIKE :statut1')
            ->setParameter('statut1', '%'.$statut.'%')
            ->setParameter('creneau', $creneau)
        ;

        return count($qb->getQuery()->getResult()) > 0;
    }

    public function findInscriptionByCreneauIdAndYear($id, $year)
    {
        $date = [
            'statut' => 'valide',
            'id' => $id,
            'dateDeb' => new \DateTime($year.'-01-01'),
            'dateFin' => new \DateTime($year.'-06-30'),
        ];

        $qb = $this->createQueryBuilder('i')
            ->innerJoin('UcaBundle\Entity\FormatActivite', 'formAct', 'WITH', 'i.formatActivite = formAct.id')
            ->where('i.statut = :statut')
            ->andWhere('i.creneau = :id')
            ->andWhere('NOT formAct.dateDebutEffective > :dateFin OR NOT formAct.dateFinEffective < :dateDeb')
            ->setParameters($date)
        ;

        return count($qb->getQuery()->getResult());
    }

    public function findInscriptionByActivite($id)
    {
        $qb = $this->createQueryBuilder('i')
            ->leftJoin('i.formatActivite', 'formAct')
            ->leftJoin('formAct.activite', 'act')
            ->andWhere('act.id = :id')
            ->setParameter('id', $id)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findInscriptionByActiviteAndStatut($id, $statut)
    {
        $qb = $this->createQueryBuilder('i')
            ->leftJoin('i.formatActivite', 'formAct')
            ->leftJoin('formAct.activite', 'act')
            ->andWhere('act.id = :id')
            ->setParameter('id', $id)
            ->andWhere('i.statut = :statut')
            ->setParameter('statut', $statut)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findInscriptionByClasseActivite($id)
    {
        $qb = $this->createQueryBuilder('i')
            ->leftJoin('i.formatActivite', 'formAct')
            ->leftJoin('formAct.activite', 'act')
            ->leftJoin('act.classeActivite', 'classeAct')
            ->andWhere('classeAct.id = :id')
            ->setParameter('id', $id)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findInscriptionByClasseActiviteAndStatut($id, $statut)
    {
        $qb = $this->createQueryBuilder('i')
            ->leftJoin('i.formatActivite', 'formAct')
            ->leftJoin('formAct.activite', 'act')
            ->leftJoin('act.classeActivite', 'classeAct')
            ->andWhere('classeAct.id = :id')
            ->setParameter('id', $id)
            ->andWhere('i.statut = :statut')
            ->setParameter('statut', $statut)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findInscriptionByTypeActivite($id)
    {
        $qb = $this->createQueryBuilder('i')
            ->leftJoin('i.formatActivite', 'formAct')
            ->leftJoin('formAct.activite', 'act')
            ->leftJoin('act.classeActivite', 'classeAct')
            ->leftJoin('classeAct.typeActivite', 'typeAct')
            ->andWhere('typeAct.id = :id')
            ->setParameter('id', $id)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findInscriptionByTypeActiviteAndStatut($id, $statut)
    {
        $qb = $this->createQueryBuilder('i')
            ->leftJoin('i.formatActivite', 'formAct')
            ->leftJoin('formAct.activite', 'act')
            ->leftJoin('act.classeActivite', 'classeAct')
            ->leftJoin('classeAct.typeActivite', 'typeAct')
            ->andWhere('typeAct.id = :id')
            ->setParameter('id', $id)
            ->andWhere('i.statut = :statut')
            ->setParameter('statut', $statut)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findInscriptionByEncadrant($id)
    {
        $qb = $this->createQueryBuilder('i')
            ->leftJoin('i.formatActivite', 'formAct')
            ->leftJoin('formAct.encadrants', 'enc')
            ->andWhere('enc.id = :id')
            ->setParameter('id', $id)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findInscriptionByEncadrantAndStatut($id, $statut)
    {
        $qb = $this->createQueryBuilder('i')
            ->leftJoin('i.formatActivite', 'formAct')
            ->leftJoin('formAct.encadrants', 'enc')
            ->andWhere('enc.id = :id')
            ->setParameter('id', $id)
            ->andWhere('i.statut = :statut')
            ->setParameter('statut', $statut)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findByCreneauAndStatut($id, $statut)
    {
        $qb = $this->createQueryBuilder('i')
            ->leftJoin('i.creneau', 'cre')
            ->andWhere('cre.id = :id')
            ->setParameter('id', $id)
            ->andWhere('i.statut = :statut')
            ->setParameter('statut', $statut)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findByFormatActiviteAndStatut($id, $statut)
    {
        $qb = $this->createQueryBuilder('i')
            ->leftJoin('i.formatActivite', 'formAct')
            ->andWhere('formAct.id = :id')
            ->setParameter('id', $id)
            ->andWhere('i.statut = :statut')
            ->setParameter('statut', $statut)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findReservation()
    {
        $qb = $this->createQueryBuilder('r')
            ->where('r.reservabilite IS NOT NULL')
        ;

        return $qb->getQuery()->getResult();
    }

    public function findInscriptionbyStatutAndDate($statut, $dateDebut, $dateFin)
    {
        $qb = $this->createQueryBuilder('i')
            ->where('i.date BETWEEN :debut AND :fin')
            ->setParameter('debut', $dateDebut)
            ->setParameter('fin', $dateFin)
            ->andWhere('i.statut = :statut')
            ->setParameter('statut', $statut)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findByCreneauAndStatutAndDate($id, $statut, $dateDebut, $dateFin)
    {
        $qb = $this->createQueryBuilder('i')
            ->leftJoin('i.creneau', 'cre')
            ->where('i.date BETWEEN :debut AND :fin')
            ->setParameter('debut', $dateDebut)
            ->setParameter('fin', $dateFin)
            ->andWhere('cre.id = :id')
            ->setParameter('id', $id)
            ->andWhere('i.statut = :statut')
            ->setParameter('statut', $statut)
        ;

        return $qb->getQuery()->getResult();
    }

    public function getNbreInscriptionByClasseActiviteForStatut($statut)
    {
        $qb = $this->createQueryBuilder('i')
            ->select('classeAct.libelle, count(i)')
            ->innerJoin('i.formatActivite', 'formAct')
            ->innerJoin('formAct.activite', 'act')
            ->innerJoin('act.classeActivite', 'classeAct')
            ->andWhere('i.statut = :statut')
            ->andWhere('i.creneau IS NOT NULL')
            ->setParameter('statut', $statut)
            ->groupBy('classeAct.libelle')
        ;

        return $qb->getQuery()->getResult();
    }

    public function getNbreInscriptionByActiviteForStatut($statut)
    {
        $qb = $this->createQueryBuilder('i')
            ->select('act.libelle, count(i)')
            ->innerJoin('i.formatActivite', 'formAct')
            ->innerJoin('formAct.activite', 'act')
            ->andWhere('i.statut = :statut')
            ->andWhere('i.creneau IS NOT NULL')
            ->setParameter('statut', $statut)
            ->groupBy('act.libelle')
        ;

        return $qb->getQuery()->getResult();
    }

    public function findInscriptionByFormatActivite($id)
    {
        $qb = $this->createQueryBuilder('i')
            ->join('i.formatActivite', 'fa')
            ->where('fa.id = :id')
            ->setParameter('id', $id)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findInscriptionByEtablissement($id)
    {
        $qb = $this->createQueryBuilder('i')
            ->join('i.creneau', 'c')
            ->leftJoin('c.lieu', 'l')
            ->leftJoin('l.etablissement', 'e')
            ->where('e.id = :id')
            ->setParameter('id', $id)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findInscriptionByLieu($id)
    {
        $qb = $this->createQueryBuilder('i')
            ->join('i.creneau', 'c')
            ->leftJoin('c.lieu', 'l')
            ->where('l.id = :id')
            ->setParameter('id', $id)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findInscriptionForDesincription($nom, $prenom, $statut, $typeActivite, $classeActivite, $activite, $formatActivite, $creneau, $encadrant, $etablissement, $lieu)
    {
        $statuts = ['annule', 'attenteajoutpanier', 'desinscrit', 'ancienneinscription', 'desinscriptionadministrative'];
        $qb = $this->createQueryBuilder('i')
            ->leftJoin('i.utilisateur', 'u')
            ->leftJoin('i.creneau', 'c')
            ->leftJoin('i.formatActivite', 'fa')
            ->leftJoin('fa.activite', 'a')
            ->leftJoin('a.classeActivite', 'ca')
            ->where('i.statut NOT IN (:statuts)')
            ->setParameter('statuts', $statuts)
        ;

        if (null != $nom and 'null' != $nom) {
            $qb
                ->andWhere('u.nom = :nom')
                ->setParameter('nom', $nom)
            ;
        }
        if (null != $prenom and 'null' != $prenom) {
            $qb
                ->andWhere('u.prenom like :prenom')
                ->setParameter('prenom', '%'.$prenom.'%')
            ;
        }
        if (null != $statut and '0' != $statut) {
            $qb
                ->andWhere('i.statut = :statut')
                ->setParameter('statut', $statut)
            ;
        }
        if (null != $creneau and 0 != $creneau) {
            $qb
                ->leftJoin('c.serie', 's')
                ->andWhere('s.id = :creneau')
                ->setParameter('creneau', $creneau)
            ;
        }
        if (null != $formatActivite and 0 != $formatActivite) {
            $qb
                ->andWhere('fa.id = :formatActivite')
                ->setParameter('formatActivite', $formatActivite)
            ;
        }
        if (null != $activite and 0 != $activite) {
            $qb
                ->andWhere('a.id = :activite')
                ->setParameter('activite', $activite)
            ;
        }
        if (null != $classeActivite and 0 != $classeActivite) {
            $qb
                ->andWhere('ca.id = :classeActivite')
                ->setParameter('classeActivite', $classeActivite)
            ;
        }
        if (null != $typeActivite and 0 != $typeActivite) {
            $qb
                ->andWhere('ca.typeActivite = :typeActivite')
                ->setParameter('typeActivite', $typeActivite)
            ;
        }
        if (null != $lieu) {
            $qb
                ->andWhere(':lieu MEMBER OF fa.lieu OR c.lieu = :lieu')
                ->setParameter('lieu', $lieu)
            ;
        }
        if (null != $etablissement) {
            $qb
                ->leftJoin('c.lieu', 'l')
                ->leftJoin('fa.lieu', 'lfa')
                ->andWhere('lfa.etablissement = :etablissement OR l.etablissement = :etablissement')
                ->setParameter('etablissement', $etablissement)
            ;
        }

        if (null != $encadrant) {
            $qb
                ->andWhere(':encadrant MEMBER OF fa.encadrants')
                ->setParameter('encadrant', $encadrant)
            ;
        }

        return $qb->getQuery()->getResult();
    }

    public function findInscriptionBascule()
    {
        $qb = $this->createQueryBuilder('i')
            ->andWhere("i.statut <> 'ancienneinscription'")
        ;

        return $qb->getQuery()->getResult();
    }
}
