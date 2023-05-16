<?php

namespace App\Controller;

use App\Service\EventManager;
use App\Service\FriendManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountController extends AbstractController
{
    #[Route('dashboard/account', name: 'app_account')]
    public function accountOverview(FriendManager $friendManager, EventManager $eventManager): Response
    {
        $user = $this->getUser();
        $googleId = $user->getGoogleID();

        $accountCreatedWithGoogle = true;
        if ($user->getPassword() !== null) {
            $accountCreatedWithGoogle = null;
        }

        $friends = $friendManager->getUserFriends($this->getUser()->getUserIdentifier());
        $events = $eventManager->getUserEvents($this->getUser()->getUserIdentifier(), 'upcoming');

        return $this->render('account/index.html.twig', [
            'user' => $user->getUserIdentifier(),
            'friends' => $friends,
            'events' => $events,
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