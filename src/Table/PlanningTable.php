<?php
namespace App\Table;
use App\Core\Table\Table;

class PlanningTable extends Table{


    protected $table = 'planning_collecte';

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
     * retourne le planning du plan de collecte utilisé
	 * @return array
	 */
	public function getUsedPlanning(){
		return $this->query('SELECT rp.secteur, pc.heure, pc.jour, rp.heure_debut, rp.heure_fin, rp.kilometrage, rp.vehicle
        from rotation_prevue rp, planning_collecte pc, plan_collecte plan
        Where pc.rotation = rp.id_rotation_prevue and rp.code_plan = plan.code_plan and plan.etat = ?
        ',['used']);
    }

    /**
     * retourne le planning d'un véhicule 
     * @return array
     */
    public function vehiclePlanning($code_vehicle){
        return $this->query("SELECT pc.jour, pc.heure, rp.heure_debut, rp.heure_fin, rp.kilometrage 
        from planning_collecte pc, rotation_prevue rp
        Where pc.rotation = rp.id_rotation_prevue and rp.vehicle = ?
        ", [$code_vehicle]);
    }
    /**
     * retourne le planning d'une équipe 
     * @return array
     */
    public function equipePlanning($code_equipe){
        return $this->query("SELECT pc.jour, pc.heure , rp.heure_debut, rp.heure_fin
        from planning_collecte pc, rotation_prevue rp
        Where pc.rotation = rp.id_rotation_prevue and (rp.equipe = ? or rp.equipe2 = ?)
        ", [$code_equipe, $code_equipe]);
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
		$this->query("INSERT INTO {$this->table} ($sql_part) values ($val)", $attributes,true);
	}


	public function edit($jour, $secteur, $heure){
        return $this->query("UPDATE planning_collecte set heure = ? 
        Where jour = ? and rotation = (select id_rotation_prevue from rotation_prevue 
        Where secteur = ?)", [$heure, $jour, $secteur]);
    }
    
    /**
     * supprimer le planning d'un plan de collecte donné
     * @return boolean
     */
    public function delete($code_plan){
        $rotations = $this->query("SELECT id_rotation_prevue 
        from rotation_prevue
        Where code_plan = ?
        ",[$code_plan]);
        
        foreach($rotations as $rotation){
            $this->query("DELETE from {$this->table} 
            Where rotation = ?",[$rotation["id_rotation_prevue"]]);
        }
        return true;
    }

    /**
     * retourne l'heure de début d'un rotation dans un secteur à jour donnée
     */
    public function getTime($day, $secteur){
        return $this->query("SELECT heure from planning_collecte
        Where jour = ? and rotation = (select id_rotation_prevue from rotation_prevue
        Where secteur = ?)", [$day, $secteur]);
    }



}
  

 ?>
