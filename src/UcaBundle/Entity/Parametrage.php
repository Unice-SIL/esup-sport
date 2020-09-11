<?php

/*
 * Classe - Paramêtrage:
 *
 * Permet de règler les paramêtre globaux du site
 * Ces élements sont notament utilisée dans la génération de facture.
*/

namespace UcaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="parametrage")
 * @ORM\Entity
 * @Gedmo\Loggable
 */
class Parametrage
{
    //region Propriétés
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="parametrage.facebook.notblank")
     * @Assert\Url(message="parametrage.lien.noturl")
     */
    private $lienFacebook;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="parametrage.instagram.notblank")
     * @Assert\Url(message="parametrage.lien.noturl")
     */
    private $lienInstagram;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="parametrage.youtube.notblank")
     * @Assert\Url(message="parametrage.lien.noturl")
     */
    private $lienYoutube;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="parametrage.mailcontact.notblank")
     * @Assert\Email(message="parametrage.mailcontact.notemail")
     */
    private $mailContact;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="parametrage.timerpanier.notblank")
     * @Assert\Type(type="integer", message="parametrage.timer.type")
     * @Assert\GreaterThan(value="0", message="parametrage.timer.zero")
     */
    private $timerPanier;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="parametrage.timercb.notblank")
     * @Assert\Type(type="integer", message="parametrage.timer.type")
     * @Assert\Expression("this.getTimerCb() > this.getTimerPaybox()", message="message.parametrage.timercb.timerpaybox")
     * @Assert\GreaterThan(value="0", message="parametrage.timer.zero")
     */
    private $timerCb;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="parametrage.timerbds.notblank")
     * @Assert\Type(type="integer", message="parametrage.timer.type")
     * @Assert\GreaterThan(value="0", message="parametrage.timer.zero")
     */
    private $timerBds;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="parametrage.timerpaybox.notblank")
     * @Assert\Type(type="integer", message="parametrage.timer.type")
     * @Assert\Expression("this.getTimerCb() > this.getTimerPaybox()", message="message.parametrage.timerpaybox.timercb")
     * @Assert\GreaterThan(value="0", message="parametrage.timer.zero")
     */
    private $timerPaybox;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="parametrage.timerpanierapresvalidation.notblank")
     * @Assert\Type(type="integer", message="parametrage.timer.type")
     * @Assert\GreaterThan(value="0", message="parametrage.timer.zero")
     */
    private $timerPanierApresValidation;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="parametrage.anneeuniversitaire.notblank")
     * @Assert\Type(type="integer", message="parametrage.anneeuniversitaire.type")
     * @Assert\GreaterThan(value="2018", message="parametrage.anneeuniversitaire.greaterthan")
     */
    private $anneeUniversitaire;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="parametrage.libelleadresse.notblank")
     */
    private $libelleAdresse;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="parametrage.adressefacturation.notblank")
     */
    private $adresseFacturation;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="bigint")
     * @Assert\NotBlank(message="parametrage.siret.notblank")
     * @Assert\Type(type="integer", message="parametrage.siret.format")
     * @Assert\Length(min = 14, max = 14, minMessage = "parametrage.siret.format", maxMessage = "parametrage.siret.format")
     */
    private $siret;
    //endregion

    //region Méthodes
    //endregion

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
     * Set lienFacebook.
     *
     * @param string $lienFacebook
     *
     * @return Parametrage
     */
    public function setLienFacebook($lienFacebook)
    {
        $this->lienFacebook = $lienFacebook;

        return $this;
    }

    /**
     * Get lienFacebook.
     *
     * @return string
     */
    public function getLienFacebook()
    {
        return $this->lienFacebook;
    }

    /**
     * Set lienInstagram.
     *
     * @param string $lienInstagram
     *
     * @return Parametrage
     */
    public function setLienInstagram($lienInstagram)
    {
        $this->lienInstagram = $lienInstagram;

        return $this;
    }

    /**
     * Get lienInstagram.
     *
     * @return string
     */
    public function getLienInstagram()
    {
        return $this->lienInstagram;
    }

    /**
     * Set lienYoutube.
     *
     * @param string $lienYoutube
     *
     * @return Parametrage
     */
    public function setLienYoutube($lienYoutube)
    {
        $this->lienYoutube = $lienYoutube;

        return $this;
    }

    /**
     * Get lienYoutube.
     *
     * @return string
     */
    public function getLienYoutube()
    {
        return $this->lienYoutube;
    }

    /**
     * Set mailContact.
     *
     * @param string $mailContact
     *
     * @return Parametrage
     */
    public function setMailContact($mailContact)
    {
        $this->mailContact = $mailContact;

        return $this;
    }

    /**
     * Get mailContact.
     *
     * @return string
     */
    public function getMailContact()
    {
        return $this->mailContact;
    }

    /**
     * Set timerPanier.
     *
     * @param int $timerPanier
     *
     * @return Parametrage
     */
    public function setTimerPanier($timerPanier)
    {
        $this->timerPanier = $timerPanier;

        return $this;
    }

    /**
     * Get timerPanier.
     *
     * @return int
     */
    public function getTimerPanier()
    {
        return $this->timerPanier;
    }

    /**
     * Set timerCb.
     *
     * @param int $timerCb
     *
     * @return Parametrage
     */
    public function setTimerCb($timerCb)
    {
        $this->timerCb = $timerCb;

        return $this;
    }

    /**
     * Get timerCb.
     *
     * @return int
     */
    public function getTimerCb()
    {
        return $this->timerCb;
    }

    /**
     * Set timerBds.
     *
     * @param int $timerBds
     *
     * @return Parametrage
     */
    public function setTimerBds($timerBds)
    {
        $this->timerBds = $timerBds;

        return $this;
    }

    /**
     * Get timerBds.
     *
     * @return int
     */
    public function getTimerBds()
    {
        return $this->timerBds;
    }

    /**
     * Set timerPaybox.
     *
     * @param int $timerPaybox
     *
     * @return Parametrage
     */
    public function setTimerPaybox($timerPaybox)
    {
        $this->timerPaybox = $timerPaybox;

        return $this;
    }

    /**
     * Get timerPaybox.
     *
     * @return int
     */
    public function getTimerPaybox()
    {
        return $this->timerPaybox;
    }

    /**
     * Set timerPanierApresValidation.
     *
     * @param int $timerPanierApresValidation
     *
     * @return Parametrage
     */
    public function setTimerPanierApresValidation($timerPanierApresValidation)
    {
        $this->timerPanierApresValidation = $timerPanierApresValidation;

        return $this;
    }

    /**
     * Get timerPanierApresValidation.
     *
     * @return int
     */
    public function getTimerPanierApresValidation()
    {
        return $this->timerPanierApresValidation;
    }

    /**
     * Set anneeUniversitaire.
     *
     * @param int $anneeUniversitaire
     *
     * @return Parametrage
     */
    public function setAnneeUniversitaire($anneeUniversitaire)
    {
        $this->anneeUniversitaire = $anneeUniversitaire;

        return $this;
    }

    /**
     * Get anneeUniversitaire.
     *
     * @return int
     */
    public function getAnneeUniversitaire()
    {
        return $this->anneeUniversitaire;
    }

    /**
     * Set libelleAdresse.
     *
     * @param string $libelleAdresse
     *
     * @return Parametrage
     */
    public function setLibelleAdresse($libelleAdresse)
    {
        $this->libelleAdresse = $libelleAdresse;

        return $this;
    }

    /**
     * Get libelleAdresse.
     *
     * @return string
     */
    public function getLibelleAdresse()
    {
        return $this->libelleAdresse;
    }

    /**
     * Set adresseFacturation.
     *
     * @param string $adresseFacturation
     *
     * @return Parametrage
     */
    public function setAdresseFacturation($adresseFacturation)
    {
        $this->adresseFacturation = $adresseFacturation;

        return $this;
    }

    /**
     * Get adresseFacturation.
     *
     * @return string
     */
    public function getAdresseFacturation()
    {
        return $this->adresseFacturation;
    }

    /**
     * Set siret.
     *
     * @param int $siret
     *
     * @return Parametrage
     */
    public function setSiret($siret)
    {
        $this->siret = $siret;

        return $this;
    }

    /**
     * Get siret.
     *
     * @return int
     */
    public function getSiret()
    {
        return $this->siret;
    }
}
