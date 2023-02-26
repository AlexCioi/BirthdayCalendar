<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\RegisterType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    protected $user;
    #[Route('/user/registration', name:'user_register')]
    public function register(Request $request): Response
    {
        $this->user = new User();
        $this->user->setRoles(['user']);
        $form = $this->createForm(RegisterType::class, $this->user);

        $form->handleRequest($request);

        $this->user = $form->getData();

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute('/user/registration/submit');
        }

        return $this->render('registration/registration.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/user/registration/submit', name: 'registration_submit')]
    public function storeUser(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();

        $entityManager->persist($this->user);
        $entityManager->flush();

        return new Response('Saved new user with id '. $this->user->getId());
    }
}