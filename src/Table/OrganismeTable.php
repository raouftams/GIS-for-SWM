<?php
namespace App\Table;
use App\Core\Table\Table;

class OrganismeTable extends Table{


    protected $table = 'organismes';

    /**
     * Retourne les points de collecte
     * @return array tableau
     */
    public function all(){
      return $this->query('SELECT *, ST_AsGeoJson(geom, 5) as geojson
      FROM "public".organismes
      ');
    }


  }

 ?>
