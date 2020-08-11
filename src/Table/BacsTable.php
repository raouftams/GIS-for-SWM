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
      $sql_parts = [];
      $attributes = [];
      $indexes = [];
      foreach ($fields as $k => $v) {
        $sql_parts[] = "?";
        $indexes[] = $k;
        $attributes[] = $v;
      }
      $sql_part = implode(',', $sql_parts);
      $columns = implode(',', $indexes);
      return $this->query("INSERT INTO {$this->table} ({$columns}) Values ($sql_part)", $attributes, true);
    }

    /**
     * 
     */
    public function edit($code, $fields){
      return $this->update($code, $fields);
	  }
  }

 ?>
