<?php

namespace App\Controller\User;

use App\Controller\AppController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class VRPController extends AbstractController{

    private $orders;
    private $depots;
    private $routes;
    

  public function __construct(){
   // parent::__construct();
    $this->app = new AppController();
    $this->app->loadModel('Points');
    $this->app->loadModel('Parc');
    $this->app->loadModel('Vehicle');
    $this->app->loadModel('CET');
  }

    /**
     * @Route("/dashboard/analyseVrp", name="analyseVrp")
     */
    public function analyse(){
        $parcs = $this->app->Parc->all();
        $cets = $this->app->CET->all();
        $depots = [];
        foreach ($parcs as $parc) {
            array_push($depots, $parc);
        }
        foreach ($cets as $cet){
            array_push($depots, $cet);
        }
        return $this->render('public/analyseVrp.html.twig', ["depots"=>$depots]);
    }
    /**
     * @Route("/dashboard/maps/VRP/getOrders", name="getOrders")
     */
    public function getOrders(){
        $points = $this->app->Points->allVrp();
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
                "DeliveryQuantities" => $point['quantited'],
                "Name" => $point['code_point'],
                "ServiceTime" => 4,
                "TimeWindowStart1" => 1355245200000,
                "TimeWindowEnd1" => 1355274000000,
                "MaxViolationTime1" => 0
            ];
            $feature = ["geometry"=>$geometry, "attributes"=>$attributes];
            array_push($features, $feature);
        }
		$featureCollection = ["features"=>$features];
		return new Response(json_encode($featureCollection));
    }

    /**
     * @Route("/dashboard/maps/VRP/getDepots", name="getDepots")
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
		  	$geo = json_decode($parc['geojson'],true);
		      $geometry=["x"=>$geo['coordinates'][0],"y"=>$geo['coordinates'][1]];
              unset($parc['geojson']);
              $attributes = [
                  "Name" => "Parc Babez",
                  "TimeWindowStart1" => 1355241600000,
                  "TimeWindowEnd1" => 1355274000000
                ];
		  	$feature = ["geometry"=>$geometry, "attributes"=>$attributes];
		  	array_push($features, $feature);
      }
      foreach($cets as $cet){
        unset($cet['geom']);
        for ($i=0; $i <count($cet) ; $i++) { 
          unset($cet[$i]);
        }
        $geo = json_decode($cet['geojson'],true);
		      $geometry=["x"=>$geo['coordinates'][0],"y"=>$geo['coordinates'][1]];
              unset($cet['geojson']);
              $attributes = [
                  "Name" => $cet['designation'],
                  "TimeWindowStart1" => 1355241600000,
                  "TimeWindowEnd1" => 1355274000000
                ];
        $feature = ["type"=>"Feature", "geometry"=>$geometry, "attributes"=>$attributes];
        array_push($features, $feature);
      }
        $featureCollection = ["features"=>$features];
		  return new Response(json_encode($featureCollection));
    }

    /**
     * @Route("/dashboard/maps/VRP/getRoutes", name="getRoutes")
     */
    public function getRoutes(){
        $vehicles = $this->app->Vehicle->all();
        $features = [];
        $j = 1;
		foreach($vehicles as $vehicle){
			for ($i=0; $i <count($vehicle) ; $i++) { 
				unset($vehicle[$i]);
            }
            if($vehicle['etat'] === "En marche"){
                $attributes = [
                    "Name"=>"Truck_".$j,
                    "StartDepotName"=>"Parc Babez",
                    "EndDepotName"=>"CET CORSO",
                    "StartDepotServiceTime"=>60,
                    "EarliestStartTime"=>1355241600000,
                    "LatestStartTime"=>1355241600000,
                    "Capacities"=> "".$vehicle['volume']*1000 ."",
                    "CostPerUnitTime"=>0.2,
                    "CostPerUnitDistance"=>1.5,
                    "MaxOrderCount"=>30,
                    "MaxTotalTime" => 360,
                    "MaxTotalTravelTime" => 120,
                    "MaxTotalDistance" => 80
                  ];
                $feature = ["attributes"=>$attributes];
                array_push($features, $feature);
                $j++;
            }
		}
        $featureCollection = ["features"=>$features];
		return new Response(json_encode($featureCollection));
    }
       
    /**
	 * @Route("/dashboard/maps/VRP", name="vrpService")
	 */
    public function index(Request $request){
        $features = [];
        $data = json_decode($request->getContent(), true);
        $depots = isset($data['depots']) ? $data['depots']: null;
        $vehicules = isset($data['vehicles']) ? $data['vehicles'] : null;
        $points = isset($data['points']) ? $data['points'] : null;
        if($depots != null && $vehicules!=null && $points != null){
            $this->orders = $this->transformeOrders($points)->getContent();
            $this->depots = $this->transformeDepots($depots)->getContent();
            $this->routes = $this->transformeRoutes($vehicules)->getContent();
        }
        $url = 'https://logistics.arcgis.com/arcgis/rest/services/World/VehicleRoutingProblem/GPServer/SolveVehicleRoutingProblem/submitJob?';
        $token = 'w02yUBjLyYhQO1216vX7oDmkBuRoyQLAVFA5R4I73_5CeWrUzIVELDvarDA2XRVcJ0DOxfeXxDGf53YwgfX4TdeGIV2SZINLXZmS7DwazTmnnlMZyg25dFycZH6GKKu5h1jCM1LTmNicRyjchJOiRQ..';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, 'orders='. str_replace("'","",$this->orders) . '&depots='. str_replace("'","",$this->depots) . '&routes='. str_replace("'","",$this->routes) . '&time_units=Minutes&distance_units=Kilometers&uturn_policy=NO_UTURNS&populate_directions=true&directions_language=en&default_date=1355212800000&f=json&token='.$token.'');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);
        $jobId = json_decode($response,true)['jobId'];
        
        sleep(15);

        //Request for routes
        $routesUrl = "https://logistics.arcgis.com/arcgis/rest/services/World/VehicleRoutingProblem/GPServer/SolveVehicleRoutingProblem/jobs/".$jobId."/results/out_routes?";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $routesUrl);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, 'f=json&token='.$token.'');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);
        
        $result = json_decode($response,true);
        $features = [];
        foreach($result['value']['features'] as $row){
            $type = "MultiLineString";
            if(array_key_exists('geometry',$row)){
                $coordinates=$row['geometry']['paths'];
                $geometry = ['type' => $type, 'coordinates' => $coordinates];
                $feature = ["type"=>"Feature", "geometry"=>$geometry, "properties"=>$row['attributes']];
                array_push($features, $feature);
            }
            
          }
          $featureCollection = ["type"=>"FeatureCollection", "features"=>$features];
        return new Response(json_encode($featureCollection));
    }

    /**
     * @Route("dashboard/analyseVrp/receiveData", name="receiveData")
     */
    public function receiveData(Request $request){
        $data = json_decode($request->getContent(), true);
        $depots = isset($data['depots']) ? $data['depots']: null;
        $vehicules = isset($data['vehicles']) ? $data['vehicles'] : null;
        $points = isset($data['points']) ? $data['points'] : null;
        if($depots != null && $vehicules!=null && $points != null){
            $this->orders = $this->transformeOrders($points)->getContent();
            $this->depots = $this->transformeDepots($depots)->getContent();
            $this->routes = $this->transformeRoutes($vehicules)->getContent();
        }
    }


    private function transformeOrders($points){
        $features = [];
        foreach($points as $point){
            $geo = $point['geometry'];
            $geometry=["x"=>$geo['coordinates'][0],"y"=>$geo['coordinates'][1]];
            $attributes = [
                "DeliveryQuantities" => $point['properties']['quantited'],
                "Name" => $point['properties']['code_point'],
                "ServiceTime" => 4,
                "TimeWindowStart1" => 1355245200000,
                "TimeWindowEnd1" => 1355274000000,
                "MaxViolationTime1" => 0
            ];
            $feature = ["geometry"=>$geometry, "attributes"=>$attributes];
            array_push($features, $feature);
        }
		$featureCollection = ["features"=>$features];
		return new Response(json_encode($featureCollection));
    }

    private function transformeDepots($depots, $cets = null){
        $parcs = [];
        
        foreach($depots as $depot){
            
            $parc = $this->app->Parc->find($depot['code']);
            if($parc != null){
                array_push($parcs, $parc[0]);
            }else{
                $cet = $this->app->CET->find($depot['code']);
                if($cet != null){
                    array_push($parcs, $cet[0]);
                }
            }
        }
        $features = [];
		foreach($parcs as $parc){
            unset($parc['geom']);
		  	for ($i=0; $i <count($parc) ; $i++) { 
                  unset($parc[$i]);
              }
			$geo = json_decode($parc['geojson'],true);
		    $geometry=["x"=>$geo['coordinates'][0],"y"=>$geo['coordinates'][1]];
            unset($parc['geojson']);
            $attributes = [
                "Name" => $parc['designation'],
                "TimeWindowStart1" => 1355241600000,
                "TimeWindowEnd1" => 1355274000000
              ];
			$feature = ["geometry"=>$geometry, "attributes"=>$attributes];
			array_push($features, $feature);
        }
        $featureCollection = ["features"=>$features];
		return new Response(json_encode($featureCollection));
    }

    private function transformeRoutes($vehicles){
        $features = [];
        $j = 1;
		foreach($vehicles as $vehicle){
            $attributes = [
                "Name"=>"Truck_".$j,
                "StartDepotName"=>$vehicle['depotDepart'],
                "EndDepotName"=>$vehicle['depotFin'],
                "StartDepotServiceTime"=>60,
                "EarliestStartTime"=>1355241600000,
                "LatestStartTime"=>1355241600000,
                "Capacities"=> "" . (int)$vehicle['volume']*1000 . "",
                "CostPerUnitTime"=> (float)$vehicle['costUnitTime'],
                "CostPerUnitDistance"=>(float)$vehicle['costUnitDistance'],
                "MaxOrderCount"=>(int)$vehicle['nbrMaxOrdres'],
                "MaxTotalTime" => 360,
                "MaxTotalTravelTime" => 120,
                "MaxTotalDistance" => 80
              ];
            $feature = ["attributes"=>$attributes];
            array_push($features, $feature);
            $j++;
		}
        $featureCollection = ["features"=>$features];
		return new Response(json_encode($featureCollection));
    }
}