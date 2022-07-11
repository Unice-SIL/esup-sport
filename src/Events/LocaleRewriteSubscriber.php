<?php

namespace App\Events;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleRewriteSubscriber implements EventSubscriberInterface
{
    private $defaultLocale;
    private $supportedLocales;

    /**
     * $defaultLocale and $supportedLocales injected from services.yaml.
     */
    public function __construct(string $defaultLocale, string $supportedLocales)
    {
        $this->defaultLocale = $defaultLocale;
        $this->supportedLocales = explode('|', $supportedLocales);
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $oldUrl = $request->getPathInfo();
        $exploded = explode('/', $oldUrl);
        $locale = $this->defaultLocale;
        $newUrl = null;

        if (false === strpos($oldUrl, '_profiler') && false === strpos($oldUrl, '_wdt') && false === strpos($oldUrl, 'media/cache') && false === strpos($oldUrl, '_gcb/generate-captcha/_captcha_captcha')) {
            if (!in_array($exploded[1], $this->supportedLocales)) {  // If no prefix or prefix not found in supported locales
                $newUrl = '/'.$locale.$oldUrl;
            }

            if ($newUrl) {
                $event->setResponse(new RedirectResponse($request->getBaseUrl().$newUrl));
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            // must be registered before the default Locale listener
            KernelEvents::REQUEST => [['onKernelRequest', 101]],
        ];
    }
}
