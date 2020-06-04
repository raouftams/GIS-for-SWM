<?php
namespace App\Core\Auth;
use App\Core\Database\Database;

  class DbAuth{

    public function __construct(Database $db){
        $this->db = $db;
    }


    public function getUserId(){
      if($this->logged()){
        return $_SESSION['auth'];
      }
      return false;
    }

    /**
     *
     * @param  $username
     * @param  $password
     * @return boolean
     */
    public function login($username, $password){
        $_SESSION['auth'] = 1;
        return true;
        
    }

    public function logged(){
      return isset($_SESSION['auth']);
    }
  }

 ?>
