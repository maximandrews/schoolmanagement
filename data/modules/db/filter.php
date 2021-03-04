<?php
include_once(MODULES_PATH.'/db/dbitem.php');

class Filter extends Base {
	public $sql_t;
	public $field;
	public $key1;
	public $key2;
	public $key3;
	public $sys_filter = 0;
	
	function Filter($field, $key1=NULL, $key2=NULL, $key3=NULL) {
		$this->field = $field;
		$this->sql_t = "%s = \"%s\""; 
		$this->SetKeys($key1, $key2, $key3);
	}
	
	function GetFieldName() {
		return $this->field;
	}

	function SetKeys($key1, $key2=NULL, $key3=NULL) {
		$this->key1 = $key1;
		$this->key2 = $key2;
		$this->key3 = $key3;
	}

	function GetSql() {
		return sprintf($this->sql_t, $this->field, $this->key1, $this->key2, $this->key3);
	}
}

class EqualsFilter extends Filter {
	function GetSql() {		
		if (!isset($this->key1) || $this->key1 !== '')
			return parent::GetSql();
		else
			return '';
	}
}

class NoQEqualsFilter extends EqualsFilter {
	function NoQEqualsFilter($field, $key1=NULL, $key2=NULL, $key3=NULL) {
		parent::Filter($field, $key1, $key2, $key3);
		$this->sql_t = "%s = %s";
	}
}

class NotEqualsFilter extends Filter {
	function NotEqualsFilter($field, $key1=NULL, $key2=NULL, $key3=NULL) {
		parent::Filter($field, $key1, $key2, $key3);
		$this->sql_t = "%s != '%s'";
	}
}

class IsNullFilter extends Filter {
	function IsNullFilter($field, $key1=NULL, $key2=NULL, $key3=NULL) {
		parent::Filter($field, $key1, $key2, $key3);
		$this->sql_t = "%s IS NULL";
	}
}

class IsNotNullFilter extends Filter {
	function IsNotNullFilter($field, $key1=NULL, $key2=NULL, $key3=NULL) {
		parent::Filter($field, $key1, $key2, $key3);
		$this->sql_t = "%s IS NOT NULL";
	}
}

class LikeFilter extends Filter {
	function LikeFilter($field, $key1=NULL, $key2=NULL, $key3=NULL) {
		parent::Filter($field, $key1, $key2, $key3);
		$this->sql_t = "%s LIKE \"%%%s%%\"";
	}

	function GetSql() {
		if ($this->key1 != '')
			return parent::GetSql();
		else
			return '';
	}
}

class NotLikeFilter extends Filter {
	function NotLikeFilter($field, $key1=NULL, $key2=NULL, $key3=NULL) {
		parent::Filter($field, $key1, $key2, $key3);
		$this->sql_t = "%s NOT LIKE \"%%%s%%\"";
	}

	function GetSql() {
		if ($this->key1 != '')
			return parent::GetSql();
		else
			return '';
	}
}

class LikeStartFilter extends Filter {
	function LikeStartFilter($field, $key1=NULL, $key2=NULL, $key3=NULL) {
		parent::Filter($field, $key1, $key2, $key3);
		$this->sql_t = "%s LIKE \"%s%%\"";
	}

	function GetSql() {
		if ($this->key1 != '')
			return parent::GetSql();
		else
			return '';
	}
}

class IpFilter extends Filter {
	function IpFilter($field, $key1=NULL, $key2=NULL, $key3=NULL) {
		parent::Filter($field, $key1, $key2, $key3);
		$this->sql_t = "%s LIKE \"%s\"";
	}

	function GetSql() {
		if ($this->key1 != '')
			return parent::GetSql();
		else
			return '';
	}
}

class CompareFilter extends Filter {
	function CompareFilter($field, $key1=NULL, $key2=NULL, $key3=NULL) {
		parent::Filter($field, $key1, $key2, $key3);
		$this->sql_t = "%1\$s %3\$s '%2\$s'";
	}

	function GetSql() {
		if (($this->key1 != '' || $this->key1 === 0) && $this->key2 != '')
			return parent::GetSql();
		else
			return '';
	}
}

class RangeFilter extends Filter {
	public $upper = NULL;
	public $lower = NULL;
	public $upper_sign = '>';
	public $lower_sign = '<';

	// set key 3 to 'in*' i.e. 'inclusive' or 'in' for filter to be inclusive
	// by default filter is exclusive
	function RangeFilter($field, $key1=NULL, $key2=NULL, $key3=NULL) {
		$this->upper = new CompareFilter($field, $key1, $this->upper_sign);
		$this->lower = new CompareFilter($field, $key2, $this->lower_sign);
		parent::Filter($field, $key1, $key2, $key3);
	}

	function CheckMode() {
		if (preg_match("/^in.*/is", $this->key3)) {
			$this->upper_sign.= '=';
			$this->lower_sign.= '=';
		}
	}

	function SetKeys($key1, $key2=NULL, $key3=NULL) {
		parent::SetKeys($key1, $key2, $key3);
		$this->CheckMode();
		$this->upper->SetKeys($this->key1, $this->upper_sign);
		$this->lower->SetKeys($this->key2, $this->lower_sign);
	}

	function GetSql() {
		$upper_sql = $this->upper->GetSql();
		$lower_sql = $this->lower->GetSql();

		if ($upper_sql == '' && $lower_sql == '') return '';

		if ($upper_sql != '' && $lower_sql != '')
			$res = $upper_sql.' AND '.$lower_sql;
		else
			$res = $upper_sql != '' ? $upper_sql:$lower_sql;

		return $res;
	}
}

class IN_Filter extends Filter {
	public $sql_t;
	public $field;
	public $keys;
	public $in;

	function IN_Filter($field, $key1=NULL, $key2=NULL, $key3=NULL) {
		$this->field = $field;
		$this->sql_t = "%s  %s (%s)"; 
		$this->SetKeys($key1, $key2, $key3);
	}

	function SetKeys($key1, $key2=NULL, $key3=NULL) {
		parent::SetKeys($key1, $key2, $key3);
		$this->CheckMode();
	}

	function CheckMode() {
		if (preg_match("/^IN/is", $this->key1)) 
			$this->in = ' IN ';
		else
			$this->in = ' NOT IN ';
	}

	function GetSql() {
		if ( strlen($this->key2) > 0 ) 
			return sprintf($this->sql_t, $this->field, $this->in, $this->key2);
		elseif ($this->key3 == TRUE) 
			return 'FALSE';
		else 
			return;
	}	
}

class MultipleFilter extends Filter {
	public $Filters = Array();

	function AddFilter(&$a_filter, $name=NULL, $system=0) {
		if (!isset($name))
			$name = $a_filter->GetFieldName();
		$this->Filters[$name] = &$a_filter;
		$this->Filters[$name]->sys_filter = $system;
	}

	function RemoveFilter($name) {
		unset($this->Filters[$name]);
	}

	function ClearAllFilters() {
		$this->Filters = Array();
	}

	function ClearUserFilters() {
		$new_filters = Array();
		foreach ($this->Filters as $name => $a_filter) {
			if ($this->Filters[$name]->sys_filter) continue;
			$new_filters[$name] =& $a_filter;
		}
		$this->Filter = $new_filters;
	}

	function GetSql($system=2) { //0 - custom, 1 - system, 2 - any
		$res = '';
		if (isset($this->Filters)) {
			foreach ($this->Filters as $name => $a_filter) {
				if (($system == 0) && ($this->Filters[$name]->sys_filter != 0)) continue;
				if (($system == 1) && ($this->Filters[$name]->sys_filter != 1)) continue;
				$filter_sql = $this->Filters[$name]->GetSql();
				if ($filter_sql != '')
					$res.=$filter_sql.' '.$this->key1.' ';
			}
		}
		$res = preg_replace("/ ".$this->key1." $/is", '', $res);

		if (isset($this->key2) && $this->key2 != '' && strlen($res) > 0 ) 
			$res = '('.$res.')';
		return $res;	
	}
}

?>