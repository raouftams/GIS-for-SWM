<?php

namespace App\Controller\User;

use App\Controller\AppController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class TourneeController extends AbstractController{


  public function __construct(){
   // parent::__construct();
    $this->app = new AppController();
    $this->app->loadModel('Tournee');
    $this->app->loadModel('Points');
    
  }

    /**
	 * @Route("/dashboard/tournees", name="tournees")
	 */
    public function index(){
        $tournees = $this->app->Tournee->allTable();
      return $this->render('public/tournee/tournee.html.twig', ["tournees" => $tournees]);
    }

    /**
     * @Route("/user/tournee/{id}", methods={"POST","GET"}, name="tourneeDetail")
     */
    public function detail($id){
        $tournee = $this->app->Tournee->findWithId($id);
        return $this->render('public/tournee/tourneeDetail.html.twig', ["tournee" => $tournee, "id_tournee" => $id]);
    }

    /**
     * @Route("/user/tournee/{id}/update", methods={"POST","GET"}, name="updateTournee")
     */
    public function update($id){
      if(empty($_POST)){
        $tournee = $this->app->Tournee->findWithId($id);
        $t = $tournee[0];
        for ($i=0; $i < count($t) ; $i++) { 
          unset($t[$i]);
        }
        return $this->render('public/tournee/edit.html.twig', ["tournee"=>$t, "result"=>null]);
      }
      return new Response("azul");
      
    }

    /**
     * @Route("/dashboard/tournees/addTournee", methods={"POST","GET"}, name="addTrounee")
     */
    public function ajouter(){
      if (!empty($_POST)) {
        $result = false;
        if ($result){
          return $this->render('public/tournee/add.html.twig',[
            "result" => "Tournée inserée",
          ]);
        }
      }
      return $this->render('public/tournee/add.html.twig',[
        "result" => null
      ]);
    }
    /**
	 * @Route("/dashboard/tournee/{id}/getPoints", methods={"GET","POST"}, name="getPointsTournee")
	 */
	public function getPoints($id){
		$points = $this->app->Points->pointsTournee($id);
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
     * @Route("/dashboard/tournee/{id}/getStops", methods={"POST","GET"}, name="getStops")
     */
    public function getStops($id){
        $points = $this->app->Points->pointsTournee($id);
		$features = [];
        foreach($points as $point){
		  unset($point['geom']);
		  for ($i=0; $i <count($point) ; $i++) { 
			  unset($point[$i]);
          }
          $geo = json_decode($point['geojson'],true);
		  $geometry=["x"=>$geo['coordinates'][0],"y"=>$geo['coordinates'][1]];
          unset($point['geojson']);
          $attributes = [
              "name" => $point['libelle']
            ];
          $feature = ["geometry"=>$geometry, "attributes"=>$attributes];
          array_push($features, $feature);
        }
		$featureCollection = ["features"=>$features];
		return new Response(json_encode($featureCollection));
    }
  
    public function transform($response){
        $result = json_decode($response,true);
        $features = [];
        foreach($result['routes']['features'] as $row){
            $type = "MultiLineString";
            if(array_key_exists('geometry',$row)){
                $coordinates=$row['geometry']['paths'];
                $geometry = ['type' => $type, 'coordinates' => $coordinates];
                $feature = ["type"=>"Feature", "geometry"=>$geometry, "properties"=>$row['attributes']];
                array_push($features, $feature);
            }
            
          }
          $featureCollection = ["type"=>"FeatureCollection", "features"=>$features];
          return json_encode($featureCollection);
    }

    /**
     * @Route("dashboard/tournee/{id}/route", name="routeService", methods={"GET","POST"})
     */
    public function getRoute($id){
        $url = "https://route.arcgis.com/arcgis/rest/services/World/Route/NAServer/Route_World/solve?";
        $token = 'cTDBZ24ZnAxLicn7qyWuYycxk_cD5L_QZklKOXtsDk4qjw0__PSnhSz1zL_Sw1wz70W63yYzQDffK6j7QBAd9zctFBIjilUXpicAj442AFS_Uy-YQ1op4932ZZbRBnLq-7W6B6rrNtg7v7Vro-UFQw.';    
        $stops = $this->getStops($id)->getContent();
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, 'stops='. str_replace("'", "", $stops) . '&f=json&token='.$token.'');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);
        return new Response($this->transform($response));
    }
}   
