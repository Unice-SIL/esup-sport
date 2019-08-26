<?php

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class MontantTarifProfilUtilisateur
{
    #region Propriétés
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

    /** @ORM\ManyToOne(targetEntity="Tarif", inversedBy="montants") */
    protected $tarif;

    /** @ORM\ManyToOne(targetEntity="ProfilUtilisateur", inversedBy="montants") */
    protected $profil;
    #endregion

    #region Méthodes

    public function __construct($tarif, $profil, $montant)
    {
        $this->tarif = $tarif;
        $this->profil = $profil;
        $this->montant = $montant;
    }
    
    public function setMontant($montant)
    {
        if ($montant != $this->montant || $this->montant == null) {
            $p = $this->getProfil();
            $t = $this->getTarif();
            $t->setModificationMontants($t->getModificationMontants() . ' / ' . $p->getLibelle() . ':' . $montant . ' €');
        }
        $this->montant = $montant;

        return $this;
    }
    #endregion

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
     * Get montant.
     *
     * @return string
     */
    public function getMontant()
    {
        return $this->montant;
    }

    /**
     * Set tarif.
     *
     * @param \UcaBundle\Entity\Tarif|null $tarif
     *
     * @return MontantTarifProfilUtilisateur
     */
    public function setTarif(\UcaBundle\Entity\Tarif $tarif = null)
    {
        $this->tarif = $tarif;

        return $this;
    }

    /**
     * Get tarif.
     *
     * @return \UcaBundle\Entity\Tarif|null
     */
    public function getTarif()
    {
        return $this->tarif;
    }

    /**
     * Set profil.
     *
     * @param \UcaBundle\Entity\ProfilUtilisateur|null $profil
     *
     * @return MontantTarifProfilUtilisateur
     */
    public function setProfil(\UcaBundle\Entity\ProfilUtilisateur $profil = null)
    {
        $this->profil = $profil;

        return $this;
    }

    /**
     * Get profil.
     *
     * @return \UcaBundle\Entity\ProfilUtilisateur|null
     */
    public function getProfil()
    {
        return $this->profil;
    }
}
