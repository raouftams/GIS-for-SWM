<?php

namespace App\Controller;


use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class IndexController extends AbstractController{


  public function __construct(){
   // parent::__construct();
    $this->app = new AppController();
    $this->app->loadModel('Points');
    $this->app->loadModel('Bacs');
    
  }

    /**
	 * @Route("/dashboard", name="dashboard")
	 */
    public function index(){
      $values=[];
      $secteurs = [];
      $qte = $this->app->Bacs->qte();
      for($i = 0; $i<10; $i++) {
        $values[$i] = $qte[$i]['qte'];
        $secteurs[$i] =  "{$qte[$i]['secteur']}";
      }
      $val = implode(",", $values);
      $sec = implode(",", $secteurs);
      
      return $this->render('public/dashboard.html.twig',["qte"=>$val, "secteur"=>$sec]);
    }
  /**
   * @Route("/dashboard/charts", name="charts")
   */
  public function charts(){
    $values=[];
    $secteurs = [];
    $qte = $this->app->Bacs->qte();
    for($i = 0; $i<10; $i++) {
      $values[$i] = $qte[$i]['qte'];
      $secteurs[$i] =  "{$qte[$i]['secteur']}";
    }
    $val = implode(",", $values);
    $sec = implode(",", $secteurs);
    return $this->render('public/charts.html.twig',["qte"=>$val, "secteur"=>$sec, "secteurQte"=> $qte]);
  }
}