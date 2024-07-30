<?php

namespace App\Controller;

use App\Entity\Module;
use App\Form\ModuleType;
use App\Repository\ModuleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ModuleController extends AbstractController
{
    #[Route('/module', name: 'app_module')]
    public function index(ModuleRepository $moduleRepository): Response
    {
        $modules = $moduleRepository->findAll();
        return $this->render('module/index.html.twig', [
            'modules' => $modules
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

    #[Route('/module/{id}/delete', name: 'delete_module')]
    public function delete(Module $module, EntityManagerInterface $entityManager){
        
        $listProgrammes = $module->getProgrammes();
        $list = "";
        
        //si $listProgrammes n'a pas de Programmes
        if(sizeof($listProgrammes) != 0){
            foreach($listProgrammes as $programme){
                $list = $list." ".$programme->getSession()." </br>";
            }
            $this->addFlash('error', 'veuillez enlever le module des sessions :</br>'.$list.' avant de continuer');
            return $this->redirectToRoute("show_module", ['id' => $module->getId()]);
        }
        else{
        $categorie = $module->getCategorie();
        $categorie->removeModule($module);
        $entityManager->remove($module);
        //execute PDO
        $entityManager->flush();

        return $this->redirectToRoute('app_home');
        }
    }

    #[Route('/module/{id}', name: 'show_module')]
    public function show(Module $module): Response
    {
        return $this->render('module/show.html.twig', [
            'module' => $module,
        ]);
    }

}
