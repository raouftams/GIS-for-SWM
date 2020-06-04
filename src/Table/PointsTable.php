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
      return $this->query('SELECT code_point, libelle, adresse, modecollecte, activites, quantited, X, Y,geom, ST_AsGeoJson(geom, 5) as geojson
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
  }

 ?>
