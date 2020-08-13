<?php

namespace App\Controller\User;

use App\Controller\AppController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ChartsController extends AbstractController{


  public function __construct(){
   // parent::__construct();
    $this->app = new AppController();
    $this->app->loadModel('Points');
    $this->app->loadModel('Bacs');
    $this->app->loadModel('Tournee');
    $this->app->loadModel('Secteur');
    
  }

   
  /**
   * @Route("/dashboard/charts", name="charts")
   */
  public function charts(){

    return $this->render('public/charts.html.twig');
  }

  /**
   * @Route("/dashboard/charts/data1", name="getdatachart1")
   */
  public function QteParSec(){
    
    $qtesec = $this->app->Secteur->qte(); 
    foreach ($qtesec as $key => $value) {
      unset($qtesec[$key][0]);
      unset($qtesec[$key][1]);
      $qtesec[$key]["data"] = floatval($qtesec[$key]["data"]);
    }

    return new Response(json_encode($qtesec));
  }

   /**
    * @Route("dashboard/charts/data2", name = "getdatachart2")
    */
    public function QteMois(){
      $qtemois = $this->app->Tournee->qtemois();
      foreach ($qtemois as $key => $value) {
      unset($qtemois[$key][0]);
      unset($qtemois[$key][1]);
      $qtemois[$key]["data"] = floatval($qtemois[$key]["data"]);
     }
      return new Response(json_encode($qtemois));
      }

    /**
     * @Route("dashboard/charts/qteDechets", name= "getQteDechets")
     */
    public function qteDechets(){
      $qtePrevue = $qteRealisee = [];
      $qtemois = $this->app->Tournee->qteRealiseEtPrevue();
      foreach ($qtemois as $key => $value) {
      unset($qtemois[$key][0]);
      unset($qtemois[$key][1]);
      array_push($qtePrevue, ["label" => $qtemois[$key]["label"], "data" => floatval($qtemois[$key]["prevuedata"])]);
      array_push($qteRealisee, ["label" => $qtemois[$key]["label"], "data" => floatval($qtemois[$key]["realisedata"])]);
     }
      return new Response(json_encode(["qtePrevue"=>$qtePrevue, "qteRealisee"=>$qteRealisee]));
    }
  
  /**
   * @Route("/dashboard/charts/data3", name ="getdatachart3")
   */
  public function DistanceVehicle(){
    $distvehic = $this->app->Tournee->distancevehicle();
    foreach ($distvehic as $key => $value) {
      unset($distvehic[$key][0]);
      unset($distvehic[$key][1]);
      $distvehic[$key]["data"] = floatval($distvehic[$key]["data"]);
     }
    return new Response(json_encode($distvehic));
  }

  /**
   * @Route("/dashboard/charts/data4", name = "getdatachart4")
   */
  public function TempsVehicle(){
    $tpsvh = $this->app->Tournee->tempvehicle();
    foreach ($tpsvh as $key => $value) {
      unset($tpsvh[$key][0]);
      unset($tpsvh[$key][1]);
      $tpsvh[$key]["data"] = floatval($tpsvh[$key]["data"]);
     }
    return new Response(json_encode($tpsvh));
  }

  /**
   * @Route("/dashboard/charts/data5", name = "getdatachart5")
   */
  public function TourneEquipe(){
    $trneqp = $this->app->Tournee->tourneeequipe();// explode tournée + equipe
    foreach ($trneqp as $key => $value) {
      unset($trneqp[$key][0]);
      unset($trneqp[$key][1]);
      $trneqp[$key]["data"] = floatval($trneqp[$key]["data"]);
     }
    return new Response(json_encode($trneqp));
  }

  /**
   * @Route("dashboard/charts/data6", name = "getdatachart6")
   */
   public function TempEquipe(){
    $tmpeqp = $this->app->Tournee->tempsequipe();
    foreach ($tmpeqp as $key => $value) {
      unset($tmpeqp[$key][0]);
      unset($tmpeqp[$key][1]);
      $tmpeqp[$key]["data"] = floatval($tmpeqp[$key]["data"]);
     }
     return new Response(json_encode($tmpeqp));
   }

  /**
   * @Route("dashboard/charts/data7", name = "getdatachart7")
   */
   public function QteVehicle(){
    $qtevehicle = $this->app->Tournee->qtevehicle();// qte + vehicule
    foreach ($qtevehicle as $key => $value) {
      unset($qtevehicle[$key][0]);
      unset($qtevehicle[$key][1]);
      $qtevehicle[$key]["data"] = floatval($qtevehicle[$key]["data"]);
     }
    return new Response(json_encode($qtevehicle));
   }

   /**
   * @Route("/dashboard/charts/data8", name = "getdatachart8")
   */
  public function TourneePrVehicle(){
    $trnvhc = $this->app->Tournee->tourneevehicle();
    foreach ($trnvhc as $key => $value) {
      unset($trnvhc[$key][0]);
      unset($trnvhc[$key][1]);
      $trnvhc[$key]["data"] = floatval($trnvhc[$key]["data"]);
     }
    return new Response(json_encode($trnvhc));
  }
  

}