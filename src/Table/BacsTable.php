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
      $qte = $this->query('SELECT secteur, sum(volume) As qte
      FROM "public".bacs
      GROUP BY secteur 
      order by secteur
      ');
      $values=[];
      $secteurs = [];
      for($i = 0;$i<sizeof($qte); $i++) {
        $values[$i] = $qte[$i]['qte'];
        $secteurs[$i] = $qte[$i]['secteur'];
      }
      $val = implode(",", $values);
      $label = implode(',',$secteurs);

      return $val.'+'.$label ;
    }

    
    /**
     * Retourne le nombre de tournées effectuées pour chaque vehicule
     * @return array 
     */
    public function tourneevehicle(){
      $tv = $this->query('SELECT vehicle, count(*) as nbt 
      FROM tournee 
      GROUP BY vehicle
      order by vehicle
      ');
      $v=[];
      $nb=[];
      for($i=0;$i<sizeof($tv);$i++){
          $v[$i]=$tv[$i]['vehicle'];
          $nb[$i]=$tv[$i]['nbt'];
      }
      $vs = implode(',',$v);
      $nbs = implode(',',$nb);
      $vs = $nbs.'+'.$vs;
      return $vs;
    }

    /**
     * Retourne la totalité de la distance parcourue pour chaque vehicule
     * @return array
     */
    public function distancevehicle(){
      $dv = $this->query('SELECT vehicle, sum(kilometrage) as distance 
      FROM tournee 
      GROUP BY vehicle
      order by vehicle
      ');
      $d = [];
      $v = [];
      for($i = 0;$i<sizeof($dv); $i++){
        $d[$i] = $dv[$i]['distance'];
        $v[$i] = $dv[$i]['vehicle'];
      }
      $distance = implode(',',$d);
      $vehicules = implode(',',$v);
      return $distance.'+'.$vehicules;
    }

    /**
     * Retourne le temps d'activité des vehicules
     * @return array
     */
    public function tempvehicle(){
      $tpv =  $this->query('SELECT vehicle, sum(temps_travail) as temp
      FROM tournee 
      GROUP BY vehicle
      order by vehicle
      ');
      $t = [];
      $v = [];
      for($i = 0;$i<sizeof($tpv); $i++){
        $t[$i] = $tpv[$i]['temp'];
        $v[$i] = $tpv[$i]['vehicle'];
      }
      $temps = implode(',',$t);
      $vehicules = implode(',',$v);
      return $temps.'+'.$vehicules;
    }
    /**
     * Retourne le nombre de tournées effectuées pour chaque equipe
     * @return array
     */
    public function tourneeequipe(){
      $var = $this->query('SELECT equipe, count(*) as nbt
      FROM tournee 
      GROUP BY equipe
      order by equipe
      ');
       $nbt = [];
       $e = [];
       for($i = 0;$i<sizeof($var); $i++){
         $nbt[$i] = $var[$i]['nbt'];
         $e[$i] = $var[$i]['equipe'];
       }
       $nbtr = implode(',',$nbt);
       $equipe = implode(',',$e);
       return $nbtr.'+'.$equipe;
      }

    /**
     * Retourne le temps d'activité des vehicules
     * @return array
     */
    public function tempsequipe(){
      $var = $this->query('SELECT equipe, sum(temps_travail) as data
      FROM tournee 
      GROUP BY equipe
      order by equipe
      ');
      $data = [];
      $label = [];
      for($i = 0;$i<sizeof($var); $i++){
        $data[$i] = $var[$i]['data'];
        $label[$i] = $var[$i]['equipe'];
      }
      $data1 = implode(',',$data);
      $label2 = implode(',',$label);
      return $data1.'+'.$label2;
    }

    /**
     * Retourne la quantité realisé pour chaque vehicule
     * @return array
     */
    public function qtevehicle(){
      $var = $this->query('SELECT vehicle, sum(qte_realise) as data
      FROM tournee 
      GROUP BY vehicle
      order by vehicle
      ');
      $data = [];
      $label = [];
      for($i = 0;$i<sizeof($var); $i++){
        $data[$i] = $var[$i]['data'];
        $label[$i] = $var[$i]['vehicle'];
      }
      $data1 = implode(',',$data);
      $label2 = implode(',',$label);
      $dr = $data1.'+'.$label2;
      return $dr;
    }

    /**
     * Retourne la quantitée de dechets rammasée selon les mois de l'année actuel 
     * @return array
     */
    public function qtemois(){
      $var = $this->query('SELECT  extract(month FROM date)as mois, sum(qte_realise) as data
      FROM tournee 
      WHERE date_trunc(\'year\', date) = date_trunc(\'year\', current_date) 
      GROUP BY mois
      ORDER BY mois');
      $data = [];
      $label = [];
      for($i = 0;$i<sizeof($var); $i++){
        $data[$i] = $var[$i]['data'];
        $label[$i] = $var[$i]['mois'];
      }
      $data1 = implode(',',$data);
      $label2 = implode(',',$label);
      return $data1.'+'.$label2;
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
