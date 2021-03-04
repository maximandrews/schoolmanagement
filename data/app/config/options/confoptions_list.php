<?php
include_once(APP_PATH.'/e_item.php');

class ConfOptions extends EDBItem {
	
	function ConfOptions($Id, &$Owner) {
		$this->table_name = 'config';
		$this->App =& KernelApplication::Instance();
		parent::DBItem($Id, $Owner);
		$this->DisplayQueries = 0;
		$this->DisplayErrors = 0;
	}
	
	function &GetFormatter($name, $type) {
		switch ($name) {
			case 'system_var':
			  $formatter = new OptionsFormatter($name, $type);
				$formatter->SetOptions( Array(0 => 'No', 1 => 'Yes') );
			  break;
			default: 
		  $formatter =& parent::GetFormatter($name, $type);
		  		break;
		}
		return $formatter;
	}
	
	function Delete() {
		if(!$this->loaded)
			$this->Load($this->GetId());
		
		if(!$this->GetDBField('system_var') && !$this->App->IsDebugMode())
			return false;
		else
			return parent::Delete();
	}
}

class ConfOptionsList extends EFilteredDBList {
	function ConfOptionsList($sql, $query_now=0, &$owner) {
		parent::FilteredDBList($sql, $query_now, $owner);
		$this->Special = $owner->Params['special'];
		
		if( !$this->App->IsDebugMode() )
			$this->AddStdFilter('equals', 'system_var', '0', false, 1);
		
		switch ($this->Special) {
			default:
			$this->sql = "SELECT * FROM config";
		}
		
		$this->DisplayQueries = 0;
	}
	
	function &NewItem() {
		$new = new ConfOptions(NULL, $this->Owner);
		return $new;
	}
}  
  
?>