<?php

namespace App\EventSubscriber;

use HWI\Bundle\OAuthBundle\Event\FilterUserResponseEvent;
use HWI\Bundle\OAuthBundle\HWIOAuthEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class EventSubscriber implements EventSubscriberInterface
{
    private $session;

    public function __construct(\SessionHandlerInterface $session)
    {
        $this->session = $session;
    }

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
            }
        }

        $url = 'https://www.googleapis.com/oauth2/v1/userinfo';

        // Set the headers for the API request
        $headers = array(
            'Authorization: Bearer ' . $accessToken
        );

        // Send a GET request to the API
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($curl);
        curl_close($curl);

        // Parse the response JSON and retrieve the user ID
        $user_data = json_decode($response, true);
        $user_id = $user_data['id'];

        $this->session->write('googleId', $user_id);

        $redirectResponse = new RedirectResponse('/dashboard/google-connect');
        $event->setResponse($redirectResponse);
    }
}