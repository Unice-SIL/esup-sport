<?php

/*
 * classe - SelectionProfil
 *
 * Service gérant la selection des profils sur la page d'acceuil
*/

namespace App\Service\Service;

use Sensio\Bundle\FrameworkExtraBundle\Security\ExpressionLanguage;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Translation\TranslatorInterface;

class SelectionProfil
{
    private $translator;
    private $router;
    private $fileDatas;

    /**
     * @codeCoverageIgnore
     */
    public function __construct(string $projectDir, RouterInterface $router, TranslatorInterface $translator)
    {
        $this->router = $router;
        $this->translator = $translator;
        $this->fileDatas = Yaml::parseFile($projectDir.'/src/Resources/config/SelectionProfil.yml');
    }

    public function getTitre($item, $context = [])
    {
        $el = new ExpressionLanguage();
        $el->register(
            'trans',
            function ($data) {
                //@codeCoverageIgnoreStart
                return $this->translator->trans($data);
                //@codeCoverageIgnoreEnd
            },
            function ($arguments, $data) {
                //@codeCoverageIgnoreStart
                return $this->translator->trans($data);
                //@codeCoverageIgnoreEnd
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
