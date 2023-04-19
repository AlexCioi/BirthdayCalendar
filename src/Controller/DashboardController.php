<?php

namespace App\Controller;

use App\Entity\Event;
use Doctrine\Persistence\ManagerRegistry;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;

class DashboardController extends AbstractController
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    #[Route('/dashboard', name: 'dashboard')]
    public function index(ManagerRegistry $doctrine, Request $request): Response
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
            'events' => $shortTermEvents,
            'isEmptyEvents' => $isEmptyEvents,
            'accessToken' => $accessToken
        ]);
    }
}
