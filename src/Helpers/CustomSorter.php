<?php

namespace App\Helpers;

use App\Entity\Event;

class CustomSorter
{
    public function sortEventsByDate(array $array1)
    {
        usort($array1, fn(Event $x, Event $y) => $x->getDueDate() <=> $y->getDueDate());

        return $array1;
    }
}