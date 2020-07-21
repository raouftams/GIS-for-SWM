<?php
namespace App\Core\Database;

use PDO;

class PostgresDatabase extends Database{

  private $db_name;
  private $db_user;
  private $db_pass;
  private $db_host;
  private $pdo;



  public function __construct($db_name, $db_user = 'postgres', $db_pass ='' ,$db_host = '127.0.0.1'){
    $this->$db_name = $db_name;
    $this->$db_user = $db_user;
    $this->$db_pass = $db_pass;
    $this->$db_host = $db_host;
  }


  public function getPDO()
  {
    if ($this->pdo === null) {
      $pdo = new PDO('pgsql:dbname=babezsig;host=127.0.0.1', 'postgres','admin');
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $this->pdo = $pdo;
    }
    return $this->pdo;
  }

  public function query($statment, $class_name = null, $one = false)
  {
    $req = $this->getPDO()->query($statment);
    if(
      strpos($statment, 'UPDATE') === 0 ||
      strpos($statment, 'INSERT') === 0 ||
      strpos($statment, 'DELETE') === 0 ) {
      return $req;
    }
    if ($class_name === null) {
      $req->setFetchMode(PDO::FETCH_OBJ);
    }
    else{
      $req->setFetchMode(PDO::FETCH_CLASS, $class_name);
    }
    if($one){
      $data = $req->fetch();
    }else{
      $data = $req->fetchAll();
    }
    return $data;

  }


  public function prepare($statment, $attributes, $class_name = null, $one=false)
  {
    $req = $this->getPDO()->prepare($statment);
    $res = $req->execute($attributes);
    if (
      strpos($statment, 'UPDATE') === 0||
      strpos($statment, 'INSERT') === 0||
      strpos($statment, 'DELETE') === 0
    ) {
      return $res;
    }
    if ($class_name === null) {
      $req->setFetchMode(PDO::FETCH_OBJ);
    }
    else{
      $req->setFetchMode(PDO::FETCH_CLASS, $class_name);
    }
    if($one){
      $data = $req->fetch();
    }else{
      $data = $req->fetchAll();
    }
    return $data;
  }

  public function lastInsertId(){
    return $this->getPDO()->lastInsertId();
  }






}

 ?>
