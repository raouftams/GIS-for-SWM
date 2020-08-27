<?php
namespace App\Table;
use App\Core\Table\Table;

class PlanTable extends Table{


    protected $table = 'plan_collecte';

    /**
     * @return array tableau
     */
    public function all(){
        return $this->query('SELECT p.code_plan, secteur, v.code as codeVehicle, v.genre, v.marque, v.matricule, v.volume, equipe, qte_dechets, kilometrage, p.carburant, heure_debut, p.heure_fin, p.nombre_points, p.etat
		FROM plan_collecte p, vehicule v
        WHERE v.code = p.vehicle and p.etat is null
        ');
	}
    
  
	/**
	 * @return array
	 */
	public function getUsedPlan(){
		return $this->query('SELECT code_plan, p.etat, "date", secteur, v.code, v.genre, v.marque, v.matricule, v.volume, equipe, qte_dechets, kilometrage, p.carburant, heure_debut, p.heure_fin, p.nombre_points
		FROM plan_collecte p, vehicule v
		WHERE v.code = p.vehicle and p.etat = ?
        ',['used']);
    }
    
    /**
	 * @return array
	 */
	public function getPlan($code){
		return $this->query('SELECT code_plan, p.etat, "date", secteur, v.code, v.genre, v.marque, v.matricule, v.volume, equipe, qte_dechets, kilometrage, p.carburant, heure_debut, p.heure_fin, p.nombre_points
		FROM plan_collecte p, vehicule v
		WHERE v.code = p.vehicle and p.code_plan = ?
        ',[$code]);
    }
    
    /**
	 * @return array
	 */
	public function getCodes(){
		return $this->query('SELECT code_plan, "date"
        FROM plan_collecte
        group by code_plan, date
        ');
	}

	/**
	 * @return boolean
	 */
	public function add($fields){
		$sql_parts = [];
		$attributes = [];
		$val = [];
      	foreach ($fields as $k => $v) {
			$sql_parts[] = "$k";
			$val[] = "?";
      		$attributes[] = $v;
		}
		$val = implode(',',$val);
		$sql_part = implode(',', $sql_parts);
		return $this->query("INSERT INTO {$this->table} ($sql_part) values ($val)", $attributes,true);
		
	}


	public function edit($code, $secteur, $fields){
		$sql_parts = [];
      	$attributes = [];
      	foreach ($fields as $k => $v) {
      	  $sql_parts[] = "$k = ?";
      	  $attributes[] = $v;
		}
        $attributes[] = $code;
        $attributes[] = $secteur;
      	$sql_part = implode(',', $sql_parts);
		return $this->query("UPDATE {$this->table} SET $sql_part WHERE code_plan = ? and secteur = ?", $attributes, true);
		
    }
    
    public function setInUse($code){
        return $this->query("UPDATE plan_collecte 
        set etat = 'used' 
        WHERE code_plan = ? 
        ",[$code]);
    }

	public function initPlanUse(){
        return $this->query("UPDATE plan_collecte set etat = 'not used'");
    }

	public function deletePlan($code){
		return $this->query("DELETE from plan_collecte WHERE code_plan = ? 
        ",[$code]);
	}

	public function updateGeom(){
		return $this->query('SELECT * from update_geom()');
	}


	public function updateSectorization($code, $data){
		$data = "'" . $data . "'";
		return $this->query("UPDATE secteurs 
		set geom = (SELECT ST_GeomFromGeoJSON({$data})) 
		where code = ? ",[$code]);
	}

}
  

 ?>
