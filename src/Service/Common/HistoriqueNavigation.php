<?php

/*
 * classe - HistoriqueNavigation
 *
 * Service gérant l'historique de navigation (bouton retour)
*/

namespace App\Service\Common;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;

class HistoriqueNavigation
{
    public static $OBJECT;
    public static $ROUTER;
    public static $DEBUG;

    public function __construct(RouterInterface $router)
    {
        self::$ROUTER = $router;
    }

    public static function getAll()
    {
        return self::$OBJECT;
    }

    public static function getLast($nb)
    {
        return array_slice(self::$OBJECT, -$nb, $nb + 1, true);
    }

    public static function getUrlHistoriqueByIndex($index)
    {
        return array_slice(self::$OBJECT, $index, 1)[0];
    }

    public static function setModeDebug($request)
    {
        self::$DEBUG = $request->get('historiqueNavigationDebug');
        if (in_array(self::$DEBUG, ['on', 'off'])) {
            $request->getSession()->set('historiqueNavigationDebug', self::$DEBUG);
        }
        self::$DEBUG = $request->getSession()->get('historiqueNavigationDebug', 'off');
    }

    public static function urlInExcludeList($url)
    {
        $array_search = [
            '/\/UcaWeb\/Paiement\/Recapitulatif/',
            '/\/UcaWeb\/Paiement\/Retour/',
            '/\/UcaWeb\/Paiement\/Validation/',
            '/\/media\/cache\/resolve/',
            '/historiqueNavigationDebug=o/',
            '/urlHistory=clear/',

            // Gestion des inscriptions
            '/\/UcaWeb\/\d+\/AjoutPanier/',
            '/\/UcaWeb\/\d+\/Annuler/',
            '/\/UcaWeb\/\d+\/SeDesinscrire/',

            // Gestion du panier
            '/\/UcaWeb\/Panier/',
            '/\/UcaWeb\/SuppressionArticle\/\d+/',
            '/\/UcaWeb\/SuppressionToutArticle\/\d+/',

            // Captcha
            '/\/_gcb/',

        ];
        foreach ($array_search as $search) {
            if (1 === \preg_match($search, $url)) {
                return true;
            }
        }

        return false;
    }

    public static function updateHistorique($request)
    {
        self::$OBJECT = $request->getSession()->get('HistoriqueNavigation', [self::$ROUTER->generate('UcaWeb_Accueil')]);
        $currentUrl = $request->getRequestUri();
        $previousUrl = self::getUrlHistoriqueByIndex(-1);
        if ($request->isXmlHttpRequest() || $request->isMethod('POST')) {
            // On exclu ces types de requêtes de l'historique.
        } elseif (null != $request->query->get('urlHistory')) {
            $index = -$request->query->get('urlHistory');
            $historiqueUrl = self::getUrlRetour($index);
            if ($historiqueUrl == $currentUrl) {
                for ($i = 1; $i <= $index; ++$i) {
                    array_pop(self::$OBJECT);
                }
            }
        } elseif (self::urlInExcludeList($currentUrl)) {
            // On exclu ces Url de l'historique de navigation.
        } elseif ($currentUrl != $previousUrl) {
            self::$OBJECT[] = $currentUrl;
        }
        $request->getSession()->set('HistoriqueNavigation', self::$OBJECT);
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        if ('clear' == $request->query->get('urlHistory')) {
            $request->getSession()->remove('HistoriqueNavigation');
        }
        self::setModeDebug($request);
        self::updateHistorique($request);

        // dump($request);
        // dump(self::$OBJECT);
        // dump(self::getUrlRetour());
        // die;
    }

    public static function getUrlRetour($index = 1)
    {
        if ($index > 1 && count(self::$OBJECT) <= $index) {
            return false;
        }
        $previousUrl = self::getUrlHistoriqueByIndex(-($index + 1));
        $previousUrl .= false !== strpos($previousUrl, '?') ? '&' : '?';
        $previousUrl .= 'urlHistory=-'.$index;

        return $previousUrl;
    }

    public function debug()
    {
        return 'on' == self::$DEBUG;
    }
}
