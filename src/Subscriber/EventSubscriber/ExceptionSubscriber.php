<?php

namespace App\Subscriber\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'processException',
        ];
    }

    public function processException(ExceptionEvent $event)
    {
        //$exception = $event->getThrowable();

        $html = '<html><body><h1> Kernel exception listened successfully </h1></body></html>';

        $response = new \Symfony\Component\HttpFoundation\Response($html);
        $response->headers->set('Content-Type', 'text/html');

        $event->setResponse($response);
    }
}