<?php

/*
 * classe - SiteMap
 *
 * Service gérant le sitemap (fil d'arianne)
*/

namespace UcaBundle\Service\Common;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Yaml\Yaml;

class SiteMap
{
    public $authorizationChecker;
    private $translator;
    private $requestStack;
    private $router;

    private $sitemapOriginal;
    private $sitemapByRoute;

    public function __construct($rootDir, $requestStack, $router, $translator, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->translator = $translator;
        $this->authorizationChecker = $authorizationChecker;
        $this->sitemapOriginal = Yaml::parseFile($rootDir.'/../src/UcaBundle/Resources/config/sitemap.yml');
        $this->sitemapByRoute = $this->getSitemapByRoute($this->sitemapOriginal);
    }

    public function get()
    {
        return $this->sitemapByRoute;
    }

    public function clean($table, $level)
    {
        if (isset($table['items'])) {
            if (0 == $level) {
                unset($table['items']);
            } else {
                foreach ($table['items'] as $k => $v) {
                    if ((!isset($v['menu']) || 0 != $v['menu']) && (!isset($v['droit']) || $this->authorizationChecker->isGranted($v['droit']))) {
                        $table['items'][$k] = $this->clean($v, $level - 1);
                    } else {
                        unset($table['items'][$k]);
                    }
                }
            }
        }

        return $table;
    }

    public function getCurrentMenu()
    {
        $currentRequest = $this->requestStack->getCurrentRequest()->get('_route');
        $menuName = 'UcaWeb_Accueil';
        if (false !== strpos($currentRequest, 'UcaGest')) {
            $menuName = 'UcaGest_Accueil';
        }
        $key = array_search($menuName, array_column($this->sitemapOriginal, 'route'));
        $res = $this->sitemapOriginal[$key];

        return $this->clean($res, $res['menuLevel']);
    }

    public function getAriane()
    {
        $currentRoute = $this->requestStack->getCurrentRequest()->get('_route');

        return isset($this->sitemapByRoute[$currentRoute]) ? $this->sitemapByRoute[$currentRoute]['ariane'] : null;
    }

    public function isCurrentAncestor($route)
    {
        if (!$this->requestStack->getCurrentRequest()->get('_route')) {
            return false;
        }
        $currentRoute = $this->requestStack->getCurrentRequest()->get('_route');
        $ariane = isset($this->sitemapByRoute[$currentRoute]) ? $this->sitemapByRoute[$currentRoute]['ariane'] : null;
        if (empty($ariane)) {
            return false;
        }
        $ancestor = array_filter($ariane, function ($value) use ($route) {
            return $value['route'] == $route;
        });

        return !empty($ancestor);
    }

    public function getUrl($item)
    {
        $url = null;
        if (!empty($item['route'])) {
            $currentRequest = $this->requestStack->getCurrentRequest();
            $options = ['_locale' => $currentRequest->get('_locale')];
            foreach ($item['params'] as $key => $param) {
                $options[$key] = $currentRequest->get($param);
            }
            $url = $this->router->generate($item['route'], $options);
        }

        return $url;
    }

    public function getTitre($itemAriane, $context = [])
    {
        $instruction = $itemAriane['titre'];
        $el = new ExpressionLanguage();
        $el->register('trans', function ($str) {
            return $this->translator->trans($str);
        }, function ($arguments, $str) {
            return $this->translator->trans($str);
        });
        $titre = $el->evaluate($instruction, $context);

        return strip_tags($titre);
    }

    public function getSitemapOriginal()
    {
        return $this->sitemapOriginal;
    }

    private function getSitemapByRoute($original, $byRoute = [])
    {
        $res = [];
        foreach ($original as $itemOriginal) {
            $routeName = $itemOriginal['route'];
            $itemByRoute['titre'] = $itemOriginal['titre'];
            $itemByRoute['route'] = $itemOriginal['route'];
            $itemByRoute['params'] = isset($itemOriginal['params']) ? $itemOriginal['params'] : [];
            $itemAriane = $byRoute;
            array_push($itemAriane, $itemByRoute);
            $itemByRoute['ariane'] = $itemAriane;
            if (!empty($routeName)) {
                $res[$routeName] = $itemByRoute;
            }
            if (isset($itemOriginal['items'])) {
                $res = $res + $this->getSitemapByRoute($itemOriginal['items'], $itemAriane);
            }
        }

        return $res;
    }
}
