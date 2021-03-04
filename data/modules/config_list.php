<?php
global $MODULES_PATH;
include_once(MODULES_PATH."/db/dbitem.php");
include_once(MODULES_PATH."/db/dblist.php");

class ConfigItem extends DBItem {
	var $Application;
	
	function __construct($Id, &$Owner) {
		$this->App =& KernelApplication::Instance();
		$this->id_field = 'name';
		$this->table_name = 'config';
		$this->CreateField('name','',1,1);
		$this->CreateField('value','',1,1);
		$this->CreateField('config_description','', 0, 0);
		$this->DisplayErrors = 0;
		$this->DisplayQueries = 0;
		parent::DBItem($Id, $Owner);
	}

	function &GetValidator($name, $type, $error_msg = null) {
		switch ($name) {
			default:
				$validator =& parent::GetValidator($name, $type, $error_msg);
		}
		return $validator;
	}

	function &GetFormatter($name, $type, $format = null) {
		switch ($name) {
			default:
				$formatter =& parent::GetFormatter($name, $type, $format);
		}
		return $formatter;
	}

	function Update($fields_list=NULL) {
		$ret = parent::Update($fields_list);
		// echo " update ".$this->GetDBField('name')."<br>";
		if ( $ret AND ereg("(per_page)",$this->GetDBField('name'))) {
			$this->App->StoreVar(
				$this->GetDBField('name'),
				$this->GetDBField('value')
			);
		}
		return $ret;
	}
}

class ConfigList extends FilteredDBList {
	function __construct($sql, $query_now=0, &$owner) {
		parent::__construct($sql, $query_now, $owner);
		$this->Special = isset($owner->Params['special'])?$owner->Params['special']:'';
		switch ($this->Special) {
			default:  
				$this->sql = " SELECT * FROM ".(defined('TLB_PREFIX')?TLB_PREFIX:'') ."config ";
		};
		$this->DisplayQueries = 0;
	} 
	
	function &NewItem () {
		$new_item = new ConfigItem(NULL, $this->Owner);
		return $new_item;
	}
	
	function GetOption($name, $default=false) {
		if ($this->Find('name', $name))
			return $this->GetCurrentFieldValue('value');
		else
			return $default;
	}
	
	function SetOption($name, $value) {
		if ($this->Find('name', $name)) {
			$tmp =& $this->GetCurrentRec();
			$tmp->SetField('value',$value);
			$tmp->Update();
		}else{
			$push_hash =  Array('name'=>$name, 'value'=>$value);
			$id = array_push($this->Records, $push_hash);
			if (count($this->IndexFields) > 0) {
	    	foreach ($this->IndexFields as $key) {
	    		if (is_string($push_hash[$key])) $store_key = strtolower($push_hash[$key]);
	    		else $store_key = $push_hash[$key];
					$this->Indexes[$key][$store_key] = $id-1;
				}	
	    }
	    $this->last_rec++;
		}
	}
}

?>