<?php

  namespace App\Core\Table;

  use App\Core\Database\PostgresDatabase;

  class Table{

    protected $table;
    protected $db;

    public function __construct(PostgresDatabase $db){
      $this->db = $db;
      if (is_null($this->table)) {
        $parts = explode('\\', get_class($this));
        $class_name = end($parts);
        $this->table = strtolower(str_replace('Table', '', $class_name)) . 's';
      }
    }

    public function all(){
      return $this->query('SELECT * FROM ' . $this->table);
    }

    public function find($id){
      return $this->query('SELECT * FROM ' . $this->table . ' WHERE code = ?', [$id], true);
    }

    public function update($code, $fields){
      $sql_parts = [];
      $attributes = [];
      foreach ($fields as $k => $v) {
        $sql_parts[] = "$k = ?";
        $attributes[] = $v;
      }
      $attributes[] = $code;
      $sql_part = implode(',', $sql_parts);
      return $this->query("UPDATE {$this->table} SET $sql_part WHERE code = ?", $attributes, true);
    }

    public function extract($key, $value){
      $records = $this->all();
      $return = [];
      foreach ($records as $k => $v) {
        $return[$v->$key] = $v->$value;
      }
      return $return;
    }

    public function create($fields){
		$sql_parts = [];
		$attributes = [];
		foreach ($fields as $k => $v) {
			$sql_parts[] = "?";
			$attributes[] = $v;
		}
		$sql_part = implode(',', $sql_parts);
		return $this->query("INSERT INTO {$this->table} Values ($sql_part)", $attributes, true);
    }

    public function delete($id){
      return $this->query("DELETE FROM {$this->table} WHERE code = ?", [$id], true);
    }


    public function query($statment, $attributes = null, $one = false){
      if ($attributes) {
        return $this->db->prepare(
          $statment,
          $attributes,
          str_replace('Table', 'Entity', get_class($this)),
          $one);
      }else{
        return $this->db->query(
          $statment,
          str_replace('Table', 'Entity', get_class($this)),
          $one);
      }
    }
  }

 ?>
