<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Friend;
use App\Form\FriendFormType;
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

class FriendController extends AbstractController
{
    #[Route('/dashboard/friend/register', name: 'friend_register')]
    public function create(Request $request, FriendManager $friendManager): Response
    {
        $friend = new Friend();

        $form = $this->createForm(FriendFormType::class, $friend);
        $form->handleRequest($request);

        if ($friendManager->processFriendForm($form, $this->getUser()->getUserIdentifier(), $friend, 'create')) {
            return $this->redirectToRoute('app_friends');
        }

        $friendType = 'register';

        return $this->render('friend_creator/index.html.twig', [
            'form' => $form,
            'friendType' => $friendType,
        ]);
    }

    #[Route('/dashboard/friends', name: 'app_friends')]
    public function read(FriendManager $friendManager): Response
    {
        if ($this->getUser() !== NULL) {
            $user = $this->getUser()->getUserIdentifier();
        } else {
            return $this->redirectToRoute('app_login');
        }

        $friends = $friendManager->getUserFriends($user);

        $isEmpty = 0;
        if (count($friends) == 0) {
            $isEmpty = 1;
        }

        return $this->render('friend/index.html.twig', [
            'user' => $user,
            'isEmpty' => $isEmpty,
            'friends' => $friends
        ]);
    }

    #[Route('/dashboard/friends/{id}/edit', name: 'friend_edit')]
    public function update(Request $request, ManagerRegistry $doctrine, FriendManager $friendManager, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $friend = $entityManager->getRepository(Friend::class)->find($id);

        $form = $this->createForm(FriendFormType::class, $friend);
        $form->handleRequest($request);

        if ($friendManager->processFriendForm($form, $this->getUser()->getUserIdentifier(), $friend, 'update')) {
            return $this->redirectToRoute('app_friends');
        }

        $friendType = 'editor';

        return $this->render('friend_creator/index.html.twig', [
            'form' => $form,
            'friendType' => $friendType
        ]);
    }

    #[Route('/dashboard/friends/{id}/delete', name: 'friend_delete')]
    public function delete(Friend $friend, EntityManagerInterface $entityManager, Request $request): Response
    {
        $entityManager->remove($friend);
        $entityManager->flush();

        return $this->redirectToRoute('app_friends');
    }

    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    #[Route('/dashboard/friends/push/{id}', name:'friend_push')]
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
        $friend = $entityManager->getRepository(Friend::class)->find($id);

        $friendBirthday = $friend->getNotificationDate()
                ->add(new \DateInterval('P'.$friend->getNotificationOffset().'D'))
                ->format('Y-m-d').'T00:00:00';

        $friendName = $friend->getFirstName().' '.
                      $friend->getLastName();

        $googleEvent = new \Google_Service_Calendar_Event([
            'summary' => $friendName.'\'s birthday!',
            'start' => [
                'dateTime' => $friendBirthday,
                'timeZone' => 'Europe/Bucharest',
            ],
            'end' => [
                'dateTime' => $friendBirthday,
                'timeZone' => 'Europe/Bucharest',
            ],
            'recurrence' => array(
                'RRULE:FREQ=YEARLY;UNTIL=20300101T000000Z'
            ),
        ]);

        $calendarId = 'primary';
        $service->events->insert($calendarId, $googleEvent);

        return $this->redirectToRoute('app_friends');
    }
}
