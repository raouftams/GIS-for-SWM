<?php

namespace App\Controller\User;

use App\Controller\AppController;
use Exception;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
          'typedechet' => $_POST['nature_dechet'],
          'pointc' => $_POST['point']
        ]);
        if ($result){
          return $this->render('admin/bacs/add.html.twig',[
            "result" => "Bac inséré avec succès.",
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
     * @Route("/admin/edit-map-bac", name="editMapBac")
     */
    public function editFromMap(Request $request){

      $data = json_decode($request->getContent(), true);
      
      $code = intval($data['code_bac']);
      $volume = $data['volume'];
      $nature = $data['nature'];
      $taux_remplissage = $data['taux_remplissage'];
      $date = $data['date'];
      $etat = $data['etat'];
      $nature_dechet = $data['nature_dechet'];
      
        $bac = $this->app->Bacs->edit($code, [
          'volume' => intval($volume),
          'dateinstal' => $date,
          'tauxrempli' => floatval($taux_remplissage),
          'typemat' => $nature,
          'etat' => $etat,
          'typedechet' => $nature_dechet
        ]);
      
      return new Response("Bac modifié avec succes");
                 
  }

    /**
     * @Route("/user/edit-tournee-bac", name="editTourneeBac")
     */
    public function editFromTournee(Request $request){
      $data = json_decode($request->getContent(), true);
      $code = intval($data['code_bac']);
      $taux_remplissage = $data['taux_remplissage'];
      $etat = $data['etat'];
      
        $bac = $this->app->Bacs->edit($code, [
          'tauxrempli' => floatval($taux_remplissage),
          'etat' => $etat
        ]);
      
      return new Response("Bac n°" + $code + "modifé avec succes");
    }

    /**
     * @Route("/admin/delete-bac/{code}", methods={"POST", "GET"}, name="deleteBac")
     */
    public function delete($code){
      $result = $this->app->Bacs->delete($code);
      return $this->index();
      
    }
}