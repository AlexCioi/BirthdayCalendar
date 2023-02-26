<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\LoginType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LoginController extends AbstractController
{
    #[Route('/user/login', name: 'user_login')]
    public function login(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(LoginType::class, $user);

        $form->handleRequest($request);

        return $this->render('login/login.html.twig', [
            'form' => $form,
            'var' => $form->getData()
        ]);
    }
}
