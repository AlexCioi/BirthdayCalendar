<?php

namespace App\Helpers;

use App\Entity\Friend;

class NotificationDateCalculator
{
    public function notDateCalc(Friend $friend): \DateTimeInterface
    {
        $birthday = clone $friend->getBirthDate();

        $localTime = new LocalTime();
        $localTime = $localTime->getLocalTime('dateTime');

        while ($localTime > $birthday) {
            $birthday->modify('+1 year');
        }

        $birthday->modify('-'.$friend->getNotificationOffset().' day');

        return $birthday;
    }
}