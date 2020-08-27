<?php

namespace App\Controller\User;

use App\Controller\AppController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PlanController extends AbstractController{


  public function __construct(){
   // parent::__construct();
    $this->app = new AppController();
	$this->app->loadModel('Plan');
	$this->app->loadModel('Vehicle');
  }


    /**
      * @Route("/dashboard/planCollecte", methods={"POST","GET"}, name="planCollecte")
    */    
    public function index(){
		$date_plan = $code_plan = $etat = $etat_plan = null;
		if(!empty($_POST)){
			$used_plan = $this->app->Plan->getPlan($_POST["code_plan"]);
		}else{
			$used_plan = $this->app->Plan->getUsedPlan();
		}

		$codes = $this->app->Plan->getCodes();
		foreach($used_plan as $k=>$line){
			for ($i=0; $i < count($line); $i++) { 
				unset($used_plan[$k][$i]);
			}
			$vehicle = ''. $line["code"] . '_' . $line["matricule"] . '_' . $line["genre"] . '_' .$line["volume"] .'';
			$used_plan[$k]["vehicle"] = $vehicle;
			$code_plan = $line["code_plan"];
			$date_plan = $line["date"];	
			$etat = $line["etat"];
		}

		if ($etat == 'used') {
			$etat_plan = 'En utilisation';
		}else{
			$etat_plan = 'Non utilisé';
		}

		return $this->render("public/planCollecte/planCollecte.html.twig",[
			"data" => $used_plan,
			"codes" => $codes,
			"date_plan" => $date_plan,
			"code_plan" => $code_plan,
			"etat_plan" => $etat_plan
		]);       
	}
	
	/**
      * @Route("/dashboard/editPlan/{code}", methods={"POST", "GET"}, name="editPlan")
	*/  
	public function editPlan($code){
		$result = null;
		if(!empty($_POST)){
			$secteur = $_POST["secteur"];
			unset($_POST["secteur"]);
			$this->app->Plan->edit($code, $secteur, $_POST);
			$result = "Plan modifié avec succès";
		}
		$vehicles = $this->app->Vehicle->vehiculesEnMarche();
		$used_plan = $this->app->Plan->getPlan($code);
		foreach($used_plan as $k=>$line){
			for ($i=0; $i < count($line); $i++) { 
				unset($used_plan[$k][$i]);
			}
			$vehicle = ''. $line["code"] . '_' . $line["matricule"] . '_' . $line["genre"] . '_' .$line["volume"] .'';
			$used_plan[$k]["vehicle"] = $vehicle;
		}
		
		return $this->render("public/planCollecte/editPlan.html.twig",[
			"data" => $used_plan,
			"vehicles" => $vehicles,
			"code_plan" => $code,
			"result" => $result
		]);
	}

	/**
      * @Route("/dashboard/usePlan/{code}", methods={"POST", "GET"}, name="usePlan")
	*/
	public function usePlan($code, Request $request){
		$init = $this->app->Plan->initPlanUse();
		$result = $this->app->Plan->setInUse($code);
		return new Response("Opération réussie, Le plan selectionner est en cours d'utilisation");
	}

	/**
      * @Route("/dashboard/deletePlan/{code}", methods={"POST", "GET"}, name="deletePlan")
	*/
	public function deletePlan($code){
		$plan = $this->app->Plan->getPlan($code);
		foreach ($plan as $line) {
			if ($line["etat"] == 'used') {
				return new Response("Attention: Le plan est en cours d'utilisation, choisissez un autre plan puis réessayer.");
			}
		}
		$this->app->Plan->deletePlan($code);
		return new Response("Opération réussie, Le plan a été supprimé.");
	}


    
}