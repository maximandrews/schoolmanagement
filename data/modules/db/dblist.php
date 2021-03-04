<?php
global $MODULES_PATH;
include_once(MODULES_PATH."/db/dbitem.php");
include_once(MODULES_PATH."/db/filter.php");

class DBList extends Base {
	var $Records = Array();
	var $cur_rec = NULL;
	var $records_count = 0;
	var $first_rec = 0;
	var $last_rec = 0;
	var $first_displayed = 0;
	var $last_displayed = 0;
	var $counted = FALSE;
	var $no_limit = FALSE;
	
	var $per_page = 10;
	var $total_pages = 0;
	
	var $sql = NULL;
	var $whereClause = NULL;
	
	var $groupByClause = NULL;
	
	var $havingClause = NULL;
	
	var $orderByClause = NULL;
	
	var $orderFields = Array();
	
	var $orderByField = NULL;
	var $orderDirection = NULL;	
	
	var $DisplayQueries = 0;
	
	var $IndexFields = Array();
	var $Indexes = Array();
	
	var $FieldsNames = Array();
	var $FieldFormula = Array();
	var $FieldFormulaType = Array();
	
	var $countedSQL = '';
	var $has_counted = false;
	
	var $Totals = Array();
	var $TotalsFiltered = Array();
	
	var $Conn;
	
	var $Application;
	
	function __construct($sql, $query_now=0, &$owner) {
		$this->App =& KernelApplication::Instance();
		$this->Conn =& $this->App->GetADODBConnection();
		
		$this->sql = $sql;
		$this->Owner =& $owner;
		if ($query_now) {
			$this->Query();
		}
	}
	
	function &NewItem () {
		$new_item = new DBItem(NULL, $this->Owner);
		return $new_item;
	}
	
	function GetCountedSQL() {
		return $this->countedSQL;
	}
	
	function SetCountedSQL($sql) {
		$sql = preg_replace("/^[ \t\n\r]*\,/is", '', $sql); //cutting possible leading comma
		$sql = preg_replace("/\,[ \t\n\r]*$/is", '', $sql); //cutting possible last comma
		if ($sql != '') {
			$this->countedSQL = $sql;
			$this->has_counted = true;
		}
	}
	
	function CountRecs() {

		if($this->counted)
			return $this->records_count;

		//echo "Counting records in ".get_class($this)."<br>";
		$adodbConnection =& $this->Conn;
		$query = $this->sql;
		$where = $this->GetWhereClause();
		$groupby = $this->GetGroupByClause();
		$having = $this->GetHavingClause();

		//$query = preg_replace("/^.*SELECT(.*)FROM/is", "SELECT COUNT(*) AS count FROM", $query);
		
		$counted_sql = '';
		if ($this->has_counted) {
			$counted_sql = $this->GetCountedSQL();
			$counted_sql = ", $counted_sql";
		}
					
		if ( preg_match("/DISTINCT(.*?)FROM(?!_)/is",$query,$regs ) ) 
			$query = preg_replace("/^.*SELECT DISTINCT(.*?)FROM(?!_)/is", "SELECT COUNT(DISTINCT ".$regs[1].") AS count FROM", $query);
		else
			$query = preg_replace("/^.*SELECT(.*?)FROM(?!_)/is", "SELECT COUNT(*) AS count $counted_sql FROM ", $query);
		if ($where != '') $query.= sprintf(' WHERE %s',$where);
		if ($groupby != '') $query.= sprintf(' GROUP BY %s',$groupby);
		if ($having != '') $query.= sprintf(' HAVING %s',$having);
		if ($this->DisplayQueries) echo get_class($this).": <b>count query</b> is ".$query."<br>";
    $result = $adodbConnection->Execute($query);
    
    if ($result && !$result->EOF) {
    	$this->records_count = $groupby != '' ? $result->RecordCount() : $result->fields['count'];
    } else {
    	$this->records_count = 0;
    }
    $this->last_rec = $this->records_count;
    $this->counted = TRUE;
    
    unset($result);
    return $this->records_count;
	}

	function Query() {
		$adodbConnection =& $this->Conn;
		$query = $this->sql;
		$where = $this->GetWhereClause();
		//echo"$where WHERE<br>";
		$having = $this->GetHavingClause();
		
		if($where != '') $query.= sprintf(' WHERE %s',$where);
		
		if(isset($this->groupByClause))
			$query.= sprintf(' GROUP BY %s',$this->GetGroupByClause());
			
		if($having != '') $query.= sprintf(' HAVING %s',$having);
			
		if(isset($this->orderByClause))
			$query.= sprintf(' ORDER BY %s',$this->GetOrderByClause());

		if ($this->records_count == 0 && !$this->counted) 
			$this->CountRecs();
		
		$offset = $this->first_rec;
		$limit = $this->last_rec - $this->first_rec;
		
		$this->first_displayed = $this->first_rec;
		$this->last_displayed = $this->last_rec;
		
		//echo get_class($this)." offset: [$offset], limit: [$limit]<BR>";
		
		if (($offset != 0 || $limit != $this->records_count) && !$this->no_limit) $query.= sprintf(' LIMIT %s,%s', $offset, $limit);
		
		$this->first_rec = 0; 
		$this->last_rec = $limit;
			if ($this->DisplayQueries) echo get_class($this).": <b>query</b> (count is ".$this->records_count.") is ".$query."<br>";
		$result = $adodbConnection->Execute($query);
		
		if ($result === false) return false;
    
		if ($result !== false) {
			for ($i=0; $i<$result->FieldCount(); $i++) {
				$fld = $result->FetchField($i);
				array_push($this->FieldsNames, $fld->name);
			}
		}
		
		while ($push_hash = $result->FetchRow())
		{
			$id = array_push($this->Records,$push_hash);
			if (count($this->IndexFields) > 0) {
				foreach ($this->IndexFields as $key) {
					if (is_string($push_hash[$key])) $store_key = strtolower($push_hash[$key]);
					else $store_key = $push_hash[$key];
					$this->Indexes[$key][$store_key] = $id-1;
				}	
			}
		}
		$this->GoFirst();
		unset($result);
	}
	
	function GetAllIds() {
		$adodbConnection =& $this->Conn;
		$query = $this->sql;
		$where = $this->GetWhereClause();
		$having = $this->GetHavingClause();
		
		$dummy =& $this->NewItem();
		
		$query = sprintf("SELECT %s FROM ".(defined('TLB_PREFIX')?TLB_PREFIX:'') ."%s ", $dummy->id_field, $dummy->table_name);

		if($where != '') $query.= sprintf(' WHERE %s',$where);
		
		if(isset($this->groupByClause))
			$query.= sprintf(' GROUP BY %s',$this->GetGroupByClause());
			
		if($having != '') $query.= sprintf(' HAVING %s',$having);
			
		if(isset($this->orderByClause))
			$query.= sprintf(' ORDER BY %s',$this->GetOrderByClause());

		return $adodbConnection->GetCol($query);
	}
	
	function GoToRec($index) {
		$this->cur_rec = $index;
	}

	function GoNext() {
		if (!$this->EOL()) $this->cur_rec++;
		else return false;
		return true;
	}
	
	function GoPrev() {
		if ($this->cur_rec > $this->first_rec) $this->cur_rec--;
		else return false;
		return true;
	}
	
	function GoFirst() {
		$this->GoToRec($this->first_rec);
	}
	
	function GoLast() {
		$this->GoToRec($this->last_rec);
	}
	
	function &GetCurrent() {
		return $this->Records[$this->cur_rec];
	}
	
	function EOL() {
		$temp = ($this->cur_rec >= $this->last_rec);
		return $temp;
	}
		
	function &GetCurrentRec() {
		$item =& $this->NewItem();
		$data = $this->Records[$this->cur_rec];
    $item->CreateFieldsFromHash($data);
		return $item;
	}
	
	function GetFieldFormulaType($name) {
		return isset($this->FieldFormulaType[$name]) ? $this->FieldFormulaType[$name] : false;
	}
	
	function SetFieldFormulaType($name, $value) {
		$this->FieldFormulaType[$name] = $value;
	}
	
	function ClearFieldFormulaType($name) {
		unset($this->FieldFormulaType[$name]);
	}
	
	function GetFieldFormula($name) {
		return isset($this->FieldFormula[$name]) ? $this->FieldFormula[$name] : $name;
	}
	
	function SetFieldFormula($name, $value) {
		$this->FieldFormula[$name] = $value;
	}
	
	function ClearFieldFormula($name) {
		unset($this->FieldFormula[$name]);
	}
	
	function GetCurrentFieldValue($name) {
		$temp =& $this->GetCurrentRec();
		if (isset($temp)) 
			return $temp->GetField($name);
		else
			echo "<b>***Error</b> while trying to get value of field <b>$name</b> in class <b>".get_class($this)."</b><br>";
	}
	
	function SetCurrentFieldValue($name, $value) {
		$temp =& $this->GetCurrentRec();
		$temp->SetField($name, $value);
		$this->Records[$this->cur_rec][$name] = $temp->GetField($name);
		return 1;
	}
	
	//Ordering
	
	function SetOrderField($name) {
		$this->orderByField = $name;
		$this->SetOrderByClause();
	}
	
	function GetOrderField($pos=NULL) {
		if(!isset($this->orderFields[$pos])) {
			$this->orderFields[$pos] = '';
		}
		
		preg_match("/^([^ ]*)/is", $this->orderFields[$pos], $rets);	
		return $rets[1];
	}
	
	function SetOrderDirection($dir) {
		$this->orderDirection = $dir;
		$this->SetOrderByClause();
	}
	
	function GetOrderDirection($pos=NULL) {
		if(!$this->orderFields[$pos])
			$pos = 0;
		
		preg_match("/ (.*)$/is", $this->orderFields[$pos], $rets);
		return $rets[1];
	}
	
	function SetOrderByClause($clause=NULL) {
		if (isset($clause)) 
			$this->orderByClause = $clause;
		else {
			if (isset($this->orderByField) && $this->orderByField != '' && isset($this->orderByField) && $this->orderDirection != '' ) {
				$this->orderByClause = sprintf("%s %s", $this->GetOrderField(), $this->GetOrderDirection());
				//For compatibility with previous versions, not using multiple order fields
				$this->AddOrderField($this->orderByField, $this->orderDirection, 0);
			}
		}
	}
	
	function ClearOrderFields() {
		$this->orderFields = Array();	
	}
	
	function AddOrderField($field, $dir, $pos=NULL) {
		if ($field == '' || $dir == '') return;
		$this->orderByField = '';
		$this->orderDirection = '';
		if (isset($pos)) {
			$before_pos = array_slice($this->orderFields, 0, $pos);
			$after_pos = array_slice($this->orderFields, $pos);
			$this->orderFields = $before_pos;
		}
		array_push($this->orderFields, "$field $dir");
		if (isset($pos)) {
			$this->orderFields = array_merge($this->orderFields, $after_pos);
		}
		$this->SetMultipleOrderByClause();
	}
	
	function SetMultipleOrderByClause() {
		$res = '';
		foreach ($this->orderFields as $val) {
			$res.="$val,";
		}
		$res = chop($res, ',');
		$this->SetOrderByClause($res);
	}

	function GetGroupByClause() {
		return $this->groupByClause;
	}
	
	function SetGroupByClause($clause) {
		$this->groupByClause = $clause;
	}
		
	
	function GetOrderByClause() {
		return $this->orderByClause;
	}
	
	function SetWhereClause($clause=NULL) {
		if (isset($clause))
			$this->whereClause = $clause;
	}
	
	function GetWhereClause() {
		return $this->whereClause;
	}
	
	function GetHavingClause() {
		return $this->havingClause;
	}
	
	//Pagination
	
	function SetPerPage($per_page) {
		if (!isset($per_page) || $per_page == '') return;
		$this->per_page = $per_page;
	}
		
	function SetPage($page) {
		if ($this->per_page == -1) {
			$this->page = 1;
			return;
		}
		if (!$page || $page < 1) $page = 1;
		if ($page > $this->GetTotalPages()) $page = $this->GetTotalPages();
		$this->first_rec = $page*$this->per_page - $this->per_page;
		if ($this->first_rec > $this->records_count) 
			$this->SetPage(1);
		else {
			//$this->Owner->App->StoreVar($this->Owner->object_name.$this->Owner->special.'_page', $page);
			$this->page = $page;
		}
		$this->last_rec = $this->first_rec + $this->per_page;
		if ($this->last_rec > $this->records_count) 
			$this->last_rec = $this->records_count;
		$this->GoFirst();
	}
	
	function GetTotalPages() {
		$this->total_pages = (($this->records_count - ($this->records_count % $this->per_page)) / $this->per_page) // integer part of division
								+ (($this->records_count % $this->per_page) != 0);  // adds 1 if there is a reminder
		if(!$this->total_pages) $this->total_pages = 1;
		return $this->total_pages;
	}
	
	function GetRecordsCount() {
		return $this->records_count;
	}
	
	function AddIndexField($field) {
		array_push($this->IndexFields, $field);
		$this->Indexes[$field] = Array();
	}
	
	function IsIndexField($field) {
		return in_array($field, $this->IndexFields);
	}
	
	function Find($field, $key) {
		if(!isset($key) || !$key) return;
		$found = false;
		
		if ($this->IsIndexField($field)) { //performing index search
			if (is_string($key)) $key = strtolower($key);
			if (array_key_exists($key, $this->Indexes[$field])) {
				$this->cur_rec = $this->Indexes[$field][$key];
				return 1;
			}
			else
				return;
		}
				
		$this->GoFirst();
		while (!$this->EOL()) {
			//echo "looking for $key in ".$this->Records[$cur_rec][$field]." (".$this->cur_rec.")<br>";
			//print_pre($this->Records[$this->cur_rec]);
			if ($this->Records[$this->cur_rec][$field] == $key) {
				$found = 1;
				break;
			}
			$this->GoNext();			
		}
		return $found;
	}
	
	function CountTotals($fields, $filter=1) {
		$adodbConnection =& $this->Conn;
		$query = $this->sql;
		$where = $this->GetWhereClause($filter ? 2 : 1); //only system filter for 0 and all filters for 1 or 2
		$groupby = $this->GetGroupByClause();
		$having = $this->GetHavingClause();

		$counted_sql = '';
		if ($this->has_counted) {
			$counted_sql = $this->GetCountedSQL();
			$counted_sql = ", $counted_sql";
		}
		
		$q = '';
		
		foreach($fields AS $field => $func) {
			$replaced = str_replace('.', '_', $field);
			$q .= "$func($field) AS _total_$replaced,";
		}
		$q = rtrim($q, ',');

		$query = preg_replace("/^.*SELECT(.*?)FROM(?!_)/is", "SELECT $q $counted_sql FROM", $query);				
		
		//if ($filter) {
			if ($where != '') $query.= sprintf(' WHERE %s',$where);
			if ($groupby != '') $query.= sprintf(' GROUP BY %s',$groupby);
			if ($having != '') $query.= sprintf(' HAVING %s',$having);
		//}
			
		if ($this->DisplayQueries) echo get_class($this).": <b>CountTotals query</b> is ".$query."<br>";
	    $result = $adodbConnection->Execute($query);

	    $res = Array();
	    while ($result && !$result->EOF) {
	    	$tmp = $result->GetRowAssoc(false);
	    	foreach ($tmp AS $field=>$val) {
	    		if (!preg_match("/^_total_(.*)/is", $field, $rets)) continue;
	    		if(!isset($res[$rets[1]])) $res[$rets[1]] = 0;
	    		$res[$rets[1]] += $val;
	    	}
	    	$result->MoveNext();
	    }
	    unset($result);
	    if ($filter >= 1) {
		    	$this->TotalsFiltered = $res;
		    	if ($filter == 2) {
		    		$this->CountTotals($fields, 0);
		    	}
	    }
	    elseif ($filter == 0)
	    	$this->Totals = $res;
	}
	
	function CountMainTotals() {
		$dummy =& $this->GetDummy();
		return $this->CountTotals(Array($dummy->id_field => 'count'), 0);
	}
	
	function &GetDummy() {
		if (isset($this->Dummy)) return $this->Dummy;
		else {
			$this->Dummy =& $this->NewItem(null, $this->Owner);	
			return $this->Dummy;
		}
	}
}

class FilteredDBList extends DBList {
	var $Filters;
	var $filter_op = 'AND';
	var $HavingFilters;
	var $having_op = 'AND';
	
	function __construct($sql, $query_now=0, &$owner) {
		$this->Filters = new MultipleFilter('general_filter', $this->filter_op);
		$this->HavingFilters = new MultipleFilter('general_having_filter', $this->having_op);
		parent::__construct($sql, $query_now, $owner);
	}
	
	function AddStdFilter($type, $field, $value, $having=false, $system=0) {
		$filter_field = $this->GetFieldFormula($field);
		$filter_field_type = $this->GetFieldFormulaType($field);
		
		// echo" $type, $field, $value $having<br>";
		if ($having || ($filter_field && $filter_field_type == 'having'))
			$function="AddHavingFilter";
		else 
			$function="AddFilter";
		
		
		switch ($type) {
			case 'equals':
				$this->$function(
					new EqualsFilter($filter_field,
						$value),
					$type.'_'.$field,
					$system
					);			
				break;
			case 'not_equals':
				$this->$function(
					new NotEqualsFilter($filter_field,
						$value),
					$type.'_'.$field,
					$system
					);			
				break;
			case 'like':
				$this->$function(
					new LikeFilter($filter_field,
						$value),
					$type.'_'.$field,
					$system
					);			
				break;
			case 'rangefrom':
				$this->$function(
					new CompareFilter($filter_field,
						$value, '>='),
					$type.'_'.$field,
					$system
					);
				break;
			case 'rangeto':
				$this->$function(
					new CompareFilter($filter_field,
						$value, '<='),
					$type.'_'.$field,
					$system
					);
				break;
			case 'datefrom':
				$dummy =& $this->GetDummy();
				$tmp_format = 'd.m.Y';
				$filter_date_format = isset($dummy->Formatters[$filter_field]) ? $dummy->Formatters[$filter_field]->format : $tmp_format;
				$formater = new DateFormatter($value, $this->Owner);
				$format_value = $formater->Parse($value, $filter_date_format);
				//echo " filter_date_format : $filter_date_format <br>";
				// echo " value : $value || format_value : $format_value <br>";
				$this->$function(
					new CompareFilter($filter_field, 
						$format_value, '>='),
					$type.'_'.$field,
					$system);
				break;
			case 'dateto':
				$dummy =& $this->GetDummy();
				$tmp_format = $this->App->ConfigOption('filter_date_format', 'm/d/Y');
				$filter_date_format = isset($dummy->Formatters[$filter_field]) ? $dummy->Formatters[$filter_field]->format : $tmp_format;
				$formater = new DateFormatter($value, $this->Owner);
				$format_value = $formater->Parse($value, $filter_date_format);
				if ( date("H:i:s",$format_value) == "00:00:00" ) $format_value += 86399; // what it is ?
				$this->$function(
					new CompareFilter($filter_field, 
						$format_value, '<='),
					$type.'_'.$field,
					$system);
				break;
		}
	}
	
	function AddFilter(&$a_filter, $name=NULL, $system=0) {
		// echo "we are in ".get_class($this)." || name = $name <br>";
		$this->Filters->AddFilter($a_filter, $name, $system);
	}
	
	function RemoveFilter($name) {
		$this->Filters->RemoveFilter($name);
	}
	
	function ClearAllFilters() {
		$this->Filters->ClearAllFilters();
	}
	
	function ClearUserFilters() {
		$this->Filters->ClearUserFilters();
	}
	
	function GetWhereClause($system=2) {
		//echo "we are in ".get_class($this)."<br>";
		
		if ($system == 2) {
			$custom = $this->Filters->GetSql(0);
			$system = $this->Filters->GetSql(1);
			
			if ($custom != '' && $system != '')
				$ret = sprintf("(%s) AND (%s)", $custom, $system);
			else
				$ret = $custom.$system; //one of two is empty
		}else
			$ret = $this->Filters->GetSql($system);

		$cur_clause = parent::GetWhereClause();
		if ($cur_clause != '') {
			if ($ret != '') 
				$ret = sprintf("(%s) AND (%s)", $cur_clause, $ret); // Adding normal WHERE clause
			else 
				$ret = $cur_clause;
		}
		return $ret;
	}
	
	function AddHavingFilter(&$a_filter, $name=NULL, $system=0) {
		//echo "we are in ".get_class($this)."<br>";
		$this->HavingFilters->AddFilter($a_filter, $name, $system);
	}
	
	function RemoveHavingFilter($name) {
		$this->HavingFilters->RemoveFilter($name);
	}
	
	function ClearAllHavingFilters() {
		$this->HavingFilters->ClearAllFilters();
	}
	
	function GetHavingClause() {
		$ret = $this->HavingFilters->GetSql();
		$cur_clause = parent::GetHavingClause();
		if ($cur_clause != '') {
			if ($ret != '') 
				$ret = sprintf("(%s) AND (%s)", $cur_clause, $ret); // Adding normal WHERE clause
			else 
				$ret = $cur_clause;
		}
		return $ret;
	}
}
?>