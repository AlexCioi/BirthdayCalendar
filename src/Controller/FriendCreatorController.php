<?php

namespace App\Controller;

use App\Entity\Friend;
use App\Form\FriendFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FriendCreatorController extends AbstractController
{
    #[Route('/dashboard/friend/register', name: 'friend_register')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $friend = new Friend();

        $form = $this->createForm(FriendFormType::class, $friend);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $friend->setUser($this->getUser()->getUserIdentifier());

            $formData = $form->getData();

            $friend->setFirstName($formData->getFirstName());
            $friend->setLastName($formData->getLastName());
            $friend->setBirthDate($formData->getBirthDate());
            $friend->setPhoneNumber($formData->getPhoneNumber());
            $friend->setEmail($formData->getEmail());
            $friend->setNotificationOffset($formData->getNotificationOffset());

            $entityManager->persist($friend);
            $entityManager->flush();

            return $this->redirectToRoute('app_friends');
        }

        $friendType = 'register';

        return $this->render('friend_creator/index.html.twig', [
            'form' => $form,
            'friendType' => $friendType
        ]);
    }
}