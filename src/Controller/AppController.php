<?php

  namespace App\Controller;

  use App\Core\Controller\Controller;
  use App\APP;
  class AppController{


   /* public function __construct(){
        $this->viewPath = ROOT . '/app/Views/';
    }
*/

    public $token = 'gTu9K0Z821wjQo8Pa3eE1yi53Cc_usE0qRlMaEuQa8cEfwghl-Z2VP3Fh1fW2ZTUXTihYXJ6vlOqhRGqpDGeajSKsRxq0RpYmizcvEGd40ddS281c0SIzeJ0IRk1uT-gp501ALYFDPeQnXWWw0vHRA..';


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
