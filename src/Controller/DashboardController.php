<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\User;
use App\Service\EventManager;
use App\Service\FriendManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DashboardController extends AbstractController
{
    private $tokenStorage, $session;

    public function __construct(TokenStorageInterface $tokenStorage, \SessionHandlerInterface $session)
    {
        $this->tokenStorage = $tokenStorage;
        $this->session = $session;
    }

    #[Route('/dashboard', name: 'dashboard')]
    public function index(ManagerRegistry $doctrine, Request $request, FriendManager $friendManager, EventManager $eventManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($this->getUser() !== NULL) {
            $user = $this->getUser()->getUserIdentifier();
        } else {
            return $this->redirectToRoute('app_login');
        }

        $eventRepo = $doctrine->getRepository(Event::class);
        $qb = $eventRepo->getQb();
        $eventRepo->getShortTermUserEvents($qb, $user);
        $shortTermEvents = $qb->getQuery()->getResult();

        $friends = $friendManager->getUserFriends($this->getUser()->getUserIdentifier());
        $events = $eventManager->getUserEvents($this->getUser()->getUserIdentifier(), 'upcoming');

        $isEmptyEvents = 0;
        if (count($shortTermEvents) === 0) {
            $isEmptyEvents = 1;
        }

        $token = $this->tokenStorage->getToken();
        $accessToken = null;
        if ($token instanceof OAuthToken) {
            $accessToken = $token->getAccessToken();
        }

        return $this->render('dashboard/index.html.twig', [
            'user' => $this->getUser()->getUserIdentifier(),
            'friends' => $friends,
            'events' => $events,
            'shortTermEvents' => $shortTermEvents,
            'isEmptyEvents' => $isEmptyEvents,
            'accessToken' => $accessToken
        ]);
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/dashboard/google-connect/{replace}', name: 'google-connect', requirements: ['replace' => '\w+'])]
    public function googleConnect(Request $request, EntityManagerInterface $entityManager, ManagerRegistry $doctrine, string $replace = ''): Response
    {
        $responseGoogleId = $this->session->read('googleId');
        $userGoogleId = $this->getUser()->getGoogleId();

        $repo = $doctrine->getRepository(User::class);
        $qb = $repo->getQb();

        $repo->findOneByGoogleId($qb, $responseGoogleId);

        //dd(count($qb->getQuery()->getResult()));
        if (count($qb->getQuery()->getResult()) !== 0) {
            if ($replace === '') {
                return $this->render('oauth_connect_consent/index.html.twig');
            } else if ($replace === 'false') {
                return $this->redirectToRoute('dashboard');
            }
        }

        if ($userGoogleId === null) {
            $this->getUser()->setGoogleId($responseGoogleId);
            $entityManager->flush();

            return $this->redirectToRoute('app_account');
        }

        return new Response(

        );
    }
}
