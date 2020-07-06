<?php

namespace App\Controller\User;

use App\Controller\AppController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

class VRPController extends AbstractController{

    private $orders = null;
    private $depots = null;
    private $routes = null;
    

    public function __construct(){
        // parent::__construct();
        $this->app = new AppController();
        $this->app->loadModel('Points');
        $this->app->loadModel('Parc');
        $this->app->loadModel('Vehicle');
        $this->app->loadModel('CET');
        $this->app->loadModel('Secteur');
    }

    /**
     * @Route("/dashboard/analyseVrp", name="analyseVrp")
     */
    public function analyse(){
        $parcs = $this->app->Parc->all();
        $cets = $this->app->CET->all();
        $vehicles = $this->app->Vehicle->vehiculesEnMarche();
        $depots = [];
        foreach ($parcs as $parc) {
            array_push($depots, $parc);
        }
        foreach ($cets as $cet){
            array_push($depots, $cet);
        }
        return $this->render('public/analyseVrp.html.twig', ["depots"=>$depots, "vehicles" => $vehicles]);
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

            for ($i=1; $i <= $point["frequence"]; $i++) { 
                if($i == 1){
                    $attributes = [
                        "PickupQuantities" => intval($point['volumetota'])*0.25/1000,
                        "Name" => "" . $point['code_point'] . "_" . $i,
                        "ServiceTime" => 4,
                        "TimeWindowStart1" => $point["debut_fenetre_temps1"],
                        "TimeWindowEnd1" => $point["fin_fenetre_temps1"],
                        "MaxViolationTime1" => 0
                    ];
                }else{
                    $attributes = [
                        "PickupQuantities" => intval($point['volumetota'])*0.2/1000,
                        "Name" => "" . $point['code_point'] . "_" . $i,
                        "ServiceTime" => 4,
                        "TimeWindowStart1" => $point["debut_fenetre_temps2"],
                        "TimeWindowEnd1" => $point["fin_fenetre_temps2"],
                        "MaxViolationTime1" => 0
                    ];
                }
                $feature = ["geometry"=>$geometry, "attributes"=>$attributes];
                array_push($features, $feature);
            }
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
                  "TimeWindowStart1" => 1355212800000,
                  "TimeWindowEnd1" => 1355245200000
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
                  "TimeWindowStart1" => 1355212800000,
                  "TimeWindowEnd1" => 1355245200000
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
        $vehicles = $this->app->Vehicle->vehiculesEnMarche();
        $features = [];
        $j = 1;
		foreach($vehicles as $vehicle){
			for ($i=0; $i <count($vehicle) ; $i++) { 
				unset($vehicle[$i]);
            }
            $names = explode(" ", $vehicle["genre"]);
            $name = "". $names[0][0] . "" . $names[1][0] ."";
            $attributes = [
                "Name"=>"".$name."" . $vehicle["volume"] ."_". $vehicle["code"] ."_" . $vehicle["matricule"] ."",
                "StartDepotName"=>"Parc Babez",
                "EndDepotName"=>"CET CORSO",
                "EarliestStartTime"=>1355209200000,
                "LatestStartTime"=>1355209200000,
                "Capacities"=> "".$vehicle['volume']*0.4 ."",
                "CostPerUnitTime"=>0.2,
                "CostPerUnitDistance"=>1.5,
                "MaxOrderCount"=>60,
                "MaxTotalTime" => 800,
                "MaxTotalTravelTime" => 800,
                "MaxTotalDistance" => 500
              ];
            $feature = ["attributes"=>$attributes];
            array_push($features, $feature);
            $j++;
        }
        $featureCollection = ["features"=>$features];
		return new Response(json_encode($featureCollection));
    }
    
    /**
     * @Route("/dashboard/maps/VRP/routeRenewals", name="routesRenew")
     */
    public function routeRenewals(){
        $routes = json_decode($this->getRoutes()->getContent(),true)["features"];
        $features =[];
        foreach($routes as $route)  { 
            $attributes = [
                "DepotName" => "CET CORSO",
                "RouteName" => $route["attributes"]["Name"],
                "ServiceTime" => 20
            ];
            $feature = ["attributes"=>$attributes];
            array_push($features, $feature);
        }
        $featureCollection = ["features"=>$features];
        return new Response(json_encode($featureCollection));
    }

    /**
	 * @Route("/dashboard/maps/VRP", name="vrpService")
	 */
    public function index(){
        set_time_limit(100);
        $features = [];

        if ($this->orders == null or $this->depots == null or $this->routes == null) {
            $this->orders = $this->getOrders()->getContent();
            $this->depots = $this->getDepots()->getContent();
            $this->routes = $this->getRoutes()->getContent();
        }

        $routeRenewals = $this->routeRenewals()->getContent();
        $url = 'https://logistics.arcgis.com/arcgis/rest/services/World/VehicleRoutingProblem/GPServer/SolveVehicleRoutingProblem/submitJob?';
        $token = '5N8gkbhlqOva3TqeqNlMxVYxdcUMj0qm9G14Hk3g31UDAeDt4viqBb7q-mL4L3Gmylf8rRs5P54BM-6JUr28ucGIo9-7nO_taQWrEW8Mdvx7Vg6LdexLDNqheEyd3UCdSrKN_8pP9lVaUFQE-YPi5pXE0tDBS2_WvxzWrQO9GcsBSJK5wKYpxZlMum4f676LNNiYOD2MfzdPRd39ImZtlm3e_7tzjxp75VF_pK4zOZY.';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, 'orders='. str_replace("'","",$this->orders) . '&depots='. str_replace("'","",$this->depots) . '&routes='. str_replace("'","",$this->routes) . '&route_renewals='. str_replace("'","",$routeRenewals) . '&time_units=Minutes&distance_units=Kilometers&uturn_policy=NO_UTURNS&populate_directions=true&directions_language=en&default_date=1355212800000&f=json&token='.$token.'');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);
        $jobId = json_decode($response,true)['jobId'];
        
        
        $result = ["value"=>null];
        while($result["value"] === null){
            sleep(2);
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
        }
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
        $vehiclesCollection = ["type"=>"FeatureCollection", "features"=>$features];
        
        $stopsUrl = "https://logistics.arcgis.com/arcgis/rest/services/World/VehicleRoutingProblem/GPServer/SolveVehicleRoutingProblem/jobs/".$jobId."/results/out_stops?";
                 
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $stopsUrl);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, 'f=json&token='.$token.'');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($response,true);
        
        $features = [];
        foreach($result['value']['features'] as $row){
          $type = "points";
          $feature = ["type"=>"Feature", "properties"=>$row['attributes']];
          array_push($features, $feature);
          
        }
        $stopsCollection = ["type"=>"FeatureCollection", "features"=>$features];
        
        // Initialisation d'un array pour la flotte utilisée
        $vehicles = [];
        foreach($vehiclesCollection["features"] as $vehicle){
            $route = [];
            $route["Name"] = $vehicle["properties"]["Name"];
            $route["demarrage"] = $vehicle["properties"]["StartTime"];
            for ($i=1; $i <= $vehicle["properties"]["RenewalCount"]+1 ; $i++) { 
                $route["cetSequence".$i] = -1;
                $route["quantite".$i] = 0;
            }
            array_push($vehicles, $route);
        }

        // Séparation des circuits des véhicules selon la séquence du CET 
        foreach($vehicles as $key=>$vehicle){
            $objectId = -1;
            foreach($stopsCollection["features"] as $point){
                if($vehicle["Name"] == $point["properties"]["RouteName"]){
                    $cet = explode(" ", $point["properties"]["Name"]);
                    if($cet[0] == "CET" && $point["properties"]["ObjectID"] != $objectId){
                        for($i=1;$i<=(count($vehicle)-2)/2;$i++){
                            if($vehicles[$key]["cetSequence".$i] == -1){
                                $vehicles[$key]["cetSequence".$i] = $point["properties"]["Sequence"];
                                $vehicles[$key]["quantite".$i] = $point["properties"]["DeliveryQuantities"];
                                break;
                            }
                        }
                        $objectId = $point["properties"]["ObjectID"];
                    }
                }
            }
        }

        // Elaboration du plan de collecte 
        $points = $stopsCollection["features"];
        $plan = [];
        foreach($vehicles as $key=>$vehicle){
            $rotations = [];
            for ($i=1; $i <=(count($vehicle)-2)/2 ; $i++) {
                $debutSecteur = $finSecteur = $finRotation = 0;
                $tempsTransport = $distanceTransport = 0;
                $tempsCollecte = $distanceCollecte = 0;
                $arriveCet = $departCet = 0;
                $nbPoints = 0; 
                $stops = [];
                foreach($points as $key=>$point){
                    if($point["properties"]["RouteName"]== $vehicle["Name"]){

                        $pointSequence = $point["properties"]["Sequence"];
                        
                        if($pointSequence<= $vehicle["cetSequence".$i]){

                            if($pointSequence>1 && $pointSequence<$vehicle["cetSequence".$i]){

                                if($i > 1){
                                    $j = $i-1;
                                    if(intval($pointSequence-1) != $vehicle["cetSequence".$j]){
                                        $distanceCollecte = $distanceCollecte + $point["properties"]["FromPrevDistance"];
                                        $tempsCollecte = $tempsCollecte + $point["properties"]["FromPrevTravelTime"] + ($point["properties"]["DepartTime"]-$point["properties"]["ArriveTime"])/60000;
                                    }else{
                                        $debutSecteur = $point["properties"]["ArriveTime"];
                                    }
                                }else{
                                    $distanceCollecte = $distanceCollecte + $point["properties"]["FromPrevDistance"];
                                    $tempsCollecte = $tempsCollecte + $point["properties"]["FromPrevTravelTime"] + ($point["properties"]["DepartTime"]-$point["properties"]["ArriveTime"])/60000;
                                    if(intval($pointSequence) == 2){
                                        $debutSecteur = $point["properties"]["ArriveTime"];
                                    }
                                }
                                
                                $nbPoints = $nbPoints + 1;
                            }
                            
                            
                            if($pointSequence == $vehicle["cetSequence".$i]){
                                $arriveCet = $point["properties"]["ArriveTime"];
                                $departCet = $point["properties"]["DepartTime"];
                                $finRotation = $point["properties"]["DepartTime"]+intval($point["properties"]["FromPrevTravelTime"]-10)*60000;
                                $tempsTransport = $point["properties"]["FromPrevTravelTime"]*2;
                                $distanceTransport = $point["properties"]["FromPrevDistance"]*2;
                            }
                            elseif ($pointSequence == $vehicle["cetSequence".$i]-1) {
                                $finSecteur = $point["properties"]["DepartTime"];
                            }
                            array_push($stops, $points[$key]);
                            unset($points[$key]);
                        }
                    }
                    
                }
                $rotation = [
                    "demarrage_parc" => $vehicle["demarrage"], 
                    "fin_rotation" => $finRotation,
                    "debut_secteur"=> $debutSecteur, 
                    "fin_secteur" => $finSecteur, 
                    "temps_total"=> $tempsCollecte + $tempsTransport, 
                    "distance_totale"=> $distanceCollecte + $distanceTransport, 
                    "distance_collecte"=>$distanceCollecte, 
                    "temps_collecte"=>$tempsCollecte,
                    "distance_transport" => $distanceTransport,
                    "temps_transport" => $tempsTransport,
                    "arrive_cet" => $arriveCet,
                    "depart_cet" => $departCet,
                    "nombre_points" => $nbPoints,
                    "quantite_dechets"=>$vehicle["quantite".$i],
                    "ordres" => $stops
                ];

                if($i != 1){
                    $rotation["demarrage_parc"] = $debutSecteur-5*60000;
                }
                
                array_push($rotations, $rotation);
            }
            array_push($plan, ["vehicle"=>$vehicle["Name"], "rotations"=>$rotations]);
        }
       
        $result = ["routes" => $vehiclesCollection, "stops" => $stopsCollection, "plan" => $plan];
        return new Response(json_encode($result));
        
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
        return $this->index();
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
        $features = [];$j = 1;
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


    /**
     * @Route("admin/maps/VRP/saveResults", methods={"POST","GET"}, name="saveVrpResults")
     */
    public function saveResults(Request $request){
        $json = json_decode('        
        {
          "plan": [
            {
              "vehicle": "BT12_17E430_007552-00-16",
              "rotations": [
                {
                  "demarrage_parc": 1355209200000,
                  "fin_rotation": 1355219170995,
                  "debut_secteur": 1355212879744,
                  "fin_secteur": 1355215074499,
                  "temps_total": 96.45818540081382,
                  "distance_totale": 58.009856773694736,
                  "distance_collecte": 3.274268893180522,
                  "temps_collecte": 37.908310770988464,
                  "distance_transport": 54.73558788051422,
                  "temps_transport": 58.549874629825354,
                  "arrive_cet": 1355216830995,
                  "depart_cet": 1355218030995,
                  "nombre_points": 8,
                  "quantite_dechets": "4.62",
                  "ordres": [
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 134,
                        "Name": "C013-00146_1",
                        "PickupQuantities": "0.99",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E430_007552-00-16",
                        "Sequence": 2,
                        "FromPrevTravelTime": 1.3290639985352755,
                        "FromPrevDistance": 0.7989481639944768,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355212879744,
                        "DepartTime": 1355213119744,
                        "ArriveTimeUTC": 1355209279744,
                        "DepartTimeUTC": 1355209519744,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.186675278688581,
                        "SnapY": 36.72138206557382,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 1.4224531304780386,
                        "ORIG_FID": 134
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 135,
                        "Name": "C013-00160_1",
                        "PickupQuantities": "0.165",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E430_007552-00-16",
                        "Sequence": 9,
                        "FromPrevTravelTime": 0.3526212740689516,
                        "FromPrevDistance": 0.1880647856837973,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355214834499,
                        "DepartTime": 1355215074499,
                        "ArriveTimeUTC": 1355211234499,
                        "DepartTimeUTC": 1355211474499,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.188612579776067,
                        "SnapY": 36.71130203830554,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 7.077891304096997,
                        "ORIG_FID": 135
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 136,
                        "Name": "C013-00159_1",
                        "PickupQuantities": "0.495",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E430_007552-00-16",
                        "Sequence": 8,
                        "FromPrevTravelTime": 0.850135188549757,
                        "FromPrevDistance": 0.4534076875171114,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355214573341,
                        "DepartTime": 1355214813341,
                        "ArriveTimeUTC": 1355210973341,
                        "DepartTimeUTC": 1355211213341,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1903393866126204,
                        "SnapY": 36.712271101825614,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0.13440918696036408,
                        "ORIG_FID": 136
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 182,
                        "Name": "C013-00158_1",
                        "PickupQuantities": "0.66",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E430_007552-00-16",
                        "Sequence": 7,
                        "FromPrevTravelTime": 0.19543863646686077,
                        "FromPrevDistance": 0.10423428319655179,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355214282333,
                        "DepartTime": 1355214522333,
                        "ArriveTimeUTC": 1355210682333,
                        "DepartTimeUTC": 1355210922333,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1882260484938065,
                        "SnapY": 36.71516022483472,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 1.2644468807475207,
                        "ORIG_FID": 182
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 196,
                        "Name": "C013-00154_1",
                        "PickupQuantities": "0.495",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E430_007552-00-16",
                        "Sequence": 3,
                        "FromPrevTravelTime": 1.2615633718669415,
                        "FromPrevDistance": 0.7058826189941558,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355213195438,
                        "DepartTime": 1355213435438,
                        "ArriveTimeUTC": 1355209595438,
                        "DepartTimeUTC": 1355209835438,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1844898208895804,
                        "SnapY": 36.71746727711819,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 4.06351672802794,
                        "ORIG_FID": 196
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 197,
                        "Name": "C013-00144_1",
                        "PickupQuantities": "0.165",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E430_007552-00-16",
                        "Sequence": 4,
                        "FromPrevTravelTime": 0.060943085700273514,
                        "FromPrevDistance": 0.03250308988712252,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355213439094,
                        "DepartTime": 1355213679094,
                        "ArriveTimeUTC": 1355209839094,
                        "DepartTimeUTC": 1355210079094,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1841992996579314,
                        "SnapY": 36.71764643187769,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 4.16885212220961,
                        "ORIG_FID": 197
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 198,
                        "Name": "C013-00151_1",
                        "PickupQuantities": "0.495",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E430_007552-00-16",
                        "Sequence": 5,
                        "FromPrevTravelTime": 0.2864573039114475,
                        "FromPrevDistance": 0.15277775017479645,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355213696282,
                        "DepartTime": 1355213936282,
                        "ArriveTimeUTC": 1355210096282,
                        "DepartTimeUTC": 1355210336282,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.182823227586251,
                        "SnapY": 36.71847086896556,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 3.57134087630664,
                        "ORIG_FID": 198
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 209,
                        "Name": "C013-00157_1",
                        "PickupQuantities": "1.155",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E430_007552-00-16",
                        "Sequence": 6,
                        "FromPrevTravelTime": 1.572087911888957,
                        "FromPrevDistance": 0.8384505137325108,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355214030607,
                        "DepartTime": 1355214270607,
                        "ArriveTimeUTC": 1355210430607,
                        "DepartTimeUTC": 1355210670607,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1872822128921108,
                        "SnapY": 36.71571380340829,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0.4656070816387497,
                        "ORIG_FID": 209
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 212,
                        "Name": "Parc Babez",
                        "PickupQuantities": "0",
                        "DeliveryQuantities": "0",
                        "StopType": 1,
                        "RouteName": "BT12_17E430_007552-00-16",
                        "Sequence": 1,
                        "FromPrevTravelTime": 0,
                        "FromPrevDistance": 0,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355209200000,
                        "DepartTime": 1355212800000,
                        "ArriveTimeUTC": 1355205600000,
                        "DepartTimeUTC": 1355209200000,
                        "WaitTime": 60,
                        "ViolationTime": 0,
                        "SnapX": 0,
                        "SnapY": 0,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0,
                        "ORIG_FID": 1
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 213,
                        "Name": "CET CORSO",
                        "PickupQuantities": "0",
                        "DeliveryQuantities": "4.62",
                        "StopType": 1,
                        "RouteName": "BT12_17E430_007552-00-16",
                        "Sequence": 10,
                        "FromPrevTravelTime": 29.274937314912677,
                        "FromPrevDistance": 27.36779394025711,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355216830995,
                        "DepartTime": 1355218030995,
                        "ArriveTimeUTC": 1355213230995,
                        "DepartTimeUTC": 1355214430995,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 0,
                        "SnapY": 0,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0,
                        "ORIG_FID": 2
                      }
                    }
                  ]
                },
                {
                  "demarrage_parc": 1355219486645,
                  "fin_rotation": 1355224889601,
                  "debut_secteur": 1355219786645,
                  "fin_secteur": 1355220870055,
                  "temps_total": 72.04169371165335,
                  "distance_totale": 55.91549378540828,
                  "distance_collecte": 1.0969759523779623,
                  "temps_collecte": 14.056820610538125,
                  "distance_transport": 54.81851783303032,
                  "temps_transport": 57.98487310111523,
                  "arrive_cet": 1355222609601,
                  "depart_cet": 1355223809601,
                  "nombre_points": 4,
                  "quantite_dechets": "3.63",
                  "ordres": [
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 104,
                        "Name": "C013-00110_1",
                        "PickupQuantities": "0.66",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E430_007552-00-16",
                        "Sequence": 13,
                        "FromPrevTravelTime": 0.9681272022426128,
                        "FromPrevDistance": 0.516336594022064,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355220382067,
                        "DepartTime": 1355220622067,
                        "ArriveTimeUTC": 1355216782067,
                        "DepartTimeUTC": 1355217022067,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1906728564232347,
                        "SnapY": 36.72103004534009,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0.26881837392072816,
                        "ORIG_FID": 104
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 149,
                        "Name": "C013-02490_1",
                        "PickupQuantities": "1.485",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E430_007552-00-16",
                        "Sequence": 12,
                        "FromPrevTravelTime": 0.955566767603159,
                        "FromPrevDistance": 0.509638285286909,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355220083979,
                        "DepartTime": 1355220323979,
                        "ArriveTimeUTC": 1355216483979,
                        "DepartTimeUTC": 1355216723979,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1881264968153715,
                        "SnapY": 36.71856808917203,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 9.095242353048093,
                        "ORIG_FID": 149
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 192,
                        "Name": "C013-00153_1",
                        "PickupQuantities": "1.155",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E430_007552-00-16",
                        "Sequence": 11,
                        "FromPrevTravelTime": 29.260842751711607,
                        "FromPrevDistance": 27.622547208644807,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355219786645,
                        "DepartTime": 1355220026645,
                        "ArriveTimeUTC": 1355216186645,
                        "DepartTimeUTC": 1355216426645,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1901977234927776,
                        "SnapY": 36.715400571725624,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 1.578950435076977,
                        "ORIG_FID": 192
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 205,
                        "Name": "C013-00109_1",
                        "PickupQuantities": "0.33",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E430_007552-00-16",
                        "Sequence": 14,
                        "FromPrevTravelTime": 0.13312664069235325,
                        "FromPrevDistance": 0.07100107306898933,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355220630055,
                        "DepartTime": 1355220870055,
                        "ArriveTimeUTC": 1355217030055,
                        "DepartTimeUTC": 1355217270055,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1906840361991504,
                        "SnapY": 36.7203902986426,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 1.2536854349968,
                        "ORIG_FID": 205
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 214,
                        "Name": "CET CORSO",
                        "PickupQuantities": "0",
                        "DeliveryQuantities": "3.63",
                        "StopType": 1,
                        "RouteName": "BT12_17E430_007552-00-16",
                        "Sequence": 15,
                        "FromPrevTravelTime": 28.992436550557613,
                        "FromPrevDistance": 27.40925891651516,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355222609601,
                        "DepartTime": 1355223809601,
                        "ArriveTimeUTC": 1355219009601,
                        "DepartTimeUTC": 1355220209601,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 0,
                        "SnapY": 0,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0,
                        "ORIG_FID": 2
                      }
                    }
                  ]
                },
                {
                  "demarrage_parc": 1355225261472,
                  "fin_rotation": 1355230200381,
                  "debut_secteur": 1355225561472,
                  "fin_secteur": 1355227388131,
                  "temps_total": 84.18596811592579,
                  "distance_totale": 55.79348139725276,
                  "distance_collecte": 1.303636771312202,
                  "temps_collecte": 26.44430449232459,
                  "distance_transport": 54.48984462594056,
                  "temps_transport": 57.7416636236012,
                  "arrive_cet": 1355229120381,
                  "depart_cet": 1355229120381,
                  "nombre_points": 7,
                  "quantite_dechets": "3.465",
                  "ordres": [
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 99,
                        "Name": "C013-00106_1",
                        "PickupQuantities": "0.33",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E430_007552-00-16",
                        "Sequence": 16,
                        "FromPrevTravelTime": 29.197858795523643,
                        "FromPrevDistance": 27.588958331836555,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355225561472,
                        "DepartTime": 1355225801472,
                        "ArriveTimeUTC": 1355221961472,
                        "DepartTimeUTC": 1355222201472,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1921916928822442,
                        "SnapY": 36.71978913092683,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 7.290382054787116,
                        "ORIG_FID": 99
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 105,
                        "Name": "C013-00107_1",
                        "PickupQuantities": "0.33",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E430_007552-00-16",
                        "Sequence": 17,
                        "FromPrevTravelTime": 0.4786952082067728,
                        "FromPrevDistance": 0.2553043116061041,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355225830194,
                        "DepartTime": 1355226070194,
                        "ArriveTimeUTC": 1355222230194,
                        "DepartTimeUTC": 1355222470194,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1913382627248086,
                        "SnapY": 36.719760719292964,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 5.199568626879824,
                        "ORIG_FID": 105
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 120,
                        "Name": "C013-00176_1",
                        "PickupQuantities": "0.66",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E430_007552-00-16",
                        "Sequence": 19,
                        "FromPrevTravelTime": 0.13191923312842846,
                        "FromPrevDistance": 0.0703572833989339,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355226405811,
                        "DepartTime": 1355226645811,
                        "ArriveTimeUTC": 1355222805811,
                        "DepartTimeUTC": 1355223045811,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1897179236016666,
                        "SnapY": 36.71992296862217,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 4.569912356652476,
                        "ORIG_FID": 120
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 137,
                        "Name": "C013-00174_1",
                        "PickupQuantities": "0.495",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E430_007552-00-16",
                        "Sequence": 21,
                        "FromPrevTravelTime": 0.1096740048378706,
                        "FromPrevDistance": 0.05849329167056714,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355226900529,
                        "DepartTime": 1355227140529,
                        "ArriveTimeUTC": 1355223300529,
                        "DepartTimeUTC": 1355223540529,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1906922227171894,
                        "SnapY": 36.71904107795109,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 5.489426533949713,
                        "ORIG_FID": 137
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 188,
                        "Name": "C013-00172_1",
                        "PickupQuantities": "0.33",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E430_007552-00-16",
                        "Sequence": 22,
                        "FromPrevTravelTime": 0.12668700516223907,
                        "FromPrevDistance": 0.06756668476447737,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355227148131,
                        "DepartTime": 1355227388131,
                        "ArriveTimeUTC": 1355223548131,
                        "DepartTimeUTC": 1355223788131,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1911734629898882,
                        "SnapY": 36.718572571843296,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 5.289987708534748,
                        "ORIG_FID": 188
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 194,
                        "Name": "C013-00173_1",
                        "PickupQuantities": "0.66",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E430_007552-00-16",
                        "Sequence": 20,
                        "FromPrevTravelTime": 0.1356416866183281,
                        "FromPrevDistance": 0.07234270626545149,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355226653949,
                        "DepartTime": 1355226893949,
                        "ArriveTimeUTC": 1355223053949,
                        "DepartTimeUTC": 1355223293949,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1902800000000298,
                        "SnapY": 36.71945000000005,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 5.707248957198485,
                        "ORIG_FID": 194
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 195,
                        "Name": "C013-00152_1",
                        "PickupQuantities": "0.66",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E430_007552-00-16",
                        "Sequence": 18,
                        "FromPrevTravelTime": 1.4616873543709517,
                        "FromPrevDistance": 0.7795724936066679,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355226157895,
                        "DepartTime": 1355226397895,
                        "ArriveTimeUTC": 1355222557895,
                        "DepartTimeUTC": 1355222797895,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1890850277469727,
                        "SnapY": 36.72029671476146,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 5.307886694967393,
                        "ORIG_FID": 195
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 215,
                        "Name": "CET CORSO",
                        "PickupQuantities": "0",
                        "DeliveryQuantities": "3.465",
                        "StopType": 1,
                        "RouteName": "BT12_17E430_007552-00-16",
                        "Sequence": 23,
                        "FromPrevTravelTime": 28.8708318118006,
                        "FromPrevDistance": 27.24492231297028,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355229120381,
                        "DepartTime": 1355229120381,
                        "ArriveTimeUTC": 1355225520381,
                        "DepartTimeUTC": 1355225520381,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 0,
                        "SnapY": 0,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0,
                        "ORIG_FID": 2
                      }
                    }
                  ]
                }
              ]
            },
            {
              "vehicle": "AR12_16M306_134171-00-16",
              "rotations": [
                {
                  "demarrage_parc": 1355209200000,
                  "fin_rotation": 1355219424859,
                  "debut_secteur": 1355212823805,
                  "fin_secteur": 1355215436986,
                  "temps_total": 100.87888343818486,
                  "distance_totale": 58.45717417911861,
                  "distance_collecte": 2.3057743001042175,
                  "temps_collecte": 43.94976554997265,
                  "distance_transport": 56.15139987901439,
                  "temps_transport": 56.929117888212204,
                  "arrive_cet": 1355217144859,
                  "depart_cet": 1355218344859,
                  "nombre_points": 10,
                  "quantite_dechets": "4.695",
                  "ordres": [
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 25,
                        "Name": "C013-00079_1",
                        "PickupQuantities": "0.06",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "AR12_16M306_134171-00-16",
                        "Sequence": 9,
                        "FromPrevTravelTime": 0.22079511173069477,
                        "FromPrevDistance": 0.1177585708051874,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355214702760,
                        "DepartTime": 1355214942760,
                        "ArriveTimeUTC": 1355211102760,
                        "DepartTimeUTC": 1355211342760,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.182779384885829,
                        "SnapY": 36.7258971001758,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 9.266438787394385,
                        "ORIG_FID": 25
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 42,
                        "Name": "C013-00061_1",
                        "PickupQuantities": "0.33",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "AR12_16M306_134171-00-16",
                        "Sequence": 2,
                        "FromPrevTravelTime": 0.39675320498645306,
                        "FromPrevDistance": 0.2116033165434948,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355212823805,
                        "DepartTime": 1355213063805,
                        "ArriveTimeUTC": 1355209223805,
                        "DepartTimeUTC": 1355209463805,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.179509705574511,
                        "SnapY": 36.72023570400422,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 5.955858989306971,
                        "ORIG_FID": 42
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 74,
                        "Name": "C013-00085_1",
                        "PickupQuantities": "0.825",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "AR12_16M306_134171-00-16",
                        "Sequence": 8,
                        "FromPrevTravelTime": 0.44722555577754974,
                        "FromPrevDistance": 0.3552641470194461,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355214449513,
                        "DepartTime": 1355214689513,
                        "ArriveTimeUTC": 1355210849513,
                        "DepartTimeUTC": 1355211089513,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.184083896995737,
                        "SnapY": 36.72604916738203,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 7.719464925468629,
                        "ORIG_FID": 74
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 75,
                        "Name": "C013-00080_1",
                        "PickupQuantities": "0.24",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "AR12_16M306_134171-00-16",
                        "Sequence": 6,
                        "FromPrevTravelTime": 0.32619328424334526,
                        "FromPrevDistance": 0.21256871842779024,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355213912916,
                        "DepartTime": 1355214152916,
                        "ArriveTimeUTC": 1355210312916,
                        "DepartTimeUTC": 1355210552916,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1805246222487065,
                        "SnapY": 36.72481279000601,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 32.487843252199724,
                        "ORIG_FID": 75
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 84,
                        "Name": "C013-00090_1",
                        "PickupQuantities": "0.66",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "AR12_16M306_134171-00-16",
                        "Sequence": 3,
                        "FromPrevTravelTime": 1.0707434806972742,
                        "FromPrevDistance": 0.5710672136228261,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355213128050,
                        "DepartTime": 1355213368050,
                        "ArriveTimeUTC": 1355209528050,
                        "DepartTimeUTC": 1355209768050,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.180650969245168,
                        "SnapY": 36.72098728797771,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 4.0945194849351685,
                        "ORIG_FID": 84
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 86,
                        "Name": "C013-00093_1",
                        "PickupQuantities": "0.96",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "AR12_16M306_134171-00-16",
                        "Sequence": 7,
                        "FromPrevTravelTime": 0.4960516281425953,
                        "FromPrevDistance": 0.30316150903165484,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355214182679,
                        "DepartTime": 1355214422679,
                        "ArriveTimeUTC": 1355210582679,
                        "DepartTimeUTC": 1355210822679,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1828615564202964,
                        "SnapY": 36.72383509727632,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 2.774174360735381,
                        "ORIG_FID": 86
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 87,
                        "Name": "C013-00095_1",
                        "PickupQuantities": "0.66",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "AR12_16M306_134171-00-16",
                        "Sequence": 4,
                        "FromPrevTravelTime": 0.4517926834523678,
                        "FromPrevDistance": 0.24095749547363962,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355213395157,
                        "DepartTime": 1355213635157,
                        "ArriveTimeUTC": 1355209795157,
                        "DepartTimeUTC": 1355210035157,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.179970000000054,
                        "SnapY": 36.72257000000005,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 1.4287892559597857,
                        "ORIG_FID": 87
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 153,
                        "Name": "C013-00094_1",
                        "PickupQuantities": "0.66",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "AR12_16M306_134171-00-16",
                        "Sequence": 5,
                        "FromPrevTravelTime": 0.3031193148344755,
                        "FromPrevDistance": 0.16166550076639916,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355213653345,
                        "DepartTime": 1355213893345,
                        "ArriveTimeUTC": 1355210053345,
                        "DepartTimeUTC": 1355210293345,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1811843848580748,
                        "SnapY": 36.72350987381708,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 2.6166676127113027,
                        "ORIG_FID": 153
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 201,
                        "Name": "C013-00086_1",
                        "PickupQuantities": "0.06",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "AR12_16M306_134171-00-16",
                        "Sequence": 11,
                        "FromPrevTravelTime": 0.022181464359164238,
                        "FromPrevDistance": 0.017108413326277015,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355215196986,
                        "DepartTime": 1355215436986,
                        "ArriveTimeUTC": 1355211596986,
                        "DepartTimeUTC": 1355211836986,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1814998062433,
                        "SnapY": 36.72585277717981,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 2.6062908331178676,
                        "ORIG_FID": 201
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 204,
                        "Name": "C013-00081_1",
                        "PickupQuantities": "0.24",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "AR12_16M306_134171-00-16",
                        "Sequence": 10,
                        "FromPrevTravelTime": 0.21490982174873352,
                        "FromPrevDistance": 0.11461941508750192,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355214955655,
                        "DepartTime": 1355215195655,
                        "ArriveTimeUTC": 1355211355655,
                        "DepartTimeUTC": 1355211595655,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1815068326180826,
                        "SnapY": 36.72576589699575,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 4.005303562895061,
                        "ORIG_FID": 204
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 216,
                        "Name": "Parc Babez",
                        "PickupQuantities": "0",
                        "DeliveryQuantities": "0",
                        "StopType": 1,
                        "RouteName": "AR12_16M306_134171-00-16",
                        "Sequence": 1,
                        "FromPrevTravelTime": 0,
                        "FromPrevDistance": 0,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355209200000,
                        "DepartTime": 1355212800000,
                        "ArriveTimeUTC": 1355205600000,
                        "DepartTimeUTC": 1355209200000,
                        "WaitTime": 60,
                        "ViolationTime": 0,
                        "SnapX": 0,
                        "SnapY": 0,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0,
                        "ORIG_FID": 1
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 217,
                        "Name": "CET CORSO",
                        "PickupQuantities": "0",
                        "DeliveryQuantities": "4.695",
                        "StopType": 1,
                        "RouteName": "AR12_16M306_134171-00-16",
                        "Sequence": 12,
                        "FromPrevTravelTime": 28.464558944106102,
                        "FromPrevDistance": 28.075699939507196,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355217144859,
                        "DepartTime": 1355218344859,
                        "ArriveTimeUTC": 1355213544859,
                        "DepartTimeUTC": 1355214744859,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 0,
                        "SnapY": 0,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0,
                        "ORIG_FID": 2
                      }
                    }
                  ]
                },
                {
                  "demarrage_parc": 1355219830371,
                  "fin_rotation": 1355226993370,
                  "debut_secteur": 1355220130371,
                  "fin_secteur": 1355224112433,
                  "temps_total": 120.39894790574908,
                  "distance_totale": 58.875926152986686,
                  "distance_collecte": 3.7391308605297207,
                  "temps_collecte": 62.36769103258848,
                  "distance_transport": 55.136795292456966,
                  "temps_transport": 58.0312568731606,
                  "arrive_cet": 1355225853370,
                  "depart_cet": 1355225853370,
                  "nombre_points": 15,
                  "quantite_dechets": "4.74",
                  "ordres": [
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 23,
                        "Name": "C013-00140_1",
                        "PickupQuantities": "0.06",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "AR12_16M306_134171-00-16",
                        "Sequence": 26,
                        "FromPrevTravelTime": 0.3781176060438156,
                        "FromPrevDistance": 0.20166348588239105,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355223614012,
                        "DepartTime": 1355223854012,
                        "ArriveTimeUTC": 1355220014012,
                        "DepartTimeUTC": 1355220254012,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1870324228251214,
                        "SnapY": 36.725213283442514,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0.7662502593988135,
                        "ORIG_FID": 23
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 66,
                        "Name": "C013-00002_1",
                        "PickupQuantities": "0.12",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "AR12_16M306_134171-00-16",
                        "Sequence": 14,
                        "FromPrevTravelTime": 0.33423404954373837,
                        "FromPrevDistance": 0.1782589909428109,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355220390425,
                        "DepartTime": 1355220630425,
                        "ArriveTimeUTC": 1355216790425,
                        "DepartTimeUTC": 1355217030425,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.18316704062976,
                        "SnapY": 36.72687321936874,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 4.2641830907399525,
                        "ORIG_FID": 66
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 72,
                        "Name": "C013-00082_1",
                        "PickupQuantities": "0.18",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "AR12_16M306_134171-00-16",
                        "Sequence": 17,
                        "FromPrevTravelTime": 0.18702280148863792,
                        "FromPrevDistance": 0.19014023489205265,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355221213104,
                        "DepartTime": 1355221453104,
                        "ArriveTimeUTC": 1355217613104,
                        "DepartTimeUTC": 1355217853104,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1821150000000253,
                        "SnapY": 36.725065000000065,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 5.028233133051428,
                        "ORIG_FID": 72
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 81,
                        "Name": "C013-00083_1",
                        "PickupQuantities": "0.06",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "AR12_16M306_134171-00-16",
                        "Sequence": 18,
                        "FromPrevTravelTime": 0.13698076270520687,
                        "FromPrevDistance": 0.11737957529638644,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355221461322,
                        "DepartTime": 1355221701322,
                        "ArriveTimeUTC": 1355217861322,
                        "DepartTimeUTC": 1355218101322,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1822777012302783,
                        "SnapY": 36.72584030579968,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 2.269088235109797,
                        "ORIG_FID": 81
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 82,
                        "Name": "C013-00084_1",
                        "PickupQuantities": "0.12",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "AR12_16M306_134171-00-16",
                        "Sequence": 19,
                        "FromPrevTravelTime": 0.10968514904379845,
                        "FromPrevDistance": 0.058499040740590244,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355221707904,
                        "DepartTime": 1355221947904,
                        "ArriveTimeUTC": 1355218107904,
                        "DepartTimeUTC": 1355218347904,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.182926182776858,
                        "SnapY": 36.725913718804975,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 3.7682545054609102,
                        "ORIG_FID": 82
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 83,
                        "Name": "C013-00089_1",
                        "PickupQuantities": "0.825",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "AR12_16M306_134171-00-16",
                        "Sequence": 22,
                        "FromPrevTravelTime": 0.30616374127566814,
                        "FromPrevDistance": 0.1632879862743005,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355222498663,
                        "DepartTime": 1355222738663,
                        "ArriveTimeUTC": 1355218898663,
                        "DepartTimeUTC": 1355219138663,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1813322583155323,
                        "SnapY": 36.72061465668163,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 1.9616375026698358,
                        "ORIG_FID": 83
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 90,
                        "Name": "C013-00097_1",
                        "PickupQuantities": "0.165",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "AR12_16M306_134171-00-16",
                        "Sequence": 16,
                        "FromPrevTravelTime": 1.0272100511938334,
                        "FromPrevDistance": 0.6817455415028576,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355220961882,
                        "DepartTime": 1355221201882,
                        "ArriveTimeUTC": 1355217361882,
                        "DepartTimeUTC": 1355217601882,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1804594419263985,
                        "SnapY": 36.72459843909357,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 3.021965723190722,
                        "ORIG_FID": 90
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 110,
                        "Name": "C013-00136_1",
                        "PickupQuantities": "0.165",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "AR12_16M306_134171-00-16",
                        "Sequence": 27,
                        "FromPrevTravelTime": 0.3070122431963682,
                        "FromPrevDistance": 0.1637404144574867,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355223872433,
                        "DepartTime": 1355224112433,
                        "ArriveTimeUTC": 1355220272433,
                        "DepartTimeUTC": 1355220512433,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1881096984822763,
                        "SnapY": 36.72603253743513,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0.828553874226144,
                        "ORIG_FID": 110
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 118,
                        "Name": "C013-00130_1",
                        "PickupQuantities": "0.12",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "AR12_16M306_134171-00-16",
                        "Sequence": 25,
                        "FromPrevTravelTime": 0.9089613314718008,
                        "FromPrevDistance": 0.5355018596024814,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355223351325,
                        "DepartTime": 1355223591325,
                        "ArriveTimeUTC": 1355219751325,
                        "DepartTimeUTC": 1355219991325,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1876172831268192,
                        "SnapY": 36.72404738798864,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 2.80976289189931,
                        "ORIG_FID": 118
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 173,
                        "Name": "C013-00091_1",
                        "PickupQuantities": "0.825",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "AR12_16M306_134171-00-16",
                        "Sequence": 21,
                        "FromPrevTravelTime": 0.16640198417007923,
                        "FromPrevDistance": 0.08874795783944703,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355222240293,
                        "DepartTime": 1355222480293,
                        "ArriveTimeUTC": 1355218640293,
                        "DepartTimeUTC": 1355218880293,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1821425266215293,
                        "SnapY": 36.721786950629316,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0.41427693711307173,
                        "ORIG_FID": 173
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 174,
                        "Name": "C013-00092_1",
                        "PickupQuantities": "0.66",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "AR12_16M306_134171-00-16",
                        "Sequence": 20,
                        "FromPrevTravelTime": 0.7067547850310802,
                        "FromPrevDistance": 0.49368036850202357,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355221990309,
                        "DepartTime": 1355222230309,
                        "ArriveTimeUTC": 1355218390309,
                        "DepartTimeUTC": 1355218630309,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1828531792183505,
                        "SnapY": 36.72234934699719,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 1.2021923153754113,
                        "ORIG_FID": 174
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 180,
                        "Name": "C013-00150_1",
                        "PickupQuantities": "0.165",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "AR12_16M306_134171-00-16",
                        "Sequence": 23,
                        "FromPrevTravelTime": 1.2010640054941177,
                        "FromPrevDistance": 0.5475058454290433,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355222810727,
                        "DepartTime": 1355223050727,
                        "ArriveTimeUTC": 1355219210727,
                        "DepartTimeUTC": 1355219450727,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1841758333435863,
                        "SnapY": 36.72105044741066,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 1.1832589696678097,
                        "ORIG_FID": 180
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 181,
                        "Name": "C013-00147_1",
                        "PickupQuantities": "0.66",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "AR12_16M306_134171-00-16",
                        "Sequence": 24,
                        "FromPrevTravelTime": 0.10100716166198254,
                        "FromPrevDistance": 0.053870769860061965,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355223056787,
                        "DepartTime": 1355223296787,
                        "ArriveTimeUTC": 1355219456787,
                        "DepartTimeUTC": 1355219696787,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.184653375034029,
                        "SnapY": 36.72134208050046,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 4.708160239921606,
                        "ORIG_FID": 181
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 189,
                        "Name": "C013-00001_1",
                        "PickupQuantities": "0.495",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "AR12_16M306_134171-00-16",
                        "Sequence": 13,
                        "FromPrevTravelTime": 29.758527303114533,
                        "FromPrevDistance": 27.52174560169094,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355220130371,
                        "DepartTime": 1355220370371,
                        "ArriveTimeUTC": 1355216530371,
                        "DepartTimeUTC": 1355216770371,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1836307510917603,
                        "SnapY": 36.726328366812254,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 3.594032292653031,
                        "ORIG_FID": 189
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 207,
                        "Name": "C013-00121_1",
                        "PickupQuantities": "0.12",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "AR12_16M306_134171-00-16",
                        "Sequence": 15,
                        "FromPrevTravelTime": 0.4970753602683544,
                        "FromPrevDistance": 0.2651087893077878,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355220660250,
                        "DepartTime": 1355220900250,
                        "ArriveTimeUTC": 1355217060250,
                        "DepartTimeUTC": 1355217300250,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.184846310339376,
                        "SnapY": 36.726108580532134,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 5.418207545068681,
                        "ORIG_FID": 207
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 218,
                        "Name": "CET CORSO",
                        "PickupQuantities": "0",
                        "DeliveryQuantities": "4.74",
                        "StopType": 1,
                        "RouteName": "AR12_16M306_134171-00-16",
                        "Sequence": 28,
                        "FromPrevTravelTime": 29.0156284365803,
                        "FromPrevDistance": 27.568397646228483,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355225853370,
                        "DepartTime": 1355225853370,
                        "ArriveTimeUTC": 1355222253370,
                        "DepartTimeUTC": 1355222253370,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 0,
                        "SnapY": 0,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0,
                        "ORIG_FID": 2
                      }
                    }
                  ]
                }
              ]
            },
            {
              "vehicle": "BT12_17E360_392697-00-16",
              "rotations": [
                {
                  "demarrage_parc": 1355209200000,
                  "fin_rotation": 1355221274443,
                  "debut_secteur": 1355212916173,
                  "fin_secteur": 1355217425963,
                  "temps_total": 131.38205280713737,
                  "distance_totale": 58.07067180222913,
                  "distance_collecte": 4.976187430884316,
                  "temps_collecte": 77.09938927926123,
                  "distance_transport": 53.09448437134481,
                  "temps_transport": 54.28266352787614,
                  "arrive_cet": 1355219054443,
                  "depart_cet": 1355220254443,
                  "nombre_points": 17,
                  "quantite_dechets": "4.62",
                  "ordres": [
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 20,
                        "Name": "C013-00118_1",
                        "PickupQuantities": "0.36",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E360_392697-00-16",
                        "Sequence": 17,
                        "FromPrevTravelTime": 1.7870519403368235,
                        "FromPrevDistance": 0.9530987103901591,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355216920162,
                        "DepartTime": 1355217160162,
                        "ArriveTimeUTC": 1355213320162,
                        "DepartTimeUTC": 1355213560162,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1960324864865557,
                        "SnapY": 36.72298308108111,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 4.000790543362796,
                        "ORIG_FID": 20
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 24,
                        "Name": "C013-00194_1",
                        "PickupQuantities": "0.165",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E360_392697-00-16",
                        "Sequence": 10,
                        "FromPrevTravelTime": 0.23267485201358795,
                        "FromPrevDistance": 0.12409405057770886,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355215010906,
                        "DepartTime": 1355215250906,
                        "ArriveTimeUTC": 1355211410906,
                        "DepartTimeUTC": 1355211650906,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.189987083244921,
                        "SnapY": 36.722092935867536,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 12.81970558630294,
                        "ORIG_FID": 24
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 35,
                        "Name": "C013-00119_1",
                        "PickupQuantities": "0.48",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E360_392697-00-16",
                        "Sequence": 18,
                        "FromPrevTravelTime": 0.43002184107899666,
                        "FromPrevDistance": 0.22934556057143196,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355217185963,
                        "DepartTime": 1355217425963,
                        "ArriveTimeUTC": 1355213585963,
                        "DepartTimeUTC": 1355213825963,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1981754345115077,
                        "SnapY": 36.722387854469915,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 2.3741395949429864,
                        "ORIG_FID": 35
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 98,
                        "Name": "C013-00105_1",
                        "PickupQuantities": "0.285",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E360_392697-00-16",
                        "Sequence": 3,
                        "FromPrevTravelTime": 0.8657143842428923,
                        "FromPrevDistance": 0.46171680952780264,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355213208116,
                        "DepartTime": 1355213448116,
                        "ArriveTimeUTC": 1355209608116,
                        "DepartTimeUTC": 1355209848116,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1921982946702023,
                        "SnapY": 36.720409698992945,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 2.5217398751357982,
                        "ORIG_FID": 98
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 101,
                        "Name": "C013-00137_1",
                        "PickupQuantities": "0.06",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E360_392697-00-16",
                        "Sequence": 2,
                        "FromPrevTravelTime": 1.9362110123038292,
                        "FromPrevDistance": 1.1558067048347263,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355212916173,
                        "DepartTime": 1355213156173,
                        "ArriveTimeUTC": 1355209316173,
                        "DepartTimeUTC": 1355209556173,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.18918148648655,
                        "SnapY": 36.721786081081156,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 7.348385162272829,
                        "ORIG_FID": 101
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 113,
                        "Name": "C013-00141_1",
                        "PickupQuantities": "0.18",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E360_392697-00-16",
                        "Sequence": 16,
                        "FromPrevTravelTime": 0.1531859003007412,
                        "FromPrevDistance": 0.08169939738013884,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355216572939,
                        "DepartTime": 1355216812939,
                        "ArriveTimeUTC": 1355212972939,
                        "DepartTimeUTC": 1355213212939,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1919663076923235,
                        "SnapY": 36.72319046153851,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 3.3087599944456505,
                        "ORIG_FID": 113
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 114,
                        "Name": "C013-00142_1",
                        "PickupQuantities": "0.165",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E360_392697-00-16",
                        "Sequence": 15,
                        "FromPrevTravelTime": 0.4792007617652416,
                        "FromPrevDistance": 0.25557519698029774,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355216323748,
                        "DepartTime": 1355216563748,
                        "ArriveTimeUTC": 1355212723748,
                        "DepartTimeUTC": 1355212963748,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.191172950039709,
                        "SnapY": 36.72335279143541,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 1.933811447542048,
                        "ORIG_FID": 114
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 115,
                        "Name": "C013-00128_1",
                        "PickupQuantities": "0.33",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E360_392697-00-16",
                        "Sequence": 12,
                        "FromPrevTravelTime": 0.21031391061842442,
                        "FromPrevDistance": 0.11216790377674035,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355215516861,
                        "DepartTime": 1355215756861,
                        "ArriveTimeUTC": 1355211916861,
                        "DepartTimeUTC": 1355212156861,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.190037734439905,
                        "SnapY": 36.723288995850695,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 1.242830811339217,
                        "ORIG_FID": 115
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 116,
                        "Name": "C013-00129_1",
                        "PickupQuantities": "0.165",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E360_392697-00-16",
                        "Sequence": 14,
                        "FromPrevTravelTime": 0.3483677711337805,
                        "FromPrevDistance": 0.18579815320761317,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355216054996,
                        "DepartTime": 1355216294996,
                        "ArriveTimeUTC": 1355212454996,
                        "DepartTimeUTC": 1355212694996,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1884283952452437,
                        "SnapY": 36.723763075780134,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 4.239752397446109,
                        "ORIG_FID": 116
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 117,
                        "Name": "C013-00131_1",
                        "PickupQuantities": "0.66",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E360_392697-00-16",
                        "Sequence": 13,
                        "FromPrevTravelTime": 0.6205451898276806,
                        "FromPrevDistance": 0.3309604390318073,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355215794094,
                        "DepartTime": 1355216034094,
                        "ArriveTimeUTC": 1355212194094,
                        "DepartTimeUTC": 1355212434094,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1886000000000765,
                        "SnapY": 36.724590000000056,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0.13440918696036408,
                        "ORIG_FID": 117
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 119,
                        "Name": "C013-00143_1",
                        "PickupQuantities": "0.165",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E360_392697-00-16",
                        "Sequence": 11,
                        "FromPrevTravelTime": 0.2222621627151966,
                        "FromPrevDistance": 0.1185401605520416,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355215264242,
                        "DepartTime": 1355215504242,
                        "ArriveTimeUTC": 1355211664242,
                        "DepartTimeUTC": 1355211904242,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1903305053508455,
                        "SnapY": 36.72264071938175,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 2.30463747356668,
                        "ORIG_FID": 119
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 121,
                        "Name": "C013-00123_1",
                        "PickupQuantities": "0.165",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E360_392697-00-16",
                        "Sequence": 5,
                        "FromPrevTravelTime": 0.2456742264330387,
                        "FromPrevDistance": 0.13102730211937985,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355213713365,
                        "DepartTime": 1355213953365,
                        "ArriveTimeUTC": 1355210113365,
                        "DepartTimeUTC": 1355210353365,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1924114077497845,
                        "SnapY": 36.721922694632134,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 4.153657088648043,
                        "ORIG_FID": 121
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 122,
                        "Name": "C013-00126_1",
                        "PickupQuantities": "0.165",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E360_392697-00-16",
                        "Sequence": 6,
                        "FromPrevTravelTime": 0.588388416916132,
                        "FromPrevDistance": 0.3138076607143677,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355213988668,
                        "DepartTime": 1355214228668,
                        "ArriveTimeUTC": 1355210388668,
                        "DepartTimeUTC": 1355210628668,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.19372257844482,
                        "SnapY": 36.72225019099594,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0.251456563620263,
                        "ORIG_FID": 122
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 123,
                        "Name": "C013-00122_1",
                        "PickupQuantities": "0.33",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E360_392697-00-16",
                        "Sequence": 4,
                        "FromPrevTravelTime": 0.1751453150063753,
                        "FromPrevDistance": 0.09341178315801643,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355213458624,
                        "DepartTime": 1355213698624,
                        "ArriveTimeUTC": 1355209858624,
                        "DepartTimeUTC": 1355210098624,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.19282000000004,
                        "SnapY": 36.720920000000035,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0.09504164755344391,
                        "ORIG_FID": 123
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 131,
                        "Name": "C013-00127_1",
                        "PickupQuantities": "0.33",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E360_392697-00-16",
                        "Sequence": 7,
                        "FromPrevTravelTime": 0.2806866429746151,
                        "FromPrevDistance": 0.14969978391652414,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355214245509,
                        "DepartTime": 1355214485509,
                        "ArriveTimeUTC": 1355210645509,
                        "DepartTimeUTC": 1355210885509,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1929986206897003,
                        "SnapY": 36.72299655172422,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0.8606387227595509,
                        "ORIG_FID": 131
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 160,
                        "Name": "C013-00125_1",
                        "PickupQuantities": "0.285",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E360_392697-00-16",
                        "Sequence": 9,
                        "FromPrevTravelTime": 0.10599706135690212,
                        "FromPrevDistance": 0.056531904512171625,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355214756946,
                        "DepartTime": 1355214996946,
                        "ArriveTimeUTC": 1355211156946,
                        "DepartTimeUTC": 1355211396946,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.191159744922084,
                        "SnapY": 36.72202826641481,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 1.306606632260916,
                        "ORIG_FID": 160
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 186,
                        "Name": "C013-00124_1",
                        "PickupQuantities": "0.33",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E360_392697-00-16",
                        "Sequence": 8,
                        "FromPrevTravelTime": 0.41794789023697376,
                        "FromPrevDistance": 0.22290590963338996,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355214510586,
                        "DepartTime": 1355214750586,
                        "ArriveTimeUTC": 1355210910586,
                        "DepartTimeUTC": 1355211150586,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.19178940402687,
                        "SnapY": 36.72201182281883,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 2.0206131435512966,
                        "ORIG_FID": 186
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 219,
                        "Name": "Parc Babez",
                        "PickupQuantities": "0",
                        "DeliveryQuantities": "0",
                        "StopType": 1,
                        "RouteName": "BT12_17E360_392697-00-16",
                        "Sequence": 1,
                        "FromPrevTravelTime": 0,
                        "FromPrevDistance": 0,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355209200000,
                        "DepartTime": 1355212800000,
                        "ArriveTimeUTC": 1355205600000,
                        "DepartTimeUTC": 1355209200000,
                        "WaitTime": 60,
                        "ViolationTime": 0,
                        "SnapX": 0,
                        "SnapY": 0,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0,
                        "ORIG_FID": 1
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 220,
                        "Name": "CET CORSO",
                        "PickupQuantities": "0",
                        "DeliveryQuantities": "4.62",
                        "StopType": 1,
                        "RouteName": "BT12_17E360_392697-00-16",
                        "Sequence": 19,
                        "FromPrevTravelTime": 27.14133176393807,
                        "FromPrevDistance": 26.547242185672406,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355219054443,
                        "DepartTime": 1355220254443,
                        "ArriveTimeUTC": 1355215454443,
                        "DepartTimeUTC": 1355216654443,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 0,
                        "SnapY": 0,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0,
                        "ORIG_FID": 2
                      }
                    }
                  ]
                },
                {
                  "demarrage_parc": 1355221657182,
                  "fin_rotation": 1355227826016,
                  "debut_secteur": 1355221957182,
                  "fin_secteur": 1355224888545,
                  "temps_total": 104.7717504594475,
                  "distance_totale": 60.323601545937535,
                  "distance_collecte": 2.589909509965611,
                  "temps_collecte": 44.85605720244348,
                  "distance_transport": 57.73369203597193,
                  "temps_transport": 59.91569325700402,
                  "arrive_cet": 1355226686016,
                  "depart_cet": 1355226686016,
                  "nombre_points": 11,
                  "quantite_dechets": "4.71",
                  "ordres": [
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 22,
                        "Name": "C013-00117_1",
                        "PickupQuantities": "0.33",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E360_392697-00-16",
                        "Sequence": 20,
                        "FromPrevTravelTime": 28.378972044214606,
                        "FromPrevDistance": 27.15221709592464,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355221957182,
                        "DepartTime": 1355222197182,
                        "ArriveTimeUTC": 1355218357182,
                        "DepartTimeUTC": 1355218597182,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1971300000000724,
                        "SnapY": 36.721670000000074,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 3.5700760131976192,
                        "ORIG_FID": 22
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 34,
                        "Name": "C013-00113_1",
                        "PickupQuantities": "0.33",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E360_392697-00-16",
                        "Sequence": 21,
                        "FromPrevTravelTime": 0.11868877708911896,
                        "FromPrevDistance": 0.06330112087225942,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355222204303,
                        "DepartTime": 1355222444303,
                        "ArriveTimeUTC": 1355218604303,
                        "DepartTimeUTC": 1355218844303,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1968291108271263,
                        "SnapY": 36.72198579879501,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 7.326225464821784,
                        "ORIG_FID": 34
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 97,
                        "Name": "C013-00195_1",
                        "PickupQuantities": "0.36",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E360_392697-00-16",
                        "Sequence": 23,
                        "FromPrevTravelTime": 0.18147237598896027,
                        "FromPrevDistance": 0.09678604421325585,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355222706626,
                        "DepartTime": 1355222946626,
                        "ArriveTimeUTC": 1355219106626,
                        "DepartTimeUTC": 1355219346626,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1947700000000623,
                        "SnapY": 36.721610000000055,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 13.358677486552553,
                        "ORIG_FID": 97
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 103,
                        "Name": "C013-00108_1",
                        "PickupQuantities": "0.33",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E360_392697-00-16",
                        "Sequence": 24,
                        "FromPrevTravelTime": 1.1974548045545816,
                        "FromPrevDistance": 0.6386459024498559,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355223018473,
                        "DepartTime": 1355223258473,
                        "ArriveTimeUTC": 1355219418473,
                        "DepartTimeUTC": 1355219658473,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.191242923076974,
                        "SnapY": 36.72111261538468,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0.40322756088109224,
                        "ORIG_FID": 103
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 109,
                        "Name": "C013-00132_1",
                        "PickupQuantities": "0.99",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E360_392697-00-16",
                        "Sequence": 25,
                        "FromPrevTravelTime": 1.0873156785964966,
                        "FromPrevDistance": 0.5799042834571966,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355223323712,
                        "DepartTime": 1355223563712,
                        "ArriveTimeUTC": 1355219723712,
                        "DepartTimeUTC": 1355219963712,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1921481609601345,
                        "SnapY": 36.72427557712679,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 7.467878935010114,
                        "ORIG_FID": 109
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 111,
                        "Name": "C013-00135_1",
                        "PickupQuantities": "0.165",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E360_392697-00-16",
                        "Sequence": 27,
                        "FromPrevTravelTime": 0.21252978965640068,
                        "FromPrevDistance": 0.11334945635780155,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355223839206,
                        "DepartTime": 1355224079206,
                        "ArriveTimeUTC": 1355220239206,
                        "DepartTimeUTC": 1355220479206,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.189890142155643,
                        "SnapY": 36.72591176272943,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0.19008329510688782,
                        "ORIG_FID": 111
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 112,
                        "Name": "C013-00133_1",
                        "PickupQuantities": "0.66",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E360_392697-00-16",
                        "Sequence": 28,
                        "FromPrevTravelTime": 0.325042475014925,
                        "FromPrevDistance": 0.1733565059044821,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355224098708,
                        "DepartTime": 1355224338708,
                        "ArriveTimeUTC": 1355220498708,
                        "DepartTimeUTC": 1355220738708,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1894980000000377,
                        "SnapY": 36.725116000000064,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 5.012940144190006,
                        "ORIG_FID": 112
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 155,
                        "Name": "C013-00112_1",
                        "PickupQuantities": "0.225",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E360_392697-00-16",
                        "Sequence": 22,
                        "FromPrevTravelTime": 0.1905779354274273,
                        "FromPrevDistance": 0.1016422581340549,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355222455738,
                        "DepartTime": 1355222695738,
                        "ArriveTimeUTC": 1355218855738,
                        "DepartTimeUTC": 1355219095738,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1956993623943193,
                        "SnapY": 36.7218950097593,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0.5541832748909732,
                        "ORIG_FID": 155
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 159,
                        "Name": "C013-00134_1",
                        "PickupQuantities": "0.495",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E360_392697-00-16",
                        "Sequence": 26,
                        "FromPrevTravelTime": 0.3790296968072653,
                        "FromPrevDistance": 0.20215109285227756,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355223586454,
                        "DepartTime": 1355223826454,
                        "ArriveTimeUTC": 1355219986454,
                        "DepartTimeUTC": 1355220226454,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.191146000000076,
                        "SnapY": 36.72577200000006,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 4.706241283151484,
                        "ORIG_FID": 159
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 200,
                        "Name": "C013-00088_1",
                        "PickupQuantities": "0.33",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E360_392697-00-16",
                        "Sequence": 29,
                        "FromPrevTravelTime": 0.616313511505723,
                        "FromPrevDistance": 0.32870152592597884,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355224375687,
                        "DepartTime": 1355224615687,
                        "ArriveTimeUTC": 1355220775687,
                        "DepartTimeUTC": 1355221015687,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.187546984179348,
                        "SnapY": 36.72624470995391,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 6.160869083349289,
                        "ORIG_FID": 200
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 202,
                        "Name": "C013-00087_1",
                        "PickupQuantities": "0.495",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT12_17E360_392697-00-16",
                        "Sequence": 30,
                        "FromPrevTravelTime": 0.5476321578025818,
                        "FromPrevDistance": 0.2920713197984487,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355224648545,
                        "DepartTime": 1355224888545,
                        "ArriveTimeUTC": 1355221048545,
                        "DepartTimeUTC": 1355221288545,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.190311229508265,
                        "SnapY": 36.7261835245902,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 1.5087393817215813,
                        "ORIG_FID": 202
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 221,
                        "Name": "CET CORSO",
                        "PickupQuantities": "0",
                        "DeliveryQuantities": "4.71",
                        "StopType": 1,
                        "RouteName": "BT12_17E360_392697-00-16",
                        "Sequence": 31,
                        "FromPrevTravelTime": 29.95784662850201,
                        "FromPrevDistance": 28.866846017985964,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355226686016,
                        "DepartTime": 1355226686016,
                        "ArriveTimeUTC": 1355223086016,
                        "DepartTimeUTC": 1355223086016,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 0,
                        "SnapY": 0,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0,
                        "ORIG_FID": 2
                      }
                    }
                  ]
                }
              ]
            },
            {
              "vehicle": "BT18_17E771_082980-00-05",
              "rotations": [
                {
                  "demarrage_parc": 1355209200000,
                  "fin_rotation": 1355225456547,
                  "debut_secteur": 1355212915792,
                  "fin_secteur": 1355221559107,
                  "temps_total": 201.89977801404893,
                  "distance_totale": 59.70671058269384,
                  "distance_collecte": 4.668332584077427,
                  "temps_collecte": 145.98512079380453,
                  "distance_transport": 55.03837799861641,
                  "temps_transport": 55.91465722024441,
                  "arrive_cet": 1355223236547,
                  "depart_cet": 1355224436547,
                  "nombre_points": 34,
                  "quantite_dechets": "6.14",
                  "ordres": [
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 1,
                        "Name": "C013-00211_1",
                        "PickupQuantities": "0.025",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 23,
                        "FromPrevTravelTime": 0.029046403244137764,
                        "FromPrevDistance": 0.0077457377972282564,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355218294616,
                        "DepartTime": 1355218534616,
                        "ArriveTimeUTC": 1355214694616,
                        "DepartTimeUTC": 1355214934616,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1913848185140385,
                        "SnapY": 36.73139346163221,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 4.630781755082479,
                        "ORIG_FID": 1
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 2,
                        "Name": "C013-00212_1",
                        "PickupQuantities": "0.025",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 22,
                        "FromPrevTravelTime": 0.10862238518893719,
                        "FromPrevDistance": 0.028966082686537935,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355218052873,
                        "DepartTime": 1355218292873,
                        "ArriveTimeUTC": 1355214452873,
                        "DepartTimeUTC": 1355214692873,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1914513105968645,
                        "SnapY": 36.731438626065824,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 3.975877368195396,
                        "ORIG_FID": 2
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 3,
                        "Name": "C013-00213_1",
                        "PickupQuantities": "0.025",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 9,
                        "FromPrevTravelTime": 1.152792066335678,
                        "FromPrevDistance": 0.3284732047623324,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355214851881,
                        "DepartTime": 1355215091881,
                        "ArriveTimeUTC": 1355211251881,
                        "DepartTimeUTC": 1355211491881,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.191536136419025,
                        "SnapY": 36.73149624360541,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 3.011485751045804,
                        "ORIG_FID": 3
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 4,
                        "Name": "C013-00214_1",
                        "PickupQuantities": "0.025",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 21,
                        "FromPrevTravelTime": 0.040962595492601395,
                        "FromPrevDistance": 0.010923401257616245,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355217806356,
                        "DepartTime": 1355218046356,
                        "ArriveTimeUTC": 1355214206356,
                        "DepartTimeUTC": 1355214446356,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1917019599249095,
                        "SnapY": 36.73160444583601,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 4.416114014348463,
                        "ORIG_FID": 4
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 5,
                        "Name": "C013-00215_1",
                        "PickupQuantities": "0.025",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 20,
                        "FromPrevTravelTime": 0.04213812202215195,
                        "FromPrevDistance": 0.011236876342315707,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355217563898,
                        "DepartTime": 1355217803898,
                        "ArriveTimeUTC": 1355213963898,
                        "DepartTimeUTC": 1355214203898,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.19179840325618,
                        "SnapY": 36.73166401377591,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 5.7127857532757975,
                        "ORIG_FID": 5
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 6,
                        "Name": "C013-00216_1",
                        "PickupQuantities": "0.025",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 19,
                        "FromPrevTravelTime": 0.03425554186105728,
                        "FromPrevDistance": 0.009134846805128258,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355217321370,
                        "DepartTime": 1355217561370,
                        "ArriveTimeUTC": 1355213721370,
                        "DepartTimeUTC": 1355213961370,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.19189761427681,
                        "SnapY": 36.731725291170996,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 5.553230683454969,
                        "ORIG_FID": 6
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 7,
                        "Name": "C013-00221_1",
                        "PickupQuantities": "0.025",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 14,
                        "FromPrevTravelTime": 0.0471050962805748,
                        "FromPrevDistance": 0.012561408088123498,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355216106113,
                        "DepartTime": 1355216346113,
                        "ArriveTimeUTC": 1355212506113,
                        "DepartTimeUTC": 1355212746113,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1924876923077417,
                        "SnapY": 36.732108461538495,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 5.242819818009831,
                        "ORIG_FID": 7
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 8,
                        "Name": "C013-00222_1",
                        "PickupQuantities": "0.025",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 13,
                        "FromPrevTravelTime": 0.067149817943573,
                        "FromPrevDistance": 0.01790668812727598,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355215863286,
                        "DepartTime": 1355216103286,
                        "ArriveTimeUTC": 1355212263286,
                        "DepartTimeUTC": 1355212503286,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1925961538462024,
                        "SnapY": 36.73218076923081,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 4.951295859155852,
                        "ORIG_FID": 8
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 9,
                        "Name": "C013-00223_1",
                        "PickupQuantities": "0.025",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 12,
                        "FromPrevTravelTime": 0.06305110454559326,
                        "FromPrevDistance": 0.01681369343970584,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355215619257,
                        "DepartTime": 1355215859257,
                        "ArriveTimeUTC": 1355212019257,
                        "DepartTimeUTC": 1355212259257,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1927507692308312,
                        "SnapY": 36.732283846153905,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 5.824742730361087,
                        "ORIG_FID": 9
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 13,
                        "Name": "C013-00009_1",
                        "PickupQuantities": "0.66",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 8,
                        "FromPrevTravelTime": 0.25758228078484535,
                        "FromPrevDistance": 0.13737776784971156,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355214542713,
                        "DepartTime": 1355214782713,
                        "ArriveTimeUTC": 1355210942713,
                        "DepartTimeUTC": 1355211182713,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.188618750813342,
                        "SnapY": 36.72980782042946,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 1.3607893032291893,
                        "ORIG_FID": 13
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 14,
                        "Name": "C013-00047_1",
                        "PickupQuantities": "0.33",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 5,
                        "FromPrevTravelTime": 0.39835634641349316,
                        "FromPrevDistance": 0.21245827752267404,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355213782568,
                        "DepartTime": 1355214022568,
                        "ArriveTimeUTC": 1355210182568,
                        "DepartTimeUTC": 1355210422568,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.187450112359603,
                        "SnapY": 36.73118382022476,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 2.0138964053752204,
                        "ORIG_FID": 14
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 17,
                        "Name": "C013-00206_1",
                        "PickupQuantities": "0.025",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 28,
                        "FromPrevTravelTime": 0.028043756261467934,
                        "FromPrevDistance": 0.0074783640551697615,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355219509365,
                        "DepartTime": 1355219749365,
                        "ArriveTimeUTC": 1355215909365,
                        "DepartTimeUTC": 1355216149365,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1908193620106875,
                        "SnapY": 36.7310153697439,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 5.616310032013931,
                        "ORIG_FID": 17
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 18,
                        "Name": "C013-00205_1",
                        "PickupQuantities": "0.025",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 29,
                        "FromPrevTravelTime": 0.03106263093650341,
                        "FromPrevDistance": 0.008283400413280054,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355219751229,
                        "DepartTime": 1355219991229,
                        "ArriveTimeUTC": 1355216151229,
                        "DepartTimeUTC": 1355216391229,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.190747549540879,
                        "SnapY": 36.730968124697974,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 5.270314309103984,
                        "ORIG_FID": 18
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 27,
                        "Name": "C013-00035_1",
                        "PickupQuantities": "0.495",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 3,
                        "FromPrevTravelTime": 1.7977766785770655,
                        "FromPrevDistance": 0.911153400828265,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355213263658,
                        "DepartTime": 1355213503658,
                        "ArriveTimeUTC": 1355209663658,
                        "DepartTimeUTC": 1355209903658,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.185809398324498,
                        "SnapY": 36.73120063214017,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 2.9232066933610517,
                        "ORIG_FID": 27
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 32,
                        "Name": "C013-00045_1",
                        "PickupQuantities": "0.495",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 32,
                        "FromPrevTravelTime": 0.35855197720229626,
                        "FromPrevDistance": 0.2365217668534483,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355220509354,
                        "DepartTime": 1355220749354,
                        "ArriveTimeUTC": 1355216909354,
                        "DepartTimeUTC": 1355217149354,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1893462008734055,
                        "SnapY": 36.732356506550275,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 5.4023464921101985,
                        "ORIG_FID": 32
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 50,
                        "Name": "C013-00217_1",
                        "PickupQuantities": "0.025",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 18,
                        "FromPrevTravelTime": 0.06319781020283699,
                        "FromPrevDistance": 0.016852814990698045,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355217079315,
                        "DepartTime": 1355217319315,
                        "ArriveTimeUTC": 1355213479315,
                        "DepartTimeUTC": 1355213719315,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.191978222003948,
                        "SnapY": 36.73177517681733,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 5.588901437092213,
                        "ORIG_FID": 50
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 54,
                        "Name": "C013-00218_1",
                        "PickupQuantities": "0.025",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 17,
                        "FromPrevTravelTime": 0.04711521416902542,
                        "FromPrevDistance": 0.012564105801496297,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355216835523,
                        "DepartTime": 1355217075523,
                        "ArriveTimeUTC": 1355213235523,
                        "DepartTimeUTC": 1355213475523,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.192126218074682,
                        "SnapY": 36.73186835952854,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 5.192615004742364,
                        "ORIG_FID": 54
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 56,
                        "Name": "C013-00219_1",
                        "PickupQuantities": "0.025",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 16,
                        "FromPrevTravelTime": 0.0607785414904356,
                        "FromPrevDistance": 0.016207674417102858,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355216592696,
                        "DepartTime": 1355216832696,
                        "ArriveTimeUTC": 1355212992696,
                        "DepartTimeUTC": 1355213232696,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.192236552062917,
                        "SnapY": 36.7319378290767,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 5.259162007843117,
                        "ORIG_FID": 56
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 60,
                        "Name": "C013-00053_1",
                        "PickupQuantities": "1",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 34,
                        "FromPrevTravelTime": 0.3178683966398239,
                        "FromPrevDistance": 0.16953051950407552,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355221021922,
                        "DepartTime": 1355221261922,
                        "ArriveTimeUTC": 1355217421922,
                        "DepartTimeUTC": 1355217661922,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.188289636923131,
                        "SnapY": 36.73323069538466,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 4.319953925002585,
                        "ORIG_FID": 60
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 63,
                        "Name": "C013-00224_1",
                        "PickupQuantities": "0.025",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 11,
                        "FromPrevTravelTime": 0.07015028037130833,
                        "FromPrevDistance": 0.01870681438285201,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355215375474,
                        "DepartTime": 1355215615474,
                        "ArriveTimeUTC": 1355211775474,
                        "DepartTimeUTC": 1355212015474,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1928960028808393,
                        "SnapY": 36.73238054735331,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 4.967687907945508,
                        "ORIG_FID": 63
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 64,
                        "Name": "C013-00225_1",
                        "PickupQuantities": "0.025",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 10,
                        "FromPrevTravelTime": 0.6564117148518562,
                        "FromPrevDistance": 0.17504380859903854,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355215131265,
                        "DepartTime": 1355215371265,
                        "ArriveTimeUTC": 1355211531265,
                        "DepartTimeUTC": 1355211771265,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1930580914656375,
                        "SnapY": 36.73248737846602,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 5.366277320985134,
                        "ORIG_FID": 64
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 65,
                        "Name": "C013-00051_1",
                        "PickupQuantities": "0.66",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 35,
                        "FromPrevTravelTime": 0.9530798103660345,
                        "FromPrevDistance": 0.5424933619831811,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355221319107,
                        "DepartTime": 1355221559107,
                        "ArriveTimeUTC": 1355217719107,
                        "DepartTimeUTC": 1355217959107,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.190950475302916,
                        "SnapY": 36.73406078285184,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0.13440918696036408,
                        "ORIG_FID": 65
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 73,
                        "Name": "C013-00209_1",
                        "PickupQuantities": "0.025",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 25,
                        "FromPrevTravelTime": 0.042074983939528465,
                        "FromPrevDistance": 0.011220039605516005,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355218779665,
                        "DepartTime": 1355219019665,
                        "ArriveTimeUTC": 1355215179665,
                        "DepartTimeUTC": 1355215419665,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1911921851401126,
                        "SnapY": 36.7312626163216,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 6.0049476068103615,
                        "ORIG_FID": 73
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 79,
                        "Name": "C013-00220_1",
                        "PickupQuantities": "0.025",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 15,
                        "FromPrevTravelTime": 0.04894052632153034,
                        "FromPrevDistance": 0.013050858051072034,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355216349049,
                        "DepartTime": 1355216589049,
                        "ArriveTimeUTC": 1355212749049,
                        "DepartTimeUTC": 1355212989049,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.19237559943587,
                        "SnapY": 36.732032454160844,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 4.763473722129656,
                        "ORIG_FID": 79
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 80,
                        "Name": "C013-00046_1",
                        "PickupQuantities": "0.495",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 33,
                        "FromPrevTravelTime": 0.22494466044008732,
                        "FromPrevDistance": 0.16614969004056218,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355220762850,
                        "DepartTime": 1355221002850,
                        "ArriveTimeUTC": 1355217162850,
                        "DepartTimeUTC": 1355217402850,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1881742696629627,
                        "SnapY": 36.732174831460746,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 3.1334929957851836,
                        "ORIG_FID": 80
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 85,
                        "Name": "C013-00048_1",
                        "PickupQuantities": "0.405",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 6,
                        "FromPrevTravelTime": 0.12957412376999855,
                        "FromPrevDistance": 0.06910703852449482,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355214030343,
                        "DepartTime": 1355214270343,
                        "ArriveTimeUTC": 1355210430343,
                        "DepartTimeUTC": 1355210670343,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.188011000000033,
                        "SnapY": 36.73109699999999,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 6.666478880698225,
                        "ORIG_FID": 85
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 92,
                        "Name": "C013-00202_1",
                        "PickupQuantities": "0.025",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 30,
                        "FromPrevTravelTime": 0.11499486863613129,
                        "FromPrevDistance": 0.03066541789423855,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355219998128,
                        "DepartTime": 1355220238128,
                        "ArriveTimeUTC": 1355216398128,
                        "DepartTimeUTC": 1355216638128,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.190475975103759,
                        "SnapY": 36.73080240663904,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 3.3870048357634572,
                        "ORIG_FID": 92
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 93,
                        "Name": "C013-00207_1",
                        "PickupQuantities": "0.025",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 27,
                        "FromPrevTravelTime": 0.050129206851124763,
                        "FromPrevDistance": 0.013367840559859474,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355219267682,
                        "DepartTime": 1355219507682,
                        "ArriveTimeUTC": 1355215667682,
                        "DepartTimeUTC": 1355215907682,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.190884195263475,
                        "SnapY": 36.73105802319968,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 6.542014229479683,
                        "ORIG_FID": 93
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 94,
                        "Name": "C013-00208_1",
                        "PickupQuantities": "0.025",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 26,
                        "FromPrevTravelTime": 0.0834877323359251,
                        "FromPrevDistance": 0.02226348204908629,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355219024674,
                        "DepartTime": 1355219264674,
                        "ArriveTimeUTC": 1355215424674,
                        "DepartTimeUTC": 1355215664674,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1910000869985984,
                        "SnapY": 36.73113426776226,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 5.754534346550001,
                        "ORIG_FID": 94
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 130,
                        "Name": "C013-00210_1",
                        "PickupQuantities": "0.025",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 24,
                        "FromPrevTravelTime": 0.042074983939528465,
                        "FromPrevDistance": 0.011220039604062518,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355218537141,
                        "DepartTime": 1355218777141,
                        "ArriveTimeUTC": 1355214937141,
                        "DepartTimeUTC": 1355215177141,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1912885018270813,
                        "SnapY": 36.73132803897691,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 5.318087639729902,
                        "ORIG_FID": 130
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 139,
                        "Name": "C013-00006_1",
                        "PickupQuantities": "0.165",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 4,
                        "FromPrevTravelTime": 0.25014646351337433,
                        "FromPrevDistance": 0.13341208679331001,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355213518667,
                        "DepartTime": 1355213758667,
                        "ArriveTimeUTC": 1355209918667,
                        "DepartTimeUTC": 1355210158667,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1860800000000613,
                        "SnapY": 36.730030000000056,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 1.1083665497819473,
                        "ORIG_FID": 139
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 140,
                        "Name": "C013-00008_1",
                        "PickupQuantities": "0.33",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 7,
                        "FromPrevTravelTime": 0.28192123025655746,
                        "FromPrevDistance": 0.16629141569612194,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355214287258,
                        "DepartTime": 1355214527258,
                        "ArriveTimeUTC": 1355210687258,
                        "DepartTimeUTC": 1355210927258,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1870902195792086,
                        "SnapY": 36.72994362305587,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0.40322756088109224,
                        "ORIG_FID": 140
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 158,
                        "Name": "C013-00139_1",
                        "PickupQuantities": "0.495",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 2,
                        "FromPrevTravelTime": 1.92986149340868,
                        "FromPrevDistance": 1.0799846367605355,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355212915792,
                        "DepartTime": 1355213155792,
                        "ArriveTimeUTC": 1355209315792,
                        "DepartTimeUTC": 1355209555792,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1849521415929605,
                        "SnapY": 36.724610123893875,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 3.552320640543809,
                        "ORIG_FID": 158
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 168,
                        "Name": "C013-00199_1",
                        "PickupQuantities": "0.06",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 31,
                        "FromPrevTravelTime": 0.16187194921076298,
                        "FromPrevDistance": 0.0431660219913093,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355220247841,
                        "DepartTime": 1355220487841,
                        "ArriveTimeUTC": 1355216647841,
                        "DepartTimeUTC": 1355216887841,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1901051331115164,
                        "SnapY": 36.73055163893515,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 4.823773258215905,
                        "ORIG_FID": 168
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 222,
                        "Name": "Parc Babez",
                        "PickupQuantities": "0",
                        "DeliveryQuantities": "0",
                        "StopType": 1,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 1,
                        "FromPrevTravelTime": 0,
                        "FromPrevDistance": 0,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355209200000,
                        "DepartTime": 1355212800000,
                        "ArriveTimeUTC": 1355205600000,
                        "DepartTimeUTC": 1355209200000,
                        "WaitTime": 60,
                        "ViolationTime": 0,
                        "SnapX": 0,
                        "SnapY": 0,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0,
                        "ORIG_FID": 1
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 223,
                        "Name": "CET CORSO",
                        "PickupQuantities": "0",
                        "DeliveryQuantities": "6.14",
                        "StopType": 1,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 36,
                        "FromPrevTravelTime": 27.957328610122204,
                        "FromPrevDistance": 27.519188999308206,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355223236547,
                        "DepartTime": 1355224436547,
                        "ArriveTimeUTC": 1355219636547,
                        "DepartTimeUTC": 1355220836547,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 0,
                        "SnapY": 0,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0,
                        "ORIG_FID": 2
                      }
                    }
                  ]
                },
                {
                  "demarrage_parc": 1355225717603,
                  "fin_rotation": 1355243050080,
                  "debut_secteur": 1355226017603,
                  "fin_secteur": 1355240363255,
                  "temps_total": 290.65502180539966,
                  "distance_totale": 60.06751728466697,
                  "distance_collecte": 6.541836168931107,
                  "temps_collecte": 235.0942017399609,
                  "distance_transport": 53.525681115735864,
                  "temps_transport": 55.56082006543875,
                  "arrive_cet": 1355242030080,
                  "depart_cet": 1355242030080,
                  "nombre_points": 12,
                  "quantite_dechets": "6.342",
                  "ordres": [
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 10,
                        "Name": "C013-00040_1",
                        "PickupQuantities": "0.45",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 41,
                        "FromPrevTravelTime": 0.20371972024440765,
                        "FromPrevDistance": 0.10865089691959068,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355227053712,
                        "DepartTime": 1355227293712,
                        "ArriveTimeUTC": 1355223453712,
                        "DepartTimeUTC": 1355223693712,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1865853846154386,
                        "SnapY": 36.733006923076985,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 2.912371365180468,
                        "ORIG_FID": 10
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 31,
                        "Name": "C013-00120_2",
                        "PickupQuantities": "0.396",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 48,
                        "FromPrevTravelTime": 0.4869586415588856,
                        "FromPrevDistance": 0.2597126183581156,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355240123255,
                        "DepartTime": 1355240363255,
                        "ArriveTimeUTC": 1355236523255,
                        "DepartTimeUTC": 1355236763255,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1960560000000378,
                        "SnapY": 36.716992000000054,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 5.5304113237728405,
                        "ORIG_FID": 31
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 47,
                        "Name": "C013-00291_2",
                        "PickupQuantities": "1",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 47,
                        "FromPrevTravelTime": 0.8355889413505793,
                        "FromPrevDistance": 0.4456498580801366,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355239854037,
                        "DepartTime": 1355240094037,
                        "ArriveTimeUTC": 1355236254037,
                        "DepartTimeUTC": 1355236494037,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.193719033025143,
                        "SnapY": 36.716474126816436,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 9.365340485724001,
                        "ORIG_FID": 47
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 49,
                        "Name": "C013-00062_2",
                        "PickupQuantities": "0.528",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 44,
                        "FromPrevTravelTime": 0.22185922414064407,
                        "FromPrevDistance": 0.11832657834106307,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355238781394,
                        "DepartTime": 1355239021394,
                        "ArriveTimeUTC": 1355235181394,
                        "DepartTimeUTC": 1355235421394,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.176444379773542,
                        "SnapY": 36.72133979194105,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 6.81124439856956,
                        "ORIG_FID": 49
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 61,
                        "Name": "C013-00053_2",
                        "PickupQuantities": "0.8",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 42,
                        "FromPrevTravelTime": 0.3289775960147381,
                        "FromPrevDistance": 0.1754554147570209,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355227313451,
                        "DepartTime": 1355238240000,
                        "ArriveTimeUTC": 1355223713451,
                        "DepartTimeUTC": 1355234640000,
                        "WaitTime": 178.10914969444275,
                        "ViolationTime": 0,
                        "SnapX": 3.188289636923131,
                        "SnapY": 36.73323069538466,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 4.319953925002585,
                        "ORIG_FID": 61
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 77,
                        "Name": "C013-00055_2",
                        "PickupQuantities": "0.396",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 45,
                        "FromPrevTravelTime": 1.121863979846239,
                        "FromPrevDistance": 0.5983310174724921,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355239088706,
                        "DepartTime": 1355239328706,
                        "ArriveTimeUTC": 1355235488706,
                        "DepartTimeUTC": 1355235728706,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1764995172414405,
                        "SnapY": 36.7169417931035,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 13.913785586378339,
                        "ORIG_FID": 77
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 78,
                        "Name": "C013-00038_1",
                        "PickupQuantities": "0.33",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 40,
                        "FromPrevTravelTime": 0.37193920463323593,
                        "FromPrevDistance": 0.1983683579048157,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355226801489,
                        "DepartTime": 1355227041489,
                        "ArriveTimeUTC": 1355223201489,
                        "DepartTimeUTC": 1355223441489,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.186417268927118,
                        "SnapY": 36.73233639108227,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0.4558037293643836,
                        "ORIG_FID": 78
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 161,
                        "Name": "C013-00043_1",
                        "PickupQuantities": "0.33",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 38,
                        "FromPrevTravelTime": 0.3921580873429775,
                        "FromPrevDistance": 0.20915178243493354,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355226281132,
                        "DepartTime": 1355226521132,
                        "ArriveTimeUTC": 1355222681132,
                        "DepartTimeUTC": 1355222921132,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.184232352941206,
                        "SnapY": 36.73138941176474,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 1.1083665497819473,
                        "ORIG_FID": 161
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 162,
                        "Name": "C013-00044_1",
                        "PickupQuantities": "0.33",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 39,
                        "FromPrevTravelTime": 0.3006753474473953,
                        "FromPrevDistance": 0.1603610021419124,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355226539173,
                        "DepartTime": 1355226779173,
                        "ArriveTimeUTC": 1355222939173,
                        "DepartTimeUTC": 1355223179173,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.185239594669671,
                        "SnapY": 36.73158600777354,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 1.7755245012716427,
                        "ORIG_FID": 162
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 193,
                        "Name": "C013-00153_2",
                        "PickupQuantities": "0.924",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 46,
                        "FromPrevTravelTime": 3.9199386835098267,
                        "FromPrevDistance": 2.1364953373749693,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355239563902,
                        "DepartTime": 1355239803902,
                        "ArriveTimeUTC": 1355235963902,
                        "DepartTimeUTC": 1355236203902,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1901977234927776,
                        "SnapY": 36.715400571725624,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 1.578950435076977,
                        "ORIG_FID": 193
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 199,
                        "Name": "C013-00007_1",
                        "PickupQuantities": "0.33",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 37,
                        "FromPrevTravelTime": 26.350930945947766,
                        "FromPrevDistance": 27.389023879288374,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355226017603,
                        "DepartTime": 1355226257603,
                        "ArriveTimeUTC": 1355222417603,
                        "DepartTimeUTC": 1355222657603,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1837458245344883,
                        "SnapY": 36.729960126703816,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 6.703637145544764,
                        "ORIG_FID": 199
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 211,
                        "Name": "C013-00063_2",
                        "PickupQuantities": "0.528",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 43,
                        "FromPrevTravelTime": 4.80137231387198,
                        "FromPrevDistance": 2.131333305146057,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355238528082,
                        "DepartTime": 1355238768082,
                        "ArriveTimeUTC": 1355234928082,
                        "DepartTimeUTC": 1355235168082,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1772829088472316,
                        "SnapY": 36.72172247989279,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 1.7395477710716916,
                        "ORIG_FID": 211
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 224,
                        "Name": "CET CORSO",
                        "PickupQuantities": "0",
                        "DeliveryQuantities": "6.342",
                        "StopType": 1,
                        "RouteName": "BT18_17E771_082980-00-05",
                        "Sequence": 49,
                        "FromPrevTravelTime": 27.780410032719374,
                        "FromPrevDistance": 26.762840557867932,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355242030080,
                        "DepartTime": 1355242030080,
                        "ArriveTimeUTC": 1355238430080,
                        "DepartTimeUTC": 1355238430080,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 0,
                        "SnapY": 0,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0,
                        "ORIG_FID": 2
                      }
                    }
                  ]
                }
              ]
            },
            {
              "vehicle": "BT16_17E568_012884-00-16",
              "rotations": [
                {
                  "demarrage_parc": 1355209200000,
                  "fin_rotation": 1355220601540,
                  "debut_secteur": 1355213104211,
                  "fin_secteur": 1355216723962,
                  "temps_total": 120.6519548073411,
                  "distance_totale": 59.49153472535442,
                  "distance_collecte": 5.157530402340705,
                  "temps_collecte": 65.3993712849915,
                  "distance_transport": 54.334004323013716,
                  "temps_transport": 55.252583522349596,
                  "arrive_cet": 1355218381540,
                  "depart_cet": 1355219581540,
                  "nombre_points": 14,
                  "quantite_dechets": "6.3",
                  "ordres": [
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 11,
                        "Name": "C013-00198_1",
                        "PickupQuantities": "0.33",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT16_17E568_012884-00-16",
                        "Sequence": 5,
                        "FromPrevTravelTime": 0.6922587361186743,
                        "FromPrevDistance": 0.3692082377606428,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355213904780,
                        "DepartTime": 1355214144780,
                        "ArriveTimeUTC": 1355210304780,
                        "DepartTimeUTC": 1355210544780,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1931987836853937,
                        "SnapY": 36.738667501820885,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 1.7107496559619955,
                        "ORIG_FID": 11
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 12,
                        "Name": "C013-00034_1",
                        "PickupQuantities": "0.495",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT16_17E568_012884-00-16",
                        "Sequence": 10,
                        "FromPrevTravelTime": 0.22329631261527538,
                        "FromPrevDistance": 0.16694631114370678,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355215187805,
                        "DepartTime": 1355215427805,
                        "ArriveTimeUTC": 1355211587805,
                        "DepartTimeUTC": 1355211827805,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1936391114245857,
                        "SnapY": 36.73990469675606,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 5.7466804677071535,
                        "ORIG_FID": 12
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 15,
                        "Name": "C013-00030_1",
                        "PickupQuantities": "1.32",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT16_17E568_012884-00-16",
                        "Sequence": 4,
                        "FromPrevTravelTime": 0.32808432541787624,
                        "FromPrevDistance": 0.17498052024903718,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355213623244,
                        "DepartTime": 1355213863244,
                        "ArriveTimeUTC": 1355210023244,
                        "DepartTimeUTC": 1355210263244,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1918317241379883,
                        "SnapY": 36.73671931034491,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 1.268013733759145,
                        "ORIG_FID": 15
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 28,
                        "Name": "C013-00052_1",
                        "PickupQuantities": "0.18",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT16_17E568_012884-00-16",
                        "Sequence": 2,
                        "FromPrevTravelTime": 5.070182343944907,
                        "FromPrevDistance": 2.7277918183402505,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355213104211,
                        "DepartTime": 1355213344211,
                        "ArriveTimeUTC": 1355209504211,
                        "DepartTimeUTC": 1355209744211,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1905638864629218,
                        "SnapY": 36.73433585152843,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 17.736920690987365,
                        "ORIG_FID": 28
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 36,
                        "Name": "C013-00196_1",
                        "PickupQuantities": "0.33",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT16_17E568_012884-00-16",
                        "Sequence": 11,
                        "FromPrevTravelTime": 0.06064736284315586,
                        "FromPrevDistance": 0.04548579020527741,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355215431443,
                        "DepartTime": 1355215671443,
                        "ArriveTimeUTC": 1355211831443,
                        "DepartTimeUTC": 1355212071443,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1933363875432823,
                        "SnapY": 36.740188726643616,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 6.56544509388995,
                        "ORIG_FID": 36
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 53,
                        "Name": "C013-00012_1",
                        "PickupQuantities": "0.33",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT16_17E568_012884-00-16",
                        "Sequence": 7,
                        "FromPrevTravelTime": 0.5147022288292646,
                        "FromPrevDistance": 0.27450872444386976,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355214420996,
                        "DepartTime": 1355214660996,
                        "ArriveTimeUTC": 1355210820996,
                        "DepartTimeUTC": 1355211060996,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.195104419263492,
                        "SnapY": 36.73879560906522,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0.7361894363412067,
                        "ORIG_FID": 53
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 55,
                        "Name": "C013-00013_1",
                        "PickupQuantities": "0.33",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT16_17E568_012884-00-16",
                        "Sequence": 9,
                        "FromPrevTravelTime": 0.18239740654826164,
                        "FromPrevDistance": 0.13557385096325625,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355214934407,
                        "DepartTime": 1355215174407,
                        "ArriveTimeUTC": 1355211334407,
                        "DepartTimeUTC": 1355211574407,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1929318160505624,
                        "SnapY": 36.73913311812451,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0.38016659021377575,
                        "ORIG_FID": 55
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 96,
                        "Name": "C013-00197_1",
                        "PickupQuantities": "0.33",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT16_17E568_012884-00-16",
                        "Sequence": 6,
                        "FromPrevTravelTime": 0.08890875428915024,
                        "FromPrevDistance": 0.04741816902159303,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355214150114,
                        "DepartTime": 1355214390114,
                        "ArriveTimeUTC": 1355210550114,
                        "DepartTimeUTC": 1355210790114,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1933311475410586,
                        "SnapY": 36.73833262295088,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 3.0294292211550333,
                        "ORIG_FID": 96
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 132,
                        "Name": "C013-00024_1",
                        "PickupQuantities": "0.33",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT16_17E568_012884-00-16",
                        "Sequence": 8,
                        "FromPrevTravelTime": 0.37444340623915195,
                        "FromPrevDistance": 0.19970387893181682,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355214683463,
                        "DepartTime": 1355214923463,
                        "ArriveTimeUTC": 1355211083463,
                        "DepartTimeUTC": 1355211323463,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.194149459459523,
                        "SnapY": 36.73908675675681,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 7.235035666136064,
                        "ORIG_FID": 132
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 143,
                        "Name": "C013-00019_1",
                        "PickupQuantities": "0.495",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT16_17E568_012884-00-16",
                        "Sequence": 13,
                        "FromPrevTravelTime": 0.3831625450402498,
                        "FromPrevDistance": 0.20435482824337095,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355215969248,
                        "DepartTime": 1355216209248,
                        "ArriveTimeUTC": 1355212369248,
                        "DepartTimeUTC": 1355212609248,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1896449805447906,
                        "SnapY": 36.738069688716,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 8.88220004268859,
                        "ORIG_FID": 143
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 146,
                        "Name": "C013-00025_1",
                        "PickupQuantities": "0.66",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT16_17E568_012884-00-16",
                        "Sequence": 15,
                        "FromPrevTravelTime": 0.3688514977693558,
                        "FromPrevDistance": 0.1967221599770274,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355216483962,
                        "DepartTime": 1355216723962,
                        "ArriveTimeUTC": 1355212883962,
                        "DepartTimeUTC": 1355213123962,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1920341203438927,
                        "SnapY": 36.73812436676224,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 2.1505469913658355,
                        "ORIG_FID": 146
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 170,
                        "Name": "C013-00029_1",
                        "PickupQuantities": "0.495",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT16_17E568_012884-00-16",
                        "Sequence": 3,
                        "FromPrevTravelTime": 0.3224692568182945,
                        "FromPrevDistance": 0.19261084477016516,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355213363559,
                        "DepartTime": 1355213603559,
                        "ArriveTimeUTC": 1355209763559,
                        "DepartTimeUTC": 1355210003559,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.190473366336657,
                        "SnapY": 36.735583663366384,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 2.171446870123554,
                        "ORIG_FID": 170
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 183,
                        "Name": "C013-00021_1",
                        "PickupQuantities": "0.33",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT16_17E568_012884-00-16",
                        "Sequence": 12,
                        "FromPrevTravelTime": 0.580252954736352,
                        "FromPrevDistance": 0.31037705255448683,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355215706259,
                        "DepartTime": 1355215946259,
                        "ArriveTimeUTC": 1355212106259,
                        "DepartTimeUTC": 1355212346259,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1908349189189673,
                        "SnapY": 36.739205513513575,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 4.443641714461922,
                        "ORIG_FID": 183
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 184,
                        "Name": "C013-00020_1",
                        "PickupQuantities": "0.345",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT16_17E568_012884-00-16",
                        "Sequence": 14,
                        "FromPrevTravelTime": 0.20971415378153324,
                        "FromPrevDistance": 0.11184821573620461,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355216221831,
                        "DepartTime": 1355216461831,
                        "ArriveTimeUTC": 1355212621831,
                        "DepartTimeUTC": 1355212861831,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1906278232858063,
                        "SnapY": 36.73863713759782,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 2.1942068207522696,
                        "ORIG_FID": 184
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 225,
                        "Name": "Parc Babez",
                        "PickupQuantities": "0",
                        "DeliveryQuantities": "0",
                        "StopType": 1,
                        "RouteName": "BT16_17E568_012884-00-16",
                        "Sequence": 1,
                        "FromPrevTravelTime": 0,
                        "FromPrevDistance": 0,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355209200000,
                        "DepartTime": 1355212800000,
                        "ArriveTimeUTC": 1355205600000,
                        "DepartTimeUTC": 1355209200000,
                        "WaitTime": 60,
                        "ViolationTime": 0,
                        "SnapX": 0,
                        "SnapY": 0,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0,
                        "ORIG_FID": 1
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 226,
                        "Name": "CET CORSO",
                        "PickupQuantities": "0",
                        "DeliveryQuantities": "6.3",
                        "StopType": 1,
                        "RouteName": "BT16_17E568_012884-00-16",
                        "Sequence": 16,
                        "FromPrevTravelTime": 27.626291761174798,
                        "FromPrevDistance": 27.167002161506858,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355218381540,
                        "DepartTime": 1355219581540,
                        "ArriveTimeUTC": 1355214781540,
                        "DepartTimeUTC": 1355215981540,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 0,
                        "SnapY": 0,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0,
                        "ORIG_FID": 2
                      }
                    }
                  ]
                },
                {
                  "demarrage_parc": 1355220882212,
                  "fin_rotation": 1355226524501,
                  "debut_secteur": 1355221182212,
                  "fin_secteur": 1355223866398,
                  "temps_total": 95.33985727839172,
                  "distance_totale": 56.816651809829466,
                  "distance_collecte": 2.6458953318973593,
                  "temps_collecte": 40.73642483912408,
                  "distance_transport": 54.17075647793211,
                  "temps_transport": 54.603432439267635,
                  "arrive_cet": 1355225504501,
                  "depart_cet": 1355225504501,
                  "nombre_points": 10,
                  "quantite_dechets": "6.03",
                  "ordres": [
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 26,
                        "Name": "C013-00049_1",
                        "PickupQuantities": "0.72",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT16_17E568_012884-00-16",
                        "Sequence": 23,
                        "FromPrevTravelTime": 0.41695051454007626,
                        "FromPrevDistance": 0.22927585555851718,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355222848786,
                        "DepartTime": 1355223088786,
                        "ArriveTimeUTC": 1355219248786,
                        "DepartTimeUTC": 1355219488786,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.192927626774898,
                        "SnapY": 36.73413740365118,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 1.9477741663916561,
                        "ORIG_FID": 26
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 51,
                        "Name": "C013-00023_1",
                        "PickupQuantities": "0.555",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT16_17E568_012884-00-16",
                        "Sequence": 17,
                        "FromPrevTravelTime": 26.677873333916068,
                        "FromPrevDistance": 26.965284730392227,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355221182212,
                        "DepartTime": 1355221422212,
                        "ArriveTimeUTC": 1355217582212,
                        "DepartTimeUTC": 1355217822212,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.194047200000047,
                        "SnapY": 36.73767960000005,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 9.14377707012305,
                        "ORIG_FID": 51
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 52,
                        "Name": "C013-00022_1",
                        "PickupQuantities": "0.72",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT16_17E568_012884-00-16",
                        "Sequence": 18,
                        "FromPrevTravelTime": 0.27320923656225204,
                        "FromPrevDistance": 0.1457121768067735,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355221438605,
                        "DepartTime": 1355221678605,
                        "ArriveTimeUTC": 1355217838605,
                        "DepartTimeUTC": 1355218078605,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1952956470588685,
                        "SnapY": 36.737443411764744,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0.8915696830485151,
                        "ORIG_FID": 52
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 133,
                        "Name": "C013-00011_1",
                        "PickupQuantities": "0.825",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT16_17E568_012884-00-16",
                        "Sequence": 22,
                        "FromPrevTravelTime": 1.2832290790975094,
                        "FromPrevDistance": 0.6943068005884718,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355222583768,
                        "DepartTime": 1355222823768,
                        "ArriveTimeUTC": 1355218983768,
                        "DepartTimeUTC": 1355219223768,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.193207660944264,
                        "SnapY": 36.7359518240344,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0.7112265652390182,
                        "ORIG_FID": 133
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 138,
                        "Name": "C013-00200_1",
                        "PickupQuantities": "0.12",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT16_17E568_012884-00-16",
                        "Sequence": 25,
                        "FromPrevTravelTime": 0.2566474415361881,
                        "FromPrevDistance": 0.18420895728194164,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355223359045,
                        "DepartTime": 1355223599045,
                        "ArriveTimeUTC": 1355219759045,
                        "DepartTimeUTC": 1355219999045,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1937052342007997,
                        "SnapY": 36.73312879553909,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 4.153657088648043,
                        "ORIG_FID": 138
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 142,
                        "Name": "C013-00032_1",
                        "PickupQuantities": "0.33",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT16_17E568_012884-00-16",
                        "Sequence": 20,
                        "FromPrevTravelTime": 0.4131501875817776,
                        "FromPrevDistance": 0.23026140333253986,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355222000819,
                        "DepartTime": 1355222240819,
                        "ArriveTimeUTC": 1355218400819,
                        "DepartTimeUTC": 1355218640819,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1891817339667723,
                        "SnapY": 36.73545971496441,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0.7361894363412067,
                        "ORIG_FID": 142
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 145,
                        "Name": "C013-00016_1",
                        "PickupQuantities": "0.78",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT16_17E568_012884-00-16",
                        "Sequence": 21,
                        "FromPrevTravelTime": 0.432590339332819,
                        "FromPrevDistance": 0.23071573808997786,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355222266775,
                        "DepartTime": 1355222506775,
                        "ArriveTimeUTC": 1355218666775,
                        "DepartTimeUTC": 1355218906775,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1888263003520363,
                        "SnapY": 36.73691951505678,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 8.18903896987076,
                        "ORIG_FID": 145
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 147,
                        "Name": "C013-00050_1",
                        "PickupQuantities": "0.66",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT16_17E568_012884-00-16",
                        "Sequence": 24,
                        "FromPrevTravelTime": 0.2476824764162302,
                        "FromPrevDistance": 0.15981159295583525,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355223103646,
                        "DepartTime": 1355223343646,
                        "ArriveTimeUTC": 1355219503646,
                        "DepartTimeUTC": 1355219743646,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.192205390431247,
                        "SnapY": 36.7332379196693,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 4.191541390349702,
                        "ORIG_FID": 147
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 185,
                        "Name": "C013-00010_1",
                        "PickupQuantities": "0.165",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT16_17E568_012884-00-16",
                        "Sequence": 26,
                        "FromPrevTravelTime": 0.45587288588285446,
                        "FromPrevDistance": 0.2611498077525809,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355223626398,
                        "DepartTime": 1355223866398,
                        "ArriveTimeUTC": 1355220026398,
                        "DepartTimeUTC": 1355220266398,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1954529729730305,
                        "SnapY": 36.7343521621622,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 2.004905751526625,
                        "ORIG_FID": 185
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 191,
                        "Name": "C013-00033_1",
                        "PickupQuantities": "1.155",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT16_17E568_012884-00-16",
                        "Sequence": 19,
                        "FromPrevTravelTime": 0.9570926781743765,
                        "FromPrevDistance": 0.5104529995307212,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355221736030,
                        "DepartTime": 1355221976030,
                        "ArriveTimeUTC": 1355218136030,
                        "DepartTimeUTC": 1355218376030,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1908409140519196,
                        "SnapY": 36.73608733969993,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 1.7160216242557762,
                        "ORIG_FID": 191
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 227,
                        "Name": "CET CORSO",
                        "PickupQuantities": "0",
                        "DeliveryQuantities": "6.03",
                        "StopType": 1,
                        "RouteName": "BT16_17E568_012884-00-16",
                        "Sequence": 27,
                        "FromPrevTravelTime": 27.301716219633818,
                        "FromPrevDistance": 27.085378238966054,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355225504501,
                        "DepartTime": 1355225504501,
                        "ArriveTimeUTC": 1355221904501,
                        "DepartTimeUTC": 1355221904501,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 0,
                        "SnapY": 0,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0,
                        "ORIG_FID": 2
                      }
                    }
                  ]
                }
              ]
            },
            {
              "vehicle": "BT18_17E758_126518-00-16",
              "rotations": [
                {
                  "demarrage_parc": 1355209200000,
                  "fin_rotation": 1355218879006,
                  "debut_secteur": 1355212959546,
                  "fin_secteur": 1355215122278,
                  "temps_total": 91.92890070565045,
                  "distance_totale": 55.837279633779495,
                  "distance_collecte": 3.557769721205028,
                  "temps_collecte": 38.70463804341853,
                  "distance_transport": 52.27950991257447,
                  "temps_transport": 53.22426266223192,
                  "arrive_cet": 1355216719006,
                  "depart_cet": 1355217919006,
                  "nombre_points": 8,
                  "quantite_dechets": "6.76125",
                  "ordres": [
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 106,
                        "Name": "C013-00102_1",
                        "PickupQuantities": "0.33",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E758_126518-00-16",
                        "Sequence": 4,
                        "FromPrevTravelTime": 0.2681115623563528,
                        "FromPrevDistance": 0.11396697764898026,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355213500956,
                        "DepartTime": 1355213740956,
                        "ArriveTimeUTC": 1355209900956,
                        "DepartTimeUTC": 1355210140956,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1926629356456795,
                        "SnapY": 36.71925051190978,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0.6304349687839457,
                        "ORIG_FID": 106
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 107,
                        "Name": "C013-00101_1",
                        "PickupQuantities": "0.495",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E758_126518-00-16",
                        "Sequence": 3,
                        "FromPrevTravelTime": 0.7553759440779686,
                        "FromPrevDistance": 0.29068624332411314,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355213244869,
                        "DepartTime": 1355213484869,
                        "ArriveTimeUTC": 1355209644869,
                        "DepartTimeUTC": 1355209884869,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.192740978432445,
                        "SnapY": 36.718273298264116,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 1.091945396801894,
                        "ORIG_FID": 107
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 127,
                        "Name": "C013-00168_1",
                        "PickupQuantities": "0.99",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E758_126518-00-16",
                        "Sequence": 8,
                        "FromPrevTravelTime": 1.1734039597213268,
                        "FromPrevDistance": 0.6258188115023876,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355214605343,
                        "DepartTime": 1355214845343,
                        "ArriveTimeUTC": 1355211005343,
                        "DepartTimeUTC": 1355211245343,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.2003695865430517,
                        "SnapY": 36.717943963213365,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 1.1444529879390397,
                        "ORIG_FID": 127
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 128,
                        "Name": "C013-00170_1",
                        "PickupQuantities": "2.475",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E758_126518-00-16",
                        "Sequence": 9,
                        "FromPrevTravelTime": 0.6155803836882114,
                        "FromPrevDistance": 0.32831081041949767,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355214882278,
                        "DepartTime": 1355215122278,
                        "ArriveTimeUTC": 1355211282278,
                        "DepartTimeUTC": 1355211522278,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.2014653846154184,
                        "SnapY": 36.72011307692315,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0.8710710881739431,
                        "ORIG_FID": 128
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 154,
                        "Name": "C013-00104_1",
                        "PickupQuantities": "0.825",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E758_126518-00-16",
                        "Sequence": 7,
                        "FromPrevTravelTime": 0.4271047282963991,
                        "FromPrevDistance": 0.22779046422970087,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355214294939,
                        "DepartTime": 1355214534939,
                        "ArriveTimeUTC": 1355210694939,
                        "DepartTimeUTC": 1355210934939,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.194824390243947,
                        "SnapY": 36.71927951219517,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 2.3144153599594386,
                        "ORIG_FID": 154
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 156,
                        "Name": "C013-00111_1",
                        "PickupQuantities": "0.33",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E758_126518-00-16",
                        "Sequence": 6,
                        "FromPrevTravelTime": 0.5567326880991459,
                        "FromPrevDistance": 0.2969255180461887,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355214029313,
                        "DepartTime": 1355214269313,
                        "ArriveTimeUTC": 1355210429313,
                        "DepartTimeUTC": 1355210669313,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.194555836298978,
                        "SnapY": 36.720579323843474,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 5.955100619279155,
                        "ORIG_FID": 156
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 187,
                        "Name": "C013-00149_1",
                        "PickupQuantities": "0.66",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E758_126518-00-16",
                        "Sequence": 2,
                        "FromPrevTravelTime": 2.6591061875224113,
                        "FromPrevDistance": 1.5413517316069918,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355212959546,
                        "DepartTime": 1355213199546,
                        "ArriveTimeUTC": 1355209359546,
                        "DepartTimeUTC": 1355209599546,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.191099649702639,
                        "SnapY": 36.7172792663583,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 2.1421299454033953,
                        "ORIG_FID": 187
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 206,
                        "Name": "C013-00103_1",
                        "PickupQuantities": "0.65625",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E758_126518-00-16",
                        "Sequence": 5,
                        "FromPrevTravelTime": 0.24922258965671062,
                        "FromPrevDistance": 0.1329191644271683,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355213755909,
                        "DepartTime": 1355213995909,
                        "ArriveTimeUTC": 1355210155909,
                        "DepartTimeUTC": 1355210395909,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.192745253886052,
                        "SnapY": 36.72044643523319,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 4.945819769894152,
                        "ORIG_FID": 206
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 228,
                        "Name": "Parc Babez",
                        "PickupQuantities": "0",
                        "DeliveryQuantities": "0",
                        "StopType": 1,
                        "RouteName": "BT18_17E758_126518-00-16",
                        "Sequence": 1,
                        "FromPrevTravelTime": 0,
                        "FromPrevDistance": 0,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355209200000,
                        "DepartTime": 1355212800000,
                        "ArriveTimeUTC": 1355205600000,
                        "DepartTimeUTC": 1355209200000,
                        "WaitTime": 60,
                        "ViolationTime": 0,
                        "SnapX": 0,
                        "SnapY": 0,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0,
                        "ORIG_FID": 1
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 229,
                        "Name": "CET CORSO",
                        "PickupQuantities": "0",
                        "DeliveryQuantities": "6.76125",
                        "StopType": 1,
                        "RouteName": "BT18_17E758_126518-00-16",
                        "Sequence": 10,
                        "FromPrevTravelTime": 26.61213133111596,
                        "FromPrevDistance": 26.139754956287234,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355216719006,
                        "DepartTime": 1355217919006,
                        "ArriveTimeUTC": 1355213119006,
                        "DepartTimeUTC": 1355214319006,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 0,
                        "SnapY": 0,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0,
                        "ORIG_FID": 2
                      }
                    }
                  ]
                },
                {
                  "demarrage_parc": 1355219241924,
                  "fin_rotation": 1355225524365,
                  "debut_secteur": 1355219541924,
                  "fin_secteur": 1355222831549,
                  "temps_total": 106.58761603012681,
                  "distance_totale": 55.13997787372521,
                  "distance_collecte": 1.5077835282089813,
                  "temps_collecte": 50.82708213850856,
                  "distance_transport": 53.632194345516226,
                  "temps_transport": 55.76053389161825,
                  "arrive_cet": 1355224504365,
                  "depart_cet": 1355224504365,
                  "nombre_points": 13,
                  "quantite_dechets": "7.025",
                  "ordres": [
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 30,
                        "Name": "C013-00120_1",
                        "PickupQuantities": "0.495",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E758_126518-00-16",
                        "Sequence": 21,
                        "FromPrevTravelTime": 0.4869586415588856,
                        "FromPrevDistance": 0.2597126183581156,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355222070211,
                        "DepartTime": 1355222310211,
                        "ArriveTimeUTC": 1355218470211,
                        "DepartTimeUTC": 1355218710211,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1960560000000378,
                        "SnapY": 36.716992000000054,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 5.5304113237728405,
                        "ORIG_FID": 30
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 46,
                        "Name": "C013-00291_1",
                        "PickupQuantities": "1.25",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E758_126518-00-16",
                        "Sequence": 20,
                        "FromPrevTravelTime": 0.11620486527681351,
                        "FromPrevDistance": 0.061976133164575856,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355221800993,
                        "DepartTime": 1355222040993,
                        "ArriveTimeUTC": 1355218200993,
                        "DepartTimeUTC": 1355218440993,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.193719033025143,
                        "SnapY": 36.716474126816436,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 9.365340485724001,
                        "ORIG_FID": 46
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 108,
                        "Name": "C013-00115_1",
                        "PickupQuantities": "0.495",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E758_126518-00-16",
                        "Sequence": 23,
                        "FromPrevTravelTime": 0.3259846791625023,
                        "FromPrevDistance": 0.17385895879250907,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355222591549,
                        "DepartTime": 1355222831549,
                        "ArriveTimeUTC": 1355218991549,
                        "DepartTimeUTC": 1355219231549,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.195757140939638,
                        "SnapY": 36.71867179865775,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 5.4031824451507315,
                        "ORIG_FID": 108
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 124,
                        "Name": "C013-00171_1",
                        "PickupQuantities": "0.495",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E758_126518-00-16",
                        "Sequence": 19,
                        "FromPrevTravelTime": 0.39988698065280914,
                        "FromPrevDistance": 0.21327369860193665,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355221554021,
                        "DepartTime": 1355221794021,
                        "ArriveTimeUTC": 1355217954021,
                        "DepartTimeUTC": 1355218194021,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1932671764706346,
                        "SnapY": 36.71628329411768,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 3.5510490025433197,
                        "ORIG_FID": 124
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 125,
                        "Name": "C013-00165_1",
                        "PickupQuantities": "0.66",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E758_126518-00-16",
                        "Sequence": 14,
                        "FromPrevTravelTime": 0.16627475060522556,
                        "FromPrevDistance": 0.08868035581046593,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355220284521,
                        "DepartTime": 1355220524521,
                        "ArriveTimeUTC": 1355216684521,
                        "DepartTimeUTC": 1355216924521,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.198568102602306,
                        "SnapY": 36.715566813382935,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0.7837335033911516,
                        "ORIG_FID": 125
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 126,
                        "Name": "C013-00164_1",
                        "PickupQuantities": "0.495",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E758_126518-00-16",
                        "Sequence": 15,
                        "FromPrevTravelTime": 0.16751369088888168,
                        "FromPrevDistance": 0.08934112260092347,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355220534571,
                        "DepartTime": 1355220774571,
                        "ArriveTimeUTC": 1355216934571,
                        "DepartTimeUTC": 1355217174571,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1976505036726373,
                        "SnapY": 36.71524737670519,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 2.6898633293363106,
                        "ORIG_FID": 126
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 129,
                        "Name": "C013-00169_1",
                        "PickupQuantities": "0.495",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E758_126518-00-16",
                        "Sequence": 11,
                        "FromPrevTravelTime": 27.048636129125953,
                        "FromPrevDistance": 26.442699247047248,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355219541924,
                        "DepartTime": 1355219781924,
                        "ArriveTimeUTC": 1355215941924,
                        "DepartTimeUTC": 1355216181924,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.2006556300268647,
                        "SnapY": 36.71620552278828,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 1.6868219089302305,
                        "ORIG_FID": 129
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 175,
                        "Name": "C013-00114_1",
                        "PickupQuantities": "0.165",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E758_126518-00-16",
                        "Sequence": 22,
                        "FromPrevTravelTime": 0.36298791132867336,
                        "FromPrevDistance": 0.19359492796482763,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355222331990,
                        "DepartTime": 1355222571990,
                        "ArriveTimeUTC": 1355218731990,
                        "DepartTimeUTC": 1355218971990,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.19492660181001,
                        "SnapY": 36.71750971493219,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 1.194654985177037,
                        "ORIG_FID": 175
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 176,
                        "Name": "C013-00167_1",
                        "PickupQuantities": "0.495",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E758_126518-00-16",
                        "Sequence": 12,
                        "FromPrevTravelTime": 0.09802306070923805,
                        "FromPrevDistance": 0.05227898805866841,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355219787806,
                        "DepartTime": 1355220027806,
                        "ArriveTimeUTC": 1355216187806,
                        "DepartTimeUTC": 1355216427806,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.2001219083782733,
                        "SnapY": 36.71602387310146,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0.6984106224581247,
                        "ORIG_FID": 176
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 177,
                        "Name": "C013-00163_1",
                        "PickupQuantities": "0.66",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E758_126518-00-16",
                        "Sequence": 16,
                        "FromPrevTravelTime": 0.1261398009955883,
                        "FromPrevDistance": 0.067274841137761,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355220782140,
                        "DepartTime": 1355221022140,
                        "ArriveTimeUTC": 1355217182140,
                        "DepartTimeUTC": 1355217422140,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1969853769322674,
                        "SnapY": 36.71496245422122,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 2.011652508962112,
                        "ORIG_FID": 177
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 178,
                        "Name": "C013-00162_1",
                        "PickupQuantities": "0.495",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E758_126518-00-16",
                        "Sequence": 17,
                        "FromPrevTravelTime": 0.16662147268652916,
                        "FromPrevDistance": 0.08886493570250578,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355221032137,
                        "DepartTime": 1355221272137,
                        "ArriveTimeUTC": 1355217432137,
                        "DepartTimeUTC": 1355217672137,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1960580386392925,
                        "SnapY": 36.714674666106745,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 2.9092681449436375,
                        "ORIG_FID": 178
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 179,
                        "Name": "C013-00161_1",
                        "PickupQuantities": "0.33",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E758_126518-00-16",
                        "Sequence": 18,
                        "FromPrevTravelTime": 0.298180865123868,
                        "FromPrevDistance": 0.1590306220304723,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355221290028,
                        "DepartTime": 1355221530028,
                        "ArriveTimeUTC": 1355217690028,
                        "DepartTimeUTC": 1355217930028,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1946104878049066,
                        "SnapY": 36.71469439024395,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 10.146780895671057,
                        "ORIG_FID": 179
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 208,
                        "Name": "C013-00166_1",
                        "PickupQuantities": "0.495",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E758_126518-00-16",
                        "Sequence": 13,
                        "FromPrevTravelTime": 0.11230541951954365,
                        "FromPrevDistance": 0.059896325986219606,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355220034544,
                        "DepartTime": 1355220274544,
                        "ArriveTimeUTC": 1355216434544,
                        "DepartTimeUTC": 1355216674544,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1995020956341627,
                        "SnapY": 36.715832964657,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0.8008351569747594,
                        "ORIG_FID": 208
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 230,
                        "Name": "CET CORSO",
                        "PickupQuantities": "0",
                        "DeliveryQuantities": "7.025",
                        "StopType": 1,
                        "RouteName": "BT18_17E758_126518-00-16",
                        "Sequence": 24,
                        "FromPrevTravelTime": 27.880266945809126,
                        "FromPrevDistance": 26.816097172758113,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355224504365,
                        "DepartTime": 1355224504365,
                        "ArriveTimeUTC": 1355220904365,
                        "DepartTimeUTC": 1355220904365,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 0,
                        "SnapY": 0,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0,
                        "ORIG_FID": 2
                      }
                    }
                  ]
                }
              ]
            },
            {
              "vehicle": "BT10_17M316_483933-00-16",
              "rotations": [
                {
                  "demarrage_parc": 1355209200000,
                  "fin_rotation": 1355218948624,
                  "debut_secteur": 1355212908640,
                  "fin_secteur": 1355214971097,
                  "temps_total": 92.76915926113725,
                  "distance_totale": 59.49697492002186,
                  "distance_collecte": 4.452769094501963,
                  "temps_collecte": 36.18495826795697,
                  "distance_transport": 55.0442058255199,
                  "temps_transport": 56.584200993180275,
                  "arrive_cet": 1355216668624,
                  "depart_cet": 1355217868624,
                  "nombre_points": 7,
                  "quantite_dechets": "3.96",
                  "ordres": [
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 67,
                        "Name": "C013-00004_1",
                        "PickupQuantities": "0.99",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT10_17M316_483933-00-16",
                        "Sequence": 5,
                        "FromPrevTravelTime": 0.0338929146528244,
                        "FromPrevDistance": 0.031212616586817565,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355213811491,
                        "DepartTime": 1355214051491,
                        "ArriveTimeUTC": 1355210211491,
                        "DepartTimeUTC": 1355210451491,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.181780764331258,
                        "SnapY": 36.72816140127392,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 2.7823026358773277,
                        "ORIG_FID": 67
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 95,
                        "Name": "C013-00003_1",
                        "PickupQuantities": "0.825",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT10_17M316_483933-00-16",
                        "Sequence": 4,
                        "FromPrevTravelTime": 1.1026773564517498,
                        "FromPrevDistance": 0.6593378148803235,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355213569458,
                        "DepartTime": 1355213809458,
                        "ArriveTimeUTC": 1355209969458,
                        "DepartTimeUTC": 1355210209458,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1820991100917917,
                        "SnapY": 36.72813936697255,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 3.270316664681438,
                        "ORIG_FID": 95
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 102,
                        "Name": "C013-00005_1",
                        "PickupQuantities": "0.33",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT10_17M316_483933-00-16",
                        "Sequence": 6,
                        "FromPrevTravelTime": 2.041932860389352,
                        "FromPrevDistance": 1.0890406803877937,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355214174007,
                        "DepartTime": 1355214414007,
                        "ArriveTimeUTC": 1355210574007,
                        "DepartTimeUTC": 1355210814007,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1815562633997474,
                        "SnapY": 36.73657675344566,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 2.460092050245482,
                        "ORIG_FID": 102
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 157,
                        "Name": "C013-00138_1",
                        "PickupQuantities": "0.495",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT10_17M316_483933-00-16",
                        "Sequence": 2,
                        "FromPrevTravelTime": 1.8106606006622314,
                        "FromPrevDistance": 1.0164105940233794,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355212908640,
                        "DepartTime": 1355213148640,
                        "ArriveTimeUTC": 1355209308640,
                        "DepartTimeUTC": 1355209548640,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1850335684647573,
                        "SnapY": 36.7240483817428,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 5.199568626879824,
                        "ORIG_FID": 157
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 163,
                        "Name": "C013-00042_1",
                        "PickupQuantities": "0.33",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT10_17M316_483933-00-16",
                        "Sequence": 3,
                        "FromPrevTravelTime": 1.9109549522399902,
                        "FromPrevDistance": 0.9715160389494779,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355213263297,
                        "DepartTime": 1355213503297,
                        "ArriveTimeUTC": 1355209663297,
                        "DepartTimeUTC": 1355209903297,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1843750000000455,
                        "SnapY": 36.73223500000006,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0.7112265652390182,
                        "ORIG_FID": 163
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 167,
                        "Name": "C013-00027_1",
                        "PickupQuantities": "0.495",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT10_17M316_483933-00-16",
                        "Sequence": 7,
                        "FromPrevTravelTime": 0.9719368368387222,
                        "FromPrevDistance": 0.5183695069070373,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355214472323,
                        "DepartTime": 1355214712323,
                        "ArriveTimeUTC": 1355210872323,
                        "DepartTimeUTC": 1355211112323,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1866643962485783,
                        "SnapY": 36.735733950762054,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 5.776468398526433,
                        "ORIG_FID": 167
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 169,
                        "Name": "C013-00028_1",
                        "PickupQuantities": "0.495",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT10_17M316_483933-00-16",
                        "Sequence": 8,
                        "FromPrevTravelTime": 0.31290274672210217,
                        "FromPrevDistance": 0.16688184276713305,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355214731097,
                        "DepartTime": 1355214971097,
                        "ArriveTimeUTC": 1355211131097,
                        "DepartTimeUTC": 1355211371097,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.188011348314685,
                        "SnapY": 36.73468415730342,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 4.252516357274375,
                        "ORIG_FID": 169
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 231,
                        "Name": "Parc Babez",
                        "PickupQuantities": "0",
                        "DeliveryQuantities": "0",
                        "StopType": 1,
                        "RouteName": "BT10_17M316_483933-00-16",
                        "Sequence": 1,
                        "FromPrevTravelTime": 0,
                        "FromPrevDistance": 0,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355209200000,
                        "DepartTime": 1355212800000,
                        "ArriveTimeUTC": 1355205600000,
                        "DepartTimeUTC": 1355209200000,
                        "WaitTime": 60,
                        "ViolationTime": 0,
                        "SnapX": 0,
                        "SnapY": 0,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0,
                        "ORIG_FID": 1
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 232,
                        "Name": "CET CORSO",
                        "PickupQuantities": "0",
                        "DeliveryQuantities": "3.96",
                        "StopType": 1,
                        "RouteName": "BT10_17M316_483933-00-16",
                        "Sequence": 9,
                        "FromPrevTravelTime": 28.292100496590137,
                        "FromPrevDistance": 27.52210291275995,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355216668624,
                        "DepartTime": 1355217868624,
                        "ArriveTimeUTC": 1355213068624,
                        "DepartTimeUTC": 1355214268624,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 0,
                        "SnapY": 0,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0,
                        "ORIG_FID": 2
                      }
                    }
                  ]
                },
                {
                  "demarrage_parc": 1355219310511,
                  "fin_rotation": 1355225476812,
                  "debut_secteur": 1355219610511,
                  "fin_secteur": 1355221389382,
                  "temps_total": 83.8955088108778,
                  "distance_totale": 58.95816717301981,
                  "distance_collecte": 3.026785215933934,
                  "temps_collecte": 25.647859532386065,
                  "distance_transport": 55.931381957085875,
                  "temps_transport": 58.247649278491735,
                  "arrive_cet": 1355223136812,
                  "depart_cet": 1355224336812,
                  "nombre_points": 6,
                  "quantite_dechets": "3.96",
                  "ordres": [
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 16,
                        "Name": "C013-00037_1",
                        "PickupQuantities": "0.66",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT10_17M316_483933-00-16",
                        "Sequence": 14,
                        "FromPrevTravelTime": 0.23712334223091602,
                        "FromPrevDistance": 0.12646630258257535,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355220900347,
                        "DepartTime": 1355221140347,
                        "ArriveTimeUTC": 1355217300347,
                        "DepartTimeUTC": 1355217540347,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1848352050473574,
                        "SnapY": 36.735693375394376,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0.8710710881739431,
                        "ORIG_FID": 16
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 62,
                        "Name": "C013-00226_1",
                        "PickupQuantities": "0.66",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT10_17M316_483933-00-16",
                        "Sequence": 10,
                        "FromPrevTravelTime": 29.031453860923648,
                        "FromPrevDistance": 27.13397318711769,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355219610511,
                        "DepartTime": 1355219850511,
                        "ArriveTimeUTC": 1355216010511,
                        "DepartTimeUTC": 1355216250511,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1905723500084995,
                        "SnapY": 36.72854079854663,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 12.783013266966448,
                        "ORIG_FID": 62
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 164,
                        "Name": "C013-00041_1",
                        "PickupQuantities": "0.495",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT10_17M316_483933-00-16",
                        "Sequence": 11,
                        "FromPrevTravelTime": 4.58889539167285,
                        "FromPrevDistance": 2.4620008542409626,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355220125844,
                        "DepartTime": 1355220365844,
                        "ArriveTimeUTC": 1355216525844,
                        "DepartTimeUTC": 1355216765844,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1849000000000274,
                        "SnapY": 36.73275000000007,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 2.6763970954159313,
                        "ORIG_FID": 164
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 165,
                        "Name": "C013-00026_1",
                        "PickupQuantities": "0.66",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT10_17M316_483933-00-16",
                        "Sequence": 15,
                        "FromPrevTravelTime": 0.15059414319694042,
                        "FromPrevDistance": 0.08031699308392172,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355221149382,
                        "DepartTime": 1355221389382,
                        "ArriveTimeUTC": 1355217549382,
                        "DepartTimeUTC": 1355217789382,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.184890000000024,
                        "SnapY": 36.736350000000044,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 11.41251935656411,
                        "ORIG_FID": 165
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 166,
                        "Name": "C013-00039_1",
                        "PickupQuantities": "0.33",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT10_17M316_483933-00-16",
                        "Sequence": 12,
                        "FromPrevTravelTime": 0.21295892633497715,
                        "FromPrevDistance": 0.11357925653455979,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355220378622,
                        "DepartTime": 1355220618622,
                        "ArriveTimeUTC": 1355216778622,
                        "DepartTimeUTC": 1355217018622,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.185793656597829,
                        "SnapY": 36.732934292527894,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0.585876063043692,
                        "ORIG_FID": 166
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 203,
                        "Name": "C013-00036_1",
                        "PickupQuantities": "1.155",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT10_17M316_483933-00-16",
                        "Sequence": 13,
                        "FromPrevTravelTime": 0.4582877289503813,
                        "FromPrevDistance": 0.24442180949191472,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355220646119,
                        "DepartTime": 1355220886119,
                        "ArriveTimeUTC": 1355217046119,
                        "DepartTimeUTC": 1355217286119,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.184595038387769,
                        "SnapY": 36.73458402111329,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 5.222968753918995,
                        "ORIG_FID": 203
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 233,
                        "Name": "CET CORSO",
                        "PickupQuantities": "0",
                        "DeliveryQuantities": "3.96",
                        "StopType": 1,
                        "RouteName": "BT10_17M316_483933-00-16",
                        "Sequence": 16,
                        "FromPrevTravelTime": 29.123824639245868,
                        "FromPrevDistance": 27.965690978542938,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355223136812,
                        "DepartTime": 1355224336812,
                        "ArriveTimeUTC": 1355219536812,
                        "DepartTimeUTC": 1355220736812,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 0,
                        "SnapY": 0,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0,
                        "ORIG_FID": 2
                      }
                    }
                  ]
                },
                {
                  "demarrage_parc": 1355225710565,
                  "fin_rotation": 1355230196659,
                  "debut_secteur": 1355226010565,
                  "fin_secteur": 1355227298658,
                  "temps_total": 76.068254660815,
                  "distance_totale": 56.902376943011504,
                  "distance_collecte": 0.7830601914620884,
                  "temps_collecte": 17.468230225145817,
                  "distance_transport": 56.11931675154941,
                  "temps_transport": 58.600024435669184,
                  "arrive_cet": 1355229056659,
                  "depart_cet": 1355229056659,
                  "nombre_points": 5,
                  "quantite_dechets": "3.21",
                  "ordres": [
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 19,
                        "Name": "C013-00017_1",
                        "PickupQuantities": "0.285",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT10_17M316_483933-00-16",
                        "Sequence": 18,
                        "FromPrevTravelTime": 0.37908028811216354,
                        "FromPrevDistance": 0.20217719028243678,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355226273309,
                        "DepartTime": 1355226513309,
                        "ArriveTimeUTC": 1355222673309,
                        "DepartTimeUTC": 1355222913309,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1862140648379698,
                        "SnapY": 36.73671129675817,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 9.056426784811164,
                        "ORIG_FID": 19
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 141,
                        "Name": "C013-00014_1",
                        "PickupQuantities": "0.75",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT10_17M316_483933-00-16",
                        "Sequence": 20,
                        "FromPrevTravelTime": 0.6866219397634268,
                        "FromPrevDistance": 0.3661998085772714,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355226796991,
                        "DepartTime": 1355227036991,
                        "ArriveTimeUTC": 1355223196991,
                        "DepartTimeUTC": 1355223436991,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1860530960854367,
                        "SnapY": 36.73845590747335,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 1.2572828181013167,
                        "ORIG_FID": 141
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 144,
                        "Name": "C013-00031_1",
                        "PickupQuantities": "0.9",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT10_17M316_483933-00-16",
                        "Sequence": 17,
                        "FromPrevTravelTime": 27.89588012546301,
                        "FromPrevDistance": 27.624807825964908,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355226010565,
                        "DepartTime": 1355226250565,
                        "ArriveTimeUTC": 1355222410565,
                        "DepartTimeUTC": 1355222650565,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1874428843265714,
                        "SnapY": 36.73604165748628,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 1.1245479392868187,
                        "ORIG_FID": 144
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 148,
                        "Name": "C013-00018_1",
                        "PickupQuantities": "0.495",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT10_17M316_483933-00-16",
                        "Sequence": 19,
                        "FromPrevTravelTime": 0.041398536413908005,
                        "FromPrevDistance": 0.02207932091680393,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355226515793,
                        "DepartTime": 1355226755793,
                        "ArriveTimeUTC": 1355222915793,
                        "DepartTimeUTC": 1355223155793,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.186040000000048,
                        "SnapY": 36.736770000000035,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0.8915696830485151,
                        "ORIG_FID": 148
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 190,
                        "Name": "C013-00015_1",
                        "PickupQuantities": "0.78",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT10_17M316_483933-00-16",
                        "Sequence": 21,
                        "FromPrevTravelTime": 0.3611294608563185,
                        "FromPrevDistance": 0.19260387168557624,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355227058658,
                        "DepartTime": 1355227298658,
                        "ArriveTimeUTC": 1355223458658,
                        "DepartTimeUTC": 1355223698658,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1871907692308197,
                        "SnapY": 36.7384238461539,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 2.0339800796739116,
                        "ORIG_FID": 190
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 234,
                        "Name": "CET CORSO",
                        "PickupQuantities": "0",
                        "DeliveryQuantities": "3.21",
                        "StopType": 1,
                        "RouteName": "BT10_17M316_483933-00-16",
                        "Sequence": 22,
                        "FromPrevTravelTime": 29.300012217834592,
                        "FromPrevDistance": 28.059658375774706,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355229056659,
                        "DepartTime": 1355229056659,
                        "ArriveTimeUTC": 1355225456659,
                        "DepartTimeUTC": 1355225456659,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 0,
                        "SnapY": 0,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0,
                        "ORIG_FID": 2
                      }
                    }
                  ]
                }
              ]
            },
            {
              "vehicle": "BT18_17E766_349363-00-16",
              "rotations": [
                {
                  "demarrage_parc": 1355209200000,
                  "fin_rotation": 1355219959793,
                  "debut_secteur": 1355212812571,
                  "fin_secteur": 1355215979951,
                  "temps_total": 109.66057424992323,
                  "distance_totale": 59.36170448055264,
                  "distance_collecte": 2.6662503913382287,
                  "temps_collecte": 52.99917917326093,
                  "distance_transport": 56.69545408921441,
                  "temps_transport": 56.6613950766623,
                  "arrive_cet": 1355217679793,
                  "depart_cet": 1355218879793,
                  "nombre_points": 12,
                  "quantite_dechets": "7.05",
                  "ordres": [
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 29,
                        "Name": "C013-00054_1",
                        "PickupQuantities": "0.33",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E766_349363-00-16",
                        "Sequence": 4,
                        "FromPrevTravelTime": 0.444722730666399,
                        "FromPrevDistance": 0.2371865663390691,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355213378470,
                        "DepartTime": 1355213618470,
                        "ArriveTimeUTC": 1355209778470,
                        "DepartTimeUTC": 1355210018470,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1783826478786614,
                        "SnapY": 36.717738429854506,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 5.6643546200675665,
                        "ORIG_FID": 29
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 33,
                        "Name": "C013-02491_1",
                        "PickupQuantities": "0.825",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E766_349363-00-16",
                        "Sequence": 11,
                        "FromPrevTravelTime": 0.3122024890035391,
                        "FromPrevDistance": 0.16650900954966355,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355215216676,
                        "DepartTime": 1355215456676,
                        "ArriveTimeUTC": 1355211616676,
                        "DepartTimeUTC": 1355211856676,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1725364280156105,
                        "SnapY": 36.722851151751,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 4.014314380949411,
                        "ORIG_FID": 33
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 39,
                        "Name": "C013-00058_1",
                        "PickupQuantities": "0.99",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E766_349363-00-16",
                        "Sequence": 5,
                        "FromPrevTravelTime": 0.5444469079375267,
                        "FromPrevDistance": 0.2903740354935069,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355213651136,
                        "DepartTime": 1355213891136,
                        "ArriveTimeUTC": 1355210051136,
                        "DepartTimeUTC": 1355210291136,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1773460000000475,
                        "SnapY": 36.72005200000007,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 3.137814063794065,
                        "ORIG_FID": 39
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 41,
                        "Name": "C013-00060_1",
                        "PickupQuantities": "0.285",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E766_349363-00-16",
                        "Sequence": 2,
                        "FromPrevTravelTime": 0.2095092535018921,
                        "FromPrevDistance": 0.11173877179092767,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355212812571,
                        "DepartTime": 1355213052571,
                        "ArriveTimeUTC": 1355209212571,
                        "DepartTimeUTC": 1355209452571,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1790612794158197,
                        "SnapY": 36.71968298079528,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 3.787382823829831,
                        "ORIG_FID": 41
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 48,
                        "Name": "C013-00062_1",
                        "PickupQuantities": "0.66",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E766_349363-00-16",
                        "Sequence": 7,
                        "FromPrevTravelTime": 0.22185922414064407,
                        "FromPrevDistance": 0.11832657834106307,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355214172451,
                        "DepartTime": 1355214412451,
                        "ArriveTimeUTC": 1355210572451,
                        "DepartTimeUTC": 1355210812451,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.176444379773542,
                        "SnapY": 36.72133979194105,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 6.81124439856956,
                        "ORIG_FID": 48
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 58,
                        "Name": "C013-00064_1",
                        "PickupQuantities": "0.24",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E766_349363-00-16",
                        "Sequence": 8,
                        "FromPrevTravelTime": 0.29427927546203136,
                        "FromPrevDistance": 0.15694985141018694,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355214430108,
                        "DepartTime": 1355214670108,
                        "ArriveTimeUTC": 1355210830108,
                        "DepartTimeUTC": 1355211070108,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.175134771573642,
                        "SnapY": 36.72203319796961,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 8.565883881658987,
                        "ORIG_FID": 58
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 59,
                        "Name": "C013-00065_1",
                        "PickupQuantities": "0.495",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E766_349363-00-16",
                        "Sequence": 9,
                        "FromPrevTravelTime": 0.33801846764981747,
                        "FromPrevDistance": 0.18027816703421543,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355214690389,
                        "DepartTime": 1355214930389,
                        "ArriveTimeUTC": 1355211090389,
                        "DepartTimeUTC": 1355211330389,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1746131911653155,
                        "SnapY": 36.72151575780657,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 2.8717679545380466,
                        "ORIG_FID": 59
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 68,
                        "Name": "C013-00075_1",
                        "PickupQuantities": "0.945",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E766_349363-00-16",
                        "Sequence": 12,
                        "FromPrevTravelTime": 0.4471065327525139,
                        "FromPrevDistance": 0.23846132207301227,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355215483502,
                        "DepartTime": 1355215723502,
                        "ArriveTimeUTC": 1355211883502,
                        "DepartTimeUTC": 1355212123502,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1712522304833146,
                        "SnapY": 36.724602899628316,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 1.9150361345121627,
                        "ORIG_FID": 68
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 69,
                        "Name": "C013-00076_1",
                        "PickupQuantities": "0.825",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E766_349363-00-16",
                        "Sequence": 13,
                        "FromPrevTravelTime": 0.2741454914212227,
                        "FromPrevDistance": 0.14621192806687516,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355215739951,
                        "DepartTime": 1355215979951,
                        "ArriveTimeUTC": 1355212139951,
                        "DepartTimeUTC": 1355212379951,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1696338461539337,
                        "SnapY": 36.72447076923082,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 1.77042972892927,
                        "ORIG_FID": 69
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 71,
                        "Name": "C013-00177_1",
                        "PickupQuantities": "0.3",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E766_349363-00-16",
                        "Sequence": 10,
                        "FromPrevTravelTime": 0.4592416789382696,
                        "FromPrevDistance": 0.24493118142215883,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355214957943,
                        "DepartTime": 1355215197943,
                        "ArriveTimeUTC": 1355211357943,
                        "DepartTimeUTC": 1355211597943,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1732648321377988,
                        "SnapY": 36.72296784218081,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 4.725395783490818,
                        "ORIG_FID": 71
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 76,
                        "Name": "C013-00055_1",
                        "PickupQuantities": "0.495",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E766_349363-00-16",
                        "Sequence": 3,
                        "FromPrevTravelTime": 0.9869273658841848,
                        "FromPrevDistance": 0.526363347483159,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355213111786,
                        "DepartTime": 1355213351786,
                        "ArriveTimeUTC": 1355209511786,
                        "DepartTimeUTC": 1355209751786,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1764995172414405,
                        "SnapY": 36.7169417931035,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 13.913785586378339,
                        "ORIG_FID": 76
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 210,
                        "Name": "C013-00063_1",
                        "PickupQuantities": "0.66",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E766_349363-00-16",
                        "Sequence": 6,
                        "FromPrevTravelTime": 0.4667197559028864,
                        "FromPrevDistance": 0.24891963233439043,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355213919140,
                        "DepartTime": 1355214159140,
                        "ArriveTimeUTC": 1355210319140,
                        "DepartTimeUTC": 1355210559140,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1772829088472316,
                        "SnapY": 36.72172247989279,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 1.7395477710716916,
                        "ORIG_FID": 210
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 235,
                        "Name": "Parc Babez",
                        "PickupQuantities": "0",
                        "DeliveryQuantities": "0",
                        "StopType": 1,
                        "RouteName": "BT18_17E766_349363-00-16",
                        "Sequence": 1,
                        "FromPrevTravelTime": 0,
                        "FromPrevDistance": 0,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355209200000,
                        "DepartTime": 1355212800000,
                        "ArriveTimeUTC": 1355205600000,
                        "DepartTimeUTC": 1355209200000,
                        "WaitTime": 60,
                        "ViolationTime": 0,
                        "SnapX": 0,
                        "SnapY": 0,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0,
                        "ORIG_FID": 1
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 236,
                        "Name": "CET CORSO",
                        "PickupQuantities": "0",
                        "DeliveryQuantities": "7.05",
                        "StopType": 1,
                        "RouteName": "BT18_17E766_349363-00-16",
                        "Sequence": 14,
                        "FromPrevTravelTime": 28.33069753833115,
                        "FromPrevDistance": 28.347727044607204,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355217679793,
                        "DepartTime": 1355218879793,
                        "ArriveTimeUTC": 1355214079793,
                        "DepartTimeUTC": 1355215279793,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 0,
                        "SnapY": 0,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0,
                        "ORIG_FID": 2
                      }
                    }
                  ]
                },
                {
                  "demarrage_parc": 1355220347581,
                  "fin_rotation": 1355228293921,
                  "debut_secteur": 1355220647581,
                  "fin_secteur": 1355225532723,
                  "temps_total": 133.45897328667343,
                  "distance_totale": 61.45142162856328,
                  "distance_collecte": 5.023512385953771,
                  "temps_collecte": 77.41903642751276,
                  "distance_transport": 56.42790924260951,
                  "temps_transport": 56.03993685916066,
                  "arrive_cet": 1355227213921,
                  "depart_cet": 1355227213921,
                  "nombre_points": 18,
                  "quantite_dechets": "6.885",
                  "ordres": [
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 21,
                        "Name": "C013-00178_1",
                        "PickupQuantities": "0.36",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E766_349363-00-16",
                        "Sequence": 26,
                        "FromPrevTravelTime": 0.4469679147005081,
                        "FromPrevDistance": 0.23838439210218873,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355223663683,
                        "DepartTime": 1355223903683,
                        "ArriveTimeUTC": 1355220063683,
                        "DepartTimeUTC": 1355220303683,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.17706000000004,
                        "SnapY": 36.71939000000003,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 13.745927075031862,
                        "ORIG_FID": 21
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 37,
                        "Name": "C013-00056_1",
                        "PickupQuantities": "0.825",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E766_349363-00-16",
                        "Sequence": 27,
                        "FromPrevTravelTime": 0.40545195899903774,
                        "FromPrevDistance": 0.21624150677359272,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355223928011,
                        "DepartTime": 1355224168011,
                        "ArriveTimeUTC": 1355220328011,
                        "DepartTimeUTC": 1355220568011,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1752954840345327,
                        "SnapY": 36.71865963507355,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 7.321908846606562,
                        "ORIG_FID": 37
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 38,
                        "Name": "C013-00057_1",
                        "PickupQuantities": "0.66",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E766_349363-00-16",
                        "Sequence": 28,
                        "FromPrevTravelTime": 0.22259549610316753,
                        "FromPrevDistance": 0.11871853310552274,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355224181366,
                        "DepartTime": 1355224421366,
                        "ArriveTimeUTC": 1355220581366,
                        "DepartTimeUTC": 1355220821366,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.174735643564409,
                        "SnapY": 36.7196235643565,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 5.7866238041867115,
                        "ORIG_FID": 38
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 40,
                        "Name": "C013-00059_1",
                        "PickupQuantities": "0.33",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E766_349363-00-16",
                        "Sequence": 25,
                        "FromPrevTravelTime": 0.6051070056855679,
                        "FromPrevDistance": 0.322724398368017,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355223396865,
                        "DepartTime": 1355223636865,
                        "ArriveTimeUTC": 1355219796865,
                        "DepartTimeUTC": 1355220036865,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.178763496644324,
                        "SnapY": 36.72009485234907,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 4.073507632482104,
                        "ORIG_FID": 40
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 43,
                        "Name": "C013-00069_1",
                        "PickupQuantities": "0.06",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E766_349363-00-16",
                        "Sequence": 24,
                        "FromPrevTravelTime": 0.07385022193193436,
                        "FromPrevDistance": 0.03938687091328549,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355223120559,
                        "DepartTime": 1355223360559,
                        "ArriveTimeUTC": 1355219520559,
                        "DepartTimeUTC": 1355219760559,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1780367807737977,
                        "SnapY": 36.722317997655374,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 3.844196738355824,
                        "ORIG_FID": 43
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 44,
                        "Name": "C013-00068_1",
                        "PickupQuantities": "0.18",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E766_349363-00-16",
                        "Sequence": 18,
                        "FromPrevTravelTime": 0.23887254111468792,
                        "FromPrevDistance": 0.12739915270847194,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355221648523,
                        "DepartTime": 1355221888523,
                        "ArriveTimeUTC": 1355218048523,
                        "DepartTimeUTC": 1355218288523,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.176568957163992,
                        "SnapY": 36.72313688626297,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 5.231608906647254,
                        "ORIG_FID": 44
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 45,
                        "Name": "C013-00067_1",
                        "PickupQuantities": "0.3",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E766_349363-00-16",
                        "Sequence": 16,
                        "FromPrevTravelTime": 4.350861122831702,
                        "FromPrevDistance": 2.320470916274459,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355221148632,
                        "DepartTime": 1355221388632,
                        "ArriveTimeUTC": 1355217548632,
                        "DepartTimeUTC": 1355217788632,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.17596735294123,
                        "SnapY": 36.722835588235355,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0.8175791661703511,
                        "ORIG_FID": 45
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 57,
                        "Name": "C013-00066_1",
                        "PickupQuantities": "0.24",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E766_349363-00-16",
                        "Sequence": 17,
                        "FromPrevTravelTime": 0.09264072775840759,
                        "FromPrevDistance": 0.04940854505594362,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355221394191,
                        "DepartTime": 1355221634191,
                        "ArriveTimeUTC": 1355217794191,
                        "DepartTimeUTC": 1355218034191,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.176072559618496,
                        "SnapY": 36.722378887122495,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 2.2410489981118817,
                        "ORIG_FID": 57
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 70,
                        "Name": "C013-00078_1",
                        "PickupQuantities": "0.33",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E766_349363-00-16",
                        "Sequence": 15,
                        "FromPrevTravelTime": 29.463134549558163,
                        "FromPrevDistance": 29.723824449166685,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355220647581,
                        "DepartTime": 1355220887581,
                        "ArriveTimeUTC": 1355217047581,
                        "DepartTimeUTC": 1355217287581,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1690542883239954,
                        "SnapY": 36.72551207185271,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 6.763333526718065,
                        "ORIG_FID": 70
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 88,
                        "Name": "C013-00096_1",
                        "PickupQuantities": "0.99",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E766_349363-00-16",
                        "Sequence": 29,
                        "FromPrevTravelTime": 1.240367079153657,
                        "FromPrevDistance": 0.6615355204414944,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355224495788,
                        "DepartTime": 1355224735788,
                        "ArriveTimeUTC": 1355220895788,
                        "DepartTimeUTC": 1355221135788,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1793594716981444,
                        "SnapY": 36.72226784905665,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 1.3574662474674313,
                        "ORIG_FID": 88
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 89,
                        "Name": "C013-00098_1",
                        "PickupQuantities": "0.825",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E766_349363-00-16",
                        "Sequence": 30,
                        "FromPrevTravelTime": 0.5651512257754803,
                        "FromPrevDistance": 0.3014160175178455,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355224769697,
                        "DepartTime": 1355225009697,
                        "ArriveTimeUTC": 1355221169697,
                        "DepartTimeUTC": 1355221409697,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.178554197080369,
                        "SnapY": 36.72380470802926,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 5.5953625969805385,
                        "ORIG_FID": 89
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 91,
                        "Name": "C013-00099_1",
                        "PickupQuantities": "0.495",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E766_349363-00-16",
                        "Sequence": 32,
                        "FromPrevTravelTime": 0.19215081445872784,
                        "FromPrevDistance": 0.10248080175634314,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355225292723,
                        "DepartTime": 1355225532723,
                        "ArriveTimeUTC": 1355221692723,
                        "DepartTimeUTC": 1355221932723,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1794000205264865,
                        "SnapY": 36.724961867906366,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0.23280354081937482,
                        "ORIG_FID": 91
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 100,
                        "Name": "C013-00100_1",
                        "PickupQuantities": "0.99",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E766_349363-00-16",
                        "Sequence": 31,
                        "FromPrevTravelTime": 0.5249402616173029,
                        "FromPrevDistance": 0.27996879659808804,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355225041194,
                        "DepartTime": 1355225281194,
                        "ArriveTimeUTC": 1355221441194,
                        "DepartTimeUTC": 1355221681194,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1785435384615615,
                        "SnapY": 36.724683692307735,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0.7112265652390182,
                        "ORIG_FID": 100
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 150,
                        "Name": "C013-00072_1",
                        "PickupQuantities": "0.06",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E766_349363-00-16",
                        "Sequence": 21,
                        "FromPrevTravelTime": 0.06120060756802559,
                        "FromPrevDistance": 0.03264039471889205,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355222388969,
                        "DepartTime": 1355222628969,
                        "ArriveTimeUTC": 1355218788969,
                        "DepartTimeUTC": 1355219028969,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1774824400000505,
                        "SnapY": 36.723130920000045,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 3.248144770631129,
                        "ORIG_FID": 150
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 151,
                        "Name": "C013-00073_1",
                        "PickupQuantities": "0.06",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E766_349363-00-16",
                        "Sequence": 20,
                        "FromPrevTravelTime": 0.06355016864836216,
                        "FromPrevDistance": 0.03389349630939386,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355222145297,
                        "DepartTime": 1355222385297,
                        "ArriveTimeUTC": 1355218545297,
                        "DepartTimeUTC": 1355218785297,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1773093170732056,
                        "SnapY": 36.723390146341494,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 3.517825071610638,
                        "ORIG_FID": 151
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 152,
                        "Name": "C013-00074_1",
                        "PickupQuantities": "0.06",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E766_349363-00-16",
                        "Sequence": 19,
                        "FromPrevTravelTime": 0.21601548790931702,
                        "FromPrevDistance": 0.11520888313485234,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355221901484,
                        "DepartTime": 1355222141484,
                        "ArriveTimeUTC": 1355218301484,
                        "DepartTimeUTC": 1355218541484,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.1771283808844935,
                        "SnapY": 36.723658580599185,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 3.693196623821522,
                        "ORIG_FID": 152
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 171,
                        "Name": "C013-00070_1",
                        "PickupQuantities": "0.06",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E766_349363-00-16",
                        "Sequence": 23,
                        "FromPrevTravelTime": 0.06865819729864597,
                        "FromPrevDistance": 0.03661778415807835,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355222876128,
                        "DepartTime": 1355223116128,
                        "ArriveTimeUTC": 1355219276128,
                        "DepartTimeUTC": 1355219516128,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.177828400000028,
                        "SnapY": 36.722631200000066,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 4.90363325676695,
                        "ORIG_FID": 171
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 172,
                        "Name": "C013-00071_1",
                        "PickupQuantities": "0.06",
                        "DeliveryQuantities": "",
                        "StopType": 0,
                        "RouteName": "BT18_17E766_349363-00-16",
                        "Sequence": 22,
                        "FromPrevTravelTime": 0.05065559595823288,
                        "FromPrevDistance": 0.027016376017302023,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355222632008,
                        "DepartTime": 1355222872008,
                        "ArriveTimeUTC": 1355219032008,
                        "DepartTimeUTC": 1355219272008,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 3.177629320000047,
                        "SnapY": 36.72291876000004,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 3.6165813396752085,
                        "ORIG_FID": 172
                      }
                    },
                    {
                      "type": "Feature",
                      "properties": {
                        "ObjectID": 237,
                        "Name": "CET CORSO",
                        "PickupQuantities": "0",
                        "DeliveryQuantities": "6.885",
                        "StopType": 1,
                        "RouteName": "BT18_17E766_349363-00-16",
                        "Sequence": 33,
                        "FromPrevTravelTime": 28.01996842958033,
                        "FromPrevDistance": 28.213954621304755,
                        "ArriveCurbApproach": 0,
                        "DepartCurbApproach": 0,
                        "ArriveTime": 1355227213921,
                        "DepartTime": 1355227213921,
                        "ArriveTimeUTC": 1355223613921,
                        "DepartTimeUTC": 1355223613921,
                        "WaitTime": 0,
                        "ViolationTime": 0,
                        "SnapX": 0,
                        "SnapY": 0,
                        "SnapZ": 0,
                        "DistanceToNetworkInMeters": 0,
                        "ORIG_FID": 2
                      }
                    }
                  ]
                }
              ]
            }
          ]
		}',true);
		$data = json_decode($request->getContent(), true);
	
		$plan = $data["plan"];
        $secteurs = ['C013-A11', 'C013-B11', 'C013-C11', 'C013-A12', 'C013-B12', 'C013-C12', 'C013-A21', 'C013-B21', 'C013-C21', 'C013-A22', 'C013-B22','C013-D11','C013-D12','C013-D21','C013-D22', 'C013-E11','C013-E12','C013-E21','C013-E22'];
        
        $i = 0;
		$unsavedRotations = [];
		$initialiser = $this->app->Points->updateAll(["circuitsecondaire"=>null]);
        foreach($plan as $value){
            $nom = explode("_",$value["vehicle"]);
            $codeVehicle = $nom[1];
            foreach($value["rotations"] as $rotation){
                if($this->checkRotation($rotation)){
                    if($i < count($secteurs)){
						$date = date("Y-m-d H:i:s",$rotation["demarrage_parc"]/1000);
						$date_array = explode(" ",$date);
						$demarrage = $date_array[1];
                        if($this->app->Secteur->exist($secteurs[$i])== false){
                            $this->app->Secteur->add(["code"=>$secteurs[$i], "vehicule"=>$codeVehicle, "horaire"=>$demarrage, "qtedechet"=>$rotation["quantite_dechets"]]);
                        }else{
                            $this->app->Secteur->update($secteurs[$i],["vehicule"=>$codeVehicle, "horaire"=>$demarrage,"qtedechet"=>$rotation["quantite_dechets"]]);
                        }
                        foreach($rotation["ordres"] as $stop){
                            $codePoint = explode("_",$stop["properties"]["Name"])[0];
							$frequence = explode("_",$stop["properties"]["Name"]);
							$frequence = end($frequence);
                            $sequence = intval($stop["properties"]['Sequence']);
                            if($frequence == 1){
                                $savePoint = $this->app->Points->update($codePoint,["ordrecollecte"=>$sequence, "secteur"=>$secteurs[$i], "circuitprincipal"=>$secteurs[$i]]);
                            }else{
                                $savePoint = $this->app->Points->update($codePoint,["circuitsecondaire"=>$secteurs[$i]]);
                            }
                        }
                        
                        $i++;
                    }
                }else{
                    array_push($unsavedRotations, ["vehicle"=>$value["vehicle"], "rotation"=>$rotation]);
                }
            }
            
        }
        return new Response("Données mises à jour avec succes.");
    }

    public function checkRotation($rotation){
        $cpt = 0;
        foreach($rotation["ordres"] as $point){
			$frequence = explode("_", $point["properties"]["Name"]);
			$frequence = end($frequence);
            if($frequence > 1){
                $cpt++;
            }
        }
        if($cpt == count($rotation["ordres"])){
            return false;
        }
        return true;
    }

    /**
     * @Route("admin/maps/editPointTime", methods={"POST","GET"}, name="editPointTime")
     */
    public function editPointTime(Request $request){
        $date = "Tue 11 December 2012";
        $data = json_decode($request->getContent(),true);
        $code = isset($data["code_point"])? $data["code_point"]: null;
        unset($data["code_point"]);
        $dft = [$date, "".$data["debut_fenetre_temps"].""];
        $fft = [$date, "".$data["fin_fenetre_temps"].""];
        $data = ["debut_fenetre_temps1"=> strtotime(implode(" ", $dft))*1000, "fin_fenetre_temps1"=>strtotime(implode(" ", $fft))*1000];
        if ($code != null) {
            $result = $this->app->Points->update($code, $data);
            return new Response("Point de collecte modifié avec succes.");
        }
        return new Response("Point de collecte non modifié.");
    }
}