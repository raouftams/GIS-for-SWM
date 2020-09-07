<?php
namespace App\Table;
use App\Core\Table\Table;

class ticketPeseeTable extends Table{
    protected $table = 'ticket_pesee';

    /**
     * Retourn les ticket des pesée entre les deux dates
     * @return array 
     */
    public function getTicket($cet, $oldDate, $newDate){
        return $this->query('SELECT date_pesee AS date, heure_pesee AS heure, tournee, cet, poids_net, montant
        FROM ticket_pesee
        where cet = ? and date_pesee between ? and ?
        ORDER BY date_pesee, heure_pesee, cet',$cet,$oldDate,$newDate);
    }

    /**
     * @return array
     */
    public function getAll(){
        return $this->query('SELECT * FROM ticket_pesee');
    }
}
?>