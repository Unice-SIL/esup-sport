<?php

/*
 * classe - SiteMap
 *
 * Service gérant le sitemap (fil d'arianne)
*/

namespace App\Service\Common;

use App\Entity\Uca\FormatActivite;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Translation\TranslatorInterface;

class SiteMap
{
    public $authorizationChecker;
    private $translator;
    private $requestStack;
    private $router;
    private $em;
    private $sitemapOriginal;
    private $sitemapByRoute;

    public function __construct(string $projectDir, RequestStack $requestStack, RouterInterface $router, TranslatorInterface $translator, AuthorizationCheckerInterface $authorizationChecker,
    EntityManagerInterface $em)
    {
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->translator = $translator;
        $this->authorizationChecker = $authorizationChecker;
        $this->sitemapOriginal = Yaml::parseFile($projectDir.'/src/Resources/config/sitemap.yml');
        $this->sitemapByRoute = $this->getSitemapByRoute($this->sitemapOriginal);
        $this->em = $em;
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
        if (false !== strpos($currentRequest, 'UcaGest') || false !== strpos($currentRequest, 'lexik_translation_')) {
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
                if($this->isEntity($this->em, $currentRequest->get($param))){
                    $options[$key] = $currentRequest->get($param)->getId();
                }else{
                    $options[$key] = $currentRequest->get($param);
                }
            }
            $url = $this->router->generate($item['route'], $options);
        }

        return $url;
    }

    /**
     * @param EntityManager $em
     * @param string|object $class
     *
     * @return boolean
     */
    function isEntity($em, $class)
    {
        if(is_object($class)){
            $class = ClassUtils::getClass($class);
        }else{
            $class = '';
        }
        return ! $em->getMetadataFactory()->isTransient($class);
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
        $el->register('icon', function ($str) {
            return '<i class="'.$str.'"></i>';
        }, function ($arguments, $str) {
            return '<i class="'.$str.'"></i>';
        });
        $titre = $el->evaluate($instruction, $context);

        return strip_tags($titre, ['i']);
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
            $itemByRoute['droit'] = isset($itemOriginal['droit']) ? $itemOriginal['droit'] : '';
            $itemAriane = $byRoute;
            array_push($itemAriane, $itemByRoute);
            $itemByRoute['ariane'] = $itemAriane;
            if (!empty($routeName)) {
                $res[$routeName] = $itemByRoute;
            }
            if (isset($itemOriginal['items'])) {
                $itemDroit = [];
                foreach($itemOriginal['items'] as $item){
                    // Première option : la priorité est pour le parent dans sitemap
                    // $item['droit'] = $itemByRoute['droit'] ?: (isset($item['droit']) ? $item['droit']  : '');

                    // Deuxième option : la priorité est pour le droit de l'item et pas du parent
                    $item['droit'] =  isset($item['droit']) ? $item['droit']  : $itemByRoute['droit'];

                    $itemDroit[] = $item;
                }
                $res = $res + $this->getSitemapByRoute($itemDroit, $itemAriane);
            }
        }
        return $res;
    }
}
