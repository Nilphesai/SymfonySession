<?php

namespace App\Controller;

use App\Entity\Session;
use App\Entity\Stagiaire;
use App\Form\StagiaireType;
use App\Repository\StagiaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StagiaireController extends AbstractController
{
    #[Route('/stagiaire', name: 'app_stagiaire')]
    public function index(StagiaireRepository $stagiaireRepository): Response
    {
        $stagiaires = $stagiaireRepository->findAll(); 
        return $this->render('stagiaire/index.html.twig', [
            'stagiaires' => $stagiaires,
        ]);
    }

    #[Route('/stagiaire/new', name: 'new_stagiaire')]
    #[Route('/stagiaire/{id}/edit', name: 'edit_stagiaire')]
    public function new(Stagiaire $stagiaire = null, Request $request, EntityManagerInterface $entityManager):Response
    {
        if(!$stagiaire){
            $stagiaire = new Stagiaire();
        }
        

        $form = $this->createForm(StagiaireType::class, $stagiaire);
        
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()){

            $stagiaire = $form->getData();
            $entityManager->persist($stagiaire);
            $entityManager->flush();

            return $this->redirectToRoute('app_stagiaire');
        }

        return $this->render('stagiaire/new.html.twig', [
            'formAddStagiaire' => $form,
            'edit' => $stagiaire->getId(),
        ]);    
    }

    #[Route('/stagiaire/remove/{id}/{sessionId}', name: 'remove_stagiaire_session')]
    public function removeSess(EntityManagerInterface $entityManager, Request $request): Response
    {
        //récupéré l'objet
        $stagId = $request->attributes->get('id');
        $sessId = $request->attributes->get('sessionId');
        $stagiaire = $entityManager->getRepository(Stagiaire::class)->find($stagId);
        $session = $entityManager->getRepository(Session::class)->find($sessId);
        if (!$session) {
            throw $this->createNotFoundException(
                'No session found'
            );
        }
        //modifié l'objet
        $stagiaire->removeSession($session);
        //execute PDO
        $entityManager->flush();

        return $this->redirectToRoute("show_stagiaire", ['id' => $stagId]);
    }

    #[Route('/stagiaire/{id}/delete', name: 'delete_stagiaire')]
    public function delete(stagiaire $stagiaire, EntityManagerInterface $entityManager){
        
        $listSessions = $stagiaire->getSessions();
        foreach($listSessions as $session){
            $session->removeStagiaire($stagiaire);
        }
        $entityManager->remove($stagiaire);
        //execute PDO
        $entityManager->flush();

        return $this->redirectToRoute('app_stagiaire');
    }

    #[Route('/stagiaire/{id}', name: 'show_stagiaire')]
    public function show(Stagiaire $stagiaire): Response
    {
        return $this->render('stagiaire/show.html.twig', [
            'stagiaire' => $stagiaire,
        ]);
    }

}
