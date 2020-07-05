<?php
namespace App\Table;
use App\Core\Table\Table;

class SecteurTable extends Table{


    protected $table = 'secteurs';

    /**
     * Retourne les points de collecte
     * @return array tableau
     */
    public function all(){
      return $this->query('SELECT *, ST_AsGeoJson(geom, 5) as geojson
      FROM "public".secteurs
      ');
    }

    /**
	 * check if key exist in table
	 * @return boolean
	 */
	public function exist($code){
		$result = $this->query('SELECT count(1) FROM "public".secteur where code = ?',[$code]);
		if($result == 0){
			return false;
		}
		return true;
	}

	/**
	 * 
	 */
	public function add($fields){
		return $this->create($fields);
	}


	public function update($code, $fields){
		$sql_parts = [];
      	$attributes = [];
      	foreach ($fields as $k => $v) {
      	  $sql_parts[] = "$k = ?";
      	  $attributes[] = $v;
      	}
      	$attributes[] = $code;
      	$sql_part = implode(',', $sql_parts);
      	return $this->query("UPDATE {$this->table} SET $sql_part WHERE code = ?", $attributes, true);
    }
}
  

 ?>
