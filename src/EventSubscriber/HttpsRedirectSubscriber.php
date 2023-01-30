<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class HttpsRedirectSubscriber implements EventSubscriberInterface
{
    public function onKernelRequest(RequestEvent $event): void
    {
        if (getenv('APP_ENV') !== 'prod') {
            return;
        }

        $req = $event->getRequest();
        if ($req->headers->get('x-forwarded-proto', '') === 'https') {
            return;
        }

        $redirectUrl = 'https://' . $req->getHttpHost() . $req->getRequestUri();
        $event->setResponse(new RedirectResponse($redirectUrl));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}
