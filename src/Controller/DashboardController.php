<?php

namespace App\Controller;

use App\Entity\Event;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser()->getUserIdentifier();


        $eventRepo = $doctrine->getRepository(Event::class);
        $qb = $eventRepo->getQb();
        $eventRepo->getShortTermUserEvents($qb, $user);
        $shortTermEvents = $qb->getQuery()->getResult();

        $isEmptyEvents = 0;
        if (count($shortTermEvents) === 0) {
            $isEmptyEvents = 1;
        }

        return $this->render('dashboard/index.html.twig', [
            'events' => $shortTermEvents,
            'isEmptyEvents' => $isEmptyEvents
        ]);
    }
}
