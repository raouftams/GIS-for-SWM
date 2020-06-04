<?php 

namespace App;

use App\Core\Config;
use App\Core\Database\PostgresDatabase;

class APP
{
	public $title = "site";
	private $db_instance;
	private static $_instance;

	public static function getInstance(){
		if (self::$_instance == null) {
			self::$_instance = new App();
		}
		return self::$_instance;
	}

    
    /*public static function load(){
		session_start();
		require 'Autoloader.php';
		App\Autoloader::register();
		require '../Core/Autoloader.php';
		Core\Autoloader::register();
	}*/

	public function getTable($name){
		$class_name = '\\App\\Table\\' .ucfirst($name) . 'Table';
		return new $class_name($this->getDb());
	}

	public function getDb(){
		$config = Config::getInstance(__DIR__. '/config/config.php');
		if (is_null($this->db_instance)) {
			$this->db_instance = new PostgresDatabase($config->get('db_name'), $config->get('db_user'), $config->get('db_pass'), $config->get('db_host') );
		}
		return $this->db_instance;
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