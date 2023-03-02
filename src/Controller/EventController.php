<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventCreateFormType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    #[Route('/dashboard/events', name: 'app_event')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $user = $this->getUser()->getUserIdentifier();

        $events = $doctrine->getRepository(Event::class)->getAllUserEvents($user);

        $isEmpty = 0;
        if (count($events) == 0) {
            $isEmpty = 1;
        }

        return $this->render('event/index.html.twig', [
            'user' => $user,
            'events' => $events,
            'isEmpty' => $isEmpty
        ]);
    }

    #[Route('/dashboard/events/edit/{id}', name: 'event_edit')]
    public function update(Request $request, ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $event = $entityManager->getRepository(Event::class)->find($id);

        $form = $this->createForm(EventCreateFormType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $event->setName($formData->getName());
            $event->setDueDate($formData->getDueDate());
            $event->setDescription($formData->getDescription());

            $entityManager->flush();

            return $this->redirectToRoute('app_event');
        }

        $eventType = 'editor';

        return $this->render('event_creator/index.html.twig', [
            'form' => $form,
            'eventType' => $eventType
        ]);
    }

    #[Route('/dashboard/events/{id}/delete', name: 'event_delete')]
    public function delete(Event $event, EntityManagerInterface $entityManager, Request $request): Response
    {
        $entityManager->remove($event);
        $entityManager->flush();

        return $this->redirectToRoute('app_event');
    }
}