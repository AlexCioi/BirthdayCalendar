<?php

namespace App\Service;

use HWI\Bundle\OAuthBundle\Connect\AccountConnectorInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class SocialConnector implements AccountConnectorInterface

{
    public function connect(UserInterface $user, UserResponseInterface $response)
    {
        // TODO: Implement connect() method.
    }
}