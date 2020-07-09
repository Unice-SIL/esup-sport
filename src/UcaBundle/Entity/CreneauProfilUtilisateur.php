<?php

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @Gedmo\Loggable
 * @UniqueEntity(fields={"creneau","profilUtilisateur"}, message="creneau.profilutilisateur.uniqueentity")
 */
class CreneauProfilUtilisateur implements \UcaBundle\Entity\Interfaces\JsonSerializable
{
    use \UcaBundle\Entity\Traits\JsonSerializable;

    /**
     * @ORM\ManyToOne(targetEntity="Creneau" , inversedBy="profilsUtilisateurs")
     * @Assert\NotBlank(message="creneau.notblank")
     */
    protected $creneau;

    /**
     * @ORM\ManyToOne(targetEntity="ProfilUtilisateur" , inversedBy="creneaux")
     * @Assert\NotBlank(message="complement.profilsutilisateurs.notblank")
     */
    protected $profilUtilisateur;

    /** @ORM\Column(type="integer", nullable=true, options={"default":0})*/
    protected $capaciteProfil;
    // region propriétés

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    // endregion

    // region Methodes
    public function __construct(Creneau $creneau, ProfilUtilisateur $profil, $capacite)
    {
        $this->capaciteProfil = $capacite ? $capacite : 0;
        $this->profilUtilisateur = $profil;
        $this->creneau = $creneau;
    }

    public function jsonSerializeProperties()
    {
        return ['creneau', 'profilUtilisateur', 'capaciteProfil'];
    }

    // endregion

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
     * Set creneau.
     *
     * @param null|\UcaBundle\Entity\Creneau $creneau
     *
     * @return CreneauProfilUtilisateur
     */
    public function setCreneau(Creneau $creneau = null)
    {
        $this->creneau = $creneau;

        return $this;
    }

    /**
     * Get creneau.
     *
     * @return null|\UcaBundle\Entity\Creneau
     */
    public function getCreneau()
    {
        return $this->creneau;
    }

    /**
     * Set profilUtilisateur.
     *
     * @param null|\UcaBundle\Entity\ProfilUtilisateur $profilUtilisateur
     *
     * @return CreneauProfilUtilisateur
     */
    public function setProfilUtilisateur(ProfilUtilisateur $profilUtilisateur = null)
    {
        $this->profilUtilisateur = $profilUtilisateur;

        return $this;
    }

    /**
     * Get profilUtilisateur.
     *
     * @return null|\UcaBundle\Entity\ProfilUtilisateur
     */
    public function getProfilUtilisateur()
    {
        return $this->profilUtilisateur;
    }

    /**
     * Set capaciteProfil.
     *
     * @param null|mixed $capaciteProfil
     *
     * @return CreneauProfilUtilisateur
     */
    public function setCapaciteProfil($capaciteProfil = null)
    {
        $this->capaciteProfil = $capaciteProfil;

        return $this;
    }

    /**
     * Get capaciteProfil.
     *
     * @return null|int
     */
    public function getCapaciteProfil()
    {
        return $this->capaciteProfil;
    }
}
