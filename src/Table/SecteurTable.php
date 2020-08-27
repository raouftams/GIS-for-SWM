<?php
namespace App\Table;
use App\Core\Table\Table;

class SecteurTable extends Table{


    protected $table = 'secteurs';

    /**
     * @return array tableau
     */
    public function all(){
      return $this->query('SELECT code, horaire, qtedechet, vehicule, ST_AsGeoJson(geom, 5) as geojson
      FROM "public".secteurs
      ');
	}
	
	/**
     * @return array tableau
     */
    public function allTournee(){ 
		return $this->query('SELECT code, horaire, qtedechet, vehicule
		FROM "public".secteurs where horaire is not null
		');
	  }

    /**
	 * check if key exist in table
	 * @return boolean
	 */
	public function exist($code){
		$result = $this->query('SELECT count(1) as cpt FROM "public".secteurs where code = ?',[$code]);

		if($result[0]["cpt"] == 0){
			return false;
		}
		return true;
	}

    /**
     * Retourne la quantité de déchets pour chaque secteur
     * @return array tableau
     */
    public function qte(){
		return $this->query('SELECT code as label, qtedechet as data
		FROM "public".secteurs 
		where horaire is not null
		GROUP BY label
		order by label
		');
	  }
  
	/**
	 * @return array
	 */
	public function getUsedSecteur(){
		return $this->query('SELECT code, horaire, qtedechet, vehicule 
		FROM "public".secteurs
		WHERE vehicule  Is NOT NULL
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
	
	public function updateGeom(){
		return $this->query('SELECT * from update_geom()');
	}


	public function updateSectorization($code, $data){
		$data = "'" . $data . "'";
		return $this->query("UPDATE secteurs 
		set geom = (SELECT ST_GeomFromGeoJSON({$data})) 
		where code = ? ",[$code]);
	}

	public function initSecteurs(){
		return $this->query('UPDATE secteurs 
		set horaire = null, vehicule = null, geom = null, qtedechet= null');
	}
}
  

 ?>
