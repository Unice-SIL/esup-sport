<?php

/*
 * Classe - FormatSimple:
 *
 * L'un des trois format d'activité (hérité)
 * Cela va correspondre aux évènements ponctuels auquels l'utilisateur pourra s'inscrire.
*/

namespace App\Entity\Uca;

use App\Service\Common\Fctn;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FormatSimpleRepository")
 * @Gedmo\Loggable
 */
class FormatSimple extends FormatActivite implements \App\Entity\Uca\Interfaces\Article
{
    //region Propriétés

    /** @ORM\OneToOne(targetEntity="DhtmlxEvenement", cascade={"persist", "remove"}, mappedBy="formatSimple", fetch="LAZY") */
    private $evenement;

    //endregion

    //region Méthodes
    public function __construct()
    {
        parent::__construct();
        $this->evenement = new DhtmlxEvenement();
        $this->evenement->setFormatSimple($this);
    }

    public function getArticleLibelle()
    {
        return $this->getLibelle()
            .' ['.$this->getArticleDateDebut()->format('d/m/Y H:i').']';
    }

    public function getArticleDescription()
    {
        return Fctn::strTruncate($this->getDescription(), 97);
    }

    public function setDateDebutEffective($dateDebutEffective)
    {
        $this->evenement->setDateDebut($dateDebutEffective);
        parent::setDateDebutEffective($dateDebutEffective);

        return $this;
    }

    public function setDateFinEffective($dateFinEffective)
    {
        $this->evenement->setDateFin($dateFinEffective);
        parent::setDateFinEffective($dateFinEffective);

        return $this;
    }

    public function setLibelle($libelle)
    {
        $this->evenement->setDescription($libelle);
        parent::setLibelle($libelle);

        return $this;
    }

    //endregion

    /**
     * Set evenement.
     *
     * @return FormatSimple
     * @codeCoverageIgnore
     */
    public function setEvenement(DhtmlxEvenement $evenement = null)
    {
        $this->evenement = $evenement;

        return $this;
    }

    /**
     * Get evenement.
     *
     * @return null|DhtmlxEvenement
     * @codeCoverageIgnore
     */
    public function getEvenement()
    {
        return $this->evenement;
    }
}