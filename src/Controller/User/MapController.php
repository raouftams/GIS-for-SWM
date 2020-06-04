<?php

namespace App\Controller\User;
use App\Controller\AppController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
	 * @Route("dashboard/maps/getBacs/{code_point}", methods={"POST","GET"}, name="getBacs")
	 */
	public function getBacs($code_point){
		$bacs = $this->app->Bacs->getBacsPoint($code_point);
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
}