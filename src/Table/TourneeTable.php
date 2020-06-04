<?php
namespace App\Table;
use App\Core\Table\Table;

class TourneeTable extends Table{


    protected $table = 'tournee';

    /**
     * Retourne les points de collecte
     * @return array tableau
     */
    public function allTable(){
      return $this->query('SELECT t.id_tournee, t.secteur, c.designation, v.marque, t.qte_realise, equipe
      FROM "public".tournee t, "public".vehicule v, "public".cet c
      WHERE v.code = t.vehicle and c.code = t.cet
      ');
    }

    /**
     * @return array
     */
    public function findWithId($id){
        return $this->query('SELECT *
        FROM "public".tournee
        WHERE id_tournee = ?'
        , [$id]);
    }
    
  }

 ?>
