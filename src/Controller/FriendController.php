<?php

namespace App\Controller;

use App\Entity\Friend;
use App\Form\FriendFormType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FriendController extends AbstractController
{
    #[Route('/dashboard/friends', name: 'app_friends')]
    public function read(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $user = $this->getUser()->getUserIdentifier();

        $friends = $doctrine->getRepository(Friend::class)->getAllUserFriends($user);

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
    public function update(Request $request, ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $friend = $entityManager->getRepository(Friend::class)->find($id);

        $form = $this->createForm(FriendFormType::class, $friend);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $friend->setFirstName($formData->getFirstName());
            $friend->setLastName($formData->getLastName());
            $friend->setBirthDate($formData->getBirthDate());
            $friend->setPhoneNumber($formData->getPhoneNumber());
            $friend->setEmail($formData->getEmail());

            $entityManager->flush();

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
