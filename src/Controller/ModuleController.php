<?php

namespace App\Controller;

use App\Entity\Module;
use App\Form\ModuleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ModuleController extends AbstractController
{
    #[Route('/module', name: 'app_module')]
    public function index(): Response
    {
        return $this->render('module/index.html.twig', [
            'controller_name' => 'ModuleController',
        ]);
    }

    #[Route('/module/new', name: 'new_module')]
    public function new(Module $module = null, Request $request, EntityManagerInterface $entityManager):Response
    {
        if(!$module){
            $module = new Module();
        }

        $form = $this->createForm(ModuleType::class, $module);
        
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()){

            $module = $form->getData();
            $entityManager->persist($module);
            $entityManager->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->render('module/new.html.twig', [
            'formAddModule' => $form,
            'edit' => $module->getId(),
        ]);    
    }
}
