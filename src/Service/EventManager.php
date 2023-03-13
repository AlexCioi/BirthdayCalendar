<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\Friend;
use App\Helpers\CustomSorter;
use App\Helpers\LocalTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

class EventManager
{
    private $doctrine;
    private $entityManager;
    private $friendManager;

    public function __construct(ManagerRegistry $doctrine, EntityManagerInterface $entityManager, FriendManager $friendManager)
    {
        $this->doctrine = $doctrine;
        $this->entityManager = $entityManager;
        $this->friendManager = $friendManager;
    }

    public function getUserEvents($user, string $eventPeriod): ?array
    {
        $repo = $this->doctrine->getRepository(Event::class);
        $qb = $repo->getQb();

        if ($eventPeriod === 'upcoming') {
            $repo->getAllUserEvents($qb, $user);
        } else if ($eventPeriod === 'past') {
            $repo->getUserPastEvents($qb, $user);
        }

        return $qb->getQuery()->getResult();
    }

    public function processEventForm($form, $user, Event $event, string $processType): bool
    {
        if ($form->isSubmitted() && $form->isValid()) {
            if ($processType === 'create') {
                $event->setUser($user);
            }

            if ($processType === 'create') {
                $this->entityManager->persist($event);
            }

            $this->entityManager->flush();

            return 1;
        } else {
            return 0;
        }
    }

    public function getUserEventsAndBirthdays($user): ?array
    {
        $sorter = new CustomSorter();

        $events = $this->getUserEvents($user, 'upcoming');
        $friends = $this->friendManager->getUpcomingBirthdays($user);

        $adapter = [];
        foreach ($friends as $friend) {
            $friendEvent = new Event();
            $friendEvent->setUser($friend->getId());
            $friendEvent->setName($friend->getFirstName().' '.$friend->getLastName()."'s birthday!");
            $friendEvent->setDescription(null);

            $dueDate = clone $friend->getNotificationDate();
            $dueDate->modify('+'.$friend->getNotificationOffset().' day');

            $friendEvent->setDueDate($dueDate);
            $adapter[] = $friendEvent;
        }

        return $sorter->sortEventsByDate(array_merge($events, $adapter));
    }

}