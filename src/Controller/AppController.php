<?php

  namespace App\Controller;

  use App\Core\Controller\Controller;
  use App\APP;
  class AppController{


   /* public function __construct(){
        $this->viewPath = ROOT . '/app/Views/';
    }
*/

    public $token = 'rf7C59mzilwT2iREmX3XZR3O46y8kJEiwhpYqb-FPOSlaSzs1eoFtEIv2G-e0-cI8xpI2U5EvFBCdA82yj0L_EJigvBAfOUWR9Ba2E9mKHvji5QRUj45a7vA_J3MVVvxDRkDsnVz4b_wraf_RiRIXg..';


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
