<?php

  namespace App\Controller;

  use App\Core\Controller\Controller;
  use App\APP;
  class AppController{


   /* public function __construct(){
        $this->viewPath = ROOT . '/app/Views/';
    }
*/

    public $token = 'SB_nOgUBThOOu0G_YRrLWwa5HXlIUcJDKuB9bvrQ2jEEEzx2NhEQkClwrvtB6dfQ_rjiuDjA1vz2DM4c08VRryWyr2zMCoqETaXolWZ9hfclEyX-hNBz8bDz1eSb6Q9f3b4z-KraOlJola7005JRfS3jOtX4NTuJbqrF1NKPtqOZNccylQnMGeNsr_tH7r2wbzn5T7_PLy1zEWpyBqtpVBqWNICLTQ-M7X8EmGIfd3k.';


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
