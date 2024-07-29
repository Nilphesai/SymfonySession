<?php

namespace App\Controller;


use App\Entity\Module;
use App\Entity\Session;
use App\Entity\Programme;
use App\Entity\Stagiaire;
use App\Form\SessionType;
use App\Form\ProgrammeType;
use App\Repository\SessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Flasher\Prime\FlasherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SessionController extends AbstractController
{
    #[Route('/session', name: 'app_session')]
    public function index(SessionRepository $sessionRepository): Response
    {
        $sessions = $sessionRepository->findSessions();
        $sessionsFini = $sessionRepository->findSessionsFini();
        $sessionsFuture = $sessionRepository->findSessionsFuture();
        return $this->render('session/index.html.twig', [
            'sessions' => $sessions,
            'sessionsFini' => $sessionsFini,
            'sessionsFuture' => $sessionsFuture,
        ]);
    }

    #[Route('/session/new', name: 'new_session')]
    public function new(Session $session = null, Request $request, EntityManagerInterface $entityManager):Response
    {
        if(!$session){
            $session = new Session();
        }
        

        $form = $this->createForm(SessionType::class, $session);
        
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()){

            $session = $form->getData();
            $entityManager->persist($session);
            $entityManager->flush();

            return $this->redirectToRoute('app_session');
        }

        return $this->render('session/new.html.twig', [
            'formAddSession' => $form,
            'edit' => $session->getId(),
        ]);    
    }

    #[Route('/session/add/{id}/{sessionId}', name: 'add_session_stagiaire')]
    public function addStag(EntityManagerInterface $entityManager, Request $request): Response
    {
        //récupéré l'objet
        $stagId = $request->attributes->get('id');
        $sessId = $request->attributes->get('sessionId');
        $stagiaire = $entityManager->getRepository(Stagiaire::class)->find($stagId);
        $session = $entityManager->getRepository(Session::class)->find($sessId);
        
        //vérifier le nombre de stagiaire
        $limiteInscrit = $session->getNbPlace();
        $nbStagiaires = count($session->getStagiaires());
        
        if ($limiteInscrit >= $nbStagiaires + 1) {
            //modifié l'objet
        $stagiaire->addSession($session);
        //execute PDO
        $entityManager->flush();

        }

        return $this->redirectToRoute("show_session", ['id' => $sessId]);
        
    }

    #[Route('/session/remove/{id}/{sessionId}', name: 'remove_session_stagiaire')]
    public function removeStag(EntityManagerInterface $entityManager, Request $request): Response
    {
        //récupéré l'objet
        $stagId = $request->attributes->get('id');
        $sessId = $request->attributes->get('sessionId');
        $stagiaire = $entityManager->getRepository(Stagiaire::class)->find($stagId);
        $removeSession = $entityManager->getRepository(Session::class)->find($sessId);
        if (!$stagiaire) {
            throw $this->createNotFoundException(
                'No stagiaire found'
            );
        }
        //modifié l'objet
        $stagiaire->removeSession($removeSession);
        //execute PDO
        $entityManager->flush();

        return $this->redirectToRoute("show_session", ['id' => $sessId]);
    }
    
    #[Route('/session/addMod/{modId}/{sessionId}', name: 'add_session_programme')]
    public function addMod(EntityManagerInterface $entityManager, Request $request): Response
    {
        //récupéré l'objet
        $sessId = $request->attributes->get('sessionId');
        $modId = $request->attributes->get('modId');
        $module = $entityManager->getRepository(Module::class)->find($modId);
        $session = $entityManager->getRepository(Session::class)->find($sessId);
        
        //vérification nbJours non dépassé
        $duree = $session->dureeSession();
        $temps = 0;
        $myNumber = $_GET['nbJours'];
        $listProgrammes = $session->getProgrammes();
        foreach($listProgrammes as $programme){
            $temps = $temps + $programme->getNbJours(); 
        }
        if ($duree >= $temps + $myNumber){
            
            
            $programme = new Programme();
            $programme->setModule($module);
            $programme->setSession($session);
            $programme->setNbJours($myNumber);
            //modifié l'objet
            //execute PDO
            $entityManager->persist($programme);
            $session->addProgramme($programme);
            $module->addProgramme($programme);
            //execute PDO
            $entityManager->flush();

            
        }
        else{
            $this->addFlash('error', 'nbJours dépasse la durée de la session !');
            return $this->redirectToRoute("show_session", ['id' => $sessId]);
        }
        return $this->redirectToRoute("show_session", ['id' => $sessId]);

        
    }

    #[Route('/session/removeMod/{progId}/{modId}/{sessionId}', name: 'remove_session_programme')]
    public function removeMod(EntityManagerInterface $entityManager, Request $request): Response
    {
        //récupéré l'objet
        $progId = $request->attributes->get('progId');
        $sessId = $request->attributes->get('sessionId');
        $modId = $request->attributes->get('sessionId');
        $module = $entityManager->getRepository(Module::class)->find($modId);
        $programme = $entityManager->getRepository(Programme::class)->find($progId);
        $session = $entityManager->getRepository(Session::class)->find($sessId);
        if (!$programme ) {
            throw $this->createNotFoundException(
                'No programme found'
            );
        }
        //modifié l'objet
        $entityManager->remove($programme);
        $session->removeProgramme($programme);
        $module->removeProgramme($programme);
        //execute PDO
        $entityManager->flush();

        return $this->redirectToRoute("show_session", ['id' => $sessId]);
    }

    #[Route('/session/{id}/delete', name: 'delete_session')]
    public function delete(Session $session, EntityManagerInterface $entityManager){
        
        $listStagiaires = $session->getStagiaires();
        $listProgrammes = $session->getProgrammes();
        foreach($listStagiaires as $stagiaire){
            $stagiaire->removeSession($session);
        }
        foreach($listProgrammes as $programme){
            $module = $programme->getModule();
            $module->removeProgramme($programme);
            $session->removeProgramme($programme);
            $entityManager->remove($programme);
        }
        $entityManager->remove($session);
        //execute PDO
        $entityManager->flush();

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
