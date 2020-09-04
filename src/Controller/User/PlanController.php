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
	$this->app->loadModel('RotationPrevue');
	$this->app->loadModel('PlanCollecte');
	$this->app->loadModel('PlanSectorisation');
	$this->app->loadModel('Vehicle');
	$this->app->loadModel('Points');
	$this->app->loadModel('Secteur');
	$this->app->loadModel('Equipe');
  }


    /**
      * @Route("/dashboard/planCollecte", methods={"POST","GET"}, name="planCollecte")
    */    
    public function index(){
		$date_plan = $code_plan = $etat = $etat_plan = $validite = null;
		if(!empty($_POST)){
			$used_plan = $this->app->RotationPrevue->getPlan($_POST["code_plan"]);
		}else{
			$used_plan = $this->app->RotationPrevue->getUsedPlan();
		}
		
		$codes = $this->app->PlanCollecte->All();
		foreach($used_plan as $k=>$line){
			for ($i=0; $i < count($line); $i++) { 
				unset($used_plan[$k][$i]);
			}
			$vehicle = ''. $line["code"] . '_' . $line["matricule"] . '_' . $line["genre"] . '_' .$line["volume"] .'';
			$used_plan[$k]["vehicle"] = $vehicle;
			$code_plan = $line["code_plan"];
			$date_plan = $line["date"];	
			$etat = $line["etat"];
			$validite = $line["fin_validite"];
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
			"etat_plan" => $etat_plan,
			"fin_validite" => $validite
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
			$this->app->RotationPrevue->edit($code, $secteur, $_POST);
			$result = "Plan modifié avec succès";
		}
		$vehicles = $this->app->Vehicle->vehiculesEnMarche();
		$equipes = $this->app->Equipe->all();
		$used_plan = $this->app->RotationPrevue->getPlan($code);
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
			"equipes" => $equipes,
			"code_plan" => $code,
			"result" => $result
		]);
	}

	/**
      * @Route("/dashboard/usePlan/{code}", methods={"POST", "GET"}, name="usePlan")
	*/
	public function usePlan($code, Request $request){
		$init = $this->app->PlanCollecte->initPlanUse();
		$result = $this->app->PlanCollecte->setInUse($code);
		$updatePoints = $this->app->Points->updateSectorisationPoints();
		return new Response("Opération réussie, Le plan selectionner est en cours d'utilisation");
	}

	/**
      * @Route("/dashboard/deletePlan/{code}", methods={"POST", "GET"}, name="deletePlan")
	*/
	public function deletePlan($code){
		$this->app->loadModel("Planning");
		
		$plan = $this->app->PlanCollecte->getEtat($code);
		
		if ($plan[0]["etat"] == 'used') {
			return new Response("Attention: Le plan est en cours d'utilisation, choisissez un autre plan puis réessayer.");
		}

		$sectorisation = $this->app->PlanCollecte->getSectorisationPlan($code)[0]["sectorisation"];

		$this->app->Planning->delete($code);
		$this->app->RotationPrevue->delete($code);
		$this->app->PlanCollecte->deletePlan($code);

		//Supprimer le plan de sectorisation s'il n'est plus utilisé
		if ($this->app->PlanSectorisation->isUsed($sectorisation) == false) {
			$this->app->Points->deleteSequence($sectorisation);
			$this->app->Secteur->deleteSectorisation($sectorisation);
			$this->app->PlanSectorisation->deletePlan($sectorisation);
		}
		return new Response("Opération réussie, Le plan a été supprimé.");
	}



    
}