<?php

namespace App\EventSubscriber;

use HWI\Bundle\OAuthBundle\Event\FilterUserResponseEvent;
use HWI\Bundle\OAuthBundle\HWIOAuthEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        // return the subscribed events, their methods and priorities
        return [
            HWIOAuthEvents::CONNECT_COMPLETED => [
                ['processEvent', 0],
            ],
        ];
    }

    public function processEvent(FilterUserResponseEvent $event)
    {
        $session = $event->getRequest()->getSession();

        $sessionKey = '_hwi_oauth.connect_confirmation.';
        foreach($_SESSION['_sf2_attributes'] as $key => $value) {
            if(strpos($key, $sessionKey) === 0) {
                $accessToken = $value['access_token'];
                break;
            }
        }

        dd($accessToken);
    }
}