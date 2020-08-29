<?php
namespace App\Table;
use App\Core\Table\Table;

class RotationPrevueTable extends Table{


    protected $table = 'rotation_prevue';

    /**
     * @return array tableau
     */
    public function all(){
        return $this->query('SELECT p.code_plan, secteur, v.code as codeVehicle, v.genre, v.marque, v.matricule, v.volume, equipe, qte_dechets, kilometrage, p.carburant, heure_debut, p.heure_fin, p.nombre_points, p.etat
		FROM rotation_prevue p, vehicule v
        WHERE v.code = p.vehicle and p.etat is null
        ');
	}
    
  
	/**
	 * @return array
	 */
	public function getUsedPlan(){
		return $this->query('SELECT p.code_plan, p.etat, p."date", rp.secteur, v.code, v.genre, v.marque, v.matricule, v.volume, equipe, qte_dechets, kilometrage, rp.carburant, rp.heure_debut, rp.heure_fin, rp.nombre_points, p.fin_validite
		FROM rotation_prevue rp, vehicule v, plan_collecte p
		WHERE v.code = rp.vehicle and p.etat = ?
        ',['used']);
    }
    
    /**
	 * @return array
	 */
	public function getPlan($code){
		return $this->query('SELECT p.code_plan, p.etat, p."date", rp.secteur, v.code, v.genre, v.marque, v.matricule, v.volume, equipe, qte_dechets, kilometrage, rp.carburant, rp.heure_debut, rp.heure_fin, rp.nombre_points, p.fin_validite
		FROM rotation_prevue rp, vehicule v, plan_collecte p
		WHERE v.code = rp.vehicle and p.code_plan = rp.code_plan and p.code_plan = ?
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

	/**
	 * Supprimer toutes les rotations d'un plan donné
	 */
	
	public function delete($code_plan){
		return $this->query('DELETE from rotation_prevue WHERE code_plan = ?',[$code_plan]);
	}

}
  

 ?>
