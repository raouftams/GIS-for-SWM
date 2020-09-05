<?php

namespace App\Controller\User;

use App\Controller\AppController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class BilanController extends AbstractController{
    public function __construct(){
        // parent::__construct();
         $this->app = new AppController();
         $this->app->loadModel('Tournee');
         $this->app->loadModel('Secteur');
         
    }

    /**
    * @Route("/dashboard/Bilan", name="Bilan")
    */
    public function Bilan(){
        $rotationsIncompletes = $this->app->Tournee->tourneeIcompletes();
        $rotations = $this->app->Tournee->tourneeMoisActuel();
        return $this->render('public/Bilan.html.twig',['Tournees'=>$rotations, 'tIncompletes'=>$rotationsIncompletes]);
    }

    /**
    * @Route("/dashboard/Bilan/QteRealiseEtPrevue", name="BilanQteParMois")
    */
    public function qteParMois(){
        $qtePrevue = $qteRealisee = [];
        $qtemois = $this->app->Tournee->qteRealiseEtPrevue();
        foreach ($qtemois as $key => $value) {
            unset($qtemois[$key][0]);
            unset($qtemois[$key][1]);
            array_push($qtePrevue, ["label" => $qtemois[$key]["label"], "data" => floatval($qtemois[$key]["prevuedata"])]);
            array_push($qteRealisee, ["label" => $qtemois[$key]["label"], "data" => floatval($qtemois[$key]["realisedata"])]);
        }
        return new Response(json_encode(["qtePrevue"=>$qtePrevue, "qteRealisee"=>$qteRealisee]));
    }  
    
    /**
     * @Route("dasboard/Bilan/TauxDuMois", name="BilanTauxMois")
     */
    public function tauxMois(){
        $data=$datap=$datar = [];
        $tauxRealisee = $this->app->Tournee->tauxRealisees();
        for( $i = 0; $i<count($tauxRealisee[0])/2; $i++){
            array_push($data,floatval($tauxRealisee[0][$i]));
        }
        for( $i = 0; $i<count($data)-1; $i+=2){
            array_push($datap, $data[$i]);
            array_push($datar, $data[$i+1]);
        }
        return new Response(json_encode(["datar"=>$datar, "datap"=>$datap]));
    }

    /**
    * @Route("dashboard/Bilan/QtesParSecteur", name="BilanQtesParSecteur")
    */
    public function qtesParSecteur(){
        $qtePrevue=$qteRealisee = $Secteurs =[];
        $qtesSecteurs = $this->app->Tournee->qtesParSEcteurs();
        foreach($qtesSecteurs as $key => $value){
            unset($qtesSecteurs[$key][0]);
            unset($qtesSecteurs[$key][1]);
            unset($qtesSecteurs[$key][2]);
            array_push($Secteurs, ["label"=> $qtesSecteurs[$key]["labels"],"data"=> $qtesSecteurs[$key]["labels"]]);
            array_push($qtePrevue, ["label" => $qtesSecteurs[$key]["labels"],"data" => floatval($qtesSecteurs[$key]["prevue"])]);
            array_push($qteRealisee, ["label" => $qtesSecteurs[$key]["labels"],"data" => floatval($qtesSecteurs[$key]["realise"])]);
        }
        return new Response(json_encode(["secteurs" => $Secteurs,"qtePrevue2"=>$qtePrevue, "qteRealisee2"=>$qteRealisee]));
    }

    //Tableau de d'info pour les rotation du mois


    /**
     * @Route("dashboard/Bilan/Historiques" , name = "Historique")
     */
    public function Historique(){
        $tableHistorique =$this->app->Tournee->historique();
        
        return $this->render('public/Historique.html.twig',['table'=>$tableHistorique]);
    }
    
    /**
     * @Route("dashboard/Bilan/AttachementBilan", name = "AttachementBilan")
     */
    public function Attachement(){
        // la quantité depuis les ticket de pesé 
        // delimité par deux dates
        return $this->render('public/Attachement.html.twig');
    }
}
