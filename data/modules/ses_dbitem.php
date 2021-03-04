<?php
global $MODULES_PATH;
include_once(MODULES_PATH."/db/dbitem.php");

class SessionData extends DblKeyDBItem  {
	function __construct($Id, $SecId=NULL, &$Owner) {
		$this->table_name = 'session_data';
		$this->id_field = 'sid';
		$this->sec_id_field = 'name';
		$this->DisplayQueries = 0;
		$this->DisplayErrors = 0;
		$this->SelectSQL = 'SELECT * FROM '.(defined('TLB_PREFIX')?TLB_PREFIX:'') .'session_data WHERE %s';
		$this->DeleteSQL = 'DELETE FROM '.(defined('TLB_PREFIX')?TLB_PREFIX:'') .'session_data WHERE %s';
		parent::__construct($Id, $SecId, $Owner);
	}
	
	function GetFieldsArray()
 	{
 		$tmp = Array();
 		foreach ($this->FieldValues as $key => $value) {
 			if ($key == 'sid') 
 				continue;
 			$tmp[$key] = $this->FieldValues[$key];
 		}
 		return $tmp;
 	}
}
	
?>