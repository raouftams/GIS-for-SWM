<?php

namespace App\Controller\User;
use App\Controller\AppController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MapController extends AbstractController{


	public function __construct(){
	// parent::__construct();
		$this->app = new AppController();
		$this->app->loadModel('Points');
		$this->app->loadModel('Secteur');
		$this->app->loadModel('Parc');
		$this->app->loadModel('CET');
		$this->app->loadModel('Route');
		$this->app->loadModel('Bacs');
		$this->app->loadModel('Quartier');
		$this->app->loadModel('Organisme');
		
	}

	/**
	 * @Route("/dashboard/maps", name="maps")
	 */
  	public function maps(){
		return $this->render('public/maps.html.twig');
	}

	/**
	 * @Route("/dashboard/maps/getPoints", name="getPoints")
	 */
	public function getPoints(){
		$points = $this->app->Points->all();
		$features = [];
        foreach($points as $point){
		  unset($point['geom']);
		  for ($i=0; $i <count($point) ; $i++) { 
			  unset($point[$i]);
		  }
		  $geometry=json_decode($point['geojson']);
		  unset($point['geojson']);
          $feature = ["type"=>"Feature", "geometry"=>$geometry, "properties"=>$point];
          array_push($features, $feature);
        }
		$featureCollection = ["type"=>"FeatureCollection", "features"=>$features];
		return new Response(json_encode($featureCollection));
	}

	/**
	 * @Route("/dashboard/maps/getSecteurs", name="getSecteurs")
	 */
	public function getSecteur(){
		$secteurs = $this->app->Secteur->all();
		$features = [];
        foreach($secteurs as $secteur){
		  unset($secteur['geom']);
		  for ($i=0; $i <count($secteur) ; $i++) { 
			  unset($secteur[$i]);
		  }
		  $geometry=json_decode($secteur['geojson']);
		  unset($secteur['geojson']);
          $feature = ["type"=>"Feature", "geometry"=>$geometry, "properties"=>$secteur];
          array_push($features, $feature);
        }
		$featureCollection = ["type"=>"FeatureCollection", "features"=>$features];
		return new Response(json_encode($featureCollection)); 
	}

	/**
	 * @Route("/dashboard/maps/getnewsecteurs/{planSecCode}", methods={"POST","GET"}, name="getNewSecteurs")
	 */
	public function getNewSecteurs($planSecCode){
		$secteurs = $this->app->Secteur->getPlanSecteurs($planSecCode);
		$features = [];
        foreach($secteurs as $secteur){
		  unset($secteur['geom']);
		  for ($i=0; $i <count($secteur) ; $i++) { 
			  unset($secteur[$i]);
		  }
		  $geometry=json_decode($secteur['geojson']);
		  unset($secteur['geojson']);
          $feature = ["type"=>"Feature", "geometry"=>$geometry, "properties"=>$secteur];
          array_push($features, $feature);
        }
		$featureCollection = ["type"=>"FeatureCollection", "features"=>$features];
		return new Response(json_encode($featureCollection)); 
	}


	/**
	 * @Route("/dashboard/maps/getQuartiers", name="getQuartiers")
	 */
	public function getQuartier(){
		$quartiers = $this->app->Quartier->all();
		$features = [];
        foreach($quartiers as $quartier){
		  unset($quartier['geom']);
		  for ($i=0; $i <count($quartier) ; $i++) { 
			  unset($quartier[$i]);
		  }
		  $geometry=json_decode($quartier['geojson']);
		  unset($quartier['geojson']);
          $feature = ["type"=>"Feature", "geometry"=>$geometry, "properties"=>$quartier];
          array_push($features, $feature);
        }
		$featureCollection = ["type"=>"FeatureCollection", "features"=>$features];
		return new Response(json_encode($featureCollection)); 
	}
	
	/**
	 * @Route("/dashboard/maps/getOrganisme", name="getOrganismes")
	 */
	public function getOrganisme(){
		$organismes = $this->app->Organisme->all();
		$features = [];
        foreach($organismes as $organisme){
		  unset($organisme['geom']);
		  for ($i=0; $i <count($organisme) ; $i++) { 
			  unset($organisme[$i]);
		  }
		  $geometry=json_decode($organisme['geojson']);
		  unset($organisme['geojson']);
          $feature = ["type"=>"Feature", "geometry"=>$geometry, "properties"=>$organisme];
          array_push($features, $feature);
        }
		$featureCollection = ["type"=>"FeatureCollection", "features"=>$features];
		return new Response(json_encode($featureCollection)); 
	}

	/**
	 * @Route("/dashboard/maps/getDepots", name="getDepotss")
	 */
	public function getDepots(){
		$parcs = $this->app->Parc->all();
		$cets = $this->app->CET->all();
		$features = [];
		foreach($parcs as $parc){
			unset($parc['geom']);
			for ($i=0; $i <count($parc) ; $i++) { 
				unset($parc[$i]);
			}
			$geometry=json_decode($parc['geojson']);
			unset($parc['geojson']);
			$feature = ["type"=>"Feature", "geometry"=>$geometry, "properties"=>$parc];
			array_push($features, $feature);
		}
		foreach($cets as $cet){
			unset($cet['geom']);
			for ($i=0; $i <count($cet) ; $i++) { 
				unset($cet[$i]);
			}
			$geometry=json_decode($cet['geojson']);
			unset($cet['geojson']);
			$feature = ["type"=>"Feature", "geometry"=>$geometry, "properties"=>$cet];
			array_push($features, $feature);
		}
		$featureCollection = ["type"=>"FeatureCollection", "features"=>$features];
		return new Response(json_encode($featureCollection)); 
	}

	/**
	 * @Route("user/maps/getBacs/{code_point}", methods={"POST","GET"}, name="getBacs")
	 */
	public function getBacs($code_point){
		$bacs = $this->app->Bacs->getBacsPoint($code_point);
		dump($bacs);
		return new Response(json_encode($bacs)); 
	}


	/**
	 * @Route("dashboard/maps/getRoutes", name="getRoutes")
	 */
	public function getRoutes(){
		$routes = $this->app->Route->all();
		$features = [];
        foreach($routes as $route){
		  unset($route['geom']);
		  for ($i=0; $i <count($route) ; $i++) { 
			  unset($route[$i]);
		  }
		  $geometry=json_decode($route['geojson']);
		  unset($route['geojson']);
          $feature = ["type"=>"Feature", "geometry"=>$geometry, "properties"=>$route];
          array_push($features, $feature);
        }
		$featureCollection = ["type"=>"FeatureCollection", "features"=>$features];
		return new Response(json_encode($featureCollection)); 
	}


	/**
	 * @Route("dashboard/maps/saveSectorization", methods={"POST","GET"}, name="saveSectorization")
	 */
	public function saveSectorization(Request $request){
		$json = $request->getContent();
		$data = json_decode($json, true);

		foreach($data["features"] as $feature){
			$this->app->Secteur->updateSectorization($feature["properties"]["code"], json_encode($feature["geometry"]));
		}

		return new Response("Données mises à jour");
		
	}


	/**
	 * @Route("dashboard/maps/editPoints", methods={"POST","GET"}, name="editMapsPoint")
	 */
	public function editPointFromMaps(Request $request){
		$date = "Mon 20 July 2020";
		$data = json_decode($request->getContent(), true);
		$code_point = $data["code_point"];
		unset($data["code_point"]);
		$dft = [$date, "".$data["debut_fenetre_temps1"].""];
        $fft = [$date, "".$data["fin_fenetre_temps1"].""];
        $data = ["adresse"=>$data["adresse"], "libelle"=>$data["libelle"], "debut_fenetre_temps1"=> strtotime(implode(" ", $dft))*1000+60*60000, "fin_fenetre_temps1"=>strtotime(implode(" ", $fft))*1000+60*60000];
        
		$edit = $this->app->Points->update($code_point, $data);
		return new Response("Données mises à jour avec Success");
	}

	/**
	 * @Route("dashboard/maps/editSecteurMap", methods={"POST", "GET"}, name="editSecteurMap")
	 */
	public function editSecteurFromMaps(Request $request){
		$data = json_decode($request->getContent(),true);
		$code = $data["code"];
		unset($data["code"]);
		$this->app->Secteur->update($code, $data);
		return new Response("Secteur modifié avec succès.");
	}
}