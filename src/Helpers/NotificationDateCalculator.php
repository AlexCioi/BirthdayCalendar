<?php

namespace App\Helpers;

use App\Entity\Friend;

class NotificationDateCalculator
{
    public function notDateCalc(Friend $friend): \DateTimeInterface
    {
        $birthday = $friend->getBirthDate();

        date_default_timezone_set('Europe/Bucharest');
        $timezone = new \DateTimeZone(date_default_timezone_get());
        $localTime = new \DateTime('now');
        $localTime->setTimezone($timezone);
        $localTime->setTime(0, 0 , 0);

        while ($localTime > $birthday) {
            $birthday->modify('+1 year');
        }

        $birthday->modify('-'.$friend->getNotificationOffset().' day');

        return $birthday;
    }
}