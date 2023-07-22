<?php

namespace App\EventSubscriber;

use GuzzleHttp\Client as GuzzleClient;
use Raygun4php\RaygunClient;
use Raygun4php\Transports\GuzzleSync;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Psr\Log\LoggerInterface;

class RaygunSubscriber implements EventSubscriberInterface
{
    private string $env;
    private RaygunClient $raygun;
    private LoggerInterface $logger;

    public function __construct(string $env, string $raygunApiKey, LoggerInterface $logger)
    {
        $this->env = $env;
        $httpClient = new GuzzleClient([
            'base_uri' => 'https://api.raygun.com',
            'headers' => ['X-ApiKey' => $raygunApiKey],
        ]);
        $transport = new GuzzleSync($httpClient);
        $this->raygun = new RaygunClient($transport);
        $this->logger = $logger;
    }

    public function onException(ExceptionEvent $event): void
    {
//        if ($this->env !== 'prod') {
//            return;
//        }

//        $exception = $event->getThrowable();
//        if ($exception instanceof NotFoundHttpException) {
//            return;
//        }

        $this->raygun->SendException($exception);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => [
                'onException',
            ],
        ];
    }
}
