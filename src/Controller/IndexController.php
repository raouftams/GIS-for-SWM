<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
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
     * @Route("/", name="home")
     */
    public function index(){
      $this->app->loadModel("Planning");
      $this->app->loadModel("Secteur");
      $planning = $this->app->Planning->getUsedPlanning();
        $secteurs = $this->app->Secteur->all();
        $jours = ["samedi","dimanche", "lundi", "mardi", "mercredi", "jeudi", "vendredi"];

        //Planning des secteurs
        $features = [];$plan = [];
        foreach($secteurs as $secteur){
            foreach($planning as $rotation){
                if($secteur["code"] == $rotation["secteur"]){
                    $feature = ["heure" => $rotation["heure"]];
                    $index = array_search($rotation["jour"], $jours);
                    $features[$index] = $feature;
                }
            }
            if ($secteur["geojson"] != null) {
              $plan[] = ["secteur" => $secteur["code"], "designation"=> $secteur["designation"], "features"=>$features];
            }
            $features = [];
        }
       

        return $this->render('home.html.twig', [
            "plan" => $plan
        ]);
    }

    /**
     * @Route("/index", name="handleLogin")
     */
    public function loginHandler(AuthorizationCheckerInterface $authChecker, Security $security){
		  if ($authChecker->isGranted('ROLE_SUPERUSER')) {
		  	return $this->adminIndex();
		  }else{
		  	return $this->userIndex($security);
		  }
    }

    /**
	 * @Route("/dashboard", name="dashboard")
	 */
    public function adminIndex(){

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
    $code_equipe = $user->getCodeEquipe();
    $tourneesEnAttente = $this->app->Tournee->getTourneesEquipe($code_equipe);
    $allTournees = $this->app->Tournee->getAllTourneesEquipe($code_equipe);
    foreach($tourneesEnAttente as $t){
      for ($i=0; $i <count($t) ; $i++) { 
        unset($t[$i]);
      }
    }
    foreach($allTournees as $t){
      for ($i=0; $i <count($t) ; $i++) { 
        unset($t[$i]);
      }
    }
		return $this->render("public/userDashboard.html.twig", ["tournees"=>$tourneesEnAttente, "allTournees"=>$allTournees]);
	}

 
}