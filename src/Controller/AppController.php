<?php

  namespace App\Controller;

  use App\Core\Controller\Controller;
  use App\APP;
  class AppController{


   /* public function __construct(){
        $this->viewPath = ROOT . '/app/Views/';
    }
*/

    public $token = 'PCvxZ6ppAvy7L2y99MX4l-yWhl3dQNqO8huLmq_OmXdk1QUZ-mFxDXHP9OL-HQ50yXKL7ynLHj6lnkXoGHEL29TkI8K0RO9c-2SG8vZiQwWFj86V99u_c8KUEzQwkeUKYJ87yN1RCYeknQgKNMX4yQ..';


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
