<?php

/*
 * Classe - DataUtilisateur:
 *
 * Données utilisateurs formatés pour la satistique
 * C'est une autre base de donnée
*/

namespace App\Entity\Statistique;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="data_utilisateur")
 * @ORM\Entity(repositoryClass="App\Repository\DataUtilisateurRepository")
 */
class DataUtilisateur
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** @ORM\Column(name="codEtu", type="string", length=255)*/
    private $codEtu;

    /** @ORM\Column(name="codEtp", type="string", length=255, nullable=true)*/
    private $codEtp;

    /** @ORM\Column(type="string", length=255, nullable=true)*/
    private $niveau;

    /** @oRM\Column(type="string",nullable=true)*/
    private $categorie;
    // CAT_A, CAT_B, CAT_C

    /** @ORM\Column(name="libCmp", type="string", length=255, nullable=true) */
    private $libCmp;

    /** @ORM\Column(name="codCmp", type="string", length=255, nullable=true) */
    private $codCmp;

    /** @ORM\Column(type="string", length=1, nullable=true) */
    private $shnu;

    /** @ORM\Column(type="string", length=1, nullable=true) */
    private $boursier;

    /** @ORM\Column(type="string", length=1)*/
    private $sexe;

    /** @ORM\Column(type="boolean") */
    private $estMembrePersonnel;

    /** @ORM\Column(name="dateNaissance", type="string", length=255) */
    private $dateNaissance;

    /** @ORM\Column(name="annee_universitaire", type="integer", length=4) */
    private $anneeUniversitaire;

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
     * Set codEtu.
     *
     * @param string $codEtu
     *
     * @return DataUtilisateur
     */
    public function setCodEtu($codEtu)
    {
        $this->codEtu = $codEtu;

        return $this;
    }

    /**
     * Get codEtu.
     *
     * @return string
     */
    public function getCodEtu()
    {
        return $this->codEtu;
    }

    /**
     * Set codEtp.
     *
     * @param string $codEtp
     *
     * @return DataUtilisateur
     */
    public function setCodEtp($codEtp)
    {
        $this->codEtp = $codEtp;

        return $this;
    }

    /**
     * Get codEtp.
     *
     * @return string
     */
    public function getCodEtp()
    {
        return $this->codEtp;
    }

    /**
     * Set niveau.
     *
     * @param null|string $niveau
     *
     * @return DataUtilisateur
     */
    public function setNiveau($niveau = null)
    {
        $this->niveau = $niveau;

        return $this;
    }

    /**
     * Get niveau.
     *
     * @return null|string
     */
    public function getNiveau()
    {
        return $this->niveau;
    }

    /**
     * Set Categorie.
     *
     * @param null|string $categorie
     *
     * @return DataUtilisateur
     */
    public function setCategorie($categorie = null)
    {
        $this->categorie = $categorie;

        return $this;
    }

    /**
     * Get categorie.
     *
     * @return null|string
     */
    public function getCategorie()
    {
        return $this->categorie;
    }

    /**
     * Set libCmp.
     *
     * @param string $libCmp
     *
     * @return DataUtilisateur
     */
    public function setLibCmp($libCmp)
    {
        $this->libCmp = $libCmp;

        return $this;
    }

    /**
     * Get libCmp.
     *
     * @return string
     */
    public function getLibCmp()
    {
        return $this->libCmp;
    }

    /**
     * Set codCmp.
     *
     * @param string $codCmp
     *
     * @return DataUtilisateur
     */
    public function setCodCmp($codCmp)
    {
        $this->codCmp = $codCmp;

        return $this;
    }

    /**
     * Get codCmp.
     *
     * @return string
     */
    public function getCodCmp()
    {
        return $this->codCmp;
    }

    /**
     * Set shnu.
     *
     * @param string $shnu
     *
     * @return DataUtilisateur
     */
    public function setShnu($shnu)
    {
        $this->shnu = $shnu;

        return $this;
    }

    /**
     * Get shnu.
     *
     * @return string
     */
    public function getShnu()
    {
        return $this->shnu;
    }

    /**
     * Set boursier.
     *
     * @param string $boursier
     *
     * @return DataUtilisateur
     */
    public function setBoursier($boursier)
    {
        $this->boursier = $boursier;

        return $this;
    }

    /**
     * Get boursier.
     *
     * @return string
     */
    public function getBoursier()
    {
        return $this->boursier;
    }

    /**
     * Set sexe.
     *
     * @param string $sexe
     *
     * @return DataUtilisateur
     */
    public function setSexe($sexe)
    {
        $this->sexe = $sexe;

        return $this;
    }

    /**
     * Get sexe.
     *
     * @return string
     */
    public function getSexe()
    {
        return $this->sexe;
    }

    /**
     * Set estMembrePersonnel.
     *
     * @param bool $estMembrePersonnel
     *
     * @return DataUtilisateur
     */
    public function setEstMembrePersonnel($estMembrePersonnel)
    {
        $this->estMembrePersonnel = $estMembrePersonnel;

        return $this;
    }

    /**
     * Get estMembrePersonnel.
     *
     * @return bool
     */
    public function getEstMembrePersonnel()
    {
        return $this->estMembrePersonnel;
    }

    /**
     * Set dateNaissance.
     *
     * @param string $dateNaissance
     *
     * @return DataUtilisateur
     */
    public function setDateNaissance($dateNaissance)
    {
        $this->dateNaissance = $dateNaissance;

        return $this;
    }

    /**
     * Get dateNaissance.
     *
     * @return string
     */
    public function getDateNaissance()
    {
        return $this->dateNaissance;
    }

    /**
     * Set anneeUniversitaire.
     *
     * @param mixed $anneeUniversitaire
     *
     * @return DataUtilisateur
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
}
