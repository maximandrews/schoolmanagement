<?php

class Formatter extends Base {
	var $format = "%s";
	var $field;
	
	function Formatter($field, &$Owner) {
		$this->field = $field;
		$this->SetOwner($Owner);
	}
	
	function Format($value, $format=NULL) {
		if (isset($format)) $this->format = $format;
		return sprintf($this->format, $value);
	}
	
	function Parse($value) {
		return $value;
	}
}

class SizeFormatter extends Formatter {
	var $format_size = FILE_SIZE;
	function Format($value, $format=NULL) {
		if($value < 0) $value = 0;
		return $value / $this->format_size;
	}
}

class OptionsFormatter extends Formatter {
	var $options = Array();
	
	function SetOptions($options) {
		$this->options = $options;
	}
	
	function GetOptions() {
		return $this->options;
	}
	
	function Format($value, $format=NULL) {
		return isset($this->options[$value]) ? $this->options[$value] : '';
	}
	
	function Parse($value) {
		return $value;
	}
}

class CheckBoxesFormatter extends OptionsFormatter {
	var $separator = ', ';

	function Format($value, $format=NULL) {
		$values = explode('|,|',substr($value, 1, strlen($value) - 2));
		
		foreach($values as $val) {
			$val_arr[$val] = isset($this->options[$val]) ? $this->options[$val] : '';
		}

		return join($this->separator, $val_arr);
	}
}

class DateFormatter extends Formatter {
	var $hour = 0;
	var $minute = 0;
	var $second = 0;
	var $month = 1;
	var $day = 1;
	var $year = 1970;
	var $patterns = Array();
	var $months = Array();
	var $months_long = Array();
	var $weekHead = Array();
	var $error = false;

	function DateFormatter($field, &$Owner) {
		parent::Formatter($field, $Owner);
		if($this->Owner->App) {
			$app = $this->Owner->App;
		}

		$this->patterns['n'] = '([0-9]{1,2})';
		$this->patterns['m'] = '([0-9]{1,2})';
		$this->patterns['M'] = '([A-Za-z]{3})';
		$this->patterns['d'] = '([0-9]{1,2})';
		$this->patterns['Y'] = '([0-9]{4})';
		$this->patterns['y'] = '([0-9]{2})';
		$this->patterns['H'] = '([0-9]{2})';
		$this->patterns['h'] = '([0-9]{1,2})';
		$this->patterns['i'] = '([0-9]{2})';
		$this->patterns['s'] = '([0-9]{2})';
		$this->patterns['a'] = '(am|pm)';
		$this->patterns['A'] = '(AM|PM)';
	}
	
	function Format($value, $format=NULL) {
		if (isset($format)) $this->format = $format;
		
		if (($this->Owner->FieldErrors[$this->field] == null || !$this->error) && !empty($value) && preg_match('/^[0-9]+$/', $value)){
			
			$output = date($this->format, $value);
			if(isset($this->weekHead_long) && is_array($this->weekHead_long))
				$output = str_replace(date('l',$value), $this->weekHead_long[date('w',$value)], $output);
			if(isset($this->$this->months_long) && is_array($this->months_long))
				$output = str_replace(date('F',$value), $this->months_long[date('n',$value)], $output);
			if(isset($this->$this->months) && is_array($this->months))
				$output = str_replace(date('M',$value), $this->months[date('n',$value)], $output);
			if(isset($this->$this->weekHead) && is_array($this->weekHead))
				$output = str_replace(date('D',$value), $this->weekHead[date('w',$value)], $output);
			
			return $output;
		}elseif(empty($value))
			return '';
		else
			return $value;
	}
	
	function Parse($value, $format=NULL) {		
		if (isset($format)) $this->format = $format;
		
		if ($value == '') {
			return '';
		}
		
		$holders_mask = preg_replace("/\w{1}/is", '(\\0)', $this->format);
		if (!preg_match("/".$holders_mask."/is", $this->format, $holders)) {
			$this->Owner->FieldErrors[$this->field]['msg'] = 'Bad date format';
			$this->Owner->FieldErrors[$this->field]['code'] = 11;
			$this->error = true;
			return $value;
		}
		
		$values_mask = $this->format;
		
		$coords = Array();
		
		foreach($this->patterns as $key => $val) {
			$tmpid = strpos($values_mask, $key);
			if(is_int($tmpid)) $coords[$tmpid] = $key;
		}
		
		ksort($coords, SORT_NUMERIC);
		$adds = 0;
		
		foreach($coords as $key => $val) {
			$values_mask = substr($values_mask, 0, $key+$adds).$this->patterns[$val].substr($values_mask, $key+$adds+1, strlen($values_mask));
			$adds += strlen($this->patterns[$val]) - 1;
		}
		
		//echo " values_mask : $values_mask <br>";
		
		if (!preg_match("/".$values_mask."/is", $value, $values)) {
			$this->Owner->FieldErrors[$this->field] = 'Bad date given';
			$this->error = true;
			return $value;
		}
		
		for ($i = 1; $i <= count($holders); $i++) {
			if(isset($holders[$i]))
			switch ($holders[$i]) {
				case 'n':
				case 'm':
					$this->month = $values[$i];
					$this->month = preg_replace("/^0{1}/is", '', $this->month);
					break;
				case 'M':
					$this->month = array_search($values[$i], $this->months);
					break;
				case 'd':
					$this->day = $values[$i];
					$this->day = preg_replace("/^0{1}/is", '', $this->day);
					break;
				case 'Y':
					$this->year = $values[$i];
					break;
				case 'y':
					$this->year = $values[$i] >= 70 ? 1900 + $values[$i] : 2000 + $values[$i];
					break;
				case 'H':
				case 'h':
					$this->hour = $values[$i];
					$this->hour = preg_replace("/^0{1}/is", '', $this->hour);
					break;
				case 'i':
					$this->minute = $values[$i];
					$this->minute = preg_replace("/^0{1}/is", '', $this->minute);
					break;
				case 's':
					$this->second = $values[$i];
					$this->second = preg_replace("/^0{1}/is", '', $this->second);
					break;
				case 'a':
				case 'A':
					if ($values[$i] == 'pm' || $values[$i] == 'PM') {
						$this->hour += 12;
						if ($this->hour == 24) $this->hour = 12;
					}
					elseif ($values[$i] == 'am' || $values[$i] == 'AM') {
						if ($this->hour == 12) $this->hour = 0;
					}
					break;
			}
		}
		
		//echo "day: $this->day, month: $this->month, year: $this->year, hour: $this->hour, minute: $this->minute<br>";
		
		if (!($this->year >= 1970 && $this->year <= 2037)) {
			$this->Owner->FieldErrors[$this->field] = 'Ievadīts nepareizs datums (gads: '.$this->year.')';
			$this->error = true;
			return $value;
		}
				
		if (!($this->month >= 1 && $this->month <= 12)) {
			$this->Owner->FieldErrors[$this->field] = 'Ievadīts nepareizs datums (mēnesis: '.$this->month.')';
			$this->error = true;
			return $value;
		}
		
		$months_days = Array ( 1 => 31,2 => 28, 3 => 31, 4 => 30,5 => 31,6 => 30, 7 => 31, 8 => 31,9 => 30,10 => 31,11 => 30,12 => 31);
		if ($this->year % 4 == 0) $months_days[2] = 29;
		
		
		if (!($this->day >=1 && $this->day <= $months_days[$this->month])) {
			$this->Owner->FieldErrors[$this->field] = 'Ievadīts nepareizs datums (diena: '.$this->day.')';
			$this->error = true;
			return $value;
		}
		
		if (!($this->hour >=0 && $this->hour <= 23)) {
			$this->Owner->FieldErrors[$this->field] = 'Ievadīts nepareizs laiks (stundas: '.$this->hour.')';
			$this->error = true;
			return $value;
		}
		
		if (!($this->minute >=0 && $this->minute <= 59)) {
			$this->Owner->FieldErrors[$this->field] = 'Ievadīts nepareizs laiks (minūtes: '.$this->minute.')';
			$this->error = true;
			return $value;
		}
		
		if (!($this->second >=0 && $this->second <= 59)) {
			$this->Owner->FieldErrors[$this->field] = 'Ievadīts nepareizs laiks (sekundes: '.$this->second.')';
			$this->error = true;
			return $value;
		}
		// echo "day: $this->day, month: $this->month, year: $this->year, hour: $this->hour, minute: $this->minute<br>";
		return (mktime($this->hour, $this->minute, $this->second, $this->month, $this->day, $this->year));
	}
}

class MySQLDateFormatter extends Formatter {
	public $hour = 0;
	public $minute = 0;
	public $second = 0;
	public $month = 1;
	public $day = 1;
	public $year = 1000;
	public $mysqlformat = 'Y-m-d H:i:s';
	public $format = 'd.m.Y';
	public $patterns = Array();
	public $error = false;

	function __construct($field, &$Owner) {
		parent::__construct($field, $Owner);
		if($this->Owner->App) $app = $this->Owner->App;

		$this->patterns['n'] = '([0-9]{1,2})';
		$this->patterns['m'] = '([0-9]{1,2})';
		$this->patterns['M'] = '([A-Za-z]{3})';
		$this->patterns['d'] = '([0-9]{1,2})';
		$this->patterns['Y'] = '([0-9]{4})';
		$this->patterns['y'] = '([0-9]{2})';
		$this->patterns['H'] = '([0-9]{2})';
		$this->patterns['h'] = '([0-9]{1,2})';
		$this->patterns['i'] = '([0-9]{2})';
		$this->patterns['s'] = '([0-9]{2})';
		$this->patterns['a'] = '(am|pm)';
		$this->patterns['A'] = '(AM|PM)';
	}
	
	function Format($value, $format=NULL) {
		if (isset($format)) $this->format = $format;
		
		$date = DateTime::createFromFormat($this->mysqlformat, $value);
		
		return $date ? $date->format($this->format, $value) : '';
	}
	
	function Parse($value, $format=NULL) {		
		if (isset($format)) $this->format = $format;
		
		if ($value == '') {
			return '';
		}
		
		$holders_mask = preg_replace("/\w{1}/is", '(\\0)', $this->format);
		if (!preg_match("/".$holders_mask."/is", $this->format, $holders)) {
			$this->Owner->FieldErrors[$this->field]['msg'] = 'Bad date format';
			$this->Owner->FieldErrors[$this->field]['code'] = 11;
			$this->error = true;
			return $value;
		}
		
		$values_mask = $this->format;
		
		$coords = Array();
		
		foreach($this->patterns as $key => $val) {
			$tmpid = strpos($values_mask, $key);
			if(is_int($tmpid)) $coords[$tmpid] = $key;
		}
		
		ksort($coords, SORT_NUMERIC);
		$adds = 0;
		
		foreach($coords as $key => $val) {
			$values_mask = substr($values_mask, 0, $key+$adds).$this->patterns[$val].substr($values_mask, $key+$adds+1, strlen($values_mask));
			$adds += strlen($this->patterns[$val]) - 1;
		}
		
		//echo " values_mask : $values_mask <br>";
		
		if (!preg_match("/".$values_mask."/is", $value, $values)) {
			$this->Owner->FieldErrors[$this->field] = 'Bad date given';
			$this->error = true;
			return $value;
		}
		
		for ($i = 1; $i <= count($holders); $i++) {
			if(isset($holders[$i]))
			switch ($holders[$i]) {
				case 'n':
				case 'm':
					$this->month = $values[$i];
					$this->month = preg_replace("/^0{1}/is", '', $this->month);
					break;
				case 'M':
					$this->month = array_search($values[$i], $this->months);
					break;
				case 'd':
					$this->day = $values[$i];
					$this->day = preg_replace("/^0{1}/is", '', $this->day);
					break;
				case 'Y':
					$this->year = $values[$i];
					break;
				case 'y':
					$this->year = $values[$i] >= 70 ? 1900 + $values[$i] : 2000 + $values[$i];
					break;
				case 'H':
				case 'h':
					$this->hour = $values[$i];
					$this->hour = preg_replace("/^0{1}/is", '', $this->hour);
					break;
				case 'i':
					$this->minute = $values[$i];
					$this->minute = preg_replace("/^0{1}/is", '', $this->minute);
					break;
				case 's':
					$this->second = $values[$i];
					$this->second = preg_replace("/^0{1}/is", '', $this->second);
					break;
				case 'a':
				case 'A':
					if ($values[$i] == 'pm' || $values[$i] == 'PM') {
						$this->hour += 12;
						if ($this->hour == 24) $this->hour = 12;
					}
					elseif ($values[$i] == 'am' || $values[$i] == 'AM') {
						if ($this->hour == 12) $this->hour = 0;
					}
					break;
			}
		}
		
		//echo "day: $this->day, month: $this->month, year: $this->year, hour: $this->hour, minute: $this->minute<br>";
		
		if (!($this->year >= 1000 && $this->year <= 9999)) {
			$this->Owner->FieldErrors[$this->field] = 'Ievadīts nepareizs datums (gads: '.$this->year.')';
			$this->error = true;
			return $value;
		}
				
		if (!($this->month >= 1 && $this->month <= 12)) {
			$this->Owner->FieldErrors[$this->field] = 'Ievadīts nepareizs datums (mēnesis: '.$this->month.')';
			$this->error = true;
			return $value;
		}
		
		$months_days = Array ( 1 => 31,2 => 28, 3 => 31, 4 => 30,5 => 31,6 => 30, 7 => 31, 8 => 31,9 => 30,10 => 31,11 => 30,12 => 31);
		if ($this->year % 4 == 0) $months_days[2] = 29;
		
		
		if (!($this->day >=1 && $this->day <= $months_days[$this->month])) {
			$this->Owner->FieldErrors[$this->field] = 'Ievadīts nepareizs datums (diena: '.$this->day.')';
			$this->error = true;
			return $value;
		}
		
		if (!($this->hour >=0 && $this->hour <= 23)) {
			$this->Owner->FieldErrors[$this->field] = 'Ievadīts nepareizs laiks (stundas: '.$this->hour.')';
			$this->error = true;
			return $value;
		}
		
		if (!($this->minute >=0 && $this->minute <= 59)) {
			$this->Owner->FieldErrors[$this->field] = 'Ievadīts nepareizs laiks (minūtes: '.$this->minute.')';
			$this->error = true;
			return $value;
		}
		
		if (!($this->second >=0 && $this->second <= 59)) {
			$this->Owner->FieldErrors[$this->field] = 'Ievadīts nepareizs laiks (sekundes: '.$this->second.')';
			$this->error = true;
			return $value;
		}
		//echo "day: $this->day, month: $this->month, year: $this->year, hour: $this->hour, minute: $this->minute<br>";
		//echo sprintf('%04d.%02d.%02d %02d:%02d:%02d', $this->year, $this->month, $this->day, $this->hour, $this->minute, $this->second);

		$date = DateTime::createFromFormat('Y.m.d H:i:s', sprintf('%04d.%02d.%02d %02d:%02d:%02d', $this->year, $this->month, $this->day, $this->hour, $this->minute, $this->second));

		return $date ? $date->format($this->mysqlformat):'';
	}
}

class TimeFormatter extends Formatter 
{	
	var $default_time = ""; 

	function TimeFormatter($field, &$Owner) {
		parent::Formatter($field, $Owner);
		$this->default_time = time();
	}

	function Format($value=0, $format=NULL) {
		if ( isset($format) ) $this->format = $format;
		//echo " this->field ".$this->field."<br>";
		//echo " this->Owner->FieldErrors[this->field]) ".print_pre($this->Owner->FieldErrors[$this->field])."<br>";	
		if (!@array_key_exists('code', $this->Owner->FieldErrors[$this->field] )) 
			return date($this->format, $value);
		else 
			return $value;
	}

	function Parse($value=0) {
		if ( $value == 0 ) $value = $this->default_time;
		return $this->format != "" ? date($this->format,$value) : $value; 
	}
}

class MoneyFormatter extends Formatter 
{
	var $accuracy = 2;
	var $dec_point = '.';
	var $thousands_sep = '';
	var $bad_dec_point = ',';
	
	/*
	function Format($value, $format=NULL) {
		if ( $value > 0 ) 
			return number_format($value,$this->accuracy,'.',' ');
		else
			return "0.00";
	}
	*/
	function Format($value, $format=NULL) {
		$value = str_replace($this->bad_dec_point, $this->dec_point, $value);
		return number_format($value, $this->accuracy, $this->dec_point, $this->thousands_sep);
	}
}

class IsoDateFormatter extends Formatter 
{	
	var $format;
	var $field_type;
	var $min_age;
	var $max_age;
	var $default;
	var	$month_field;
	var	$days_field;
	var	$year_field;
	
	function Format($value, $format=NULL) {
		$aDate=explode("-",$value);	
		if ($value == "0000-00-00") {
		 	if (!$default)
				return '';
			elseif ($default == 'today' && $this->field_type != 'drop_down')
				return date($this->format);
		} else {
			if ($this->field_type != 'drop_down') {
				if (strlen($aDate[1]) == 1)
					$aDate[1]="0".$aDate[1];
				if (strlen($aDate[2]) == 1)
					$aDate[2]="0".$aDate[2];
				$ret_date = preg_replace("/Y/is",$aDate[0],$this->format);
				$ret_date = preg_replace("/m/is",$aDate[1],$ret_date);
				$ret_date = preg_replace("/d/is",$aDate[2],$ret_date);
				return $ret_date;
			} 
			if ($this->field_type == 'drop_down') {
				$ret_date = preg_replace("/Y/is",$aDate[0],$this->format);
				$ret_date = preg_replace("/m/is",$aDate[1],$ret_date);
				$ret_date = preg_replace("/d/is",$aDate[2],$ret_date);
				$this->Owner->App->SetVar($this->year_field,$aDate[0]);
				$this->Owner->App->SetVar($this->month_field,$aDate[1]);
				$this->Owner->App->SetVar($this->days_field,$aDate[2]);
				return $ret_date;
			}
		}
	}
	
	function Parse($value=0) {
		$month = 0;
		$day = 0;
		$year = 0;
		
		$patterns['m'] = '(\d{1,2})';
		$patterns['d'] = '(\d{1,2})';
		$patterns['Y'] = '(\d{4})';
		$patterns['y'] = '(\d{2})';
		$patterns['H'] = '(\d{2})';
		
		$values_mask = $this->format;
		$holders_mask = preg_replace("/\w{1}/is", '(\\0)', $this->format);
		if (!preg_match("/".$holders_mask."/is", $this->format, $holders)) {
			$this->Owner->FieldErrors[$this->field]['msg'] = 'Bad date format(1)';
			$this->Owner->FieldErrors[$this->field]['code'] = 13;
			return $value;
		}
		
		foreach ($patterns as $key => $val) {
			$values_mask = str_replace($key, $val, $values_mask);
		}
		if (!preg_match("/".$values_mask."/is", $value, $values)) {
			$this->Owner->FieldErrors[$this->field]['msg'] = 'Bad date given(2)';
			$this->Owner->FieldErrors[$this->field]['code'] = 13;
			return $value;
		}
		
		for ($i = 1; $i <= count($holders); $i++) {
			switch ($holders[$i]) {
				case 'm':
					$month = $values[$i];
					$month = preg_replace("/^0{1}/is", '', $month);
					break;
				case 'd':
					$day = $values[$i];
					$day = preg_replace("/^0{1}/is", '', $day);
					break;
				case 'Y':
					$year = $values[$i];
					break;
				case 'y':
					$year = $values[$i];
					break;
			}
		}
		
		//echo "day: $day, month: $month, year: $year, hour: $hour, minute: $minute<br>";
		$max_year=date("Y") - $this->min_age;
		$min_year=date("Y") - $this->max_age;
		if (!($year >= $min_year && $year <= $max_year)) {
			$this->Owner->FieldErrors[$this->field]['msg'] = 'Bad date given (min max age)';
			$this->Owner->FieldErrors[$this->field]['code'] = 13;
			return $value;
		}
				
		if(!checkdate($month, $day, $year)) {
			$this->Owner->FieldErrors[$this->field]['msg'] = 'Bad date given';
			$this->Owner->FieldErrors[$this->field]['code'] = 13;
			return $value;
		}
		//echo"$value<br>";
		return $year."-".$month."-".$day;
	}
}

class PictureFormatter extends Formatter 
{
	
	var $max_file_size = 1000000;
	var $default_picture = 'no_image.gif';
	var $image_width = "";
	var $image_height = "";
	var $images_path = UPLOAD_IMAGES_PATH;
	var	$server_path = VIEW_IMAGES_PATH;	
	var $empty_value = 'no';
	function Format($value, $format=NULL) {
		$o = $this->server_path."/";
		if ($this->empty_value == 'no')
			return $value == '' ? $o.$this->default_picture : $o.$value;
		else
			return $value == '' ? '' : $o.$value;
	}
	
	function Parse($value="") {
		if ( $value != "" or is_array($value) ) {
		 if ( is_array($value) AND $value['error'] == 0 ) {
		 		
				if ( !in_array($value['type'], Array('image/pjpeg', 'image/png', 'image/gif', 'image/bmp'))) {
					$this->Owner->FieldErrors[$this->field]['msg'] = 'Incorrect file format';
					$this->Owner->FieldErrors[$this->field]['code'] = 14;
					$ret = $this->Owner->OriginalValues[$this->field];    
				}
				elseif ( $value['size'] > $this->max_file_size ) {
					$this->Owner->FieldErrors[$this->field]['msg'] = 'Incorrect file size';
					$this->Owner->FieldErrors[$this->field]['code'] = 15;
					$ret = $this->Owner->OriginalValues[$this->field];
				}
				else {
					$this->display_perms($this->images_path);
					$real_name = $this->ValidateFileName($value['name']);
					$file_name = $this->images_path."/".$real_name;
//					echo " real_name : $real_name || file_name : $file_name <br>";
					if ( !move_uploaded_file($value['tmp_name'], $file_name) )
						echo "Can't move uploaded file <br>";
					$ret = $real_name;
				}
			
			}	else {
	//			echo " Simple return data <br>";
				$value = $this->Owner->FieldValues[$this->field];
				//echo " value : $value <br>";
				$ret = str_replace($this->images_path."/","",$value);
			}
		} 
		else {
			$this->Owner->FieldOptions[$this->field]['db'] = 0;
			$ret = $this->Owner->OriginalValues[$this->field];
		}
		// echo " ret : ".print_pre($ret)." <br>";
		return $ret;
	}
	
	function ValidateFileName($name) {
		// echo " $name : NAME full : ".$this->images_path."/".$name."<br>";
		if ( file_exists($this->images_path."/".$name) ) {
   		// get extension
   		$tok = strtok ($name,".");
   		while ($tok) {  
   			$ext=".".$tok; 
   		  $tok = strtok(".");
   		}
   		$new_file_name = substr($name, 0, -(strlen($ext)));
   		$new_file_name_bak = $new_file_name;
   		$i = 1;
   		// echo " full name = ".$this->images_path."/".$new_file_name.$ext."<br>";
   		while(file_exists($this->images_path."/".$new_file_name.$ext)){
   		  $new_file_name = $new_file_name_bak.$i;
   		  $i++;
   		}
   		$name = $new_file_name.$ext;
			// echo " out name = ".$name."<br>";
			return $name;
		}else
			return $name;
	}

	function display_perms($path) {
		if (!is_writable($path))
			echo"<br><font size='18' color='red'>INCORRECT IMAGES FOLDER PERMISSION</font><br>";
	}
}

class UrlFormater extends Formatter {

	function Format($value, $format=NULL) {
		return parent::Format($value, $format);
	}
	
	function CheckUrl($url) {
		  if (!preg_match("~^(?:(?:https?|ftp|telnet)://(?:[a-z0-9_-]{1,32}".
				"(?::[a-z0-9_-]{1,32})?@)?)?(?:(?:[a-z0-9-]{1,128}\.)+(?:com|net|".
				"org|mil|edu|arpa|gov|biz|info|aero|inc|name|[a-z]{2})|(?!0)(?:(?".
				"!0[^.]|255)[0-9]{1,3}\.){3}(?!0|255)[0-9]{1,3})(?:/[a-z0-9.,_@%&".
				"?+=\~/-]*)?(?:#[^ '\"&<>]*)?$~i",$url,$ok))
					return false;
		  if (!strstr($url,"://")) 
		  	$url="http://".$url;
		   $url=preg_replace("~^[a-z]+~ie","strtolower('\\0')",$url);
		   return $url;
	
	}

	function Parse($value) {
		if ($this->Owner->FieldOptions[$this->field]['required'] || $value != '') {
			$url=$this->checkurl($value);
			if ($url === false) {
				$this->Owner->FieldErrors[$this->field]['msg'] = 'Incorrect Url';
				$this->Owner->FieldErrors[$this->field]['code'] = 20;
				return $value;
			}
			else 
				$value=$url;
		}
		return $value;
	}
}


class NumberFormatter extends Formatter 
{
	var $accuracy = 4;
	function Format($value, $format=NULL) {
		if ( $value > 0 ) return number_format($value,$this->accuracy,'.','');
	}
}

class UploadFormatter extends Formatter 
{
	var $max_file_size = 1000000;
	var $default_picture = 'no_image.gif';
	var $image_width = '';
	var $image_height = '';
	var $images_path = '';
	var	$server_path = '';
	var $empty_value = 'no';
	var $AllowedAllTypes = 0;
	
	var $AllowedTypes = Array();
	
	function UploadFormatter($name, $owner) {
		$this->images_path = defined('UPLOAD_IMAGES_PATH') ? UPLOAD_IMAGES_PATH : '';
		$this->server_path = defined('VIEW_IMAGES_PATH') ? VIEW_IMAGES_PATH : '';
		$this->Formatter($name, $owner);
	}
	
	function Format($value, $format=NULL) {
		$o = $this->server_path.($this->server_path{strlen($this->server_path)-1} != '/' && strlen($this->server_path) > 0 ? "/" : '');
		if ($this->empty_value == 'no')
			return $value == '' ? $o.$this->default_picture : $o.$value;
		else
			return $value == '' ? '' : $o.$value;
	}
	
	function GetPath() {
		return $this->images_path;
	}
	
	function Parse($value=Array()) {
		if ( $value != "" || is_array($value) ) {
		 if ( is_array($value) && $value['error'] == 0 ) {
				if ( !$this->AllowedAllTypes && !in_array($value['type'], $this->AllowedTypes)) {
					$this->Owner->FieldErrors[$this->field] = 'Incorrect file format';
					//$this->Owner->FieldErrors[$this->field]['code'] = 14;
					$ret = $this->Owner->OriginalValues[$this->field];    
				}
				elseif ( $value['size'] > $this->max_file_size ) {
					$this->Owner->FieldErrors[$this->field] = 'Incorrect file size';
					//$this->Owner->FieldErrors[$this->field]['code'] = 15;
					$ret = $this->Owner->OriginalValues[$this->field];
				}
				else {
					$this->display_perms($this->GetPath());
					$real_name = $this->ValidateFileName($value['name']);
					if (!move_uploaded_file($_FILES[$this->field]['tmp_name'], $this->GetPath()."/".$real_name ))
						echo "Can't move uploaded file ".$_FILES[$this->field]['tmp_name']." to ".$this->GetPath()."/".$real_name."<br>";
					$ret = $this->StoredName($real_name);
				}
			}	else {
	//			echo " Simple return data <br>";
				$value = $this->Owner->FieldValues[$this->field];
				//echo " value : $value <br>";
				$ret = str_replace($this->GetPath()."/","",$value);
			}
		} 
		else {
			$this->Owner->FieldOptions[$this->field]['db'] = 0;
			$ret = $this->Owner->OriginalValues[$this->field];
		}
		return $ret;
	}
	
	function StoredName($real_name) {
		return $real_name;
	}
	
	function GetLocalFileName($tmp_name) {
		$real_name = $this->ValidateFileName($tmp_name);
		return $this->GetPath()."/".$real_name;
	}
	
	function ValidateFileName($name) {
		//echo " $name : NAME full : ".$this->GetPath()."/".$name."<br>";
		if ( file_exists($this->GetPath()."/".$name) ) {
   		// get extension
   		$tok = strtok ($name,".");
   		while ($tok) {  
   			$ext=".".$tok; 
   		  $tok = strtok(".");
   		}
   		$new_file_name = substr($name, 0, -(strlen($ext)));
   		$new_file_name_bak = $new_file_name;
   		$i = 1;
   		// echo " full name = ".$this->images_path."/".$new_file_name.$ext."<br>";
   		while(file_exists($this->GetPath()."/".$new_file_name.$ext)){
   		  $new_file_name = $new_file_name_bak.$i;
   		  $i++;
   		}
   		$name = $new_file_name.$ext;
			// echo " out name = ".$name."<br>";
			return $name;
		}else
			return $name;
	}

	function display_perms($path) {
		if (!is_writable($path))
			echo"<br><font size='18' color='red'>INCORRECT IMAGES FOLDER PERMISSION [$path]</font><br>";
	}
}

?>