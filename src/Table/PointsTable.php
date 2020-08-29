<?php
namespace App\Table;
use App\Core\Table\Table;

class PointsTable extends Table{


    protected $table = 'points_collecte';

    /**
     * Retourne les points de collecte
     * @return array tableau
     */
    public function all(){
      return $this->query('SELECT code_point, libelle, adresse, modecollecte, activites,secteur, quantited,frequence,debut_fenetre_temps1, fin_fenetre_temps1, helpcreategeom, X, Y,geom, ST_AsGeoJson(geom, 5) as geojson
      FROM "public".points_collecte
      ');
    }

    /**
     * Retourne les points de collecte d'un secteur donné
     * @return array
     */
    public function findWithSector($secteur){
        return $this->query('SELECT code_point, libelle, adresse, modecollecte, activites
        FROM "public".points_collecte 
        WHERE secteur = ?
        ', [$secteur]);
    }

    /**
     * retourne un tableau d'attribut d'un point donné
     * @return array
     */
    public function getPoint($code){
      return $this->query('SELECT * 
      FROM "public".points_collecte
      WHERE code_point = ?
      ', [$code]);
    }

    /**
     * @return array
     */
    public function allVrp(){
      return $this->query('SELECT *,geom, ST_AsGeoJson(geom, 5) as geojson
      FROM "public".points_collecte
      ');
    }
    /**
     * @return boolean 
     */
    public function add($fields){
      $sql_parts = [];
      $attributes = [];
      $indexes = [];
      $geom = $this->query("SELECT ST_SetSRID( ST_Point( {$fields['X']}, {$fields['Y']}), 4326)")[0][0];
      

      foreach ($fields as $k => $v) {
        if ($k == "debut_fenetre_temps1") {
          $sql_parts[] = "?";
          $indexes[] = "geom";
          $attributes[] = $geom;
        }else{
          $sql_parts[] = "?";
          $indexes[] = $k;
          $attributes[] = $v;
        }
        
      }

      $sql_part = implode(',', $sql_parts);
      $index = implode(',', $indexes);
      return $this->query("INSERT INTO {$this->table} ($index) Values ($sql_part)", $attributes, true);
    }

    /**
     * @return boolean
     */
    public function update($code, $fields){
      $sql_parts = [];
      $attributes = [];
      foreach ($fields as $k => $v) {
        $sql_parts[] = "$k = ?";
        $attributes[] = $v;
      }
      $attributes[] = $code;
      $sql_part = implode(',', $sql_parts);
      return $this->query("UPDATE {$this->table} SET $sql_part WHERE code_point = ?", $attributes, true);
    }

    public function updateAll($fields){
      $sql_parts = [];
      $attributes = [];
      foreach ($fields as $k => $v) {
        $sql_parts[] = "$k = ?";
        $attributes[] = $v;
      }
      $sql_part = implode(',', $sql_parts);
      return $this->query("UPDATE {$this->table} SET $sql_part", $attributes, true);
    
    }

    /**
     * @return boolean
     * cette fonction crée une geometry point à partir des cordonnées données
     */
    public function addgeom($code, $x, $y){
      return  $this->query('UPDATE "public".points_collecte
      SET geom = ST_SetSRID(ST_MakePoint('. $x .','. $y.'), 4326)
      WHERE code_point = ?
      ', [$code]);
    }

    /**
     * @return array
     */
    public function getLocations(){
      return $this->query('SELECT X,Y
      FROM "public".points_collecte
      ');
    }

    /**
     * @return array 
     * recherche les points d'un secteur appartenant à un tournée données
     */
    public function pointsTournee($id){
      return $this->query('SELECT *, ST_AsGeoJson(geom, 5) as geojson
      FROM "public".points_collecte 
      WHERE secteur in (SELECT secteur FROM "public".tournee WHERE id_tournee = ?)'
      , [$id]);
    }

    /**
     * @return array
     */
    public function getSecteursQte(){
      return $this->query('SELECT secteur, sum(quantited) as qte
      FROM "public".points_collecte
      GROUP BY secteur
      ');
    }


    /**
     * mettre à jour la table point_collecte pour l'utilisation d'un nouveau plan de collect
     * @return boolean
     */
    public function updateSectorisationPoints(){
      $secteurs = $this->query("SELECT code 
      from secteurs s, plan_sectorisation ps, plan_collecte pc
      where s.sectorisation = ps.code_plan 
			and ps.code_plan = pc.sectorisation and pc.etat = 'used' 
      ");

      foreach ($secteurs as $secteur) {
        $points = $this->query("SELECT p.code_point 
        from points_collecte as p 
        join secteurs s on ST_WITHIN(p.geom, s.geom) where s.code = ?
        ", [$secteur['code']]);

        foreach ($points as $point) {
          $this->query('UPDATE points_collecte 
          set secteur = ?
          where code_point = ?
          ', [$secteur["code"], $point["code_point"]]);
        }

      }
      return true;

    }
}

 ?>
