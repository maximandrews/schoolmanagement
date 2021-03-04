<?php

class Validator extends Base {
	var $Options = Array();
	var $field;
	var $ErrorMessage;
	var $defaultErrorMessage = 'Obligāti aizpildāms lauks';
	
	function Validator($field, &$Owner, $error_msg=null) {
		$this->field = $field;
		$this->SetOwner($Owner);
		$this->SetErrorMessage($error_msg);
	}
	
	function SetErrorMessage($error_msg = null) {
		if ($error_msg != null) 
			$this->ErrorMessage = $error_msg;
		else
			$this->ErrorMessage = $this->defaultErrorMessage;
	}
	
	function Validate($value) {
		if ($this->Owner->FieldOptions[$this->field]['required'] && (!isset($value) || (!is_array($value) && strlen($value) == 0 || is_array($value) && !count($value)))) {
			$this->Owner->FieldErrors[$this->field] = $this->ErrorMessage;
			return false;
		}
		
		return true;
	}
}

class FloatValidator extends Validator {
	
	var $greater_th_zero = 0;
	var $gr_eq_th_zero = 0;
	
	function SetErrorMessage($error_msg = null) {
		$this->defaultErrorMessage = 'Nederīgs formāts ( Tikai peldošie vai veseli cipari )';
		parent::SetErrorMessage($error_msg);
	}
	
	function Validate($value) {
			
		if (!parent::Validate($value)) 
			return false;

		if ($this->Owner->FieldOptions[$this->field]['required'] || $value != '') {
			$this->Owner->FieldValues[$this->field] = preg_replace("/,/is", '.', $this->Owner->FieldValues[$this->field]);			
			
			if(preg_match("/^\.{1}/is", $this->Owner->FieldValues[$this->field]))
				$this->Owner->FieldValues[$this->field] = '0'.$this->Owner->FieldValues[$this->field];
			
			$value = $this->Owner->FieldValues[$this->field];
			
			if (!preg_match("/^[+\-]{0,1}[0-9]*[,.]{0,1}[0-9]*$/is", $value)) {
				$this->Owner->FieldErrors[$this->field] = $this->ErrorMessage;
				return false;
			}
			
			if ( $this->greater_th_zero && $value <= 0 ) {
				$this->Owner->FieldErrors[$this->field] = $this->ErrorMessage;
				return false;
			}
			
			if ( $this->gr_eq_th_zero && $value < 0 ) {
				$this->Owner->FieldErrors[$this->field] = $this->ErrorMessage;
				return false;
			}
		}
		return true;
	}
}

class IntegerValidator extends Validator {
	public $minValue = null;
	public $maxValue = null;

	function SetErrorMessage($error_msg = null) {
		$this->defaultErrorMessage = 'Nederīgs formāts ( Tikai veseli skaitļi )';
		parent::SetErrorMessage($error_msg);
	}
	
	function Validate($value) {
		if (!parent::Validate($value)) 
			return false;
			
		if ($this->Owner->FieldOptions[$this->field]['required'] || $value != '') {
			if (!preg_match("/^[+\-]{0,1}[0-9]*$/is", $value)) {
				$this->Owner->FieldErrors[$this->field] = $this->ErrorMessage;
				return false;
			}
			if ( $this->minValue && $value < $this->minValue ) {
				$this->Owner->FieldErrors[$this->field] = 'Minimāla vērtība šim laukam ir '.$this->minValue.'!';
				return false;
			}
			if ( $this->maxValue && $value > $this->maxValue ) {
				$this->Owner->FieldErrors[$this->field] = 'Maksimāla vērtība šim laukam ir '.$this->maxValue.'!';
				return false;
			}
		}
		return true;
	}
}

class EmailValidator extends Validator {
	function SetErrorMessage($error_msg = null) {
		$this->defaultErrorMessage = 'E-pasts ievadīts nekorekti';
		parent::SetErrorMessage($error_msg);
	}
	
	function Validate($value) {
		if (!parent::Validate($value)) 
			return false;

		if ($this->Owner->FieldOptions[$this->field]['required'] || $value != '') {
			if (!preg_match("/^[a-z0-9_.-]+@([a-z0-9.-]+)+(\.[a-z0-9]+)$/is", $value)) {
				$this->Owner->FieldErrors[$this->field] = $this->ErrorMessage;
				return false;
			}
		}
		return true;
	}
}

class LoginValidator extends Validator {
	
	var $min_length;
	var $max_length;
	function Validate($value) {
		if (!parent::Validate($value)) 
			return false;
		
		if (!$this->min_length)
			$this->min_length = 4;
		if (!$this->max_length)
			$this->max_length = 25;
			
		if ($this->Owner->FieldOptions[$this->field]['required'] || $value != '') {
			if (strlen($value) < $this->min_length || strlen($value) >  $this->max_length) {
				$this->Owner->FieldErrors[$this->field]['msg'] = 'Nepareizs lauka garums';
				$this->Owner->FieldErrors[$this->field]['code'] = 4;
				return false;
			}
			if (preg_match("/[^a-zA-Z0-9_]/is", $value)) {
				$this->Owner->FieldErrors[$this->field]['msg'] = 'Laukā ir kļūdaini simboli';
				$this->Owner->FieldErrors[$this->field]['code'] = 5;
				return false;
			}
		}
		return true;
	}
}

class PasswordValidator extends Validator {
	
	var $min_length;
	var $max_length;
	var $repass_field;
	var $format;
	//$this->Owner->CreateField
	// 
	
	function Validate($value) {
//	 	echo 'Pass: '.$value.'<br>';	 	
		
		if (!$this->min_length)
			$this->min_length = 4;
		if (!$this->max_length)
			$this->max_length = 25;

		if (!parent::Validate($value))
	 		return false;

	 	//echo 'Min: '.$this->min_length."<br>\n";	 	
	 	//echo 'Max: '.$this->max_length."<br>\n";	 	

		if ($this->Owner->FieldOptions[$this->field]['required']) {
			if (!isset($this->Owner->FieldValues[$this->repass_field]) || $this->Owner->FieldValues[$this->repass_field] == '') {
				$this->Owner->FieldErrors[$this->field]['msg'] = 'Parole atkārtoti nav ievadīta';
				$this->Owner->FieldErrors[$this->field]['code'] = 9;
				return false;
			}
			if (strlen($value) < $this->min_length || strlen($value) >  $this->max_length) {
				//echo strlen($value)."<br>\n";
				$this->Owner->FieldErrors[$this->field]['msg'] = 'Nepareizs lauka garums';
				$this->Owner->FieldErrors[$this->field]['code'] = 7;
				return false;
			}
			if (preg_match("/ /is", $value)) {
				$this->Owner->FieldErrors[$this->field]['msg'] = 'Parole ievadīta ar atstārpem';
				$this->Owner->FieldErrors[$this->field]['code'] = 8;
				return false;
			}
			if ($this->Owner->FieldValues[$this->repass_field] != $value) {
				$this->Owner->FieldErrors[$this->field]['msg'] = 'Paroles neatbilst';
				$this->Owner->FieldErrors[$this->field]['code'] = 10;
				$this->Owner->FieldErrors[$this->repass_field]['msg'] = 'Paroles neatbilst';
				$this->Owner->FieldErrors[$this->repass_field]['code'] = 10;
				return false;
			}
			if ($this->format == "SHA1")
				$this->Owner->FieldValues[$this->field] = SHA1($value);
			elseif ($this->format == "MD5")
				$this->Owner->FieldValues[$this->field] = MD5($value);
			elseif ($this->format == "MYSQLENCRYPT")
				$this->Owner->FieldValues[$this->field] = $this->Owner->Conn->GetOne('SELECT ENCRYPT(\''.mysql_escape_string($value).'\')');
			elseif ($this->format == "MYSQLPASSWORD")
				$this->Owner->FieldValues[$this->field] = $this->Owner->Conn->GetOne('SELECT PASSWORD(\''.mysql_escape_string($value).'\')');
			elseif ($this->format == "EMAILCRYPT")
				$this->Owner->FieldValues[$this->field] = crypt($value, '$1$caea3837$');
					
		}
		return true;
	}
}


class SpecialCharsValidator extends Validator {
	// if specified chars are present in field value, then return error	
	
	function Validate($value) {
		if (!parent::Validate($value))
	 		return 0;
		
		if ($this->Owner->FieldOptions[$this->field]['required']) {
			if( preg_match("/[^-a-zA-Z0-9_]/is",$value) ) {
				$this->Owner->FieldErrors[$this->field]['msg'] = 'Speciālie simboli nav atļauti';
				$this->Owner->FieldErrors[$this->field]['code'] = 40;
				return 0;
			}
					
		}
		return 1;
	}
}

class TimeValidator extends Validator {
	function Validate($value) {
	 	if ( $value == '' ) $this->Owner->SetDBField($this->field,time());
		return parent::Validate($value);
	}
}

class EditorValidator extends Validator 
{	
	function Validate($value) {
		if (!parent::Validate($value)) return false;
		// $value = $this->xmlClear($value);
		$this->Owner->FieldValues[$this->field] = $value;
		if ($this->Owner->FieldOptions[$this->field]['required'] || $value != '') {
			// strtoupper($value) == "<P></P>" or
			if ( $value == "" ) {
				$this->Owner->FieldErrors[$this->field]['msg'] = 'Kļūdains rakstlaukums';
				$this->Owner->FieldErrors[$this->field]['code'] = 11;
				return false;
			}
		}
		return true;
	}

	function xmlClear($value) {
		// echo " EditorValidator :: xml Clear <br>";
		// echo "before: ".htmlspecialchars($value).'<BR><BR>'; 
		$tmp = preg_replace ("/<\?xml[^>]*>/is", '', $value); 
		$tmp = preg_replace("/&lt;%/is","<%",$tmp);
		$tmp = preg_replace("/%&gt;/is","%>",$tmp);		
		// echo "after: ".htmlspecialchars($value).'<BR><BR>'; 
		return $tmp;
	}
}


class CSVOptionsValidator extends Validator {
	
	var $options = Array();
	
	function SetErrorMessage($error_msg = null) {
		$this->defaultErrorMessage = 'Nav tādas izvēles';
		parent::SetErrorMessage($error_msg);
	}
	
	function SetOptions($options) {
		if(is_array($options)) {
			$this->options = array_flip($options);
			return true;
		}else
			return false;
	}
	
	function Validate($value) {
		if (!parent::Validate($value)) 
			return false;
			
		if ($this->Owner->FieldOptions[$this->field]['required'] || $value != '')
		{
			if (!$this->options[$value]) {
				$this->Owner->FieldErrors[$this->field] = sprintf($this->ErrorMessage, $value);
				return false;
			}
		}
		return true;
	}
}

class OptionsValidator extends Validator {
	
	function SetErrorMessage($error_msg = null) {
		$this->defaultErrorMessage = 'Nav tādas izvēles';
		parent::SetErrorMessage($error_msg);
	}
	
	function Validate($value) {
		if (!parent::Validate($value)) 
			return false;
			
		if ($this->Owner->FieldOptions[$this->field]['required'] || $value)
		{
			$options = method_exists($this->Owner->Formatters[$this->field], 'GetOptions') ? $this->Owner->Formatters[$this->field]->GetOptions():Array();
			if (!isset($options[$value])) {
				$this->Owner->FieldErrors[$this->field] = $value ? sprintf($this->ErrorMessage, $value) : 'Obligāti aizpildāms lauks';
				return false;
			}
		}
		return true;
	}
}

// System Validator
class UniqueValidator extends Validator {
	var $uniqueFields = Array();
		
	function SetErrorMessage($error_msg = null) {
		$this->defaultErrorMessage = 'Ieraksts eksistē';
		parent::SetErrorMessage($error_msg);
	}
	
	function SetUniqueFields($fields) {
		$this->uniqueFields = $fields;
	}
	
	function Validate($value) {
		$sql = 'SELECT COUNT(*) AS recs FROM '.$this->Owner->table_name.' WHERE ';
		
		if(count($this->uniqueFields) === 0)
			$this->uniqueFields[] = $this->field;
		
		$where = Array();
		foreach($this->uniqueFields as $unique) {
			$where[] = '`'.$unique.'` = \''.mysql_escape_string($this->Owner->GetDBField($unique)).'\'';
		}
		
		if($this->Owner->GetId() > 0) {
			$where[] = '`'.$this->Owner->id_field.'` != '.$this->Owner->GetId();
		}
		
		$sql .= join(' AND ', $where);
		
		//echo "$sql<br>\n";
		
		$row = $this->Owner->Conn->GetRow($sql);
		
		if($row['recs'] > 0) {
			$this->Owner->FieldErrors[$this->field] = $this->ErrorMessage;
			return false;
		}else
			return true;
	}
}
?>