<?php
class Base 
{
	var $Property = Array();	
	var $error_msg = NULL;
	var $error_code = NULL;
	var $Owner = NULL;
	
	function SetProperty($name,$value) {
		$this->Property[$name] = $value;
		$this->OnSetProperty($name, $value);
	}	
	
	function OnSetProperty($name, $value) //abstract
	{
	
	}
	
	function GetProperty($name) {
		if (isset($this->Property[$name]))
			return $this->Property[$name];
		else
			return;
	}
	
	function UnsetProperty($name) {
		unset($this->Property[$name]);
	}
	
	
	function GetPropertyArray() {
		return $this->Property;		
	}
	
	function GetError() {
		return $this->error_msg;
	}
	
	function SetError($msg, $code=NULL) {
		$this->error_msg = $msg;
		if (isset($code)) $this->SetErrorCode($code);
	}
	
	function GetErrorCode() {
		return $this->error_code;
	}
	
	function SetErrorCode($code) {
		$this->error_code = $code;
	}
	
	function SetOwner(&$owner) {
		$this->Owner =& $owner;
	}
}
?>