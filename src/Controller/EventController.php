<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Friend;
use App\Form\EventFormType;
use App\Helpers\CustomSorter;
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
        $sorter = new CustomSorter();

        $events = $doctrine->getRepository(Event::class)->getAllUserEvents($user);
        $friends = $doctrine->getRepository(Friend::class)->getAllUserFriends($user);

        $localTime = new \DateTime('now');
        $year = $localTime->format('Y');

        foreach ($friends as $friend) {
            $birthday = $friend->getBirthDate();
            date_date_set($birthday, intval($year), date_format($birthday, 'm'), date_format($birthday, 'd'));
        }

        foreach ($friends as $friend) {
            if ($localTime > $friend->getBirthDate()) {
                array_shift($friends);
            }
        }

        $adapter = [];
        foreach ($friends as $friend) {
            $friendEvent = new Event();
            $friendEvent->setUser($friend->getId());
            $friendEvent->setName($friend->getFirstName().' '.$friend->getLastName()."'s birthday!");
            $friendEvent->setDescription(null);
            $friendEvent->setDueDate($friend->getBirthDate());
            $adapter[] = $friendEvent;
        }

        $all = array_merge($events, $adapter);
        $all = $sorter->sortEventsByDate($all);

        $isEmptyEvents = 0;
        if (count($events) == 0) {
            $isEmptyEvents = 1;
        }

        $isEmptyFriends = 0;
        if (count($friends) == 0) {
            $isEmptyFriends = 1;
        }

        return $this->render('event/index.html.twig', [
            'user' => $user,
            'events' => $events,
            'friends' => $friends,
            'all' => $all,
            'isEmptyEvents' => $isEmptyEvents,
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