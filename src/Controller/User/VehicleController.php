<?php

namespace App\Controller\User;

use App\Controller\AppController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class VehicleController extends AbstractController{


  public function __construct(){
   // parent::__construct();
    $this->app = new AppController();
    $this->app->loadModel('Vehicle');
  }


    /**
      * @Route("/dashboard/vehicles", name="vehiclesIndex")
    */    
    public function index(){
      $vehicles = $this->app->Vehicle->all();
      return $this->render('public/Vehicles.html.twig',[
        "vehicles" => $vehicles
      ]);        
    }

    /**
     * @Route("/admin/add-vehicle", methods={"POST","GET"}, name="addVehicle")
     */
    public function Add(){
      if (!empty($_POST)) {
        $result = $this->app->Vehicle->add([
          'code' => $_POST['code'],
          'genre' => $_POST['genre'],
          'marque' => $_POST['marque'],
          'matricule' => $_POST['matricule'],
          'volume' => $_POST['volume'],
          'poids' => $_POST['poids'],
          'longueur' => $_POST['longueur'],
          'largeur' => $_POST['largeur'],
          'puissance' => $_POST['puissance'],
          'mise_en_marche' => $_POST['date'],
          'etat' => $_POST['etat'],
          'hauteur' => $_POST['hauteur']
        ]);
        if ($result){
          return $this->render('admin/vehicles/add.html.twig',[
            "result" => "Véhicule inseré",
            "vehicles" => null
          ]);
        }
      }
      return $this->render('admin/vehicles/add.html.twig',[
        "vehicle" => null,
        "result" => null
      ]);
    }

    /**
     * @Route("/admin/edit-vehicle/{code}", methods={"POST","GET"}, name="editVehicle")
     */
    public function edit($code){
      if (!empty($_POST)) {
        $vehicule = $this->app->Vehicle->update($_POST['code'], [
          'genre' => $_POST['genre'],
          'marque' => $_POST['marque'],
          'matricule' => $_POST['matricule'],
          'volume' => $_POST['volume'],
          'poids' => $_POST['poids'],
          'longueur' => $_POST['longueur'],
          'largeur' => $_POST['largeur'],
          'puissance' => $_POST['puissance'],
          'mise_en_marche' => $_POST['date'],
          'etat' => $_POST['etat'],
          'hauteur' => $_POST['hauteur']
        ]);
        return $this->index();
      }
      $vehicule = $this->app->Vehicle->getVehicle($code);
      return $this->render('admin/vehicles/edit.html.twig',[
        "vehicle" => $vehicule,
        "result" => null
      ]);
    }

    /**
     * @Route("/admin/delete-point", methods={"POST"}, name="deletePoint")
     */
    public function delete(){
      if (!empty($_POST)) {
        $result = $this->app->Points->delete($_POST['id']);
        return $this->index();
      }
    }
}