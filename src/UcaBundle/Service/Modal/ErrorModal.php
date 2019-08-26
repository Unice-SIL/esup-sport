<?php 
namespace UcaBundle\Service\Modal;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Yaml\Yaml;

class ErrorModal{

    private $title;
    private $message;
    private $translator;

    function __construct($translator = null, $title = null, $message = null)
    {
        $this->translator = $translator;
        $this->title = is_null($title) ? "Error" : $title;
        $this->message = is_null($message) ? "An error occured" : $message;
    }

    public function getTitle(){
        return $this->translator->trans($this->title);
    }

    public function getMessage(){
        return $this->translator->trans($this->message);
    }
}