<?php

namespace App\Form\Handler;

use HWI\Bundle\OAuthBundle\Form\RegistrationFormHandlerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class SocialRegistrationFormHandler implements RegistrationFormHandlerInterface
{
    public function process(Request $request, FormInterface $form, UserResponseInterface $userInformation): bool
    {
        dump($userInformation); exit();

        return false;
    }
}