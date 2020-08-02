<?php

namespace App\Controller\User;

use App\Controller\AppController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class PointsController extends AbstractController{


  public function __construct(){
   // parent::__construct();
    $this->app = new AppController();
    $this->app->loadModel('Points');
  }


    /**
      * @Route("/dashboard/points-de-collecte", name="pointsIndex")
    */    
    public function index(){
      $points = $this->app->Points->all();
      return $this->render('public/points.html.twig',[
        "points" => $points
      ]);        
    }

    /**
      * @Route("/dashboard/points-de-collecte/par-secteur", name="parSecteur")
    */ 
    public function parSecteur(){
      if(!empty($_POST) && $_POST['code']!='tout'){
        $points = $this->app->Points->findWithSector($_POST['code']);
        return $this->render('public/points.html.twig',[
          "points" => $points
        ]);
      }
      return $this->index();
      
    }

    /**
     * @Route("/admin/add-point", methods={"POST","GET"}, name="addPoint")
     */
    public function Add(){
      if (!empty($_POST)) {
        if ($_POST["selectAdresse"] == "") {
            $adresse = $_POST["adresse"];
        }else{
            $adresse = $_POST["selectAdresse"];
        }
        $point = $this->app->Points->add([
          'code_point' => $_POST['code'],
          'adresse' => $adresse,
          'X' => floatval($_POST['longitude']),
          'Y' => floatval($_POST['latitude']),
          'secteur' => $_POST['secteur'],
          'activites' => $_POST['activite'],
          'frequence' => $_POST["frequence"],
          'libelle' => $_POST['libelle'],
          'debut_fenetre_temps1' => $this->datetimeToMs($_POST["debut_fenetre_temps"]),
          'fin_fenetre_temps1' => $this->datetimeToMs($_POST["fin_fenetre_temps"]),
          'debut_fenetre_temps2' => $this->datetimeToMs($_POST["debut_fenetre_temps"])+6*60*60000,
          'fin_fenetre_temps2' => $this->datetimeToMs($_POST["fin_fenetre_temps"])+6*60*60000
          
        ]);
        $geom = $this->app->Points->addgeom($_POST['code'], $_POST['longitude'], $_POST['latitude']);
        return $this->index();
      }
      
      return $this->render('admin/points/add.html.twig',[
        "point" => []
      ]);
    }

    /**
     * @Route("/admin/edit-point/{code}", methods={"POST","GET"}, name="editPoint")
     */
    public function edit($code){
      if (!empty($_POST)) {
        $point = $this->app->Points->update($_POST['code'], [
          'libelle' => $_POST['libelle'],
          'adresse' => $_POST['adresse'],
          'activites' => $_POST['activite'],
          'frequence' => $_POST['frequence'],
          'debut_fenetre_temps1' => $this->datetimeToMs($_POST['debut_fenetre_temps']),
          'fin_fenetre_temps1' => $this->datetimeToMs($_POST['fin_fenetre_temps'])
        ]);
        return $this->index();
      }
      $point = $this->app->Points->getPoint($code)[0];
      for ($i=0; $i <count($point) ; $i++) { 
          unset($point[$i]);
      }
      $point["debut_fenetre_temps1"] = $this->msToTime($point["debut_fenetre_temps1"]);
      $point["fin_fenetre_temps1"] = $this->msToTime($point["fin_fenetre_temps1"]);
      return $this->render('admin/points/edit.html.twig',[
        "point" => $point
      ]);
    }

    /**
     * @Route("/admin/get_json_point/{code}", methods={"POST","GET"}, name="getJsonPoint")
     */
    public function get_json_point($code){
      $point = $this->app->Points->getPoint($code);
      return new Response(json_encode($point));
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


    private function datetimeToMs($time){
      $date = "Mon 20 July 2020";
      $newTime = [$date, "".$time.""];

      return strtotime(implode(" ", $newTime))*1000;
  }


  private function msToTime($ms){
    $date = date("Y-m-d H:i:s",$ms/1000);
    $date_array = explode(" ",$date);
    return $date_array[1];
  }
}