<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountController extends AbstractController
{
    #[Route('dashboard/account', name: 'app_account')]
    public function accountOverview(): Response
    {
        $user = $this->getUser();
        $googleId = $user->getGoogleID();

        $accountCreatedWithGoogle = true;
        if ($user->getPassword() !== null) {
            $accountCreatedWithGoogle = null;
        }

        return $this->render('account/index.html.twig', [
            'user' => $user->getUserIdentifier(),
            'googleId' => $googleId,
            'accountCreatedWithGoogle' => $accountCreatedWithGoogle,
        ]);
    }

    #[Route('dashboard/account/remove-link', name: 'account_disconnect')]
    public function removeConnection(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if ($user->getPassword() !== null) {
            $user->setGoogleID(null);
            $em->flush();

            return $this->redirectToRoute('app_account');
        } else {
            $key = '#'.strval($user->getId()).'#';
            $user->setGoogleID($key.$user->getGoogleId());
            $user->setUsername(str_replace('@', $key, $user->getUsername()));
            $user->setEmail($user->getUsername());
            $em->flush();

            return $this->redirectToRoute('app_logout');
        }


    }
}