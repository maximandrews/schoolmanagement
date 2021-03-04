<?php
include_once(APP_PATH.'/e_item.php');

class ClassCourse extends EDBItem {

	function __construct($Id, $Owner) {
		$this->table_name = 'classcourses';
		$this->App =& KernelApplication::Instance();
		$this->DisplayQueries = 0;
		$this->DisplayErrors = 0;
		parent::__construct($Id, $Owner);
	}
//cc_id, cc_cl_id, cc_cr_id, cc_teacher, cc_statuss, cc_hoursweek, cc_created, cc_modified
	function &GetValidator($name, $type, $error_msg = null) { 
		switch ($name) {
			case 'cc_hoursweek':
				$validator = new IntegerValidator($name, $this, $error_msg);
				$validator->minValue = 1;
			break;
			case 'cc_cl_id':
			case 'cc_cr_id':
			case 'cc_teacher':
				$validator = new OptionsValidator($name, $this, $error_msg);
			break;
			default:
				$validator =& parent::GetValidator($name, $type, $error_msg);
		}
		return $validator;
	}

	function &GetFormatter($name, $type, $format = null) {
		switch ($name) {
			case 'cc_cl_id':
				$formatter = new OptionsFormatter($name, $this);
				$sql = 'SELECT cl_id, CONCAT(cl_level, \'.\', cl_postfix) FROM classes';
				$options = $this->CustomOptions($sql);
				$formatter->SetOptions($options);
			break;
			case 'cc_cr_id':
				$formatter = new OptionsFormatter($name, $this);
				$sql = 'SELECT cr_id, cr_name FROM courses';
				$options = $this->CustomOptions($sql);
				$formatter->SetOptions($options);
			break;
			case 'cc_teacher':
				$formatter = new OptionsFormatter($name, $this);
				$sql = 'SELECT ps_id, CONCAT(ps_firstname, ps_lastname) tname FROM persons WHERE ps_ut_id=3';
				$options = $this->CustomOptions($sql);
				$formatter->SetOptions($options);
			break;
			case 'cc_statuss':
				$formatter = new OptionsFormatter($name, $this);
				$options = Array( 1 => 'Klasç', 2 => 'Grupâ');
				$formatter->SetOptions($options);
			break;
			case 'cc_created':
			case 'cc_modified':
				$formatter = new DateFormatter($name, $this);
				$formatter->format = 'd.m.Y';
			break;
			default:
				$formatter =& parent::GetFormatter($name, $type);
			break;
		}
		return $formatter;
	}

	function Update($fields_list=NULL) {
		$sql = 'SELECT cl_level FROM classes WHERE cl_id ='.intval($this->GetDBField('cc_cl_id'));
		$cllevel = $this->Conn->GetOne($sql);
		if($cllevel > 0) {
			$sql = 'SELECT cr_id, cr_name FROM courses WHERE cr_level ='.$cllevel;
			$options = $this->CustomOptions($sql);
			$this->Formatters['cc_cr_id']->SetOptions($options);
		}
		$this->SetDBField('cc_modified', time());
		return parent::Update();
	}

	function Create() {
		$sql = 'SELECT cl_level FROM classes WHERE cl_id ='.intval($this->GetDBField('cc_cl_id'));
		$cllevel = $this->Conn->GetOne($sql);
		if($cllevel > 0) {
			$sql = 'SELECT cr_id, cr_name FROM courses WHERE cr_level ='.$cllevel;
			$options = $this->CustomOptions($sql);
			$this->Formatters['cc_cr_id']->SetOptions($options);
		}
		$this->SetDBField('cc_created', time());
		$this->SetDBField('cc_modified', time());
		return parent::Create();
	}

	function Delete() {
		$this->Load($this->id);
		if($this->GetDBField('cc_statuss') == 2) { 
			$sql = 'DELETE FROM personcourses WHERE pc_cc_id='.intval($this->GetId());
			//echo "$sql\n";
			$this->Conn->Execute($sql);
		}
		return parent::Delete();
	}
}

class ClassCoursesList extends EFilteredDBList {
	function __construct($sql, $query_now=0, $owner=null) {
		parent::__construct($sql, $query_now, $owner);
		
		$class = $this->App->GetVar('classId');
		$prefix = $owner->prefix;
		switch($prefix) {
			default:
				$this->sql = 'SELECT
												cc.cc_id,
												cc_cl_id,
												CONCAT(cl.cl_level,\'.\',cl.cl_postfix) cc_cl_txt,
												cc_teacher,
												CONCAT(ps.ps_firstname,\' \',ps.ps_lastname) cc_teacher_txt,
                        cr.cr_id cc_cr_id,
												cr.cr_name cc_cr_txt,
												cc.cc_hoursweek,
												cc.cc_statuss
											FROM classcourses cc
											LEFT JOIN `persons` ps ON ps.ps_id=cc.cc_teacher AND ps.ps_ut_id = 3
											LEFT JOIN `courses` cr ON cr.cr_id=cc.cc_cr_id
											LEFT JOIN `classes` cl ON cl.cl_id=cc.cc_cl_id';
				$this->AddFilter(new EqualsFilter('cc.cc_cl_id', intval($class)), 'onlyselclass', 1);
		}
		$this->DisplayQueries = 0;
	}
	
	function &NewItem() {
		$new = new ClassCourse(NULL, $this->Owner);
		return $new;
	}

	/*function AddStdFilter($type, $field, $value, $having=false, $system=0) {
		// types
		// equals, not_equals, like, rangefrom, rangeto, datefrom, dateto
		switch($field) {
			case 'ps_ut_id':
				$type = 'equals';
			break;
			case 'ps_cl_txt':
				$having = true;
			case 'ps_firstname':
			case 'ps_lastname':
			case 'ps_personcode':
			case 'ps_personcode':
				$type = 'like';
			break;
		}
		parent::AddStdFilter($type, $field, $value, $having, $system);
	}*/
}
?>