<?php
include_once(MODULES_PATH."/db/validators.php");
include_once(MODULES_PATH."/db/formatters.php");

class DBItem extends Base {
	public $id = NULL; //Primary key value of current record
	public $id_field = 'id'; //Primary key field_name
	public $table_name = NULL; //Database Table name
	
	public $AutoStructure = 1; //Automatically get fields structure from database
	public $FieldValues = Array(); //Current values of fields
	public $OriginalValues = Array(); //Not modified values of fields
	public $FieldOptions = Array(); //Options arrays for each field with keys 'required', 'db', 'format'...
	public $FieldTypes = Array(); //Types of fields
	public $FieldErrors = Array(); //Field Errors
	public $Validators = Array(); //Arrays of Validators objects for each field 
	public $Formatters = Array(); //Formatter objects for each field
	
	public $SelectSQL = NULL; //Select statement template
	public $DeleteSQL = NULL; //Delete statement template
	public $WriteEmptyValues = 1; //Include empty values in Update statement
	
	public $DisplayQueries = 0; //For debugging
	public $DisplayErrors = 0; //For debugging
	
	public $DefaultDisplayErrorMsgMode = 1;
		
	public $loaded = false; //Has the item been loaded from DB
	public $dataValid = false; //Is data in fields valid (according to Validators objects)
	
	public $Conn;
	
	function __construct($Id, &$Owner) {
		$this->App =& KernelApplication::Instance();
		$this->Conn =& $this->App->GetADODBConnection();
		
		$this->SetOwner($Owner); //Setting Owner class - normally descendant of TagProcessor
		//Setting standard query templates
		if (!isset($this->SelectSQL)) $this->SelectSQL = sprintf("SELECT * FROM `".(defined('TLB_PREFIX')?TLB_PREFIX:'') ."%s` WHERE %%s", $this->table_name);
		if (!isset($this->DeleteSQL)) $this->DeleteSQL = sprintf("DELETE FROM `".(defined('TLB_PREFIX')?TLB_PREFIX:'') ."%s` WHERE %%s", $this->table_name);
		
		//Getting fields structure of table from database
		if ($this->AutoStructure) $this->GetStructure();
		
		$this->PredefineFields();
		
		//Loading data from database if $Id was passed
		if(isset($Id)) {
			$this->id = $Id;
			$this->loaded = $this->Load($Id);
		}
	}
	
	function GetTableName() {
		return $this->table_name;
	}
	
	function SetTableName($name) {
		$this->table_name = $name;
	}
	
	function GetTables($ids) {
		//abstract
		return false;
	}	
	
	function GetIDFieldName() {
		return $this->id_field;
	}
	
	function PredefineFields() {
		//abstract
	}
	
	//Gets Fields structure of table. Currently only MySQL is supported
	function GetStructure($create_fields=1, $set_id=1) {
		$conn =& $this->Conn;
		$sql = 'DESCRIBE `'.(defined('TLB_PREFIX')?TLB_PREFIX:'').$this->table_name.'`';
		if ($this->DisplayQueries) echo "DBItem structure: $sql<br>\n";
		$rs = $conn->Execute($sql); //Get fields structure for table_name
		while ($rs && ($row = $rs->FetchRow())) {
			$field = $row['Field'];
			$type = $row['Type'];
			$required = $row['Null'] != 'YES' ? 1 : 0;
			$key = $row['Key'] == 'PRI' ? 1 : 0;
			$def = $row['Default'];
			
			$type = preg_replace("/\(.*/is", '', $type);
			
			//see ADODB documentation for MetaTypes
			$MetaType = $rs->MetaType($type);
			
			switch ($MetaType) {
				case 'I':
					$type = 'integer';
					break;
				case 'N':
					$type = 'float';
					break;
				default:
					$type = 'default';
			}
			
			if ($set_id && $key) $this->id_field = $field;
			if ($key) $required = 0;
			
			if ($create_fields) {
				$this->CreateField($field, $def, $required, 1, $type);
			}
		}
		$this->CreateUniqueValidators();
	}
	
	function CreateUniqueValidators() {
		$sql = 'SHOW INDEX FROM `'.$this->table_name.'` FROM `'.SQL_DB.'`';
		$conn =& $this->Conn;
		$res = $conn->Execute($sql);
		$uniques = Array();
		while ($res && $indexItem = $res->FetchRow()) {
			if($indexItem['Non_unique'] == '0') {
				$uniques[$indexItem['Key_name']][intval($indexItem['Seq_in_index'])] = $indexItem['Column_name'];
			}
		}
		
		if(is_array($uniques) && count($uniques) > 0) {
			foreach($uniques as $unique) {
				if(!isset($this->FieldValues[$unique[1]]) && !isset($this->FieldOptions[$unique[1]]))
					$this->CreateField($unique[1], '');
				$validator = new UniqueValidator($unique[1], $this, null);
				$validator->SetUniqueFields($unique);
				$this->Validators[$unique[1]][] =& $validator;
			}
		}
	}
	
	//Returns formatted value of the field
	function GetField($name) {
		if (array_key_exists($name, $this->FieldValues)) {
			if (isset($this->Formatters[$name]))
				return $this->Formatters[$name]->Format($this->FieldValues[$name]);
			else
				return $this->FieldValues[$name];
		}else
			return NULL;
	}
	
	//Return raw (database) value of the field
	function GetDBField($name) {
		if (array_key_exists($name, $this->FieldValues))
			return $this->FieldValues[$name];
		else 
			return NULL;
	}
	
	//Returns formatted original value of the field
	function GetOriginal($name) {
		if (array_key_exists($name, $this->OriginalValues)) {
			if (isset($this->Formatters[$name])) 
				return $this->Formatters[$name]->Format($this->OriginalValues[$name]);
			else
				return $this->OriginalValues[$name];
		}else
			return NULL;
	}
	
	//Return raw (database) original value of the field
	function GetDBOriginal($name) {
		if (array_key_exists($name, $this->OriginalValues))
			return $this->OriginalValues[$name];
		else 
			return NULL;
	}
	
	//Set value of the field parsing it according to Formatter setting
	function SetField($name, $value, $required=0, $db=1, $type='default') {
		if ($name == $this->id_field) $this->id = $value;
		if (array_key_exists($name, $this->FieldValues)) {
			$this->FieldValues[$name] = $this->Formatters[$name]->Parse($value);
			//echo "$name value [$value] parsed to ".$this->FieldValues[$name]."<BR>";
		}else
			$this->CreateField($name, $value, $required, $db, $type);
	}
	
	//Set raw (database) value of the field
	function SetDBField($name, $value, $required=0, $db=1, $type='default') {
		if ($name == $this->id_field) $this->id = $value;
		if (array_key_exists($name, $this->FieldValues))
			$this->FieldValues[$name] = $value;
		else
			$this->CreateField($name, $value, $required, $db, $type);
	}
	
	//Return Primary Key of DBItem
	function GetId() {
		return $this->id;
	}
	
	//Set fields (passed as comma separated list or array) as required (for validation)
	//if second argument is 0 - makes the fields not required
	function SetRequiredFields($fields_list, $status=1) {
		if (!is_array($fields_list)) 
			$fields_list = explode(',', $fields_list);
		foreach ($fields_list as $key) {
			if (isset($this->FieldOptions[$key])) 
				$this->FieldOptions[$key]['required'] = $status;
		}
	}
	
	function IsRequired($field_name) {
		return $this->FieldOptions[$field_name]['required'];
	}
	
	//Set fields (passed as comma separated list or array) as db (included in Update or Create queries)
	//if second argument is 0 - makes the fields virtual (not included in Update or Create queries)
	function SetDBFields($fields_list, $status=1) {
		if (!is_array($fields_list)) 
			$fields_list = explode(',', $fields_list);
		foreach ($fields_list as $key) {
			if (isset($this->FieldOptions[$key])) 
				$this->FieldOptions[$key]['db'] = $status;
		};
	}
	
	//Set up (creates) the field, sets passed options, formatter and validator, creates empty errors array
	function CreateField($name, $value, $required=0, $db=1, $type='default') {
		if ($name == $this->id_field) $this->id = $value; //If creating Primary Key, set it's value separately
		$this->FieldValues[$name] = $value;
		$this->FieldOptions[$name]['required'] = $required;
		$this->FieldOptions[$name]['db'] = $db;
		$this->SetDisplayErrorMsgMode($name, $this->DefaultDisplayErrorMsgMode);
		$this->Formatters[$name] =& $this->GetFormatter($name, $type);
		$this->Validators[$name] = Array();
		$this->Validators[$name][] =& $this->GetValidator($name, $type);
		$this->FieldErrors[$name] = null;
	}
	
	//Creates fields with raw (db) values from passed hash
	function CreateFieldsFromHash($list) {
		foreach ($list as $name => $value) {
			if (preg_match("/^[0-9]+$/is", $name)) continue;
	    $this->SetDBField($name, $value);
		}
	}
	
	//Set fields values from passed hash, parsing according to field's formatter
	function SetFieldsFromHash($list) {
		foreach ($list as $name => $value) {
			if (preg_match("/^[0-9]+$/is", $name)) continue;
			if (!array_key_exists($name, $this->FieldValues)) continue;
			$this->SetField($name, $value);
		}
	}
	
	function SetDBFieldsFromHash($list) {
		foreach ($list as $name => $value) {
			if (preg_match("/^[0-9]+$/is", $name)) continue;
			if (!array_key_exists($name, $this->FieldValues)) continue;
			$this->SetDBField($name, $value);
		}
	}
	
	//Return the primary key part of WHERE clause for Select statement
	function GetKey($action) {
		$adodbConnection =& $this->Conn;
		return sprintf('%s = %s', 
										$this->id_field, 
										$adodbConnection->qstr($this->id, 0));
	}
	
	//Returns default Validator for given field type
	function &GetValidator($name, $type, $error_msg = null) {
		//echo "validator called in class ".get_class($this).', field: <b>'.$name.'</b><br>';
		switch ($type) {
			case 'float':
				$validator = new FloatValidator($name, $this, $error_msg);
				break;
			case 'integer':
				$validator = new IntegerValidator($name, $this, $error_msg);
				break;
			default:
				$validator = new Validator($name, $this, $error_msg);
		}
		return $validator;
	}
	
	//Returns default Formatter for given field type
	function &GetFormatter($name, $type) {
		switch ($type) {
			default:
				$formatter = new Formatter($name, $this);
		}
		return $formatter;
	}
	
	//For backward compatibility
	function LoadFromDatabase($Id, $IdField=NULL) {
		return $this->Load($Id, $IdField);
	}

	//Load databse record with given Id into object using Primary Key (id_field)
	//Specifing IdField allows loading data using another Key Field
	function Load($Id, $IdField=NULL) {
		if(!isset($Id))            
			return false;
			
		$this->id = $Id;
	
		$adodbConnection =& $this->Conn; // create a connection
	  
	  //storing current id_field and changing id_field if second parameter was passed
	  if (isset($IdField)) {
	  	$cur_id_field = $this->id_field;
	  	$this->id_field = $IdField;
	  }
	  
	  $sql = sprintf($this->GetSelectSQL(), $this->GetKey('select')); //Formatting Select statement
	  
	  if ($this->DisplayQueries) echo "DBItem: $sql<br>ID is ".$this->id.'<BR>';
	  //restoring previously stored id_field if needed
	  if (isset($IdField)) {
	  	$this->id_field = $cur_id_field;
	  	$this->id = $this->GetField($this->id_field);
	  }
	  
	  $result = $adodbConnection->Execute($sql); //Executing query
	  
	  if ($result === false) {
	    if ($this->DisplayQueries)
	  		echo "Error executing statement: ".$adodbConnection->ErrorMsg()."<br>";
	  }
	  
	  if ($result === false || $result->RecordCount() == 0)
	  {
	  	return false;
		}
	  $this->CreateFieldsFromHash($result->fields); //Creating fields with values from fetched record
	  //echo "fields created<br>";
	  $this->loaded = true;
	  $this->OriginalValues = $this->FieldValues;
	  return true;
	}
	
	//Deletes record from database using Primary Key (id_field)
	function Delete() {
		if(!isset($this->id))
		   return false;
	
		$adodbConnection =& $this->Conn;    // create a connection
		
		$sql = sprintf($this->GetDeleteSQL(), $this->GetKey('delete')); //formatting Delete query
		
		if ($this->DisplayQueries) echo "Delete DBItem: $sql<br> Database is: ".$adodbConnection->database."<br>";
		
		if ($adodbConnection->Execute($sql) === false) //Executing query and checking result
		{
			if ($this->DisplayQueries)
	  		echo "Error executing statement: ".$adodbConnection->ErrorMsg()."<br>";
		  return false;
		}
		return true;
	}
	
	//Updates the record in the database with current fields values
	//field_list - array of fields to update or NULL to use fields marked as db in FieldOptions
	function Update($fields_list=NULL) {
		if(!isset($this->id))
			return false;
		
		if(!$this->Validate()) //Validating all the fields before updating
			return false;
	    
		if(count($this->FieldValues) == 0) //Nothing to update
			return true;
				
		if (isset($fields_list)) {
			if (!is_array($fields_list))
				$fields_list = explode(',', $fields_list);
		}
		
		$adodbConnection =& $this->Conn;
		
		$sql = sprintf("UPDATE `".(defined('TLB_PREFIX')?TLB_PREFIX:'') ."%s` SET ", $this->table_name);
		foreach ($this->FieldValues as $key => $value) {
			if (isset($fields_list)) { //If fields_list (second argument) is set updating only fields in list
				if (!in_array($key, $fields_list))
					continue;
			}
			if ($this->FieldOptions[$key]['db'] != 1) continue; //skipping 'virtual' field
			if (!$this->WriteEmptyValues && $this->FieldValues[$key] == '') continue; //skipping empty values if not forced to write it
			if ($key == $this->id_field) continue; //skipping Primary Key
			
			$real_field_name = preg_replace("/^.*\./is", '', $key); //removing table names from field names
			//Adding part of SET clause for current field, escaping data with ADODB' qstr
			$sql.= sprintf("%s=%s, ",$real_field_name,$adodbConnection->qstr($this->FieldValues[$key], 0));
		}
			
		$sql = preg_replace("/, $/is", '', $sql); //Removing last comma and space
			
		$sql.= sprintf(' WHERE %s', $this->GetKey('update')); //Adding WHERE clause with Primary Key
		
		if ($this->DisplayQueries) echo "Sql: $sql<br>";
		
		if ($adodbConnection->Execute($sql) === false) { //Executing query and checking results
			if ($this->DisplayQueries)
				echo "Error executing statement: ".$adodbConnection->ErrorMsg()."<br>";
			return false;
		}else {
			$this->OriginalValues = $this->FieldValues;
			return true;
		}
	}
		
	//Set Primary Key after Insert statement for futher updating current object and its underlaying record
	function SetInsertID() {
		$adodbConnection =& $this->Conn;  
		$this->id = $adodbConnection->Insert_ID();
		$tmp_id = $adodbConnection->Insert_ID();
		if (preg_match("/^postgres/is", $adodbConnection->databaseType)) {
			echo "postgres<BR>";
			$query = sprintf("SELECT %s FROM ".(defined('TLB_PREFIX')?TLB_PREFIX:'') ."%s WHERE oid=%s", $this->id_field, $this->table_name, $tmp_id);
			$res = $adodbConnection->Execute($query);
			if ($res && !$res->EOF) {
				$tmp_id = $res->fields[$this->id_field];
			}
		}
		$this->id = $tmp_id;
		$this->SetDBField($this->id_field,$tmp_id);
	}
	
	//Checks that there is no record with same Primary Key in the database
	function CheckUniqueKey() {
		$this_class = get_class($this);
		if ($this->DisplayErrors) echo get_class($this)." Checking Id $this->id<br>";
		$tmp = new $this_class($this->id, $this->Owner); //Creating the instance of current class and trying to load it
	  if ($tmp->loaded) { //If succseeded in loading - it means that there IS a record with same Primary Key
	  	if ($this->DisplayErrors) echo get_class($this)." Record with this key already exists<br>";
	  	$this->SetError('Record with this key already exists', 1);
	  	unset($tmp);
	  	return false;
	  }
	  unset($tmp);
	  return true;
	}
	
	//Creates the record in database with current fields' values
	function Create() {
		if(!$this->Validate()) //Validating fields before attempting to create record
	  	return false;
	  
	  /*
	  skiping, validator created
	  //Checking if record with the same key exists
	  if (!$this->CheckUniqueKey()) return false;
	  */
	  
		$adodbConnection =& $this->Conn;    // create a connection
		$fields_sql = '';
		$values_sql = '';
	  foreach ($this->FieldValues as $key => $value) {
	  	if (!$this->FieldOptions[$key]['db']) continue; //skipping 'virtual' fields
	  	if (!$this->WriteEmptyValues && !isset($this->FieldValues[$key])) continue; //skipping empty fields unless forced to include it
			$fields_sql.= sprintf("`%s`, ",$key); //Adding field name to fields block of Insert statement
			//Adding field' value to Values block of Insert statement, escaping it with ADODB' qstr
			$values_sql.= sprintf("%s, ",$adodbConnection->qstr($this->FieldValues[$key], 0)); 
	  }
	  //Cutting last commas and spaces
	  $fields_sql = preg_replace("/, $/is", '', $fields_sql); 
	  $values_sql = preg_replace("/, $/is", '', $values_sql);
	   
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
	
	function FormInsertSQL($table, $fields, $values) {
		return sprintf("INSERT INTO `".(defined('TLB_PREFIX')?TLB_PREFIX:'') ."%s` (%s) VALUES (%s)", $table, $fields, $values); //Formatting query
	}
	
	//Validates fields values using Validator objects, assigned to the fields
	function Validate() {
		$dataValid = true; //assumption
		foreach ($this->FieldValues as $name => $a_field) {
			if(!$this->ValidateField($name))
				$dataValid = false;
		}
		$this->dataValid = $dataValid;
		return $dataValid;    	
	}
	
	function ValidateField($field) {
		$dataValid = true; //assumption
		for ($i=0; $i<count($this->Validators[$field]); $i++) { //There could be more than one Validator for a field, cycling
			//Trying to Validate field' value with Validtor or check if errors for field are already set
			//Errors could be set while parsing the value in SetField()
			if (
						($this->FieldErrors[$field] != null) ||
						!$this->Validators[$field][$i]->Validate($this->FieldValues[$field])) {
				$this->SetError("Error while validating fields", 2); //Setting general error for this object
				//if ($this->DisplayErrors) echo "Error in field $name: ".$this->FieldErrors[$name]."<br>";
				if ($this->DisplayErrors) {
					 echo "Error in field <b>$field</b> using validator <b>".get_class($this->Validators[$field][$i])."</b>: ";
					 print_pre($this->FieldErrors[$field]);
				}
				$dataValid = false;
			}
		}
		
		return $dataValid;
	}
	
	function SetDisplayErrorMsgMode($name, $mode) {
		$this->FieldOptions[$name]['display_error_msg'] = $mode;
	}
	
	function GetDisplayErrorMsgMode($name) {
		return $this->FieldOptions[$name]['display_error_msg'];
	}
	
	function GetErrors() {
		$errors = Array();
		foreach ($this->FieldErrors as $field => $an_error) {
			if ($this->GetDisplayErrorMsgMode($field) && $an_error != null) {
				$errors[] = Array('id' => $field, 'msg' => is_array($an_error) && isset($an_error['msg']) ? $an_error['msg']:$an_error);
			}
		}

		return $errors;
	}
	
	//Returns default error message for passed field or numerical error code if second argument is 'true'
	function GetFieldError($name, $as_code=NULL) {
		if (isset($this->FieldErrors[$name]))
			return $this->FieldErrors[$name];
		else
			return NULL;
	}
	
	//Need to comment ?
	function GetSelectSQL() {
		return $this->SelectSQL;
	}
	
	function GetDeleteSQL() {
		return $this->DeleteSQL;
	}
	
	function GetFieldsArray() {
		return $this->FieldValues;
	}

	function GetFieldOptions($table, $field, $short=false, $optionAsValue=false, $charValueAsOption=false) {
		$int_value = $optionAsValue ? '`option`': 'int_value';
		$int_value = $charValueAsOption ? 'char_value': $int_value;
		
		$query = sprintf(	"SELECT * FROM ".(defined('TLB_PREFIX')?TLB_PREFIX:'') ."field_options WHERE `table` = '%s' AND `field` = '%s' ORDER BY $int_value",
											$table,
											$field);
		$conn =& $this->Conn;
		// echo " GetFieldOptions : $query <br>";
		$rs = $conn->Execute($query);
		
		$options = Array();
		while ($rs && !$rs->EOF) {
			$value = $optionAsValue ? $rs->fields['option'] : $rs->fields['int_value'];
			$option = $short ? $rs->fields['option_short'] : $rs->fields['option'];
			$option = $charValueAsOption ? $rs->fields['char_value'] : $option;
			$options[$value] = $option;
			$rs->MoveNext();
		}
		// print_pre($options);
		return $options;
	}
}

//DBItem with double Primery Key (two fields)
class DblKeyDBItem extends DBItem  {
	public $sec_id = NULL;
	public $sec_id_field = NULL;
	
	function __construct($Id, $Sec_Id, &$Owner) {
		parent::__construct(NULL, $Owner);
		$this->sec_id = $Sec_Id;
		if ($Id != null) {
			$this->id = $Id;
			$this->Load($Id);
		}
	}
	
	function GetSecID() {
		return $this->sec_id;
	}
	
	function SetInsertID() {
  }
  
  function CheckUniqueKey() {
		$this_class = get_class($this);
		//echo "checking unique key ".$this->id.", ".$this->sec_id."<BR>";
		$tmp = new $this_class($this->id, $this->sec_id, $this->Owner);
   	if ($tmp->loaded) {
     	$this->SetError('Record with this key already exists', 1);
     	unset($tmp);
     	return false;
     }
     unset($tmp);
     return true;
  }
  	
	function GetKey($action) {
		$adodbConnection =& $this->Conn;
  	return sprintf(' %s = %s AND %s = %s', 
  			$this->id_field, 
  			$adodbConnection->qstr($this->id, 0),
  			$this->sec_id_field, 
  			$adodbConnection->qstr($this->sec_id, 0));
	}
	
	function CreateField($name, $value, $required=0, $db=1, $type='default') {
		if ($name == $this->sec_id_field) 
			$this->sec_id = $value;
		parent::CreateField($name, $value, $required, $db, $type);
	}
	
	
	function SetField($name, $value, $required=0, $db=1, $type='default') {
		if ($name == $this->sec_id_field)
			$this->sec_id = $value;
		parent::SetField($name, $value, $required, $db, $type);
	}
	
	function SetDBField($name, $value, $required=0, $db=1, $type='default') {
		if ($name == $this->sec_id_field)
			$this->sec_id = $value;
		parent::SetDBField($name, $value, $required, $db, $type);
	}
}

?>