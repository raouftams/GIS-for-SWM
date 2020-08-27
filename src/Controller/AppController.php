<?php

  namespace App\Controller;

  use App\Core\Controller\Controller;
  use App\APP;
  class AppController{


   /* public function __construct(){
        $this->viewPath = ROOT . '/app/Views/';
    }
*/

    public $token = 'Dzfk8NXqZp6GLD46i4ZrbL4STOWfkz0hHYeZVPd94MsV2DqVfe6IdOWOAMuU0dFPIOjWeXY-zSU16O3xKmKdQBpej9H93O-AbeR2gJ0r_TJcnprcqwksM4-OTdMOmoxOS-N-tSqMpbgArI62ZgJLGQ..';


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
