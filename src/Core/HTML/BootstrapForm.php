<?php
  namespace App\Core\HTML;
  /**
   *
   */
  class BootstrapForm extends Form{


    /**
     * Permet d'entourer les schamps par une tag html
     * @param  $html Le code à entrourer
     * @return string
     */
    protected function surround($html){
      return "<div class=\"form-group\">{$html}</div>";
    }



    public function input($name, $label, $options = []){
      $type = isset($options['type']) ? $options['type'] : 'text';
      $label = '<label>' . $label. ': </label>';
      if ($type === 'textarea') {
        $input = '<textarea class="form-control" name="' . $name . '">'  . $this->getValue($name) . '</textarea>';
      }else {
        $input = '<input class="form-control" type="' . $type . '" name="' . $name . '"  value="' . $this->getValue($name) . '">';
      }

      return $this->surround($label . $input);
    }

    public function select($name, $label, $options = []){
      $label = '<label>' . $label. ': </label>';
      $input = '<select class="form-control" name="' . $name . '">';
      foreach($options as $k => $v){
        $attributes = '';
        if($k == $this->getValue($name)){
          $attributes = 'selected';
        }
        $input .= "<option value='$k'$attributes>$v</option>";
      }
      $input .= '</select>';
      return $this->surround($label . $input);
    }

    /**
     *
     * @return string
     */

    public function submit(){
      return '<button type="submit" class="btn btn-primary"> Envoyer</button>';
    }

  }


 ?>
