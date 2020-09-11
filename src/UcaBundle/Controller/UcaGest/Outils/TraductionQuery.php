<?php

/*
 * Classe - TraductionQuery
 *
 * Gestion des traduction: liste des requêtes (BDD) nécessaires à la traduction
*/

namespace UcaBundle\Controller\UcaGest\Outils;

class TraductionQuery
{
    private $em;
    private $lang = [];
    private $cols = [];
    private $joins = [];

    public function __construct($em, $langAll, $LangDefault)
    {
        $this->em = $em;
        $this->lang['all'] = $langAll;
        $this->lang['default'] = $LangDefault;
        $this->setCkeQueryInfo();
        $this->setDefaultQueryInfo();
        $this->setOtherLangQueryInfo();
    }

    public function getCols()
    {
        return $this->cols;
    }

    public function getJoins()
    {
        return $this->joins;
    }

    public function qbInit()
    {
        $qb = $this->em->createQueryBuilder();
        $qb->from('UcaBundle\Entity\Annotation', 'annotation');

        return $qb;
    }

    public function qbTranslatableFilter(&$qb)
    {
        $qb->select('annotation');
        $qb->andWhere('annotation.annotation = :annotation');
        $qb->setParameter('annotation', 'Gedmo\Mapping\Annotation\Translatable');
    }

    public function qbPersonalize(&$qb)
    {
        $this->qbTranslatableFilter($qb);
        foreach ($this->joins as $join) {
            $qb->leftJoin($join['table'], $join['alias'], \Doctrine\ORM\Query\Expr\Join::WITH, $join['join']);
        }
        foreach ($this->cols as $alias => $col) {
            $qb->addSelect($col['sql'].' AS '.$alias);
        }
        $qb->andWhere($this->cols['val'.$this->lang['default']]['sql'].' <> \'\' ');
    }

    public function qbFindOne(&$qb, $entity, $field, $id)
    {
        $qb->andWhere("annotation.entity='{$entity}'");
        $qb->andWhere("annotation.field='{$field}'");
        $qb->andWhere($this->joins[$entity]['alias'].".id = {$id}");
    }

    private function setCkeQueryInfo()
    {
        $this->joins['UcaBundle\Entity\Annotation'] = [
            'alias' => 'cke',
            'table' => 'UcaBundle\Entity\Annotation',
            'join' => "cke.entity = annotation.entity AND cke.field = annotation.field AND cke.annotation = 'UcaBundle\\Annotations\\CKEditor'",
        ];
        $this->cols['ckeditor'] = ['sql' => 'cke.annotation', 'config' => 'hidden'];
    }

    private function setDefaultQueryInfo()
    {
        $qb = $this->qbInit();
        $this->qbTranslatableFilter($qb);
        $res = $qb->getQuery()->getResult();

        $this->cols['entityid'] = ['sql' => 'CASE ', 'config' => 'hidden'];
        $this->cols['val'.$this->lang['default']] = ['sql' => 'CASE ', 'config' => 'readonly'];
        foreach ($res as $k => $v) {
            $entity = $v->getEntity();
            if (!in_array($entity, array_keys($this->joins))) {
                $this->joins[$entity] = ['alias' => 't'.$k, 'table' => $entity, 'join' => "annotation.entity = '".$entity."'"];
                $alias = $this->joins[$entity]['alias'];
                $this->cols['entityid']['sql'] .= 'WHEN '.$alias.'.id IS NOT NULL THEN '.$alias.'.id ';
            } else {
                $alias = $this->joins[$entity]['alias'];
            }
            $this->cols['val'.$this->lang['default']]['sql'] .= 'WHEN '.$alias.'.id IS NOT NULL AND annotation.field = \''.$v->getField().'\' THEN '.$alias.'.'.$v->getField().' ';
        }
        $this->cols['entityid']['sql'] .= 'ELSE -1 END';
        $this->cols['val'.$this->lang['default']]['sql'] .= 'ELSE \'\' END';
    }

    private function setOtherLangQueryInfo()
    {
        foreach (explode('|', $this->lang['all']) as $lang) {
            if ($this->lang['default'] != $lang) {
                $join = 'annotation.entity = t'.$lang.'.objectClass and annotation.field = t'.$lang.'.field and t'.$lang.".locale = '".$lang."' ";
                $join .= 'and t'.$lang.'.foreignKey = '.$this->cols['entityid']['sql'];
                $this->joins['Gedmo\Translatable\Entity\Translation\\'.$lang] = [
                    'alias' => 't'.$lang,
                    'table' => 'Gedmo\Translatable\Entity\Translation', 'join' => $join,
                ];
                $this->cols['val'.$lang] = ['sql' => 't'.$lang.'.content', 'lang' => $lang, 'config' => 'write'];
            }
        }
    }
}
