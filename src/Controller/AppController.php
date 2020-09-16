<?php

  namespace App\Controller;

  use App\Core\Controller\Controller;
  use App\APP;
  class AppController{


   /* public function __construct(){
        $this->viewPath = ROOT . '/app/Views/';
    }
*/

    public $token = '6ybx0HWFjxxTha-UawQwGl5SD8pfpxmt0io61ZMWkYlTw-CrAS_LakB1ncCz3goqSiJ05EB_ml_bzbs-uBe8dQ12nsrHrRT8g_oBPXoQRbrxPkQzlvUx44oKC-VxdiThVcKlt17rEIszBOrTppkiUQ..';


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
