<?php

namespace App\Controller\User;

use App\Controller\AppController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class EmployeController extends AbstractController{


  public function __construct(){
   // parent::__construct();
    $this->app = new AppController();
    $this->app->loadModel('Employe');
  }


    /**
      * @Route("/dashboard/employes", name="employesIndex")
    */    
    public function index(){
      $employes = $this->app->Employe->allTable();
      return $this->render('public/employe.html.twig',[
        "employes" => $employes
      ]);        
    }

    /**
      * @Route("/dashboard/employes/addEmploye", name="addEmploye")
    */    
    public function addEmploye(){
      $result = null;
        if(!empty($_POST)){
          $employe = $this->app->Employe->create([
            "matricule" => $_POST["matricule"],
            "nom" => $_POST["nom"],
            "prenom" => $_POST["prenom"],
            "adresse" => $_POST["adresse"],
            "date_naissance" => $_POST["date_naissance"],
            "telephone" => $_POST["telephone"],
            "grade" => $_POST["grade"],
            "fonction" => $_POST["fonction"],
            "debut_contrat" => $_POST["debut_contrat"],
            "fin_contrat" => $_POST["fin_contrat"],
            "heure_pause" => $_POST["heure_pause"],            
            "equipe" => intval($_POST["equipe"]),
            "debut_conge" => $_POST["debut_conge"],
            "fin_conge" => $_POST["fin_conge"]
          ]);
          
          $result = "Employé ajouté avec succes.";
        }   
        return $this->render('admin/employes/add.html.twig',["result"=>$result]);   
    }

    /**
      * @Route("/dashboard/employes/editEmploye/{matricule}", methods={"POST","GET"}, name="editEmploye")
    */    
    public function editEmploye($matricule){
      $result = null;
      $employe = $this->app->Employe->find($matricule)[0];
        if(!empty($_POST)){
          $e = $this->app->Employe->edit($matricule, [
            "matricule" => $_POST["matricule"],
            "nom" => $_POST["nom"],
            "prenom" => $_POST["prenom"],
            "adresse" => $_POST["adresse"],
            "date_naissance" => $_POST["date_naissance"],
            "telephone" => $_POST["telephone"],
            "grade" => $_POST["grade"],
            "fonction" => $_POST["fonction"],
            "debut_contrat" => $_POST["debut_contrat"],
            "fin_contrat" => $_POST["fin_contrat"],
            "heure_pause" => $_POST["heure_pause"],            
            "equipe" => intval($_POST["equipe"]),
            "debut_conge" => $_POST["debut_conge"],
            "fin_conge" => $_POST["fin_conge"]
          ]);
          
          $result = "Employé modifé avec succes.";
        }   
        return $this->render('admin/employes/edit.html.twig',["result"=>$result, "employe"=>$employe]);   
    }
    
}