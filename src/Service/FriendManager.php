<?php

namespace App\Service;

use App\Entity\Friend;
use App\Helpers\NotificationDateCalculator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

class FriendManager
{
    private $doctrine;
    private $entityManager;

    public function __construct(ManagerRegistry $doctrine, EntityManagerInterface $entityManager)
    {
        $this->doctrine = $doctrine;
        $this->entityManager = $entityManager;
    }

    public function getUserFriends($user)
    {
        $repo = $this->doctrine->getRepository(Friend::class);
        $qb = $repo->getQb();
        $repo->getAllUserFriends($qb, $user);

        return $qb->getQuery()->getResult();
    }

    public function processFriendForm($form, $user, Friend $friend, string $processType): bool
    {
        if ($form->isSubmitted() && $form->isValid()) {
            if ($processType === 'create') {
                $friend->setUser($user);
            }

            if ($friend->getCheckBox() == 1) {
                if ($friend->getNotificationOffset() != NULL) {
                    $notificationCalculator = new NotificationDateCalculator();
                    $friend->setNotificationDate($notificationCalculator->notDateCalc($friend));
                }
            } else {
                $friend->setNotificationOffset(0);
                $notificationCalculator = new NotificationDateCalculator();
                $friend->setNotificationDate($notificationCalculator->notDateCalc($friend));
            }

            if ($processType === 'create') {
                $this->entityManager->persist($friend);
            }

            $this->entityManager->flush();

            return 1;
        } else {
            return 0;
        }
    }

    public function getUpcomingBirthdays($user): ?array
    {
        $repo = $this->doctrine->getRepository(Friend::class);
        $qb = $repo->getQb();
        $repo->getUserUpcomingBirthdays($qb, $user);

        return $qb->getQuery()->getResult();
    }
}