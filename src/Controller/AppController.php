<?php

  namespace App\Controller;

  use App\Core\Controller\Controller;
  use App\APP;
  class AppController{


   /* public function __construct(){
        $this->viewPath = ROOT . '/app/Views/';
    }
*/
    public function loadModel($model_name){
      $this->$model_name =  APP::getInstance()->getTable($model_name);
    }

    public function forbidden(){
        header('HTTP/1.0 403 Forbidden');
        die('Acces interdit');
      }
  
    public function notFound(){
        header('HTTP/1.0 404 Not Found');
        die('Page introuvable');
      }
  }



 ?>