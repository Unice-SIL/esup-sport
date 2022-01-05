<?php

/*
 * Classe - KpiGenerauxEtudiants:
 *
 * Données de KPI géréaux sur les étudiants
*/

namespace StatistiqueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="kpi_generaux_etudiants")
 * @ORM\Entity(repositoryClass="StatistiqueBundle\Repository\KpiGenerauxEtudiantsRepository")
 */
class KpiGenerauxEtudiants
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** @ORM\Column(name="annee_universitaire", type="integer", length=4, nullable=false) */
    private $anneeUniversitaire;

    /** @ORM\Column(name="nb_etudiants", type="integer", length=4, nullable=true) */
    private $nbEtudiants;

    /** @ORM\Column(name="nb_inscrits", type="integer", length=4, nullable=true) */
    private $nbInscrits;

    /** @ORM\Column(name="nb_boursier", type="integer", length=4, nullable=true) */
    private $nbBoursier;

    /** @ORM\Column(name="nb_shnu", type="integer", length=4, nullable=true) */
    private $nbShnu;

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
     * @return KpiGenerauxEtudiants
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
     * Set nbEtudiants.
     *
     * @param mixed $nbEtudiants
     *
     * @return KpiGenerauxEtudiants
     */
    public function setNbEtudiants($nbEtudiants)
    {
        $this->nbEtudiants = $nbEtudiants;

        return $this;
    }

    /**
     * Get nbEtudiants.
     *
     * @return \int
     */
    public function getNbEtudiants()
    {
        return $this->nbEtudiants;
    }

    /**
     * Set nbInscrits.
     *
     * @param mixed $nbInscrits
     *
     * @return KpiGenerauxEtudiants
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
     * Set nbBoursier.
     *
     * @param mixed $nbBoursier
     *
     * @return KpiGenerauxEtudiants
     */
    public function setNbBoursier($nbBoursier)
    {
        $this->nbBoursier = $nbBoursier;

        return $this;
    }

    /**
     * Get nbBoursier.
     *
     * @return \int
     */
    public function getNbBoursier()
    {
        return $this->nbBoursier;
    }

    /**
     * Set nbShnu.
     *
     * @param mixed $nbShnu
     *
     * @return KpiGenerauxEtudiants
     */
    public function setNbShnu($nbShnu)
    {
        $this->nbShnu = $nbShnu;

        return $this;
    }

    /**
     * Get nbShnu.
     *
     * @return \int
     */
    public function getNbShnu()
    {
        return $this->nbShnu;
    }
}
