<?php
namespace App\Table;
use App\Core\Table\Table;

class PlanCollecteTable extends Table{


    protected $table = 'Plan_collecte';

    /**
     * @return array tableau
     */
    public function all(){
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
	
	public function getEtat($code){
		return $this->query('SELECT etat 
		from plan_collecte 
		WHERE code_plan = ?
		', [$code]);
	}

	public function initPlanUse(){
        return $this->query("UPDATE plan_collecte set etat = 'not used'");
    }

	public function deletePlan($code){
		return $this->query("DELETE from plan_collecte WHERE code_plan = ? 
        ",[$code]);
    }
    
    /**
	 * check if key exist in table
	 * @return boolean
	 */
	public function exist($code){
		$result = $this->query('SELECT count(1) as cpt FROM plan_collecte where code_plan = ?',[$code]);

		if($result[0]["cpt"] == 0){
			return false;
		}
		return true;
	}

}
  

 ?>
