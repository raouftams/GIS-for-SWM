<?php
namespace App\Table;
use App\Core\Table\Table;

class RouteTable extends Table{


    protected $table = 'route1';

    /**
     * @return array tableau
     */
    public function all(){
      return $this->query('SELECT *, ST_AsGeoJson(geom, 5) as geojson
      FROM "public".route1
      ');
    }

}
?>
