<?php
include_once(APP_PATH.'/e_item.php');

class Classes extends EDBItem {

	function __construct($Id, $Owner) {
		$this->table_name = 'classes';
		$this->App =& KernelApplication::Instance();
		$this->DisplayQueries = 0;
		$this->DisplayErrors = 0;
		parent::__construct($Id, $Owner);
	}
// Visi tabulas lauki
// cl_id, cl_level, cl_postfix, cl_year, cl_teacher, cl_created, cl_modified
	function &GetValidator($name, $type, $error_msg = null) {
		switch ($name) {
			case 'cl_level':
			case 'cl_year':
				$validator = new IntegerValidator($name, $this, $error_msg);
				if($name == 'cl_level') {
					$validator->minValue = 1;
					$validator->maxValue = 12;
				}
			break;
			case 'cl_postfix':
			//postfix - latîòu alfabçta mazie burti
				$validator = new ClassPostfixValidator($name, $this, $error_msg);
			break;
			case 'cl_teacher':
			// parbaudam combo box izvçli
				$validator = new OptionsValidator($name, $this, $error_msg);
			break;
			default:
				$validator =& parent::GetValidator($name, $type, $error_msg);
		}
		return $validator;
	}
//cl_id, cl_level, cl_postfix, cl_year, cl_teacher, cl_created, cl_modified
	function &GetFormatter($name, $type, $format = null) {
		switch ($name) {
			case 'cl_teacher':
				$formatter = new OptionsFormatter($name, $this);
				$sql = 'SELECT ps_id, CONCAT(ps_firstname, ps_lastname) tname FROM persons WHERE ps_ut_id=3 ORDER BY ps_lastname ASC, ps_firstname ASC';
				$options = $this->CustomOptions($sql);

				$formatter->SetOptions($options);
			break;
			case 'cl_created':
			case 'cl_modified':
				$formatter = new DateFormatter($name, $this);
				$formatter->format = 'd.m.Y';
			default:
				$formatter =& parent::GetFormatter($name, $type);
			break;
		}
		return $formatter;
	}
	
	function Update($fields_list=NULL) {
		$this->SetDBField('cl_modified', time());
		return parent::Update();
	}

	function Create() {
		$this->SetDBField('cl_created', time());
		$this->SetDBField('cl_modified', time());
		
//Iegûstam izlaiduma gadu
		$val = $this->GetField('cl_level');
		if(preg_match("/^\d$/is", $val))
			$this->SetField('cl_year', 12-$val+date('Y'));

		return parent::Create();
	}
}

class ClassesList extends EFilteredDBList {			
	function __construct($sql, $query_now=0, $owner=null) {
		parent::__construct($sql, $query_now, $owner);

//cl_id, cl_level, cl_postfix, cl_year, cl_teacher, cl_created, cl_modified
// CONCAT(cl.cl_level,\'.\',cl.cl_postfix) cl_txt - klases lauks (lîmenis + burts)
// CONCAT(ps.ps_lastname, \' \', ps.ps_firstname) cl_teacher_txt, - skolotâja vârds, uzvârds
		$this->sql = 'SELECT
				cl.cl_id,
				cl.cl_level,
				cl.cl_postfix,
				CONCAT(cl.cl_level,\'.\',cl.cl_postfix) cl_txt,
				cl.cl_year,
				cl.cl_teacher,
				CONCAT(ps.ps_lastname, \' \', ps.ps_firstname) cl_teacher_txt,
				COUNT(ps1.ps_id) cl_ps_count,
				cl.cl_created,
				cl.cl_modified
			FROM `classes` cl
			LEFT JOIN persons ps ON cl.cl_teacher=ps.ps_id AND ps.ps_ut_id=3
			LEFT JOIN persons ps1 ON cl.cl_id=ps1.ps_cl_id AND ps1.ps_ut_id=1';
		$this->DisplayQueries = 0;
		$this->setGroupByClause('cl.cl_id');
	}
	
	function &NewItem() {
		$new = new Classes(NULL, $this->Owner);
		return $new;
	}

	function AddStdFilter($type, $field, $value, $having=false, $system=0) {
		switch($field) {
			case 'cl_level':
				$type = 'equals';
			break;
		}
		parent::AddStdFilter($type, $field, $value, $having, $system);
	}
}
?>