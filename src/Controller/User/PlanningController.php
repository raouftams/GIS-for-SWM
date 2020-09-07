<?php

namespace App\Controller\User;

use App\Controller\AppController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Date;

class PlanningController extends AbstractController{


  public function __construct(){
   // parent::__construct();
    $this->app = new AppController();
    $this->app->loadModel('Planning');
    $this->app->loadModel('Secteur');
    $this->app->loadModel('RotationPrevue');
  }


    /**
      * @Route("/dashboard/planningCollecte", methods={"POST","GET"}, name="planningCollecte")
    */    
    public function index(){
        $planning = $this->app->Planning->getUsedPlanning();
        $secteurs = $this->app->Secteur->all();
        $vehicles = $this->app->RotationPrevue->getVehicles();
        $equipes = $this->app->RotationPrevue->getEquipes();
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
        //Planning des véhicules
        $features = []; $heures = []; $feature = [];
        $tempsTotal = $kilometrage = 0;
        foreach ($vehicles as $vehicle) {
            $vehicleData = $this->app->Planning->vehiclePlanning($vehicle["code"]);
            foreach($jours as $jour){    
                foreach($vehicleData as $data){
                    
                    if($data["jour"] == $jour){
                        $heures[] = $data["heure"];
                        $kilometrage = $kilometrage + $data["kilometrage"];
                        $debut = strtotime($data["heure_debut"]);
                        $fin = strtotime($data["heure_fin"]);
                        $calcul = ($fin - $debut)/3600;
                        $tempsTotal = $tempsTotal + $calcul;
                    }
                }
                
                $index = array_search($jour, $jours);
                $features[$index] = $heures;
                $heures = [];
            }
            $vehiclePlan[] = [
                "code_vehicle" => $vehicle["code"],
                "matricule" => $vehicle["matricule"],
                "genre" => $vehicle["genre"],
                "volume" => $vehicle["volume"],
                "kilometrage" => intval($kilometrage),
                "tempsTotal" => intval($tempsTotal),
                "features" => $features
            ];
            $features = $features = [];
            $tempsTotal = $kilometrage = 0;
        }
        
        //Planning des équipes
        $tempsTotal = 0;
        $features = []; $heures = []; $feature = [];
        foreach ($equipes as $equipe) {
            $equipeData = $this->app->Planning->equipePlanning($equipe["equipe"]);
            foreach($jours as $jour){    
                foreach($equipeData as $data){
                    if($data["jour"] == $jour){
                        $heures[] = $data["heure"];
                        $debut = strtotime($data["heure_debut"]);
                        $fin = strtotime($data["heure_fin"]);
                        $calcul = ($fin - $debut)/3600;
                        $tempsTotal = $tempsTotal + $calcul;
                    }
                }
                
                $index = array_search($jour, $jours);
                $features[$index] = $heures;
                $heures = [];
            }
            $equipePlan[] = [
                "code_equipe" => $equipe["equipe"],
                "tempsTotal" => intval($tempsTotal),
                "features" => $features
            ];
            $features = $features = [];
            $tempsTotal = 0;
        }

        return $this->render('public/planning_collecte.html.twig', [
            "plan" => $plan,
            "vehiclePlan" => $vehiclePlan,
            "equipePlan" => $equipePlan
        ]);
    }
	




    
}