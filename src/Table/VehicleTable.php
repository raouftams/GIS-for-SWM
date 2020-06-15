<?php
namespace App\Table;
use App\Core\Table\Table;

class VehicleTable extends Table{


    protected $table = 'vehicule';

    /**
     * Retourne les points de collecte
     * @return array tableau
     */
    public function all(){
      return $this->query('SELECT code, marque, genre, volume, mise_en_marche, etat
      FROM "public".vehicule
      ');
    }

    /**
     * @return array
     */
    public function vehiculesEnMarche(){
      return $this->query('SELECT code, volume From "public".vehicule where etat = ?',['En marche']);
    }

    /**
     * retourne un tableau d'attribut d'un point donné
     * @return array
     */
    public function getVehicle($code){
      return $this->find($code);
    }

    /**
     * @return boolean 
     */
    public function add($fields){
      	return $this->create($fields);
    }

    /**
     * 
     */
    public function edit(){

	}
  }

 ?>
