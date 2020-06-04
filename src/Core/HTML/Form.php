<?php

  namespace App\Core\HTML;

  /**
  * class Form
  * Permet de générer un formulaire
  */
  class Form{

    /**
     * @var array Données utilisées par le formulaire
    */
    protected $data;

    /**
     * @var string Tag utilisé pour entourer les champs
     */
    public $surround = 'p';

    /**
     * @param array $data Données utilisées par le formulaire
     */
    public function __construct($data = array()){
      $this->data = $data;
    }

    /**
     * Permet d'entourer les schamps par une tag html
     * @param  $html Le code à entrourer
     * @return
     */
    protected function surround($html){
      return "<{$this->surround}>{$html}</{$this->surround}";
    }

    protected function getValue($index){
      if (is_object($this->data)) {
        return $this->data->$index;
      }
      return isset($this->data[$index]) ? $this->data[$index]: null;
    }

    public function input($name, $label, $options = []){
      $type = isset($options['type']) ? $options['type'] : 'text';
      return $this->surround(
        '<input type="' . $type . '" name="' . $name . '" placeholder="' . $name . '" value="' . $this->getValue($name) . '"></br>');
    }

    public function submit(){
      return $this->surround('<button type="submit"> Envoyer</button>');
    }

  }


 ?>
