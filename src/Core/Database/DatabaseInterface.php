<?php

namespace App\Core\Database;

Interface DatabaseInterface{

    public function prepare($statment, $attributes, $class_name, $one);
    public function query($statment, $class_name, $one);
}