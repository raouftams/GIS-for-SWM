<?php
namespace App\Table;
use App\Core\Table\Table;

class ParcTable extends Table{


    protected $table = 'parc';

    /**
     * @return array tableau
     */
    public function all(){
      return $this->query('SELECT *, ST_AsGeoJson(geom, 5) as geojson
      FROM "public".parc
      ');
    }

    /**
     * @return array retourne les données d'un parc donné
     */
    public function find($code){
      return $this->query('SELECT designation,geom, ST_AsGeoJson(geom, 5) as geojson
      FROM "public".parc
      WHERE code = ?
      ', [$code]);
    }
}
?>
