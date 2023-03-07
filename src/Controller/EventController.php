<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Friend;
use App\Form\EventFormType;
use App\Helpers\EventFetcher;
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
        $eventFetcher = new EventFetcher();
        $user = $this->getUser()->getUserIdentifier();

        $repo = $doctrine->getRepository(Event::class);
        $qb = $repo->getQb();
        $repo->getAllUserEvents($qb, $user);
        $events = $qb->getQuery()->getResult();

        $repo->getUserPastEvents($qb, $user);
        $pastEvents = $qb->getQuery()->getResult();

        $repo = $doctrine->getRepository(Friend::class);
        $qb = $repo->getQb();
        $repo->getAllUserFriends($qb, $user);
        $friends = $qb->getQuery()->getResult();

        $all = $eventFetcher->fetchAllEvents($doctrine, $user);

        $isEmptyEvents = 0;
        if (count($events) == 0) {
            $isEmptyEvents = 1;
        }

        $isEmptyFriends = 0;
        if (count($friends) == 0) {
            $isEmptyFriends = 1;
        }

        $isEmptyPastEvents = 0;
        if (count($pastEvents) == 0) {
            $isEmptyPastEvents = 1;
        }

        return $this->render('event/index.html.twig', [
            'user' => $user,
            'events' => $events,
            'friends' => $friends,
            'all' => $all,
            'pastEvents' => $pastEvents,
            'isEmptyEvents' => $isEmptyEvents,
            'isEmptyPastEvents' => $isEmptyPastEvents,
            'isEmptyBirthdays' => $isEmptyFriends
        ]);
    }

    #[Route('/dashboard/events/edit/{id}', name: 'event_edit')]
    public function update(Request $request, ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $event = $entityManager->getRepository(Event::class)->find($id);

        $form = $this->createForm(EventFormType::class, $event);
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