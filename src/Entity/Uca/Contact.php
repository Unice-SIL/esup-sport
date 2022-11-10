<?php

/*
 * Classe - Contact:
 *
 * Contrôle le formulaire de contact.
*/

namespace App\Entity\Uca;

use Symfony\Component\Validator\Constraints as Assert;

class Contact
{
    //region Propriétés

    /** @Assert\NotBlank(message="contact.email.notblank")
     * @Assert\Email(message="contact.email.invalide") */
    private $email;
    /** @Assert\NotBlank(message="contact.objet.notblank") */
    private $objet;
    /** @Assert\NotBlank(message="contact.message.notblank") */
    private $message;

    //endregion

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return Contact
     * @codeCoverageIgnore
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Set objet.
     *
     * @param string $objet
     *
     * @return Contact
     * @codeCoverageIgnore
     */
    public function setObjet($objet)
    {
        $this->objet = $objet;

        return $this;
    }

    /**
     * Set message.
     *
     * @param string $message
     *
     * @return Contact
     * @codeCoverageIgnore
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getObjet()
    {
        return $this->objet;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getMessage()
    {
        return $this->message;
    }
}