<?php

namespace App\Controller;

use App\Repository\ModuleRepository;
use App\Repository\CategorieRepository;
use App\Repository\FormateurRepository;
use App\Repository\FormationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig',[]);
    }

    #[Route('/home/{id}', name: 'app_adminPanel')]
    public function adminPanel(FormateurRepository $formateurRepository, FormationRepository $formationRepository, ModuleRepository $moduleRepository, CategorieRepository $categorieRepository): Response
    {
        $formateurs = $formateurRepository->findAll();
        $formations = $formationRepository->findAll();
        $modules = $moduleRepository->findAll();
        $categories = $categorieRepository->findAll();
        return $this->render('home/adminPanel.html.twig', [
            'formateurs' => $formateurs,
            'formations' => $formations,
            'modules' => $modules,
            'categories' => $categories
        ]);
    }
}
