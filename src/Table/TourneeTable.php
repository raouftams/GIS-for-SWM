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
      WHERE v.code = t.vehicle and c.code = t.cet order by t.date desc
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

    /**
     * @return boolean 
     */
    public function ajouter($fields){
        return $this->create($fields);
    }

    /**
     * update tournee
     * @return boolean 
     */

    public function edit($id, $fields){
      $sql_parts = [];
      $attributes = [];
      foreach ($fields as $k => $v) {
        $sql_parts[] = "$k = ?";
        $attributes[] = $v;
      }
      $attributes[] = $id;
      $sql_part = implode(',', $sql_parts);
      return $this->query("UPDATE {$this->table} SET $sql_part WHERE id_tournee = ?", $attributes, true);
    }


    public function delete($id){
      return $this->query("DELETE FROM {$this->table} WHERE id_tournee = ?", [$id], true);
    }
    
    /**
     * @return array
     * retourne le parc d'une tournée
     */
    public function getParc(){
      return $this->query('SELECT *, ST_AsGeoJson(geom, 5) as geojson
      FROM "public".parc');
    }

    /**
     * @return array
     * retourne le cet visité lors d'un tournée donnée
     */
    public function getCet($id){
        return $this->query('SELECT *, ST_AsGeoJson(geom,5) as geojson
        FROM "public".cet 
        WHERE code in 
            (SELECT cet 
            FROM "public".tournee 
            WHERE id_tournee = ?)',
            [$id]);
    }

    /**
	 * @return array
	 * retourne les tournees en attente
	 */
	public function getTourneesEnAttente(){
		return $this->query('SELECT  t.secteur, c.designation, v.marque, t.qte_prevue, heure_demarrage_parc, equipe, t.vehicle, t.date
    FROM "public".tournee t, "public".vehicule v, "public".cet c
    WHERE v.code = t.vehicle and c.code = t.cet and (t.date > current_date or (t.date = current_date and t.heure_demarrage_parc > current_time ) )');
  }
  
  /**
   * @return array
   * retourne les tournees en cours
   */
  public function nbtourneesEnCours(){
      return $this->query('SELECT count(*) 
      FROM "public".tournee t 
      WHERE t.date = current_date and t.heure_demarrage_parc <= current_time and t.heure_pesee is Null
      ');
  }


  /**
   * @return array
   * retourne les tournées d'une equipe donnée
   */
  public function getTourneesEquipe($id){
    return $this->query('SELECT t.id_tournee, t.secteur, c.designation, v.marque, v.volume, t.qte_prevue, t.heure_demarrage_parc, t.date
      FROM "public".tournee t, "public".vehicule v, "public".cet c
      WHERE v.code = t.vehicle and c.code = t.cet and t.equipe = ? and t.date >= current_date
      ',[$id]);
  }


  /**
   * @return int
   * retourne le nombre de tournées effectuer
   */
  public function nbTournee(){
    return $this->query('SELECT count(*) 
    FROM "public".tournee t
    where t.date < current_date or (t.date = current_date and t.heure_demarrage_parc < current_time )');
  }




  }

 ?>
