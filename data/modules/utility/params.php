<?php

class Params {
	public $_Params = Array();

	function Set($name, $val) {
		$this->_Params[$name] = $val;
	}

	function Get($name) {
		if (array_key_exists($name, $this->_Params)) 
			return $this->_Params[$name];
		else
			return false;
	}

	function GetParams() {
		return $this->_Params;
	}
}

?>