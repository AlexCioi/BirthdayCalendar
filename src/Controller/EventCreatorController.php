<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventCreateFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventCreatorController extends AbstractController
{
    #[Route('/dashboard/events/creator', name: 'app_event_creator')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();

        $form = $this->createForm(EventCreateFormType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event->setUser($this->getUser()->getUserIdentifier());

            $formData = $form->getData();

            $event->setName($formData->getName());
            $event->setDueDate($formData->getDueDate());
            $event->setDescription($formData->getDescription());

            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('app_event');
        }

        $eventType = 'creator';

        return $this->render('event_creator/index.html.twig', [
            'controller_name' => 'EventCreatorController',
            'form' => $form,
            'eventType' => $eventType
        ]);
    }
}
