<?php

namespace App\Controller;

use App\Entity\Event;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    #[Route('/dashboard/events', name: 'app_event')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $user = $this->getUser()->getUserIdentifier();

        /**
        $eventExample = new Event();
        $eventExample->setName('Larisa Barbu');
        $eventExample->setUser($user);
        $date = new \DateTimeImmutable('2023-03-13');
        $eventExample->setDueDate($date);

        $eventExample->setDescription('Urmeaza ziua sotiei lui Florin Barbu');

        $entityManager->persist($eventExample);
        $entityManager->flush();
        */

        $events = $doctrine->getRepository(Event::class)->getAllUserEvents($user);

        $isEmpty = 0;
        if (count($events) == 0) {
            $isEmpty = 1;
        }

        return $this->render('event/index.html.twig', [
            'user' => $user,
            'events' => $events,
            'isEmpty' => $isEmpty
        ]);
    }
}