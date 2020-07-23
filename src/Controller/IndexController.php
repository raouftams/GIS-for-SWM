<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Security;

class IndexController extends AbstractController{


  public function __construct(){
   // parent::__construct();
    $this->app = new AppController();
    $this->app->loadModel('Points');
    $this->app->loadModel('Bacs');
    $this->app->loadModel('Tournee');
    
  }

    /**
     * @Route("/index", name="handleLogin")
     */
    public function loginHandler(AuthorizationCheckerInterface $authChecker, Security $security){
		  if ($authChecker->isGranted('ROLE_SUPERUSER')) {
		  	return $this->index();
		  }else{
		  	return $this->userIndex($security);
		  }
    }

    /**
	 * @Route("/dashboard", name="dashboard")
	 */
    public function index(){

      $tournees = $this->app->Tournee->getTourneesEnAttente();
      $nbTourneesAttente = count($tournees);
      $nbTourneesEffectuees = $this->app->Tournee->nbTournee()[0];
      $nbTourneesEnCours = $this->app->Tournee->nbTourneesEnCours()[0];
      return $this->render('public/dashboard.html.twig',["tournees" => $tournees, "nbTourneesAttente" => $nbTourneesAttente, "nbTourneesEffectuees" => $nbTourneesEffectuees["count"], "tourneesEnCours"=>$nbTourneesEnCours["count"]] );
    }

	/**
	 * @Route("/UserIndex", name="userIndex")
	 */
	public function userIndex(Security $security){
    $user_id = $security->getUser()->getId();
    $user = $this->getDoctrine()->getRepository(User::class)->find($user_id);
    $id_equipe = $user->getIdEquipe();
    $tournees = $this->app->Tournee->getTourneesEquipe($id_equipe);
    foreach($tournees as $t){
      for ($i=0; $i <count($t) ; $i++) { 
        unset($t[$i]);
      }
    }
		return $this->render("userbase.html.twig", ["tournees"=>$tournees]);
	}

 
}