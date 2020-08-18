<?php

  namespace App\Controller;

  use App\Core\Controller\Controller;
  use App\APP;
  class AppController{


   /* public function __construct(){
        $this->viewPath = ROOT . '/app/Views/';
    }
*/

    public $token = '5CkibKIiSdaC8TlEZC8-ssedZkbSEJ3-TZDPbWGDaFfn4qnrC4y8BBgBdePJVp1uJ1Q1kowLaNctLQqCxJEJV0btyRel-pI9AIVxXCSFLGA9mh0vSdCv5f9LI3ACv_ywueJcsosc2qWv0XU8lSYXeyiNC1JQw3el2En8bdL6F0O_W_vK7AP1yogOSrZqZSJuWIYuqgCm9So7QqTU2olMQUxFGAnf1GXQH55GvGHAVOA.';


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
