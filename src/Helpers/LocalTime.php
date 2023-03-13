<?php

namespace App\Helpers;

class LocalTime
{
    public function getLocalTime(string $localTimeType): \DateTimeInterface
    {
        date_default_timezone_set('Europe/Bucharest');
        $timezone = new \DateTimeZone(date_default_timezone_get());
        $localTime = new \DateTime('now');
        $localTime->setTimezone($timezone);
        if ($localTimeType === 'dateTime') {
            $localTime->setTime(0, 0, 0);
        }

        return $localTime;
    }
}