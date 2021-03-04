<?php

class View {
	public $Controller;
	public $S;

	function __construct(&$controller) {
		$this->Controller =& $controller;
		$this->App =& $this->Controller->App;
		$this->S =& $this->Controller->S;
	}
	
	
}

?>