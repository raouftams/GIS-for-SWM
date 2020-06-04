<?php

namespace App\Controller\User;

use App\Controller\AppController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BacController extends AbstractController{


  public function __construct(){
   // parent::__construct();
    $this->app = new AppController();
    $this->app->loadModel('Bacs');
  }


    /**
      * @Route("/dashboard/bacs", name="bacsIndex")
    */    
    public function index(){
      $bacs = $this->app->Bacs->all();
      return $this->render('public/bacs.html.twig',[
        "bacs" => $bacs
      ]);        
    }

    /**
     * @Route("/admin/add-bac", methods={"POST","GET"}, name="addBac")
     */
    public function Add(){
      if (!empty($_POST)) {
        $result = $this->app->Bacs->add([
          'volume' => (int)$_POST['volume'],
          'dateinstal' => $_POST['date'],
          'adresse' => $_POST['adresse'],
          'typemat' => $_POST['nature'],
          'etat' => $_POST['etat'],
          'typedechet' => $_POST['nature_dechet']
        ]);
        if ($result){
          return $this->render('admin/bacs/add.html.twig',[
            "result" => "Bac inséré.",
            "bac" => null
          ]);
        }
      }
      return $this->render('admin/bacs/add.html.twig',[
        "bac" => null,
        "result" => null
      ]);
    }

    /**
     * @Route("/admin/edit-bac/{code}", methods={"POST","GET"}, name="editBac")
     */
    public function edit($code){
        if (!empty($_POST)) {
            $bac = $this->app->Bacs->edit($code, [
                'volume' => (int)$_POST['volume'],
                'dateinstal' => $_POST['date'],
                'adresse' => $_POST['adresse'],
                'typemat' => $_POST['nature'],
                'etat' => $_POST['etat'],
                'typedechet' => $_POST['nature_dechet']
            ]);
            return $this->index();
        }
        $bac = $this->app->Bacs->getBac($code);
        $bac = $bac[0];
        return $this->render('admin/bacs/edit.html.twig', [
            "result" => null,
            "bac" => $bac
        ]);
    }

    /**
     * @Route("/admin/delete-bac/{code}", methods={"POST", "GET"}, name="deleteBac")
     */
    public function delete($code){
      $result = $this->app->Bacs->delete($code);
      return $this->index();
      
    }
}