<?php

namespace App\Controller\User;

use App\Controller\AppController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class VRPController extends AbstractController{

    private $orders = null;
    private $depots = null;
    private $routes = null;
    private $routeRenewals = null;
    

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
                  "TimeWindowStart1" => 1595228400000,
                  "TimeWindowEnd1" => 1595271600000
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
                  "TimeWindowStart1" => 1595228400000,
                  "TimeWindowEnd1" => 1595271600000
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
                  "EarliestStartTime"=>1595228400000,
                  "LatestStartTime"=>1595228400000,
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
            
            $this->depots = $this->getDepots()->getContent();
            $this->routes = $this->getRoutes()->getContent();
            $this->routeRenewals = $this->routeRenewals()->getContent();
        }

        $this->orders = $this->getOrders()->getContent();
        
        $url = 'https://logistics.arcgis.com/arcgis/rest/services/World/VehicleRoutingProblem/GPServer/SolveVehicleRoutingProblem/submitJob?';
        $token = 'WE1qV9qxfkmkcPUXzYCl2fhHyumB0-WJEXu5NY7942peDYaan0Q8PhpUZoJIfXuLjJs7hty2FxeUySSF7OmwWaqIqIo_RUOV14qPS2Gd0JHIZ9zQDec-dTCGKio3EEax5rpaO3QqLeUCaQIzj3Lzmt9BqoNTcF-VWLDFtYdUm_soTyfilQ1gWvM0Xm44tEcecOsxYQC6s8FRllJ43ooBPi_ehOyg99xKI6sj_DgCK-U.';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, 'orders='. str_replace("'","",$this->orders) . '&depots='. str_replace("'","",$this->depots) . '&routes='. str_replace("'","",$this->routes) . '&route_renewals='. str_replace("'","",$this->routeRenewals) . '&restrictions=[Driving a Truck,Width Restriction]&time_impedance=TruckTravelTime&time_units=Minutes&distance_units=Kilometers&uturn_policy=NO_UTURNS&populate_directions=true&directions_language=en&default_date=1355212800000&f=json&token='.$token.'');
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
            $routeData = json_decode($this->transformeRoutes($vehicules)->getContent(), true);
            $this->routes = json_encode($routeData["routes"]);
            $this->routeRenewals = json_encode($routeData["renewals"]);
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
        $features = [];
        
        foreach($depots as $depot){
            
            $parc = $this->app->Parc->findVRP($depot['code']);
            
            if($parc != null){
                $parc = $parc[0];
                $geo = json_decode($parc["geojson"],true);
                $geometry=["x"=>$geo['coordinates'][0],"y"=>$geo['coordinates'][1]];
                $attributes = [
                "Name" => $parc['designation'],
                "TimeWindowStart1" => $this->datetimeToMs($depot["heureDebut"]),
                "TimeWindowEnd1" => $this->datetimeToMs($depot["heureFin"])
                ];
                $feature = ["geometry"=>$geometry, "attributes"=>$attributes];
                array_push($features, $feature);
                
            }else{
                $cet = $this->app->CET->find($depot['code']);
                if ($cet != null) {
                    $cet = $cet[0];
                    $geo = isset($cet["geojson"]) ? json_decode($cet["geojson"],true): null;
                    $geometry=["x"=>$geo['coordinates'][0],"y"=>$geo['coordinates'][1]];
                    $attributes = [
                    "Name" => $cet['designation'],
                    "TimeWindowStart1" => $this->datetimeToMs($depot["heureDebut"]),
                    "TimeWindowEnd1" => $this->datetimeToMs($depot["heureFin"])
                    ];
                    $feature = ["geometry"=>$geometry, "attributes"=>$attributes];
                    array_push($features, $feature);
                }
                
            }         
            
        }
        $featureCollection = ["features"=>$features];
		return new Response(json_encode($featureCollection));
    }

    private function transformeRoutes($vehicles){
        $features = [];
        $renewalFeatures = [];
		foreach($vehicles as $vehicle){
            $code = explode("_", $vehicle["code"])[1];
            $dbVehicle = $this->app->Vehicle->getVehicle($code);
            for ($i=0; $i <count($dbVehicle) ; $i++) { 
                unset($dbVehicle[$i]);
            }
            $names = explode(" ", $dbVehicle["genre"]);
            $name = "". $names[0][0] . "" . $names[1][0] ."";
            $attributes = [
                "Name"=>"".$name."" . $dbVehicle["volume"] ."_". $dbVehicle["code"] ."_" . $dbVehicle["matricule"] ."",
                "StartDepotName"=>$vehicle['depotDepart'],
                "EndDepotName"=>$vehicle['depotFin'],
                "StartDepotServiceTime"=>60,
                "EarliestStartTime"=>$this->datetimeToMs($vehicle["heureDebut"]),
                "LatestStartTime"=>$this->datetimeToMs($vehicle["heureFin"]),
                "Capacities"=> "".$dbVehicle['volume']*0.4 ."",
                "CostPerUnitTime"=> (float)$vehicle['costUnitTime'],
                "CostPerUnitDistance"=>(float)$vehicle['costUnitDistance'],
                "MaxOrderCount"=>(int)$vehicle['nbrMaxOrdres'],
                "MaxTotalTime" =>(int)$vehicle["chargeHoraire"]*60,
                "MaxTotalTravelTime" => 120,
                "MaxTotalDistance" => 80
              ];
            $feature = ["attributes"=>$attributes];
            array_push($features, $feature);
            
            if ($vehicle["renewalDepot"] != "") {
				$cet = $this->app->CET->find($vehicle['renewalDepot'])[0];
                $attributes = [
                    "DepotName" => $cet["designation"],
                    "RouteName" => "".$name."" . $dbVehicle["volume"] ."_". $dbVehicle["code"] ."_" . $dbVehicle["matricule"] ."",
                    "ServiceTime" => 20
                ];
                $feature = ["attributes"=>$attributes];
                array_push($renewalFeatures, $feature);
            }
		}
        $routeCollection = ["features"=>$features];
        $renewalCollection = ["features"=> $renewalFeatures];
		return new Response(json_encode(["routes"=>$routeCollection, "renewals"=>$renewalCollection]));
    }


    /**
     * @Route("admin/maps/VRP/saveResults", methods={"POST","GET"}, name="saveVrpResults")
     */
    public function saveResults(Request $request){
        $data = $request->getContent();
		$plan = json_decode($data,true)["plan"];
        $secteurs = ['C013-A11', 'C013-B11', 'C013-C11', 'C013-A12', 'C013-B12', 'C013-C12', 'C013-A21', 'C013-B21', 'C013-C21', 'C013-A22', 'C013-B22','C013-D11','C013-D12','C013-D21','C013-D22', 'C013-E11','C013-E12','C013-E21','C013-E22'];
        
        $i = 0;
		$unsavedRotations = [];
        $initialiser = $this->app->Points->updateAll(["circuitsecondaire"=>null]);
        $initsecteurs = $this->app->Secteur->initSecteurs();
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
        $this->app->Secteur->updateGeom();
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
        $date = "Mon 20 July 2020";
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
 
    
    private function datetimeToMs($time){
        $date = "Mon 20 July 2020";
        $newTime = [$date, "".$time.""];

        return strtotime(implode(" ", $newTime))*1000+60*60*1000;
    }
}