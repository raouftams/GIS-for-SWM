<?php
namespace App\Table;
use App\Core\Table\Table;

class PlanSectorisationTable extends Table{


    protected $table = 'plan_sectorisation';

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


    
    
	public function deletePlan($code){
		return $this->query("DELETE from plan_sectorisation WHERE code_plan = ? 
        ",[$code]);
    }
    
    /**
	 * check if key exist in table
	 * @return boolean
	 */
	public function exist($code){
		$result = $this->query('SELECT count(1) as cpt FROM plan_sectorisation where code_plan = ?',[$code]);

		if($result[0]["cpt"] == 0){
			return false;
		}
		return true;
    }
    
    /**
     * Mise à jour de la geometry des secteurs
     * @return boolean
     */
    public function updateGeomSecteurs($codeSectorisation){
        $secteurs = $this->query('SELECT code from secteurs where sectorisation = ?',[$codeSectorisation]);
        foreach ($secteurs as $secteur) {
            
            $geom = $this->query('SELECT ST_ConvexHull(
                ST_Collect(
                    ARRAY(select geom from points_collecte where helpcreategeom = ?)
                    )
                )
            ', [$secteur['code']]);

            if($geom[0]["st_convexhull"] == null){
                
            }else{
                $this->query('UPDATE secteurs 
                set geom = ? 
                where code = ? and sectorisation = ?
                ', [$geom[0]["st_convexhull"], $secteur['code'], $codeSectorisation]);
            }
        }
        return true;
    }

     

    /**
	 * Vérifier si un plan de sectorisation est utilisé
	 * @return boolean
	 */
	public function isUsed($codeSectorisation){
		$result = $this->query('SELECT count(code_plan) as cpt FROM plan_collecte where sectorisation = ?',[$codeSectorisation]);

		if($result[0]["cpt"] == 0){
			return false;
		}
		return true;
	}
}

?>
