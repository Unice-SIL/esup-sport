<?php

/*
 * classe - SelectionProfil
 *
 * Service gérant la selection des profils sur la page d'acceuil
*/

namespace UcaBundle\Service\Service;

use Sensio\Bundle\FrameworkExtraBundle\Security\ExpressionLanguage;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Yaml\Yaml;

class SelectionProfil
{
    private $translator;
    private $router;
    private $fileDatas;

    public function __construct($rootDir, RouterInterface $router, TranslatorInterface $translator)
    {
        $this->router = $router;
        $this->translator = $translator;
        $this->fileDatas = Yaml::parseFile($rootDir.'/../src/UcaBundle/Resources/config/SelectionProfil.yml');
    }

    public function getTitre($item, $context = [])
    {
        $el = new ExpressionLanguage();
        $el->register(
            'trans',
            function ($data) {
                return $this->translator->trans($data);
            },
            function ($arguments, $data) {
                return $this->translator->trans($data);
            }
        );

        return strip_tags($el->evaluate($item['titre'], $context));
    }

    public function getUrl($item)
    {
        foreach ($this->fileDatas as $section) {
            if ($section['shibboleth'] === $item['shibboleth']) {
                $url = $this->router->generate($section['route']);
            }
        }

        return isset($url) ? $url : false;
    }

    public function getFileDatas()
    {
        return $this->fileDatas;
    }
}
