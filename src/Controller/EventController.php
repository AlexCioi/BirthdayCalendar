<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventFormType;
use App\Service\EventManager;
use App\Service\FriendManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    #[Route('/dashboard/events/creator', name: 'app_event_creator')]
    public function create(Request $request, EventManager $eventManager): Response
    {
        $event = new Event();
        $user = $this->getUser()->getUserIdentifier();

        $form = $this->createForm(EventFormType::class, $event);
        $form->handleRequest($request);

        if ($eventManager->processEventForm($form, $user, $event, 'create')) {
            return $this->redirectToRoute('app_event');
        }

        $eventType = 'creator';

        return $this->render('event_creator/index.html.twig', [
            'form' => $form,
            'eventType' => $eventType
        ]);
    }

    #[Route('/dashboard/events', name: 'app_event')]
    public function read(ManagerRegistry $doctrine, EventManager $eventManager, FriendManager $friendManager): Response
    {
        if ($this->getUser() !== NULL) {
            $user = $this->getUser()->getUserIdentifier();
        } else {
            return $this->redirectToRoute('app_login');
        }

        $upcomingEvents = $eventManager->getUserEvents($user, 'upcoming');
        $pastEvents = $eventManager->getUserEvents($user, 'past');
        $friends = $friendManager->getUpcomingBirthdays($user);
        $all = $eventManager->getUserEventsAndBirthdays($user);

        $isEmptyEvents = 0;
        if (count($upcomingEvents) == 0) { $isEmptyEvents = 1; }

        $isEmptyFriends = 0;
        if (count($friends) == 0) { $isEmptyFriends = 1; }

        $isEmptyPastEvents = 0;
        if (count($pastEvents) == 0) { $isEmptyPastEvents = 1; }

        return $this->render('event/index.html.twig', [
            'user' => $user,
            'events' => $upcomingEvents,
            'friends' => $friends,
            'all' => $all,
            'pastEvents' => $pastEvents,
            'isEmptyEvents' => $isEmptyEvents,
            'isEmptyPastEvents' => $isEmptyPastEvents,
            'isEmptyBirthdays' => $isEmptyFriends
        ]);
    }

    #[Route('/dashboard/events/edit/{id}', name: 'event_edit')]
    public function update(Request $request, ManagerRegistry $doctrine, EventManager $eventManager, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $event = $entityManager->getRepository(Event::class)->find($id);

        $form = $this->createForm(EventFormType::class, $event);
        $form->handleRequest($request);

        if ($eventManager->processEventForm($form, '', $event, 'update')) {
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