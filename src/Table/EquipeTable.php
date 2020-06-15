<?php
namespace App\Table;
use App\Core\Table\Table;

class EquipeTable extends Table{


    protected $table = 'equipe';

    /**
     * Retourne toutes les equipes
     * @return array tableau
     */
    public function all(){
      return $this->query('SELECT id
      FROM "public".equipe
      ');
    }
  }

 ?>
