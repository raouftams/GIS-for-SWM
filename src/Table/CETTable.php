<?php
namespace App\Table;
use App\Core\Table\Table;

class CETTable extends Table{


    protected $table = 'CET';

    /**
     * @return array tableau
     */
    public function all(){
      return $this->query('SELECT *, ST_AsGeoJson(geom, 5) as geojson
      FROM "public".CET
      ');
    }

    /**
     * @return array retourne les données d'un cet donné
     */
    public function find($code){
      return $this->query('SELECT designation,geom, ST_AsGeoJson(geom, 5) as geojson
      FROM "public".cet
      WHERE code = ?
      ', [$code]);
    }

}
?>
