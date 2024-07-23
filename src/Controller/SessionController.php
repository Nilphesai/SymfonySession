<?php

namespace App\Controller;

use App\Entity\Session;
use App\Form\SessionType;
use App\Repository\SessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SessionController extends AbstractController
{
    #[Route('/session', name: 'app_session')]
    public function index(SessionRepository $sessionRepository): Response
    {
        $sessions = $sessionRepository->findAll(); 
        return $this->render('session/index.html.twig', [
            'sessions' => $sessions,
        ]);
    }

    #[Route('/session/new', name: 'new_session')]
    #[Route('/session/{id}/edit', name: 'edit_session_stagiaire')]
    public function new(Session $session = null, Request $request, EntityManagerInterface $entityManager):Response
    {
        $session = new Session();

        $form = $this-createForm(SessionType::class, $session);
        
        $form->handleRequest($request);
        
        if($form->isSubmited() && $form->isValid()){

            $session = $form-getData();
            $entityManager->persist($session);
            $entityManager->flush();
        }

        


        return $this->redirectToRoute('app_session');
    }

    

    #[Route('/session/{id}', name: 'show_session')]
    public function show(Session $session = null, SessionRepository $sr): Response
    {
        $nonInscrits = $sr->findNonInscrits($session->getId());
        $nonProgrammes = $sr->findNonProgrammes($session->getId());

        return $this->render('session/show.html.twig', [
            'session' => $session,
            'nonInscrits' => $nonInscrits,
            'nonProgrammes' => $nonProgrammes,
        ]);
    }

}
