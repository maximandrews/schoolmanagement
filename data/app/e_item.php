<?php
include_once(MODULES_PATH."/db/dbitem.php");

class EDBItem extends DBItem {

	function CustomOptions($sql) {
		// custom options formatter (not from field_options table)
		$db =& $this->Conn;	
		//echo $sql."<br>\n";
		$rs = $db->Execute($sql);
		$ret = array();
		while($rs !== false && !$rs->EOF)
		{
			$rec =& $rs->fields; $rec = array_values($rec);
			$ret[ $rec[0] ] = $rec[1];
			$rs->MoveNext();	
		}
		return $ret;
	}
	
	function JoinWords($word1, $word2, $separator, $no_empty = null) {
		if( !strlen($word1) ) return $word2;
		
		$result = $word1;
		if( strlen($word2) )
			$result .= $separator.$word2;
		elseif( isset($no_empty) )
			$result .= $separator.$no_empty;
		
		return $result;
	}
	
	function Clear() {
		$this->id = NULL;
		$this->dataValid = false;
		$this->loaded = false;
		foreach($this->FieldValues as $field => $val) {
			$this->FieldValues[$field] = '';
			$this->FieldErrors[$field] = null;
			$this->OriginalValues[$field] = '';
		}
	}
}

class EFilteredDBList extends FilteredDBList {
	var $FieldErrors = Array();
	
	function GetErrors() {
		$errors = Array();
		foreach ($this->FieldErrors as $id => $Errors)
			foreach ($Errors as $field => $an_error) {
				if ($an_error != null) 
					array_push($errors, $an_error);
			}
		return $errors;
	}
}

class ClassPostfixValidator extends Validator {
	
	var $min_length;
	var $max_length;
	function Validate($value) {
		if (!parent::Validate($value)) 
			return false;
		
		if (!$this->min_length)
			$this->min_length = 1;
		if (!$this->max_length)
			$this->max_length = 1;
			
		if ($this->Owner->FieldOptions[$this->field]['required'] || $value != '') {
			if (strlen($value) < $this->min_length || strlen($value) >  $this->max_length) {
				$this->Owner->FieldErrors[$this->field]['msg'] = 'Nepareizs lauka garums';
				$this->Owner->FieldErrors[$this->field]['code'] = 4;
				return false;
			}
			if (preg_match("/[^a-z]/is", $value)) {
				$this->Owner->FieldErrors[$this->field]['msg'] = 'Laukā ir kļūdaini simboli';
				$this->Owner->FieldErrors[$this->field]['code'] = 5;
				return false;
			}
		}
		return true;
	}
}

class MultiFormatter extends Formatter {
	var $formats = Array();
	var $pointerField = NULL;
	
	function SetFormats($formats) {
		$this->formats = $formats;
	}
	
	function Format($value, $format=NULL) {
		if(preg_match("/grid/is", get_class($this->Owner->Owner)) && isset($this->Owner->Owner->Grid->Records[$this->Owner->Owner->Grid->cur_rec]) && is_array($this->Owner->Owner->Grid->Records[$this->Owner->Owner->Grid->cur_rec])) {
			$grid =& $this->Owner->Owner->Grid;
			@$cformat = $this->formats[$grid->Records[$grid->cur_rec][$this->pointerField]];
		}else
			@$cformat = $this->formats[$this->Owner->GetDBField($this->pointerField)];
		
		return isset($cformat) ? sprintf("%01.".$cformat."f", $value) : $value;
	}
}


class LVNum2TXTFormatter extends Formatter {
	var $ranges = Array();
	
	function __construct($field, &$Owner) {
		parent::__construct($field, $Owner);
		$ranges = Array();
		$ranges[] = Array('min' => '0',																			'one' => 'nulle');                                 
		$ranges[] = Array('min' => '1',																			'one' => 'viens');                                 
		$ranges[] = Array('min' => '2',																			'one' => 'divi');                                  
		$ranges[] = Array('min' => '3',																			'one' => 'trоs');                                  
		$ranges[] = Array('min' => '4',																			'one' => 'иetri');                                 
		$ranges[] = Array('min' => '5',																			'one' => 'pieci');                                 
		$ranges[] = Array('min' => '6',																			'one' => 'seрi');                                  
		$ranges[] = Array('min' => '7',																			'one' => 'septiтi');                               
		$ranges[] = Array('min' => '8',																			'one' => 'astoтi');                                
		$ranges[] = Array('min' => '9',																			'one' => 'deviтi');                                
		$ranges[] = Array('min' => '10',																		'one' => 'desmit');                                
		$ranges[] = Array('min' => '11',																		'one' => 'vienpadsmit');                           
		$ranges[] = Array('min' => '12',																		'one' => 'divpadsmit');                            
		$ranges[] = Array('min' => '13',																		'one' => 'trоspadsmit');                           
		$ranges[] = Array('min' => '14',																		'one' => 'иetrpadsmit');                           
		$ranges[] = Array('min' => '15',																		'one' => 'piecpadsmit');                           
		$ranges[] = Array('min' => '16',																		'one' => 'seрpadsmit');                            
		$ranges[] = Array('min' => '17',																		'one' => 'septiтpadsmit');                         
		$ranges[] = Array('min' => '18',																		'one' => 'astoтpadsmit');                          
		$ranges[] = Array('min' => '19',																		'one' => 'deviтpadsmit');                          
		$ranges[] = Array('min' => '20', 'max' => '29',											'one' => 'divdesmit');                             
		$ranges[] = Array('min' => '30', 'max' => '39',											'one' => 'trоsdesmit');                            
		$ranges[] = Array('min' => '40', 'max' => '49',											'one' => 'иetrdesmit');                            
		$ranges[] = Array('min' => '50', 'max' => '59',											'one' => 'piecdesmit');                            
		$ranges[] = Array('min' => '60', 'max' => '69',											'one' => 'seрdesmit');                             
		$ranges[] = Array('min' => '70', 'max' => '79',											'one' => 'septiтdesmit');                          
		$ranges[] = Array('min' => '80', 'max' => '89',											'one' => 'astoтdesmit');                           
		$ranges[] = Array('min' => '90', 'max' => '99',											'one' => 'deviтdesmit');                           
		$ranges[] = Array('min' => '100', 'max' => '999',										'one' => 'simts',						'mltpl' => 'simti');   
		$ranges[] = Array('min' => '1000', 'max' => '999999',								'one' => 'tыkstotis',				'mltpl' => 'tыkstoрi');
		$ranges[] = Array('min' => '1000000', 'max' => '999999999',					'one' => 'miljons',					'mltpl' => 'miljoni'); 
		$ranges[] = Array('min' => '1000000000', 'max' => '999999999999',		'one' => 'miljards',				'mltpl' => 'miljardi');
		
		$this->ranges = $ranges;
	}
	
	function Format($value, $format=NULL) {
		return $this->num2text($value);
	}
	
	function num2text($num) {
		$str = '';
		
		$isvalid = preg_match("/^[0-9]+$/is", $num);
		if(!$isvalid)
			return '';
		
		if($num != intval($num))
			return '';
		
		$range = $this->numRange($num);
		
		if(!$range)
			return '';
		
		if($range['min'] > 0) {
			$last = $num % $range['min'];
		}else {
			$last = $num;
		}
		
		$base = $num - $last;
		
		if(intval($base) === intval($range['min']))
			$str = ($base > 99  ? $this->num2text($base / $range['min']).' ' : '').$range['one'];
		elseif($base / $range['min'] > 1)
			$str = $this->num2text($base / $range['min']).' '.$range['mltpl'];
		
		if($last > 0)
			$str .= ' '.$this->num2text($last);
		
		return $str;
	}
	
	function numRange($num) {
		foreach($this->ranges as $range)
			if((intval($range['min']) === intval($num) && !isset($range['max'])) || (isset($range['max']) && $range['min'] <= $num && $range['max'] >= $num)) return $range;
		
		return false;
	}
}

class MultiValidator extends Validator {
	var $Validators = Array();
	var $ValidatorClasses = Array();
	var $pointerField = NULL;
		
	function SetErrorMessage($error_msg = null) {
		$this->defaultErrorMessage = 'Invalid value';
		parent::SetErrorMessage($error_msg);
	}
	
	function SetPointerField($field) {
		$this->pointerField = $field;
	}
	
	function AddValidator($index, $class, $error_msg=null) {
		$class = strtolower($class);
		if(isset($this->ValidatorClasses[$class]))
			$this->Validators[$index] =& $this->Validators[$this->ValidatorClasses[$class]];
		else{
			$this->Validators[$index] = new $class($this->field, $this->Owner, $error_msg);
			$this->ValidatorClasses[$class] = $index;
		}
	}
	
	function Validate($value) {
		if (!parent::Validate($value)) return false;
		if(isset($this->Validators[$this->Owner->FieldValues[$this->pointerField]])) {
//			echo $this->field.': '.$this->Owner->FieldValues[$this->pointerField].' -> '.get_class($this->Validators[$this->Owner->FieldValues[$this->pointerField]])."<br>\n";
			return $this->Validators[$this->Owner->FieldValues[$this->pointerField]]->Validate($value);
		}
		return true;
	}
}

class LVPersonCodeValidator extends Validator {
	function SetErrorMessage($error_msg = null) {
		$this->defaultErrorMessage = 'Nepareizs personas kods';
		parent::SetErrorMessage($error_msg);
	}
	
	function Validate($value) {
		if (!parent::Validate($value)) return false;
		if ($this->Owner->FieldOptions[$this->field]['required'] || $value != '') {
			if(!preg_match("/^\d{6}-?\d{5}$/is", $value)) {
				$this->Owner->FieldErrors[$this->field] = 'Personas kods sastāv precīzi no 11 cipariem';
				return false;
			}

			if(!preg_match("/^(\d)(\d)(\d)(\d)(\d)(\d)-?(\d)(\d)(\d)(\d)(\d)$/is", $value, $regs) ||
				 !checkdate($regs[3].$regs[4],$regs[1].$regs[2],($regs[5].$regs[6] > date('y') ? '19':'20').$regs[5].$regs[6]) ||
				 time() <= mktime(0,0,0,$regs[3].$regs[4],$regs[1].$regs[2],($regs[5].$regs[6] > date('y') ? '19':'20').$regs[5].$regs[6]) ||
				 !($regs[7] >= 0 && $regs[7] <= 2) ||
				 ($regs[11] != 1101 - ($sum = $regs[1] + $regs[2]*6 + $regs[3]*3 + $regs[4]*7 + $regs[5]*9 + $regs[6]*10 + $regs[7]*5 + $regs[8]*8 + $regs[9]*4 + $regs[10]*2)-floor((1101-$sum)/11)*11)) {
				$this->Owner->FieldErrors[$this->field] = $this->ErrorMessage;
				return false;
			}

			$this->Owner->FieldValues[$this->field] = $regs[1].$regs[2].$regs[3].$regs[4].$regs[5].$regs[6].$regs[7].$regs[8].$regs[9].$regs[10].$regs[11];
		}
		return true;
	}
}

class IPValidator extends Validator {
	function SetErrorMessage($error_msg = null) {
		$this->defaultErrorMessage = 'Invalid IP address';
		parent::SetErrorMessage($error_msg);
	}
	
	function Validate($value) {
		if (!parent::Validate($value)) return false;
		if ($this->Owner->FieldOptions[$this->field]['required'] || $value != '') {
			if (!preg_match("/^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$/is", $value, $regs) || $regs[1] >= 255 || $regs[2] >= 255 || $regs[3] >= 255 || $regs[4] >= 255) {
				$this->Owner->FieldErrors[$this->field] = $this->ErrorMessage;
				return false;
			}
		}
		return true;
	}
}

class FWIPValidator extends Validator {
	function SetErrorMessage($error_msg = null) {
		$this->defaultErrorMessage = 'Invalid IP address';
		parent::SetErrorMessage($error_msg);
	}
	
	function Validate($value) {
		if (!parent::Validate($value)) return false;
		if ($this->Owner->FieldOptions[$this->field]['required'] || $value != '') {
			if ((!preg_match("/^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$/is", $value, $regs) || $regs[1] >= 255 || $regs[2] >= 255 || $regs[3] >= 255 || $regs[4] >= 255 || $value == '127.0.0.1') && $value != 'any') {
				$this->Owner->FieldErrors[$this->field] = $this->ErrorMessage;
				return false;
			}
		}
		return true;
	}
}

class FWPortValidator extends Validator {
	function SetErrorMessage($error_msg = null) {
		$this->defaultErrorMessage = 'Invalid Port';
		parent::SetErrorMessage($error_msg);
	}
	
	function Validate($value) {
		if (!parent::Validate($value)) return false;
		if ($this->Owner->FieldOptions[$this->field]['required'] || $value != '') {
			if (!preg_match("/^[0-9]+$/is", $value, $regs) || $value == 21 || $value == 22 || $value == 25 || $value == 80 || $value == 110 || $value == 995 || $value == 465) {
				$this->Owner->FieldErrors[$this->field] = $this->ErrorMessage;
				return false;
			}
		}
		return true;
	}
}

class HostValidator extends Validator {
	function SetErrorMessage($error_msg = null) {
		$this->defaultErrorMessage = 'Invalid host name';
		parent::SetErrorMessage($error_msg);
	}
	
	function Validate($value) {
		if (!parent::Validate($value)) return false;
		if ($this->Owner->FieldOptions[$this->field]['required'] || $value != '') {
			if (!preg_match("/^[a-z0-9-]+(\.[a-z0-9-]+)*\.[a-z]+$/is", $value)) {
				$this->Owner->FieldErrors[$this->field] = $this->ErrorMessage;
				return false;
			}
		}
		return true;
	}
}

class AHostValidator extends Validator {
	var $baseField;
	function SetErrorMessage($error_msg = null) {
		$this->defaultErrorMessage = 'Invalid "A" records host name';
		parent::SetErrorMessage($error_msg);
	}
	
	function Validate($value) {
		if (!parent::Validate($value)) return false;
		if ($this->Owner->FieldOptions[$this->field]['required'] || $value != '') {
			if (!preg_match("/^[a-z0-9-]+(\.[a-z0-9-]+)*\.[a-z]+\.$/is", $value)) {
				$this->Owner->FieldErrors[$this->field] = $this->ErrorMessage;
				return false;
			}
			/*
			if (isset($this->baseField) && in_array($this->Owner->FieldValues[$this->pointerField], $this->valsField) && preg_match("/\.$/is", $value) && !preg_match("/".str_replace('.', '\.', str_replace('\\', '\\\\', $this->Owner->GetField($this->baseField)))."\.$/is", $value)) {
				$this->Owner->FieldErrors[$this->field] = '"A" record`s hostname should ends with zone name and dot';
				return false;
			}*/
		}
		return true;
	}
	
	function SetBaseField($field, $pointer, $vals) {
		$this->baseField = $field;
		$this->pointerField = $pointer;
		$this->valsField = $vals;
	}
}

class ACustomHostValidator extends Validator {
	var $baseField;
	function SetErrorMessage($error_msg = null) {
		$this->defaultErrorMessage = 'Invalid "A" custom host name';
		parent::SetErrorMessage($error_msg);
	}
	
	function Validate($value) {
		if (!parent::Validate($value)) return false;
		if ($this->Owner->FieldOptions[$this->field]['required'] || $value != '') {
			if (!preg_match("/^[a-z0-9-]+(\.[a-z0-9-]+)*\.?$/is", $value)) {
				$this->Owner->FieldErrors[$this->field] = $this->ErrorMessage;
				return false;
			}/*
			if (isset($this->baseField) && preg_match("/\.$/is", $value) && !preg_match("/".str_replace('.', '\.', str_replace('\\', '\\\\', $this->Owner->GetField($this->baseField)))."\.$/is", $value)) {
				$this->Owner->FieldErrors[$this->field] = '"A" record`s hostname should ends with zone and dot or without any dot';
				return false;
			}*/
		}
		return true;
	}
	
	function SetBaseField($field) {
		$this->baseField = $field;
	}
}

class UnixPathValidator extends Validator {
	var $NotEmpty = 1;
	function SetErrorMessage($error_msg = null) {
		$this->defaultErrorMessage = 'Invalid path';
		parent::SetErrorMessage($error_msg);
	}
	
	function Validate($value) {
		if (!parent::Validate($value)) return false;
		if ($this->Owner->FieldOptions[$this->field]['required'] || $value != '') {
			if (!preg_match("/^\/[a-zA-Z0-9._-]".($this->NotEmpty ? "+" : "*")."(:?\/[a-zA-Z0-9._-]+)*$/is", $value) || preg_match("/\.\.\//is", $value)) {
				$this->Owner->FieldErrors[$this->field] = $this->ErrorMessage;
				return false;
			}
		}
		return true;
	}
}

class EmailPartValidator extends Validator {
	function SetErrorMessage($error_msg = null) {
		$this->defaultErrorMessage = 'Invalid email part';
		parent::SetErrorMessage($error_msg);
	}
	
	function Validate($value) {
		if (!parent::Validate($value)) return false;
		if ($this->Owner->FieldOptions[$this->field]['required'] || $value != '') {
			if (!preg_match("/^[A-Za-z0-9_.-]+$/is", $value)) {
				$this->Owner->FieldErrors[$this->field] = $this->ErrorMessage;
				return false;
			}
		}
		return true;
	}
}

class TTLValidator extends Validator {
	function SetErrorMessage($error_msg = null) {
		$this->defaultErrorMessage = 'Invalid TTL';
		parent::SetErrorMessage($error_msg);
	}
	
	function Validate($value) {
		if (!parent::Validate($value)) return false;
		if ($this->Owner->FieldOptions[$this->field]['required'] || $value != '') {
			if (!preg_match("/^([0-9]+[WwDdHhMm]{0,1})+$/is", $value)) {
				$this->Owner->FieldErrors[$this->field] = $this->ErrorMessage;
				return false;
			}
		}
		return true;
	}
}

class UniquePathValidator extends Validator {
	var $NotEmpty = 1;
	function SetErrorMessage($error_msg = null) {
		$this->defaultErrorMessage = 'Path not unique';
		parent::SetErrorMessage($error_msg);
	}
	
	function Validate($value) {
		if (!parent::Validate($value)) return false;
		if ($this->Owner->FieldOptions[$this->field]['required'] || $value != '') {
			$path = $value;
			$paths = Array();
			$last = 0;
			while ($path != '' && is_int($last)) {
				$paths[] = $path;
				$last = strrpos($path, '/');
				$path = substr($path, 0, $last);
			}
			
			$sql = 'SELECT COUNT(*) FROM `'.$this->Owner->table_name.'` WHERE (`'.$this->field.'` LIKE \''.join('\' OR `'.$this->field.'` LIKE \'', $paths).'\' OR `'.$this->field.'` LIKE \''.$value.'%\')';
			if($this->Owner->GetId() > 0)
				$sql .= ' AND `'.$this->Owner->id_field.'` != '.$this->Owner->GetId();
			$count = $this->Owner->Conn->GetOne($sql);
			if( $count > 0 ) {
				echo $count;
				$this->Owner->FieldErrors[$this->field] = $this->ErrorMessage;
				return false;
			}
		}
		return true;
	}
}

class QuotaValidator extends Validator {
	var $AllowedQtyField = '';
	var $CurrentQtySQL = '';
	var $FilterValueSQL = '';
		
	function SetErrorMessage($error_msg = null) {
		$this->defaultErrorMessage = 'Quota reached';
		parent::SetErrorMessage($error_msg);
	}
	
	function SetAllowedField($field) {
		$this->AllowedQtyField = $field;
	}
	
	function SetCurrentSQL($sql) {
		$this->CurrentQtySQL = $sql;
	}
	
	function SetFilterSQL($sql) {
		$this->FilterValueSQL = $sql;
	}
	
	function Validate($value) {
		if (!parent::Validate($value)) return false;
		$keyId = $this->Owner->suId();
		$allowedQty = $this->Owner->Conn->GetOne('SELECT '.$this->AllowedQtyField.' FROM sysusers WHERE su_id='.$keyId);
		$currentQty = $this->Owner->Conn->GetOne(sprintf($this->CurrentQtySQL.($this->Owner->GetId() ? ' AND `'.$this->Owner->id_field.'`!='.$this->Owner->GetId() : ''),$keyId));
		if ($allowedQty > -1 && $allowedQty <= $currentQty) {
			$this->Owner->FieldErrors[$this->field] = $this->ErrorMessage;
			return false;
		}
		return true;
	}
}

class UniqueNotRequiredValidator extends Validator {
	var $uniqueFields = Array();
		
	function SetErrorMessage($error_msg = null) {
		$this->defaultErrorMessage = 'Such record exists';
		parent::SetErrorMessage($error_msg);
	}
	
	function SetUniqueFields($fields) {
		$this->uniqueFields = $fields;
	}
	
	function Validate($value) {
		if ($this->Owner->FieldOptions[$this->field]['required'] || $value != '') {
			$sql = 'SELECT COUNT(*) AS recs FROM '.$this->Owner->table_name.' WHERE ';
			
			if(count($this->uniqueFields) === 0)
				$this->uniqueFields[] = $this->field;
			
			$where = Array();
			foreach($this->uniqueFields as $unique) {
				$where[] = '`'.$unique.'` = '.$this->Conn->qstr($this->Owner->GetDBField($unique));
			}
			
			if($this->Owner->GetId() > 0) {
				$where[] = '`'.$this->Owner->id_field.'` != '.$this->Owner->GetId();
			}
			
			$sql .= join(' AND ', $where);
			
			$row = $this->Owner->Conn->GetRow($sql);
			
			if($row['recs'] > 0) {
				$this->Owner->FieldErrors[$this->field] = $this->ErrorMessage;
				return false;
			}
		}
		return true;
	}
}

class LVPhoneShortValidator extends Validator {
	function SetErrorMessage($error_msg = null) {
		$this->defaultErrorMessage = 'Tālruņa numurs ir 8 cipu skaitlis';
		parent::SetErrorMessage($error_msg);
	}
	
	function Validate($value) {
		if (!parent::Validate($value)) return false;
		if ($this->Owner->FieldOptions[$this->field]['required'] || $value != '') {
			if (!preg_match("/^\d{8}$/is", $value)) {
				$this->Owner->FieldErrors[$this->field] = $this->ErrorMessage;
				return false;
			}
		}
		return true;
	}
}

class LVPhoneValidator extends Validator {
	function SetErrorMessage($error_msg = null) {
		$this->defaultErrorMessage = 'Invalid Phone Number';
		parent::SetErrorMessage($error_msg);
	}
	
	function Validate($value) {
		if (!parent::Validate($value)) return false;
		if ($this->Owner->FieldOptions[$this->field]['required'] || $value != '') {
			if (!preg_match("/^\+371[68]{1}[0-9]{7}$/is", $value)) {
				$this->Owner->FieldErrors[$this->field] = $this->ErrorMessage;
				return false;
			}
		}
		return true;
	}
}

class LVFaxValidator extends Validator {
	function SetErrorMessage($error_msg = null) {
		$this->defaultErrorMessage = 'Invalid Phone Number';
		parent::SetErrorMessage($error_msg);
	}
	
	function Validate($value) {
		if (!parent::Validate($value)) return false;
		if ($this->Owner->FieldOptions[$this->field]['required'] || $value != '') {
			if (!preg_match("/^\+371[0-9]{8}$/is", $value)) {
				$this->Owner->FieldErrors[$this->field] = $this->ErrorMessage;
				return false;
			}
		}
		return true;
	}
}

class LVCellValidator extends Validator {
	function SetErrorMessage($error_msg = null) {
		$this->defaultErrorMessage = 'Invalid Phone Number';
		parent::SetErrorMessage($error_msg);
	}
	
	function Validate($value) {
		if (!parent::Validate($value)) return false;
		if ($this->Owner->FieldOptions[$this->field]['required'] || $value != '') {
			if (!preg_match("/^\+3712[0-9]{7}$/is", $value)) {
				$this->Owner->FieldErrors[$this->field] = $this->ErrorMessage;
				return false;
			}
		}
		return true;
	}
}

class PDFFileFormatter extends Formatter {
	var $options = Array();
	
	function Format($value, $format=NULL) {
		return basename($value);
	}
}

class DateRangeValidator extends Validator {
	var $fieldFrom;
	var $fieldUntil;
	var $type = 'NOT IN';
	var $keyFields = Array();
		
	function SetErrorMessage($error_msg = null) {
		$this->defaultErrorMessage = $this->type == 'NOT IN' ? 'field not unique' : 'field not in range!';
		parent::SetErrorMessage($error_msg);
	}
	
	function SetFieldFrom($field) {
		$this->fieldFrom = $field;
	}
	
	function SetFieldUntil($field) {
		$this->fieldUntil = $field;
	}
	
	function SetAddKeyField($field) {
		$this->keyFields[] = $field;
	}
	
	function SetVType($type) {
		$this->type = strtoupper($type) == 'NOT IN' ? $type : 'NOT IN RANGE';
	}
	
	function Validate($value) {
		if (!parent::Validate($value)) return false;
		
		$pAddQuery = Array();
		if(is_array($this->keyFields) && count($this->keyFields) > 0)
			foreach($this->keyFields as $kField)
				$pAddQuery[] = '`'.$kField.'` = '.$this->Owner->GetDBField($kField);
		
		if(count($pAddQuery) > 0)
			$addQuery = join(' AND ', $pAddQuery);
		
		if($this->type == 'NOT IN')
			$sql = 'SELECT `'.$this->Owner->id_field.'` FROM `'.$this->Owner->table_name.'` WHERE `'.$this->fieldFrom.'` <= '.$value.' AND `'.$this->fieldUntil.'` >= '.$value.($addQuery ? ' AND '.$addQuery:'').($this->Owner->GetId() ? ' AND `'.$this->Owner->id_field.'`!='.$this->Owner->GetId() : '');
		else
			$sql = 'SELECT `'.$this->Owner->id_field.'` FROM `'.$this->Owner->table_name.'` WHERE `'.$this->fieldFrom.'` > '.$value.' AND `'.$this->fieldUntil.'` < '.$value.($addQuery ? ' AND '.$addQuery:'').($this->Owner->GetId() ? ' AND `'.$this->Owner->id_field.'`!='.$this->Owner->GetId() : '');
		
		//echo "$sql<br>\n";
		
		$check = $this->Owner->Conn->GetOne($sql);
		if ($check > 0) {
			$this->Owner->FieldErrors[$this->field] = $this->ErrorMessage;
			return false;
		}
		return true;
	}
}

class EmptyUniqueValidator extends UniqueValidator {
	
	function Validate($value) {
		if ($this->Owner->FieldOptions[$this->field]['required'] || (isset($value) && strlen($value) > 0)) {
			$ret = parent::Validate($value);
			return $ret;
		}
		
		return true;
	}
}

?>