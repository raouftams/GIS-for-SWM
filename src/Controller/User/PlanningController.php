<?php

namespace App\Controller\User;

use App\Controller\AppController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PlanningController extends AbstractController{


  public function __construct(){
   // parent::__construct();
    $this->app = new AppController();
    $this->app->loadModel('Planning');
    $this->app->loadModel('Secteur');
  }


    /**
      * @Route("/dashboard/planningCollecte", methods={"POST","GET"}, name="planningCollecte")
    */    
    public function index(){
        $planning = $this->app->Planning->getUsedPlanning();
        $secteurs = $this->app->Secteur->all();
        $jours = ["samedi","dimanche", "lundi", "mardi", "mercredi", "jeudi", "vendredi"];
        $features = [];
        $plan = [];
        foreach($secteurs as $secteur){
            foreach($planning as $rotation){
                if($secteur["code"] == $rotation["secteur"]){
                    $feature = ["heure" => $rotation["heure"]];
                    $index = array_search($rotation["jour"], $jours);
                    $features[$index] = $feature;
                }
            }
            $plan[] = ["secteur" => $rotation["secteur"], "features"=>$features];
            $features = [];
        }
        return $this->render('public/planning_collecte.html.twig', [
            "plan" => $plan
        ]);

    }
	




    
}