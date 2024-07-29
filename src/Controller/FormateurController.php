<?php

namespace App\Controller;

use App\Entity\Formateur;
use App\Form\FormateurType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FormateurController extends AbstractController
{
    #[Route('/formateur', name: 'app_formateur')]
    public function index(): Response
    {
        return $this->render('formateur/index.html.twig', [
            'controller_name' => 'FormateurController',
        ]);
    }

    #[Route('/formateur/new', name: 'new_formateur')]
    public function new(Formateur $formateur = null, Request $request, EntityManagerInterface $entityManager):Response
    {
        if(!$formateur){
            $formateur = new Formateur();
        }

        $form = $this->createForm(FormateurType::class, $formateur);
        
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()){

            $formateur = $form->getData();
            $entityManager->persist($formateur);
            $entityManager->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->render('formateur/new.html.twig', [
            'formAddFormateur' => $form,
            'edit' => $formateur->getId(),
        ]);    
    }
}
