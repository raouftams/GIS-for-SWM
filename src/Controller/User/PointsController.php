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
        $point = $this->app->Points->add([
          'secteur' => $_POST['secteur'],
          'code_point' => $_POST['code'],
          'X' => floatval($_POST['longitude']),
          'libelle' => $_POST['libelle'],
          'Y' => floatval($_POST['latitude']),
          'adresse' => $_POST['adresse'],
          'activites' => $_POST['activite']
          
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
          'X' => $_POST['longitude'],
          'Y' => $_POST['latitude'],
          'adresse' => $_POST['adresse'],
          'activites' => $_POST['activite'],
          'secteur' => $_POST['secteur'],
        ]);
        return $this->index();
      }
      $point = $this->app->Points->getPoint($code);
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
}