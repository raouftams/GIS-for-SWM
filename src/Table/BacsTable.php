<?php
namespace App\Table;
use App\Core\Table\Table;

class BacsTable extends Table{


    protected $table = 'bacs';


    /**
     * retourne toutes les données de la table bacs
     * @return array
     */
    public function all(){
      return $this->query('SELECT code, typemat, adresse, volume, typedechet, dateinstal, etat
      FROM "public".bacs
      ');
    }

    /**
     * Retourne la quantité de déchets dans chaque point de collecte
     * @return array tableau
     */
    public function qte(){
      return $this->query('SELECT secteur, sum(volume) As qte
      FROM "public".bacs
      GROUP BY secteur 
      ');
    }


    /**
     * @return array
     */
    public function getBac($code){
      return $this->query('SELECT code, adresse, typemat, typedechet, dateinstal, volume, etat
      FROM "public".bacs
      WHERE code=?
      ',[$code]);
    }

    /**
     * Retourne les bacs d'un points donné
     * @return array
     */
    public function getBacsPoint($code){
      return $this->query('SELECT code, adresse, typemat, typedechet, dateinstal, volume, etat, tauxrempli
      FROM "public".bacs
      WHERE pointc = ?
      ', [$code]);
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
    public function edit($code, $fields){
      return $this->update($code, $fields);
	  }
  }

 ?>
