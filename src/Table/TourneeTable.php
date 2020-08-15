<?php
namespace App\Table;
use App\Core\Table\Table;
use Symfony\Component\VarDumper\Exception\ThrowingCasterException;

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
     * Retourne le nombre de tournées effectuées pour chaque vehicule
     * @return array 
     */
    public function tourneevehicle(){
      return $this->query('SELECT vehicle AS label, count(*) as data 
      FROM "public".tournee 
      GROUP BY label
      order by label
      ');
    }

    /**
     * Retourne la totalité de la distance parcourue pour chaque vehicule
     * @return array
     */
    public function distancevehicle(){
      return $this->query('SELECT vehicle AS label , sum(kilometrage) as data 
      FROM "public".tournee 
      GROUP BY label
      order by label
      ');
      
    }

    /**
     * Retourne le temps d'activité des vehicules
     * @return array
     */
    public function tempvehicle(){
      return $this->query('SELECT vehicle as label, sum(temps_travail) as data
      FROM "public".tournee 
      GROUP BY label
      order by label
      ');
    }
    /**
     * Retourne le nombre de tournées effectuées pour chaque equipe
     * @return array
     */
    public function tourneeequipe(){
      return $this->query('SELECT equipe as label, count(*) as data
      FROM "public".tournee 
      GROUP BY label
      order by label
      ');
      }

    /**
     * Retourne le temps d'activité des vehicules
     * @return array
     */
    public function tempsequipe(){
      return $this->query('SELECT equipe as label, sum(temps_travail) as data
      FROM "public".tournee 
      GROUP BY label
      order by label
      ');
    }

    /**
     * Retourne la quantité realisé pour chaque vehicule
     * @return array
     */
    public function qtevehicle(){
      return $this->query('SELECT vehicle as label, sum(qte_realise) as data
      FROM "public".tournee 
      GROUP BY label
      order by label
      ');
    }

    /**
     * Retourne la quantitée de dechets rammasée selon les mois de l'année actuel 
     * @return array
     */
    public function qtemois(){
      return $this->query('SELECT  extract(month FROM date) AS label , sum(qte_realise) as data
      FROM "public".tournee 
      WHERE date_trunc(\'year\', date) = date_trunc(\'year\', current_date) 
      GROUP BY label
      ORDER BY label');
    }   

    /**
     * Retourne la quantité de dechets prévue et réalisée
     * @return array
     */
    public function qteRealiseEtPrevue(){
      return $this->query('SELECT  extract(month FROM date) AS label , sum(qte_realise) as realiseData, sum(qte_prevue) as prevueData
      FROM "public".tournee 
      WHERE date_trunc(\'year\', date) = date_trunc(\'year\', current_date) and qte_realise is not null 
      GROUP BY label
      ORDER BY label');
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
        $ajout = $this->create($fields);
        return $this->query('SELECT max(id_tournee) from "public".tournee');
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
	return $this->query('SELECT t.id_tournee,  t.secteur, c.designation, v.marque, t.qte_prevue, heure_demarrage_parc, equipe, t.vehicle, t.date
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

  /**
   * @return array
   * retourne le bon de transport d'une tournée donnée
   */
  public function getBonTransport($id){
    return $this->query('SELECT * FROM "public".bon_transport WHERE tournee = ?',[$id]);
  }

  /**
   * @return array
   * retourne le ticket de pesée d'une tournée donnée
   */
  public function getTicketPesee($id){
    return $this->query('SELECT * FROM "public".ticket_pesee WHERE tournee = ?',[$id]);
  }

  /**
   * @return boolean
   * Initialisation du bon de transport et du ticket de pesée
   */
  public function initialiser($tournee){
    $tournee = $this->query('SELECT id_tournee, "date", heure_demarrage_parc, vehicle, secteur, equipe, cet 
    FROM "public".tournee
    WHERE id_tournee = ?', [$tournee]);
    $tournee = $tournee[0];
    $bon = [
      "code" => "BTR-".$tournee["id_tournee"],
      "date" => $tournee["date"],
      "heure_debut" => $tournee["heure_demarrage_parc"],
      "vehicule" => $tournee["vehicle"],
      "heure_fin" => null,
      "equipe" => $tournee["equipe"],
      "tournee" => $tournee["id_tournee"],
      "secteur" => $tournee["secteur"]
    ];
    $ajout = $this->addBonTicket("bon_transport", $bon);

    $ticket = [
      "code" => "TP-".$tournee["id_tournee"],
      "cet" => null,
      "date_ticket" => null,
      "heure_ticket" => null,
      "poids_brute" => null,
      "tare" => null,
      "poids_net" => null,
      "taux_compaction" => null,
      "tournee" => $tournee["id_tournee"]
    ];

    return $this->addBonTicket("ticket_pesee", $ticket);

  }

  private function addBonTicket($table, $fields){
    $sql_parts = [];
		$attributes = [];
		foreach ($fields as $k => $v) {
			$sql_parts[] = "?";
			$attributes[] = $v;
		}
		$sql_part = implode(',', $sql_parts);
		return $this->query("INSERT INTO {$table} Values ($sql_part)", $attributes, true);
  }

	public function updateBonTransport($tournee, $bonData){
		$sql_parts = [];
		$attributes = [];
		foreach ($bonData as $k => $v) {
			$sql_parts[] = "$k = ?";
			$attributes[] = $v;
		}
		$attributes[] = $tournee;
		$sql_part = implode(',', $sql_parts);
		return $this->query("UPDATE bon_transport SET $sql_part WHERE tournee = ?", $attributes, true);
	}

	public function updateTicketPesee($tournee, $ticketData){
		$sql_parts = [];
		$attributes = [];
		foreach ($ticketData as $k => $v) {
			$sql_parts[] = "$k = ?";
			$attributes[] = $v;
		}
		$attributes[] = $tournee;
		$sql_part = implode(',', $sql_parts);
		return $this->query("UPDATE ticket_pesee SET $sql_part WHERE tournee = ?", $attributes, true);
	}

  }

 ?>
