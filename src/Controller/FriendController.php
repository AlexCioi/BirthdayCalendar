<?php

namespace App\Controller;

use App\Entity\Friend;
use App\Form\FriendFormType;
use App\Helpers\NotificationDateCalculator;
use App\Service\FriendManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function PHPUnit\Framework\throwException;

class FriendController extends AbstractController
{
    #[Route('/dashboard/friend/register', name: 'friend_register')]
    public function create(Request $request, FriendManager $friendManager): Response
    {
        $friend = new Friend();

        $form = $this->createForm(FriendFormType::class, $friend);
        $form->handleRequest($request);

        if ($friendManager->processFriendForm($form, $this->getUser()->getUserIdentifier(), $friend, 'create')) {
            return $this->redirectToRoute('app_friends');
        }

        $friendType = 'register';

        return $this->render('friend_creator/index.html.twig', [
            'form' => $form,
            'friendType' => $friendType,
        ]);
    }

    #[Route('/dashboard/friends', name: 'app_friends')]
    public function read(FriendManager $friendManager): Response
    {
        if ($this->getUser() !== NULL) {
            $user = $this->getUser()->getUserIdentifier();
        } else {
            return $this->redirectToRoute('app_login');
        }

        $friends = $friendManager->getUserFriends($user);

        $isEmpty = 0;
        if (count($friends) == 0) {
            $isEmpty = 1;
        }

        return $this->render('friend/index.html.twig', [
            'user' => $user,
            'isEmpty' => $isEmpty,
            'friends' => $friends
        ]);
    }

    #[Route('/dashboard/friends/{id}/edit', name: 'friend_edit')]
    public function update(Request $request, ManagerRegistry $doctrine, FriendManager $friendManager, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $friend = $entityManager->getRepository(Friend::class)->find($id);

        $form = $this->createForm(FriendFormType::class, $friend);
        $form->handleRequest($request);

        if ($friendManager->processFriendForm($form, $this->getUser()->getUserIdentifier(), $friend, 'update')) {
            return $this->redirectToRoute('app_friends');
        }

        $friendType = 'editor';

        return $this->render('friend_creator/index.html.twig', [
            'form' => $form,
            'friendType' => $friendType
        ]);
    }

    #[Route('/dashboard/friends/{id}/delete', name: 'friend_delete')]
    public function delete(Friend $friend, EntityManagerInterface $entityManager, Request $request): Response
    {
        $entityManager->remove($friend);
        $entityManager->flush();

        return $this->redirectToRoute('app_friends');
    }
}
