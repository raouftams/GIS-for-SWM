<?php

  namespace App\Controller;

  use App\Core\Controller\Controller;
  use App\APP;
  class AppController{


   /* public function __construct(){
        $this->viewPath = ROOT . '/app/Views/';
    }
*/

    public $token = '-NlEcmXYz_jXOv4da7sEooEJeTF0zlHnp0BFy2Z8pDbsKn_3pGJDVoSJ4keR0u5Ot0Jay7w8slvxCW7qDET271DWZWraPlAMQvFS2JYsNvARCdMb03LX9KtTD4syr5jSPBYBrbB3rfHt56ol5ZaS9g..';


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
