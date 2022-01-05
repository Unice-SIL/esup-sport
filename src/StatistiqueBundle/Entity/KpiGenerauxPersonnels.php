<?php

/*
 * Classe - KpiGenerauxPersonnels:
 *
 * Données de KPI géréaux sur les personnels
*/

namespace StatistiqueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="kpi_generaux_personnels")
 * @ORM\Entity(repositoryClass="StatistiqueBundle\Repository\KpiGenerauxPersonnelsRepository")
 */
class KpiGenerauxPersonnels
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** @ORM\Column(name="annee_universitaire", type="integer", length=4, nullable=false) */
    private $anneeUniversitaire;

    /** @ORM\Column(name="nb_personnels", type="integer", length=4, nullable=true) */
    private $nbPersonnels;

    /** @ORM\Column(name="nb_inscrits", type="integer", length=4, nullable=true) */
    private $nbInscrits;

    /** @ORM\Column(name="nb_cat_a", type="integer", length=4, nullable=true) */
    private $nb_cat_a;

    /** @ORM\Column(name="nb_cat_b", type="integer", length=4, nullable=true) */
    private $nb_cat_b;

    /** @ORM\Column(name="nb_cat_c", type="integer", length=4, nullable=true) */
    private $nb_cat_c;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set anneeUniversitaire.
     *
     * @param mixed $anneeUniversitaire
     *
     * @return KpiGenerauxPersonnels
     */
    public function setAnneeUniversitaire($anneeUniversitaire)
    {
        $this->anneeUniversitaire = $anneeUniversitaire;

        return $this;
    }

    /**
     * Get anneeUniversitaire.
     *
     * @return \int
     */
    public function getAnneeUniversitaire()
    {
        return $this->anneeUniversitaire;
    }

    /**
     * Set nbPersonnels.
     *
     * @param mixed $nbPersonnels
     *
     * @return KpiGenerauxPersonnels
     */
    public function setNbPersonnels($nbPersonnels)
    {
        $this->nbPersonnels = $nbPersonnels;

        return $this;
    }

    /**
     * Get nbPersonnels.
     *
     * @return \int
     */
    public function getNbPersonnels()
    {
        return $this->nbPersonnels;
    }

    /**
     * Set nbInscrits.
     *
     * @param mixed $nbInscrits
     *
     * @return KpiGenerauxPersonnels
     */
    public function setNbInscrits($nbInscrits)
    {
        $this->nbInscrits = $nbInscrits;

        return $this;
    }

    /**
     * Get nbInscrits.
     *
     * @return \int
     */
    public function getNbInscrits()
    {
        return $this->nbInscrits;
    }

    /**
     * Set nb_cat_a.
     *
     * @param mixed $nb_cat_a
     *
     * @return KpiGenerauxPersonnels
     */
    public function setNbCatA($nb_cat_a)
    {
        $this->nb_cat_a = $nb_cat_a;

        return $this;
    }

    /**
     * Get nb_cat_a.
     *
     * @return \int
     */
    public function getNbCatA()
    {
        return $this->nb_cat_a;
    }

    /**
     * Set nb_cat_b.
     *
     * @param mixed $nb_cat_b
     *
     * @return KpiGenerauxPersonnels
     */
    public function setNbCatB($nb_cat_b)
    {
        $this->nb_cat_b = $nb_cat_b;

        return $this;
    }

    /**
     * Get nb_cat_b.
     *
     * @return \int
     */
    public function getNbCatB()
    {
        return $this->nb_cat_b;
    }

    /**
     * Set nb_cat_c.
     *
     * @param mixed $nb_cat_c
     *
     * @return KpiGenerauxPersonnels
     */
    public function setNbCatC($nb_cat_c)
    {
        $this->nb_cat_c = $nb_cat_c;

        return $this;
    }

    /**
     * Get nb_cat_c.
     *
     * @return \int
     */
    public function getNbCatC()
    {
        return $this->nb_cat_c;
    }
}
