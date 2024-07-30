<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Form\FormationType;
use App\Repository\FormationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FormationController extends AbstractController
{
    #[Route('/formation', name: 'app_formation')]
    public function index(FormationRepository $formationRepository): Response
    {
        $formations = $formationRepository->findAll();
        return $this->render('formation/index.html.twig', [
            'formations' => $formations,
        ]);
    }

    #[Route('/formation/new', name: 'new_formation')]
    public function new(Formation $formation = null, Request $request, EntityManagerInterface $entityManager):Response
    {
        if(!$formation){
            $formation = new Formation();
        }
        

        $form = $this->createForm(FormationType::class, $formation);
        
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()){

            $formation = $form->getData();
            $entityManager->persist($formation);
            $entityManager->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->render('formation/new.html.twig', [
            'formAddFormation' => $form,
            'edit' => $formation->getId(),
        ]);    
    }

    #[Route('/formation/{id}/delete', name: 'delete_formation')]
    public function delete(Formation $formation, EntityManagerInterface $entityManager){
        
        $listSessions = $formation->getSessions();
        $list = "";
        //si $listSessions n'a pas de session
        if(sizeof($listSessions) != 0){
            foreach($listSessions as $session){
                $list = $list." ".$session." </br>";
            }
            $this->addFlash('error', 'veuillez changer la formation des sessions :</br>'.$list.' avant de continuer');
            return $this->redirectToRoute("show_formation", ['id' => $formation->getId()]);
        }
        else{
        $entityManager->remove($formation);
        //execute PDO
        $entityManager->flush();

        return $this->redirectToRoute('app_home');
        }
    }

    #[Route('/formation/{id}', name: 'show_formation')]
    public function show(Formation $formation): Response
    {
        return $this->render('formation/show.html.twig', [
            'formation' => $formation,
        ]);
    }
}
