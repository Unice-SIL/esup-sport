<?php

namespace UcaBundle\Datatables\Filter;

use Doctrine\ORM\Query\Expr\Composite;
use Doctrine\ORM\QueryBuilder;
use Sg\DatatablesBundle\Datatable\Filter\TextFilter;
use UcaBundle\Entity\Activite;

class ActivitiesFilter extends TextFilter
{
    /**
     * Get an expression.
     *
     * @param string $searchType
     * @param string $searchField
     * @param mixed  $searchValue
     * @param string $searchTypeOfField
     * @param int    $parameterCounter
     *
     * @return Composite
     */
    protected function getExpression(Composite $expr, QueryBuilder $qb, $searchType, $searchField, $searchValue, $searchTypeOfField, &$parameterCounter)
    {
        $arrayIdFormat = json_decode($searchValue, true);
        $orX = $qb->expr()->orX();

        if (!empty($arrayIdFormat['recherche'])) {
            $qb->innerJoin('UcaBundle\Entity\FormatActivite', 'formAct', 'WITH', 'formAct.id = inscription.formatActivite');
            // Quelle activite le datatable doit renvoyer
            if ('Activite' == $arrayIdFormat['recherche']) {
                $qb->innerJoin('UcaBundle\Entity\Activite', 'act', 'WITH', 'act.id = formAct.activite');
                $qb->andWhere('formAct.activite = :activite');
                $qb->setParameter('activite', $arrayIdFormat['id']);
            }
            // Quelle classe d'activite le datatable doit renvoyer
            if ('ClasseActivite' == $arrayIdFormat['recherche']) {
                $qb->innerJoin('UcaBundle\Entity\Activite', 'act', 'WITH', 'act.id = formAct.activite');
                $qb->innerJoin('UcaBundle\Entity\ClasseActivite', 'classe', 'WITH', 'classe.id = act.classeActivite');
                $qb->andWhere('classe = :activite');
                $qb->setParameter('activite', $arrayIdFormat['id']);
            }
            // Quel type d'activite le datatable doit renvoyer
            elseif ('TypeActivite' == $arrayIdFormat['recherche']) {
                $qb->innerJoin('UcaBundle\Entity\Activite', 'act', 'WITH', 'act.id = formAct.activite');
                $qb->innerJoin('UcaBundle\Entity\ClasseActivite', 'classe', 'WITH', 'classe.id = act.classeActivite');
                $qb->innerJoin('UcaBundle\Entity\TypeActivite', 'type', 'WITH', 'type.id = classe.typeActivite');
                $qb->andWhere('type = :activite');
                $qb->setParameter('activite', $arrayIdFormat['id']);
            }
            // Quel format d'activite le datatable doit renvoyer
            elseif ('FormatActivite' == $arrayIdFormat['recherche']) {
                $qb->andWhere('formAct = :activite');
                $qb->setParameter('activite', $arrayIdFormat['id']);
            }
            ++$parameterCounter;
            // Quels creneaux le datatable doit renvoyer
            if ('allCreneaux' == $arrayIdFormat['recherche']) {
                $idFormat = str_replace('allCreneaux_', '', $arrayIdFormat['id']);
                $orX->add('inscription.creneau IS NULL and formAct = :activite');
                $qb->setParameter('activite', $idFormat);
                ++$parameterCounter;
            } elseif ('Creneau' == $arrayIdFormat['recherche']) {
                $qb->innerJoin('UcaBundle\Entity\DhtmlxSerie', 'serie', 'WITH', 'serie.creneau = inscription.creneau');
                $qb->andWhere('serie = :event');
                $qb->setParameter('event', $arrayIdFormat['id']);
                ++$parameterCounter;
            }
        }

        $expr->add($orX);

        return $expr;
    }
}
