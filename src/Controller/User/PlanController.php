<?php

namespace App\Controller\User;

use App\Controller\AppController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PlanController extends AbstractController{


  public function __construct(){
   // parent::__construct();
    $this->app = new AppController();
    $this->app->loadModel('Vehicle');
    $this->app->loadModel('Secteur');
  }


    /**
      * @Route("/dashboard/planCollecte", name="planCollecte")
    */    
    public function index(){
        $data = [];
        $feature = [];
        $secteurs = $this->app->Secteur->getUsedSecteur();
        foreach($secteurs as $secteur){
            $vehicle = $this->app->Vehicle->getVehicle($secteur["vehicule"]);
            $name = $vehicle["genre"];
            
            $feature = [
                "secteur" => $secteur["code"],
                "vehicule" => "".$name." ". $vehicle["marque"]." " . $vehicle["volume"] ."m3_". $vehicle["code"] ."_" . $vehicle["matricule"] ."",
                "horaire" => $secteur["horaire"],
                "quantite_prevue" => $secteur["qtedechet"]
            ];
        
            array_push($data, $feature);
        }
        
        return $this->render('public/planCollecte.html.twig',[
          "data" => $data
        ]);
        
    }

    
}