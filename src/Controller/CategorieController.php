<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CategorieController extends AbstractController
{
    #[Route('/categorie', name: 'app_categorie')]
    public function index(CategorieRepository $categorieRepository): Response
    {
        $categories = $categorieRepository->findAll();
        return $this->render('categorie/index.html.twig', [
            'categories' => $categories
        ]);
    }

    #[Route('/categorie/new', name: 'new_categorie')]
    public function new(Categorie $categorie = null, Request $request, EntityManagerInterface $entityManager):Response
    {
        if(!$categorie){
            $categorie = new Categorie();
        }

        $form = $this->createForm(CategorieType::class, $categorie);
        
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()){

            $categorie = $form->getData();
            $entityManager->persist($categorie);
            $entityManager->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->render('categorie/new.html.twig', [
            'formAddCategorie' => $form,
            'edit' => $categorie->getId(),
        ]);    
    }

    #[Route('/categorie/{id}/delete', name: 'delete_categorie')]
    public function delete(Categorie $categorie, EntityManagerInterface $entityManager){
        
        $listModules = $categorie->getModules();
        
        $list = "";
        //si $listModules n'a pas de module
        if(sizeof($listModules) != 0){
            foreach($listModules as $module){
                $list = $list." ".$module." </br>";
            }
            $this->addFlash('error', 'veuillez changer la catégorie des Modules :</br>'.$list.' avant de continuer');
            return $this->redirectToRoute("show_categorie", ['id' => $categorie->getId()]);
        }
        else{
        $entityManager->remove($categorie);
        //execute PDO
        $entityManager->flush();

        return $this->redirectToRoute('app_home');
        }
    }

    #[Route('/categorie/{id}', name: 'show_categorie')]
    public function show(Categorie $categorie): Response
    {
        return $this->render('categorie/show.html.twig', [
            'categorie' => $categorie,
        ]);
    }
}
