<?php

/*
 * Classe - InscriptionRepository
 *
 * Requêtes à la base de données pour l'entité inscription
*/

namespace App\Repository;

use App\Entity\Uca\Inscription;
use App\Service\Common\Parametrage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;

class InscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Inscription::class);
    }

    public static function criteriaANettoyer()
    {
        $eb = Criteria::expr();
        $criteria = Criteria::create();
        $criteria->where(
            $eb->orX(
                $eb->andX(
                    $eb->eq('statut', 'attenteajoutpanier'),
                    $eb->eq('estPartenaire', null),
                    $eb->eq('listeEmailPartenaires', null),
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
            // ->innerJoin('App\Entity\Uca\FormatActivite', 'formAct', 'WITH', 'i.formatActivite = formAct.id')
            ->where('i.creneau IS NOT NULL')
            ->andWhere('i.statut NOT IN (:listeStatut)')
            // ->andWhere('formAct.activite IN (:listeActivite)')
            ->setParameter('listeStatut', ['desinscrit', 'annule', 'ancienneinscription', 'desinscriptionadministrative'])
            // ->setParameter('listeActivite', $listeActivite)
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
            ->innerJoin('App\Entity\Uca\FormatActivite', 'formAct', 'WITH', 'i.formatActivite = formAct.id')
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
            ->select('classeAct.libelle, count(i) AS nbr')
            ->innerJoin('i.formatActivite', 'formAct')
            ->innerJoin('formAct.activite', 'act')
            ->innerJoin('act.classeActivite', 'classeAct')
            ->andWhere('i.statut = :statut')
            ->andWhere('i.creneau IS NOT NULL')
            ->setParameter('statut', $statut)
            ->groupBy('classeAct.libelle')
            ->orderBy('count(i)')
        ;

        return $qb->getQuery()->getResult();
    }

    public function getNbreInscriptionByActiviteForStatut($statut)
    {
        $qb = $this->createQueryBuilder('i')
            ->select('act.libelle, count(i) AS nbr')
            ->innerJoin('i.formatActivite', 'formAct')
            ->innerJoin('formAct.activite', 'act')
            ->andWhere('i.statut = :statut')
            ->andWhere('i.creneau IS NOT NULL')
            ->setParameter('statut', $statut)
            ->groupBy('act.libelle')
            ->orderBy('count(i)')
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

    public function findInscriptionPartenairesANettoyer()
    {
        return $this->createQueryBuilder('i')
            ->andWhere("i.reservabilite is not null and i.statut in ('attentepaiement', 'attentepartenaire') and i.date < :dateTimeout and i.listeEmailPartenaires is not null and i.estPartenaire is null")
            ->setParameter('dateTimeout', Parametrage::getDateDebutInscriptionPartenaires())
            ->getQuery()
            ->getResult()
        ;
    }

    public function findInscriptionsPartenairesPostPaiement($parentId, $currentId)
    {
        $qb = $this->createQueryBuilder('i');

        if ($parentId == $currentId) {
            $qb->andWhere("i.estPartenaire = :parentId and i.statut = 'attentepartenaire'")
                ->setParameter('parentId', $parentId)
            ;
        } else {
            $qb->andWhere("i.estPartenaire = :parentId and ((i.id <> :currentId and i.statut = 'attentepartenaire') or (i.id = :currentId and i.statut in ('attentepaiement', 'attentepartenaire')))")
                ->setParameters([
                    'parentId' => $parentId,
                    'currentId' => $currentId,
                ])
            ;
        }

        return $qb->getQuery()->getResult();
    }

    public function getNbInscriptionPartenaireValide($inscriptionId, $utilisateurId)
    {
        return (int) $this->createQueryBuilder('i')
            ->select('count(i)')
            ->andWhere('i.estPartenaire = :inscriptionId and i.utilisateur = :utilisateurId and i.statut not in (:statut)')
            ->setParameters([
                'inscriptionId' => $inscriptionId,
                'utilisateurId' => $utilisateurId,
                'statut' => Inscription::STATUT_INVALIDE,
            ])
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findAssociatedInscriptionsPartenaire(Inscription $inscription)
    {
        $qb = $this->createQueryBuilder('i');
        if ((bool) $inscription->getListeEmailPartenaires()) {
            $qb = $qb->andWhere('i.estPartenaire = :inscriptionId and i.statut = \'valide\'')
                ->setParameter('inscriptionId', $inscription->getId())
            ;
        } else {
            $qb = $qb->andWhere('((i.id = :partenaireId and i.listeEmailPartenaires is not null) or (i.estPartenaire = :partenaireId and i.id <> :inscriptionId)) and i.statut = \'valide\'')
                ->setParameters(['inscriptionId' => $inscription->getId(), 'partenaireId' => $inscription->getEstPartenaire()])
            ;
        }

        return $qb->getQuery()->getResult();
    }
}