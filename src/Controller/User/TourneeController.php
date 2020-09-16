<?php

namespace App\Controller\User;

use App\Controller\AppController;
use PhpParser\Node\Stmt\TryCatch;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TourneeController extends AbstractController{


  public function __construct(ParameterBagInterface $parameterBag){
   // parent::__construct();
    $this->app = new AppController();
    $this->app->loadModel('Tournee');
    $this->app->loadModel('Points');
    $this->app->loadModel('Vehicle');
    $this->app->loadModel('Equipe');
    $this->app->loadModel('Secteur');
    $this->app->loadModel('RotationPrevue');
    $this->app->loadModel('Employe');
	
	$this->parameterBag = $parameterBag;
    
  }

    /**
	 * @Route("/dashboard/tournees", name="tournees")
	 */
    public function index(){
        $tournees = $this->app->Tournee->allTable();
      return $this->render('public/tournee/tournee.html.twig', ["tournees" => $tournees]);
    }

    /**
     * @Route("/user/tournee/{id}/maps", methods={"POST","GET"}, name="tourneeMaps")
     */
    public function map($id){
        $tournee = $this->app->Tournee->findWithId($id);
        $tournee = $tournee[0];
        return $this->render('public/tournee/tourneeMaps.html.twig', ["tournee" => $tournee, "id_tournee" => $id]);
    }

    /**
     * @Route("/dashboard/tournee/{id}/details", methods={"POST","GET"}, name="tourneeDetail")
     */
    public function detail($id){
      $bon = $this->app->Tournee->getBonTransport($id);
      $ticket = $this->app->Tournee->getTicketPesee($id);
      for ($i=0; $i <count($bon[0]) ; $i++) { 
        unset($bon[0][$i]);
      }
      for ($i=0; $i <count($bon[0]) ; $i++) { 
        unset($ticket[0][$i]);
      }
      $vehicle = $this->app->Vehicle->getVehicle($bon[0]["vehicule"]);
      $vehicleName = "".$vehicle["code"]." ".$vehicle["matricule"]." ".$vehicle["genre"]; 
      return $this->render('public/tournee/tourneeDetail.html.twig', ["bon" => $bon[0], "vehicle"=>$vehicleName, "ticket"=>$ticket[0], "id_tournee" => $id, "ticket_image" => $ticket[0]["image_name"]]);
  }

  /**
     * @Route("/user/tournee/{id}/details", methods={"POST","GET"}, name="tourneeDetailUser")
     */
    public function userTourneeDetail($id){
      $bon = $this->app->Tournee->getBonTransport($id);
      for ($i=0; $i <count($bon[0]) ; $i++) { 
        unset($bon[0][$i]);
      }
      $employes = $this->app->Employe->getEmployesEquipe($bon[0]["equipe"]);
      foreach($employes as $key => $employe){
        if($employe["fonction"] == "chauffeur"){
          $chauffeur = $employe;
          unset($employes[$key]);
        }
      }
      $secteur = $this->app->Secteur->getSecteur($bon[0]["secteur"])[0];
      $vehicle = $this->app->Vehicle->getVehicle($bon[0]["vehicule"]);
      $vehicleName = "".$vehicle["code"]." ".$vehicle["matricule"]." ".$vehicle["genre"]; 
      return $this->render('public/tournee/tourneeDetailUser.html.twig', [
        "bon" => $bon[0], 
        "vehicle"=>$vehicleName, 
        "id_tournee" => $id,
        "chauffeur" => $chauffeur,
        "agents" => $employes,
        "secteur" => $secteur
        
      ]);
  }

    /**
     * @Route("/dashboard/tournee/{id}/update", methods={"POST","GET"}, name="updateTournee")
     */
    public function update($id){
      $secteurs = $this->app->Secteur->allTournee();
      $vehicules = $this->app->Vehicle->vehiculesEnMarche();
      $equipes = $this->app->Equipe->all();
      if(empty($_POST)){
        $tournee = $this->app->Tournee->findWithId($id);
        $t = $tournee[0];
        for ($i=0; $i < count($t) ; $i++) { 
          unset($t[$i]);
        }
        return $this->render('public/tournee/edit.html.twig', [
        "tournee_id"=> $id,
        "tournee"=>$t, 
        "vehicles"=>$vehicules, 
        "secteurs"=> $secteurs, 
        "equipes" => $equipes,
        "result"=>null]);
      }

      $this->app->Tournee->edit($id, [
              "date" => $_POST['date'],
              "qte_prevue" => $_POST['qte_prevue'],
              "heure_demarrage_parc" =>  $_POST['heure_demarrage_parc'],
              "secteur" =>  $_POST['secteur'],
              "vehicle" => $_POST['vehicle'],
              "cet" => $_POST['cet'],
              "equipe" => intval($_POST['equipe'])
      ]);
      $this->app->Tournee->updateBonTransport($id, [
        "date" => $_POST["date"],
        "heure_debut" => $_POST["heure_demarrage_parc"],
        "vehicule" => $_POST["vehicle"],
        "equipe" => $_POST["equipe"],
        "secteur" => $_POST["secteur"]
      ]);
      $this->app->Tournee->updateTicketPesee($id, [
        "date_ticket" => $_POST["date"]
      ]);
      $tournee = $this->app->Tournee->findWithId($id);
      $t = $tournee[0];
      for ($i=0; $i < count($t) ; $i++) { 
        unset($t[$i]);
      }
      return $this->render('public/tournee/edit.html.twig', [
        "tournee_id"=> $id,
        "tournee"=>$t, 
        "vehicles"=>$vehicules, 
        "secteurs"=> $secteurs, 
        "equipes" => $equipes,
        "result"=> "Rotation mise à jour avec succés."]);
        
    } 

    /**
     * @Route("user/editTourneeUser", name="userEditTournee")
     */
    public function userEdit(Request $request){
        
        $data = json_decode($request->getContent(), true);
        $id = intval($data['id_tournee']);
        
        $heure_demarrage_parc =  ($data['heure_demarrage_parc'] != "")? $data['heure_demarrage_parc'] : null;
        $heure_debut_secteur =  ($data['heure_debut_secteur'] != "")? $data['heure_debut_secteur'] : null;
        $heure_fin_secteur = ( $data['heure_fin_secteur'] != "")? $data['heure_fin_secteur'] : null;
        $heure_pesee = ($data['heure_pesee'] != "")? $data['heure_pesee'] : null;
        $temps_travail = ($data['temps_travail'] != "")? floatval($data['temps_travail']) : null ;
        $duree_attente = ($data['duree_attente'] != "")? floatval($data['duree_attente']) : null;
        $qte_realise = ($data['qte_realise'] != "")? floatval($data['qte_realise']) : null;
        $kilometrage = ($data['kilometrage'] != "")? floatval($data['kilometrage']) : null;
        $carburant = ($data['carburant'] != "")? floatval($data['carburant']) : null;
        
        $tournee = $this->app->Tournee->edit($id, [
            "heure_demarrage_parc" =>  $heure_demarrage_parc,
            "heure_debut_secteur" =>  $heure_debut_secteur,
            "heure_fin_secteur" => $heure_fin_secteur,
            "heure_pesee" => $heure_pesee,
            "temps_travail" => $temps_travail,
            "duree_attente" => $duree_attente,
            "qte_realise" => $qte_realise,
            "kilometrage" => $kilometrage,
            "carburant" => $carburant
        ]);

        return new Response("Tournée mise à jour avec succes.");
       
    }

    /**
     * @Route("dashboard/tournee/getRotationsPrevues", name="getRotationsPrevues")
     */
    public function getRotationsPrevues(){
      $data = $this->app->RotationPrevue->getUsedPlan();
      foreach($data as $k=>$line){
        for ($i=0; $i <count($line) ; $i++) { 
          unset($data[$k][$i]);
        }
      }
      return new Response(json_encode($data));
    }
    
    
    /**
     * @Route("/dashboard/tournees/addTournee", methods={"POST","GET"}, name="addTournee")
     */
    public function ajouter(){
      $secteurs = $this->app->RotationPrevue->getSecteurs();
      $vehicules = $this->app->RotationPrevue->getVehicles();
      $equipes = $this->app->RotationPrevue->getEquipes();
      if (!empty($_POST)) {
        $tournee = $this->app->Tournee->ajouter([
            "date" => $_POST['date'],
            "qte_prevue" => $_POST['qte_prevue'],
            "qte_realise" => null,
            "taux_realisation" => null,
            "kilometrage" => null,
            "carburant" => null,
            "heure_demarrage_parc" =>  $_POST['heure_demarrage_parc'],
            "heure_debut_secteur" =>  null,
            "heure_fin_secteur" => null,
            "heure_pesee" => null,
            "duree_attente" => null,
            "temps_travail" => null,
            "secteur" =>  $_POST['secteur'],
            "vehicle" => $_POST['vehicle'],
            "cet" => $_POST['cet'],
            "equipe" => $_POST['equipe']
        ]);
        $tournee = $this->app->Tournee->initialiser($tournee[0]["max"]);
        
          return $this->render('public/tournee/add.html.twig',[
            "secteurs" => $secteurs,
            "vehicles" => $vehicules,
            "equipes" => $equipes,
            "result" => "Rotation planifiée avec succès."
          ]);
      }
      return $this->render('public/tournee/add.html.twig',[
        "secteurs" => $secteurs,
        "vehicles" => $vehicules,
        "equipes" => $equipes,
        "result" => null
      ]);
    }
    

    /**
     * @Route("/dashboard/tournees/deleteTournee/{id}", methods={"POST","GET"}, name="deleteTournee")
     */
    public function delete($id){
      $result = $this->app->Tournee->delete($id);
      return $this->index();
    }

	/**
	 * @Route("/dashboard/tournees/addTournee/getSecteursVehiculesEquipes", name="getSecteursVehiclesEquipes")
	 */
	public function getSecteursVehicles(){
		$secteurs = $this->app->Secteur->allTournee();
		$vehicules = $this->app->Vehicle->all();
		$equipes = $this->app->Equipe->all();
		foreach($secteurs as $key => $value ){
		for ($i=0; $i < count($value); $i++) { 
			unset($secteurs[$key][$i]);
		}
		}
    
		$data = ["secteurs"=>$secteurs, "vehicules"=>$vehicules, "equipes"=>$equipes];
		return new Response(json_encode($data));
	}

	/**
	 * @Route("/dashboard/tournees/getTournees/", methods={"POST","GET"}, name="tourneesEnAttente")
	 */
	public function getTourneesEnAttente(){
		return new Response(json_encode($this->app->Tournee->getTourneesEnAttente()));
	}

    /**
	 * @Route("/user/tournee/{id}/getPoints", methods={"GET","POST"}, name="getPointsTournee")
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
     * @Route("/user/tournee/{id}/getDepots", methods={"GET","POST"}, name="getDepotsTournee")
     */
    public function getDepots($id){
        $depots = [];
        array_push($depots, $this->app->Tournee->getParc()[0]);
        array_push($depots,$this->app->Tournee->getCet($id)[0]);
        $features = [];
        foreach($depots as $depot){
        unset($depot['geom']);
        for ($i=0; $i <count($depot) ; $i++) { 
            unset($depot[$i]);
        }
        $geometry=json_decode($depot['geojson']);
        unset($depot['geojson']);
        $feature = ["type"=>"Feature", "geometry"=>$geometry, "properties"=>$depot];
        array_push($features, $feature);
        }
        $featureCollection = ["type"=>"FeatureCollection", "features"=>$features];
        return new Response(json_encode($featureCollection));
    }

   
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
    

    public function getVrpDepots($id){
        $features = [];
        $depots = [];
        array_push($depots, $this->app->Tournee->getCet($id)[0]);
        array_push($depots, $this->app->Tournee->getParc()[0]);
        foreach($depots as $depot){
            unset($depot['geom']);
            for ($i=0; $i <count($depot) ; $i++) { 
                unset($depot[$i]);
            }
            $geo = json_decode($depot['geojson'],true);
            $geometry=["x"=>$geo['coordinates'][0],"y"=>$geo['coordinates'][1]];
            unset($depot['geojson']);
            $attributes = [
                "name" => $depot['designation'],
                "TimeWindowStart1" => 1355241600000,
                "TimeWindowEnd1" => 1355274000000
            ];
            $feature = ["geometry"=>$geometry, "attributes"=>$attributes];
            array_push($features, $feature);
        }
        $featureCollection = ["features"=>$features];
      return new Response(json_encode($featureCollection));
    }

    
    public function getVrpRoutes($id){
        $cet = $this->app->Tournee->getCet($id)[0];
        $attributes = [
            "Name"=>"Truck_1",
            "StartDepotName"=>"PARC BAB EZZOUAR",
            "EndDepotName"=> $cet['designation'],
            "StartDepotServiceTime"=>60,
            "EarliestStartTime"=>1355241600000,
            "LatestStartTime"=>1355241600000,
            "Capacities"=> "30000",
            "CostPerUnitTime"=>0.2,
            "CostPerUnitDistance"=>1.5,
            "MaxOrderCount"=>50,
            "MaxTotalTime" => 360,
            "MaxTotalTravelTime" => 120,
            "MaxTotalDistance" => 80
          ];
        $feature = ["attributes"=>$attributes];
        $features = [];
        array_push($features, $feature);
        $featureCollection = ["features"=> $features];
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
     * @Route("user/tournee/{id}/route", name="routeService", methods={"GET","POST"})
     */
    public function getRoute($id){
        $url = 'https://logistics.arcgis.com/arcgis/rest/services/World/VehicleRoutingProblem/GPServer/SolveVehicleRoutingProblem/submitJob?';
        $token =  $this->app->token;
        $stops = $this->getStops($id)->getContent();
        $depots = $this->getVrpDepots($id)->getContent();
        $routes = $this->getVrpRoutes($id)->getContent();

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, 'orders='. str_replace("'","",$stops) . '&depots='. str_replace("'","",$depots) . '&routes='. str_replace("'","",$routes) . '&time_units=Minutes&distance_units=Kilometers&uturn_policy=NO_UTURNS&populate_directions=true&directions_language=en&default_date=1355212800000&f=json&token='.$token.'');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);
        $jobId = json_decode($response,true)['jobId'];
        $result = ["value"=>null];
        while($result["value"] == null){
          sleep(2);
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
          $featureCollection = ["type"=>"FeatureCollection", "features"=>$features];
        
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
          $featureCollection1 = ["type"=>"FeatureCollection", "features"=>$features];
          $result = ["featureCollection" => $featureCollection, "featureCollectionStops"=>$featureCollection1];
        return new Response(json_encode($result));
    }

    /**
     * @Route("user/tournee/updateTournee/{id}", methods={"POST","GET"}, name="userUpdateTournee")
     */
    public function userUpdate($id){
		$result = $erreur = null;
		$tournee = $this->app->Tournee->findWithId($id)[0];
		$ticketPesee = $this->app->Tournee->getTicketPesee($id)[0];
		if (!empty($_POST)) {
			try {
			$tourneeData = [
				"heure_demarrage_parc" =>  $_POST['heure_demarrage_parc'],
				"heure_debut_secteur" =>  $_POST['heure_debut_secteur'],
				"heure_fin_secteur" => $_POST['heure_fin_secteur'],
				"heure_fin_rotation" => $_POST['heure_fin_rotation'],
				"duree_attente" => $_POST['duree_attente'],
				"qte_realise" => $_POST['qte_realise'],
				"kilometrage" => $_POST['kilometrage'],
				"carburant" => $_POST['carburant']
			];
			$ticketData = [
				"date_pesee" =>  $_POST['date_pesee'],
				"heure_pesee" =>  $_POST['heure_pesee'],
				"poids_brut" => $_POST['poids_brut'],
				"poids_net" => $_POST['poids_net'],
				"montant" => $_POST['montant'],
				"image_name" => $this->uploadImage($ticketPesee['code'], $_FILES)
			];
			$this->app->Tournee->edit($id, $tourneeData);
			$this->app->Tournee->updateTicketPesee($id, $ticketData);
			$result = "Données inserées avec succès.";
		} catch (\Throwable $th) {
			$erreur = "Données saisies non valides.";
		}
        
      }
      return $this->render('public/tournee/updateTournee.html.twig', [
		  "tournee" => $tournee, 
		  "ticket"=>$ticketPesee,
		  "result" => $result,
		  "erreur" => $erreur
		  ]);
	}
	

	private function uploadImage($code_ticket, $files){
		$imageFileType = strtolower(pathinfo($_FILES["image"]["name"],PATHINFO_EXTENSION));
		$target_dir = $this->parameterBag->get('kernel.project_dir') . '/public/img/'; 
		$target_file = $target_dir . basename($code_ticket .".". $imageFileType);
		$uploadOk = 1;
		// Check if image file is an actual image or fake image
		$check = getimagesize($files["image"]["tmp_name"]);
		if($check == false) {
			return "File is not an image.";
			$uploadOk = 0;
		}
		if (file_exists($target_file)) {
			echo "Sorry, file already exists.";
			$uploadOk = 0;
		}
		if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
			echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
			$uploadOk = 0;
		}
		
		if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
			return $code_ticket .".". $imageFileType;
		} 
		

  }
  
  /**
   * @Route("/dashboard/tournee/getTime", methods={"POST","GET"}, name="getTimeForDay")
   */
  public function getTimeTournee(Request $request){
    $this->app->loadModel("Planning");
    $data = json_decode($request->getContent(), true);
    $jours = [
      "6" => "samedi",
      "0" => "dimanche", 
      "1" => "lundi", 
      "2" => "mardi", 
      "3" => "mercredi", 
      "4" => "jeudi", 
      "5" => "vendredi"
    ];
    $date = strtotime($data["date"]);
    $day = $jours[date('w', $date)];
    $heure = $this->app->Planning->getTime($day, $data["secteur"]);

    return new Response(json_encode($heure[0]));
  }
}   
