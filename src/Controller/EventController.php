<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventFormType;
use App\Service\EventManager;
use App\Service\FriendManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Google\Client;
use Google\Service\Calendar;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class EventController extends AbstractController
{
    #[Route('/dashboard/events/creator', name: 'app_event_creator')]
    public function create(Request $request, EventManager $eventManager): Response
    {
        $event = new Event();
        $user = $this->getUser()->getUserIdentifier();

        $form = $this->createForm(EventFormType::class, $event);
        $form->handleRequest($request);

        if ($eventManager->processEventForm($form, $user, $event, 'create')) {
            $emails = array();

//            $iterator = 0;
//            foreach($form->getData()->getAttendees()->toArray() as $email) {
//                array_push($emails, $email->getEmail());
//                $iterator++;
//            }

            //dd($event->getAttendees());
            return $this->redirectToRoute('app_events');
        }

        $eventType = 'creator';

        return $this->render('event_creator/index.html.twig', [
            'form' => $form,
            'eventForm' => $form->createView(),
            'eventType' => $eventType
        ]);
    }

    #[Route('/dashboard/events', name: 'app_events')]
    public function read(ManagerRegistry $doctrine, EventManager $eventManager, FriendManager $friendManager): Response
    {
        if ($this->getUser() !== NULL) {
            $user = $this->getUser()->getUserIdentifier();
        } else {
            return $this->redirectToRoute('app_login');
        }

        $upcomingEvents = $eventManager->getUserEvents($user, 'upcoming');
        $pastEvents = $eventManager->getUserEvents($user, 'past');
        $friends = $friendManager->getUpcomingBirthdays($user);
        $all = $eventManager->getUserEventsAndBirthdays($user);

        $isEmptyEvents = count($upcomingEvents);
        $isEmptyFriends = count($friends);
        $isEmptyPastEvents = count($pastEvents);

        return $this->render('event/index.html.twig', [
            'user' => $user,
            'events' => $upcomingEvents,
            'friends' => $friends,
            'all' => $all,
            'pastEvents' => $pastEvents,
            'isEmptyEvents' => $isEmptyEvents,
            'isEmptyPastEvents' => $isEmptyPastEvents,
            'isEmptyBirthdays' => $isEmptyFriends
        ]);
    }

    #[Route('/dashboard/events/edit/{id}', name: 'event_edit')]
    public function update(Request $request, ManagerRegistry $doctrine, EventManager $eventManager, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $event = $entityManager->getRepository(Event::class)->find($id);

        $form = $this->createForm(EventFormType::class, $event);
        $form->handleRequest($request);

        if ($eventManager->processEventForm($form, '', $event, 'update')) {
            return $this->redirectToRoute('app_events');
        }

        $eventType = 'editor';

        return $this->render('event_creator/index.html.twig', [
            'form' => $form,
            'eventForm' => $form->createView(),
            'eventType' => $eventType
        ]);
    }

    #[Route('/dashboard/events/{id}/delete', name: 'event_delete')]
    public function delete(Event $event, EntityManagerInterface $entityManager, Request $request): Response
    {
        $entityManager->remove($event);
        $entityManager->flush();

        return $this->redirectToRoute('app_events');
    }

    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    #[Route('/dashboard/events/push/{id}', name:'event_push')]
    public function pushToGoogle(ManagerRegistry $doctrine, Request $request, int $id): Response
    {
        $token = $this->tokenStorage->getToken();
        if ($token instanceof OAuthToken) {
            $accessToken = $token->getAccessToken();
        }

        $client = new Client();
        $client->setAccessToken($accessToken);

        $service = new Calendar($client);

        $entityManager = $doctrine->getManager();
        $event = $entityManager->getRepository(Event::class)->find($id);

        $eventDate = $event->getDueDate()->format('Y-m-d').'T00:00:00';
        $eventName = $event->getName();
        $eventDescription = $event->getDescription();

        //dd($eventDate);

        $googleEvent = new \Google_Service_Calendar_Event([
            'summary' => $eventName,
            'description' => $eventDescription,
            'start' => [
                'dateTime' => $eventDate,
                'timeZone' => 'Europe/Bucharest',
            ],
            'end' => [
                'dateTime' => $eventDate,
                'timeZone' => 'Europe/Bucharest',
            ],
        ]);

        $calendarId = 'primary';
        $googleEvent = $service->events->insert($calendarId, $googleEvent);

        return $this->redirectToRoute('app_events');
    }
}