<?php

class Controller {
	public $prefix = null;
	public $Views = Array();
	public $Conn;
	private $Inited = false;

	function __construct($prefix) {
		$this->prefix = $prefix;
		$this->App =& KernelApplication::Instance();
		$this->Conn =& $this->App->GetADODBConnection();
		$this->S =& $this->App->Session;
	}

	function Init() {
		if($this->Inited)
			return false;

		$this->ProcessControlFlow();
		$this->Inited = true;
	}

	function ProcessControlFlow() {
		$this->ProcessAction('preact');
		$this->InitModel();
		$this->ProcessAction('act');
	}

	function InitModel() {
		$this->CreateView();
	}

	function CreateView() {
		//abstract for user defined methods
	}

	function CheckMethod($method) {
		return $this->FindMethodInViews($method) !== false;
	}

	function AddView(&$a_view) {
		$this->Views[] =& $a_view;
	}
	
	function &FindMethodInViews($Method) {
		for ($i=0; $i < count($this->Views); $i++) {
			if (method_exists($this->Views[$i], $Method)) {
				return $this->Views[$i];
			}
		}

		$ret = false;
		return $ret;
	}

	function GetAction() {
		$action = $this->App->GetVar('action');
		if ($action == '') $action = 'load';
		return $action;
	}

	function SetAction($action = '') {
		if ($action == '') $action = 'load';
		$this->App->SetVar('action', $action);
	}

	function ProcessAction($mode='act') {
		$action = $this->GetAction();
		$method = $mode.$action;
		if (defined('DEBUG_ACTIONS')) echo "class: ".get_class($this)."; action method: $method<br>";
		if (method_exists($this, $method))
			return $this->$method();
		else {
			if (defined('DEBUG_ACTIONS')) "Action method $method not found in class ".get_class($this)."<BR>";
			return false;
		}
	}

	function GetObjName($type) {
		return $this->Prefix.'_'.$type;
	}
}

?>