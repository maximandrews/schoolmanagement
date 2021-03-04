<?php
include_once(APP_PATH.'/e_item.php');

class FieldOpt extends eDBItem
{
	
	function FieldOpt($Id, &$Owner) {
		$this->table_name = 'field_options';
		$this->App =& KernelApplication::Instance();
		parent::DBItem($Id, $Owner);
		$this->DisplayQueries = 0;
		$this->DisplayErrors = 0;
	}
	
	function Create() {
		if(!$this->Validate()) //Validating fields before attempting to create record
	  	return false;
	  
	  //Checking if record with the same key exists
	  if (!$this->CheckUniqueKey()) return false;
	  
		$adodbConnection = GetADODBConnection();    // create a connection
		$fields_sql = '';
		$values_sql = '';
	  foreach ($this->FieldValues as $key => $value) {
	  	if (!$this->FieldOptions[$key]['db']) continue; //skipping 'virtual' fields
	  	if (!$this->WriteEmptyValues && $this->FieldValues[$key] == '') continue; //skipping empty fields unless forced to include it
			$fields_sql.= sprintf("`%s`, ",$key); //Adding field name to fields block of Insert statement
			//Adding field' value to Values block of Insert statement, escaping it with ADODB' qstr
			$values_sql.= sprintf("%s, ",$adodbConnection->qstr($this->FieldValues[$key], 0)); 
	  }
	  //Cutting last commas and spaces
	  $fields_sql = ereg_replace(", $", '', $fields_sql); 
	  $values_sql = ereg_replace(", $", '', $values_sql);
	   
	  $sql = $this->FormInsertSQL($this->table_name, $fields_sql, $values_sql);
	  
	  if ($this->DisplayQueries) echo "$sql<br> Database is: ".$adodbConnection->database."<br>";
	  
	  if ($adodbConnection->Execute($sql) === false) {//Executing the query and checking the result
	  	if ($this->DisplayQueries)
	  		echo "Error executing statement: ".$adodbConnection->ErrorMsg()."<br>";
	  	return false;
	  }
		$this->SetInsertID(); //Setting Primary Key ($this->id) for futher using the object
	  $this->OriginalValues = $this->FieldValues;
		return true;
	}
	
	function Update($fields_list=NULL) {
		if(!isset($this->id))
				return false;
	   
	   	if(!$this->Validate()) //Validating all the fields before updating
				return false;
	    
	    if(count($this->FieldValues) == 0) //Nothing to update
				return true;
				
			if (isset($fields_list)) {
				if (!is_array($fields_list)) {
					$fields_list = explode(',', $fields_list);
				}
			}

	    $adodbConnection = GetADODBConnection();
	    
	    $sql = sprintf("UPDATE %s SET ", $this->table_name);
	    foreach ($this->FieldValues as $key => $value)
	    {
	    	if (isset($fields_list)) { //If fields_list (second argument) is set updating only fields in list
	    		if (!in_array($key, $fields_list)) {
	    			continue; 
	    		}
	    	}
	    	if ($this->FieldOptions[$key]['db'] != 1) continue; //skipping 'virtual' field
	    	if (!$this->WriteEmptyValues && $this->FieldValues[$key] == '') continue; //skipping empty values if not forced to write it
	    	if ($key == $this->id_field) continue; //skipping Primary Key
	    	$real_field_name = eregi_replace("^.*\.", '', $key); //removing table names from field names
	    	//Adding part of SET clause for current field, escaping data with ADODB' qstr
	    	$sql.= sprintf("`%s`=%s, ",$real_field_name,$adodbConnection->qstr($this->FieldValues[$key], 0));
	   	}
	   	$sql = ereg_replace(", $", '', $sql); //Removing last comma and space
	   	
	    $sql.= sprintf(' WHERE %s', $this->GetKey('update')); //Adding WHERE clause with Primary Key
	
	    if ($this->DisplayQueries) echo "Sql: $sql<br>";
	
	    if ($adodbConnection->Execute($sql) === false) { //Executing query and checking results
	    	if ($this->DisplayQueries)
	  			echo "Error executing statement: ".$adodbConnection->ErrorMsg()."<br>";
	      return false;
	    }
			else {
	    	$this->OriginalValues = $this->FieldValues;
	    	return true;
	    }
	}
}


   
class FieldOptList extends EFilteredDBList
{
  function FieldOptList($sql, $query_now=0, &$owner) {
		parent::FilteredDBList($sql, $query_now, $owner);
		
		$this->Special = $owner->Params['special'];
		switch ($this->Special) {
			default:
			$this->sql = "SELECT * FROM field_options";
		}
		$this->DisplayQueries = 0;
	}
	
	function &NewItem() {
		$new = new FieldOpt(NULL, $this->Owner);
		return $new;
	}
	
	function AddStdFilter($type, $field, $value, $having='') {
		// echo" $type, $field, $value $having<br>";
		if ($having)
			$function="AddHavingFilter";
		else 
			$function="AddFilter";
		switch ($type) {
			case 'equals':
				$this->$function(
					new EqualsFilter('`'.$field.'`',
						$value),
					$type.'_'.$field
					);			
				break;
			case 'not_equals':
				$this->$function(
					new NotEqualsFilter('`'.$field.'`',
						$value),
					$type.'_'.$field
					);			
				break;
			case 'like':
				$this->$function(
					new LikeFilter('`'.$field.'`',
						$value),
					$type.'_'.$field
					);			
				break;
			case 'rangefrom':
				$this->$function(
					new CompareFilter('`'.$field.'`',
						$value, '>='),
					$type.'_'.$field
					);
				break;
			case 'rangeto':
				$this->$function(
					new CompareFilter('`'.$field.'`',
						$value, '<='),
					$type.'_'.$field
					);
				break;
			case 'datefrom':
				// $filter_date_format = $this->Owner->Session->Config->GetOption('filter_date_format');
				$filter_date_format = "m/d/Y";
				$formater = new DateFormatter($value, $this->Owner);
				$format_value = $formater->Parse($value, $filter_date_format);
				// echo " filter_date_format : $filter_date_format <br>";
				// echo " value : $value || format_value : $format_value <br>";
				$this->$function(
					new CompareFilter('`'.$field.'`', 
						$format_value, '>='),
					$type.'_'.$field);
				break;
			case 'dateto':
				// $filter_date_format = $this->Owner->Session->Config->GetOption('filter_date_format');
				$filter_date_format = "m/d/Y";
				$formater = new DateFormatter($value, $this->Owner);
				$format_value = $formater->Parse($value, $filter_date_format);
				if ( date("H:i:s",$format_value) == "00:00:00" ) $format_value += 86399; // what it is ?
				$this->$function(
					new CompareFilter('`'.$field.'`', 
						$format_value, '<='),
					$type.'_'.$field);
				break;
		}
	}
	
	
	function SetMultipleOrderByClause() {
		$res = '';
		foreach ($this->orderFields as $val) {
			list($field,$dir) = explode(' ',$val);
			$res.="`$field` $dir,";
		}
		$res = chop($res, ',');
		$this->SetOrderByClause($res);
	}
}  
  
?>