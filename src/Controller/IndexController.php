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
      $values=[];
      $secteurs = [];
      $qte = $this->app->Bacs->qte();
      
      $tournees = $this->app->Tournee->getTourneesEnAttente();
      $nbTourneesAttente = count($tournees);
      $nbTourneesEffectuees = $this->app->Tournee->nbTournee()[0];
      $nbTourneesEnCours = $this->app->Tournee->nbTourneesEnCours()[0];
      return $this->render('public/dashboard.html.twig',["qte"=>$qte, "tournees" => $tournees, "nbTourneesAttente" => $nbTourneesAttente, "nbTourneesEffectuees" => $nbTourneesEffectuees["count"], "tourneesEnCours"=>$nbTourneesEnCours["count"]] );
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

  /**
   * @Route("/dashboard/charts", name="charts")
   */
  public function charts(){
    return $this->render('public/charts.html.twig');
  }
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  /**
   * @Route("/dashboard/charts/data1", name="getdatachart1")
   */
  public function QteParSec(){
    
    $qtesec = $this->app->Bacs->qte();
    $var1 = explode('+',$qtesec);
    $qte = explode(',',$var1[0]);
    for($i=0;$i<sizeof($qte);$i++){
      $qte[$i]=intval($qte[$i]);
    }
    $var1[0] = implode(',',$qte);

    return new Response(json_encode($var1));
  }
  /**
   * @Route("/dashboard/charts/data8", name = "getdatachart8")
   */
  public function TourneePrVehicle(){
    $trnvhc = $this->app->Bacs->tourneevehicle();
    $var1 = explode("+", $trnvhc);
    $trn = explode(',',$var1[0]);
    for($i=0;$i<sizeof($trn);$i++){
      $trn[$i]=intval($trn[$i]);
    }
    $var1[0] = implode(',',$trn);

    return new Response(json_encode($var1));
  }
  /**
   * @Route("/dashboard/charts/data3", name ="getdatachart3")
   */
  public function DistanceVehicle(){
    $distvehic = $this->app->Bacs->distancevehicle();
    $var1 = explode('+', $distvehic);
    $distn = explode(',',$var1[0]);
    for($i=0;$i<sizeof($distn);$i++){
      $distn[$i]=intval($distn[$i]);
    }
    $var1[0] =json_encode($distn);
    return new Response(json_encode($var1));
  }
  /**
   * @Route("/dashboard/charts/data4", name = "getdatachart4")
   */
  public function TempsVehicle(){
    $tpsvh = $this->app->Bacs->tempvehicle();
    $var1 = explode('+',$tpsvh);
    $temps = explode(';',$var1[0]);
    for($i=0;$i<sizeof($temps);$i++){
      $temps[$i]=intval($temps[$i]);
    }
    $var1[0] = implode(',',$temps);
    return new Response(json_encode($var1));
  }
  /**
   * @Route("/dashboard/charts/data5", name = "getdatachart5")
   */
  public function TourneEquipe(){
    $trneqp = $this->app->Bacs->tourneeequipe();// explode tournée + equipe
    $var1 = explode('+',$trneqp);
    $trn = explode(',',$var1[0]);
    for($i=0;$i<sizeof($trn);$i++){
      $trn[$i]=intval($trn[$i]);
    }
    $var1[0] = implode(',',$trn);

    return new Response(json_encode($var1));
  }
  /**
   * @Route("dashboard/charts/data6", name = "getdatachart6")
   */
   public function TempEquipe(){
    $tmpeqp = $this->app->Bacs->tempsequipe();
    $var1 = explode("+",$tmpeqp);
    $temps = explode(',',$var1[0]);
    for($i=0;$i<sizeof($temps);$i++){
      $temps[$i]=intval($temps[$i]);
    }
    $var1 = implode(',',$temps);
     return new Response(json_encode($var1));
   }
  /**
   * @Route("dashboard/charts/data7", name = "getdatachart7")
   */
   public function QteVehicle(){
    $qtevehicle = $this->app->Bacs->qtevehicle();// qte + vehicule
    $var1 = explode('+',$qtevehicle);
    $qte = explode(',',$var1[0]);
    for($i=0;$i<sizeof($qte);$i++){
      $qte[$i]=intval($qte[$i]);
    }
    $var1[0] = implode(',',$qte);
    return new Response(json_encode($var1));
   }
   /**
    * @Route("dashboard/charts/data2", name = "getdatachart2")
    */
    public function QteMois(){
    $qtemois = $this->app->Bacs->qtemois();
    $var1 = explode("+", $qtemois);
    $qte = explode(',',$var1[0]);
    for($i=0;$i<sizeof($qte);$i++){
      $qte[$i]=intval($qte[$i]);
    }
    $var1[0] = implode(',',$qte);
      return new Response(json_encode($var1));
    }
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}