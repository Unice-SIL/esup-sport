<?php

/*
 * Classe - MontantTarifProfilUtilisateur:
 *
 * Entité technique permettant de saisir un tarif selon le profil utilisateur.
*/

namespace App\Entity\Uca;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MontantTarifProfilUtilisateurRepository")
 */
class MontantTarifProfilUtilisateur
{
    /** @ORM\ManyToOne(targetEntity="Tarif", inversedBy="montants") */
    protected $tarif;

    /** @ORM\ManyToOne(targetEntity="ProfilUtilisateur", inversedBy="montants") */
    protected $profil;
    //region Propriétés
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** @ORM\Column(type="decimal", precision=10, scale=2,options={"default":-1})
     * @Assert\NotBlank(message="tarif.montant.notnull")
     */
    private $montant;
    //endregion

    //region Méthodes

    public function __construct($tarif, $profil, $montant)
    {
        $this->tarif = $tarif;
        $this->profil = $profil;
        $this->montant = $montant;
    }

    public function setMontant($montant)
    {
        if ($montant != $this->montant || null == $this->montant) {
            $p = $this->getProfil();
            $t = $this->getTarif();
            $t->setModificationMontants($t->getModificationMontants().' / '.$p->getLibelle().':'.$montant.' €');
        }
        $this->montant = $montant;

        return $this;
    }

    //endregion

    /**
     * Get id.
     *
     * @return int
     * @codeCoverageIgnore
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get montant.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getMontant()
    {
        return $this->montant;
    }

    /**
     * Set tarif.
     *
     * @return MontantTarifProfilUtilisateur
     * @codeCoverageIgnore
     */
    public function setTarif(Tarif $tarif = null)
    {
        $this->tarif = $tarif;

        return $this;
    }

    /**
     * Get tarif.
     *
     * @return null|Tarif
     * @codeCoverageIgnore
     */
    public function getTarif()
    {
        return $this->tarif;
    }

    /**
     * Set profil.
     *
     * @return MontantTarifProfilUtilisateur
     * @codeCoverageIgnore
     */
    public function setProfil(ProfilUtilisateur $profil = null)
    {
        $this->profil = $profil;

        return $this;
    }

    /**
     * Get profil.
     *
     * @return null|ProfilUtilisateur
     * @codeCoverageIgnore
     */
    public function getProfil()
    {
        return $this->profil;
    }
}