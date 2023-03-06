<?php

namespace App\Helpers;

use App\Entity\Event;
use App\Entity\Friend;
use Doctrine\Persistence\ManagerRegistry;

class EventFetcher
{
    public function fetchAllEvents(ManagerRegistry $doctrine, $user): ?array
    {
        $sorter = new CustomSorter();

        $repo = $doctrine->getRepository(Event::class);
        $qb = $repo->getQb();
        $repo->getAllUserEvents($qb, $user);
        $events = $qb->getQuery()->getResult();

        $repo = $doctrine->getRepository(Friend::class);
        $qb = $repo->getQb();
        $repo->getAllUserFriends($qb, $user);
        $friends = $qb->getQuery()->getResult();

        date_default_timezone_set('Europe/Bucharest');
        $timezone = new \DateTimeZone(date_default_timezone_get());
        $localTime = new \DateTime('now');
        $localTime->setTimezone($timezone);
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

        return $sorter->sortEventsByDate(array_merge($events, $adapter));
    }
}