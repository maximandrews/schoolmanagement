<?php
include_once(MODULES_PATH.'/db/views/item_view.php');
include_once(MODULES_PATH.'/db/dbitem.php');

class ItemController extends Controller {
	var $Item;
	var $Application;
	var $S;
	var $Dummy;
	
	var $Model;
	
	function CreateView() {
		$this->AddView(new ItemView($this));
	}
	
	function CreateModel() {
		$this->Item = new DBItem(null, $this);
	}

	function InitModel() {
		$this->CreateModel();

		$this->OrigianlTableName = $this->Item->GetTableName();

		$this->CreateView();
	}

	function GetPassedId() {
		return $this->App->GetVar($this->Item->id_field);
	}

	function LoadItem() {
		//echo "In loaditem";
		$id = $this->GetPassedId();
		if($id > 0) $this->Item->Load($id);
	
		return $this->Item->loaded;
	}

	// Actions
	function actUpdate($notskip=0) {
		if(!defined('ADMIN') && !$notskip)
			return false;

		$this->LoadItem();
		$this->Item->SetFieldsFromHash($this->App->GetVars());
		return $this->Item->Update();
	}

	function actCreate($notskip=0) {
		if(!defined('ADMIN') && !$notskip)
			return false;

		$this->Item->SetFieldsFromHash($this->App->GetVars());
		return $this->Item->Create();
	}

	function actDelete($notskip=0) {
		if(!defined('ADMIN') && !$notskip)
			return false;

		print_r($_REQUEST);
		$this->LoadItem();
		return $this->Item->Delete();
	}
}
?>