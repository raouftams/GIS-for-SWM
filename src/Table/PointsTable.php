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
      return $this->query('SELECT code_point, libelle, adresse, modecollecte, activites,secteur, quantited,debut_fenetre_temps1, fin_fenetre_temps1, X, Y,geom, ST_AsGeoJson(geom, 5) as geojson
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
      return $this->create($fields);
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
  }

 ?>
