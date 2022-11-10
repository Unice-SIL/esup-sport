<?php

/*
 * classe - ErrorModal
 *
 * Service gérant l'affichage du modal d'erreur
*/

namespace App\Service\Modal;

class ErrorModal
{
    private $title;
    private $message;
    private $translator;

    public function __construct($translator = null, $title = null, $message = null)
    {
        $this->translator = $translator;
        $this->title = is_null($title) ? 'Error' : $title;
        $this->message = is_null($message) ? 'An error occured' : $message;
    }

    public function getTitle()
    {
        return $this->translator->trans($this->title);
    }

    public function getMessage()
    {
        return $this->translator->trans($this->message);
    }
}
