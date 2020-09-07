<?php

  namespace App\Controller;

  use App\Core\Controller\Controller;
  use App\APP;
  class AppController{


   /* public function __construct(){
        $this->viewPath = ROOT . '/app/Views/';
    }
*/

    public $token = 'qfl4_kGgZn1lvp-iZy0jtxtdc6ItniioW7rR2DRodOpZPQDJ4BQo-FedHTDxBuUrmRm06SZJ4HvLrZBQmc3OzBo0vSbwwWAY4UY4CquDDPXdBQxSFFTcAtjr6xnxnf8AfRG4xA45KQhD8KRgGChS5Q..';


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
