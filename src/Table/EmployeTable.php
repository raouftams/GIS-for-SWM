<?php
namespace App\Table;
use App\Core\Table\Table;

class EmployeTable extends Table{


    protected $table = 'employe';


    /**
     * retourne les données de la table de gestion
     * @return array
     */
    public function allTable(){
      return $this->query('SELECT matricule, nom, prenom, fonction, debut_contrat
      FROM "public".employe
      ');
    }

    /**
     * @return array
     */
    public function all(){
        return $this->query('SELECT * FROM "public".employe');
    }

    /**
     * @return array
     * retourne les données d'un employé donnée
     */
    public function find($mat){
      return $this->query('SELECT * from "public".employe WHERE matricule = ?', [$mat]);
    }

    /**
     * @return boolean
     */
    public function edit($mat, $fields){
      $sql_parts = [];
      $attributes = [];
      foreach ($fields as $k => $v) {
        $sql_parts[] = "$k = ?";
        $attributes[] = $v;
      }
      $attributes[] = $mat;
      $sql_part = implode(',', $sql_parts);
      return $this->query("UPDATE {$this->table} SET $sql_part WHERE matricule = ?", $attributes, true);
    }

    public function getEmployesEquipe($equipe){
      return $this->query("SELECT * from employe Where equipe = ?",[$equipe]);
    }

  }

 ?>
