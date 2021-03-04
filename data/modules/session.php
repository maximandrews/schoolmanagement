<?php
include_once(MODULES_PATH.'/simple_session.php');
include_once(MODULES_PATH.'/ses_dbitem.php');
include_once(MODULES_PATH.'/config_list.php');

class Session extends SimpleSession {
	function Init() {
		parent::Init();
		
		// Loading configuration table		
		$a_config = new ConfigList("SELECT * FROM ".(defined('TLB_PREFIX')?TLB_PREFIX:'') ."config", 1, $this->Owner);
		$this->Config = &$a_config;
		
		// Creating language object
		$this->StoreVar('debug', 0);
		$creation_ip = $this->RecallVar('creation_ip');
		if (empty($creation_ip)) 
		{
			$this->StoreVar('creation_ip', getenv("REMOTE_ADDR"));
			$this->StoreVar('creation_sub_ip', getenv("HTTP_X_FORVARDED_FOR"));
		}
	}
	
	function SP($name, $value) {
		return $this->SetProperty($name, $value);
	}
	
	function GP($name) {
		return $this->GetProperty($name);
	}
	
	function RV($name) {
		return $this->RecallVar($name);
	}
	
	function SV($name, $val, $def=null) {
		return $this->StoreVar($name, $val, $def);
	}


}
?>