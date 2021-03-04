<?php

include_once(MODULES_PATH.'/db/controllers/grid_controller.php');
include_once(MODULES_PATH.'/db/controllers/item_controller.php');

class ClassesFactory {
	private $Classes = Array();
	private $Instances = Array();
	
	function __construct() {
	}
	
	function RegisterClass($prefix, $class, $file) {
		$this->Classes[$prefix]['cls'] = $class;
		$this->Classes[$prefix]['file'] = $file;
	}
	
	function IncludeClassFile($prefix) {
		if (!isset($this->Classes[$prefix]['file'])) die ('<b>Fatal error: File is not set for Prefix '.$prefix.'</b><br>');
		if (!file_exists($this->Classes[$prefix]['file'])) die ('<b>Fatal error: Include file for class '.$this->Classes[$prefix]['cls'].' ('.$this->Classes[$prefix]['file'].') does not exists</b><br>');
		include_once($this->Classes[$prefix]['file']);
	}
	
	function &MakeClass($prefix) {
		if(!array_key_exists($prefix, $this->Instances)) {
			if (!isset($this->Classes[$prefix])) die('<b>Fatal error: Prefix '.$prefix.' is not registered with the Factory</b><br>');
			if (!isset($this->Classes[$prefix]['cls'])) die ('<b>Fatal error: Class is not set for Prefix '.$prefix.'</b><br>');
			$this->IncludeClassFile($prefix);
			if(!class_exists($this->Classes[$prefix]['cls'])) die ('<b>Fatal error: Class '.$this->Classes[$prefix]['cls'].' is not exists for Prefix '.$prefix.'</b><br>');
			$class = $this->Classes[$prefix]['cls'];
			$this->Instances[$prefix] = new $class($prefix);
		}

		return $this->Instances[$prefix];
	}
}

?>