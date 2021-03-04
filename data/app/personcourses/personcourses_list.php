<?php
include_once(APP_PATH.'/e_item.php');

class PersonCourse extends EDBItem {

	function __construct($Id, $Owner) {
		$this->table_name = 'personcourses';
		$this->App =& KernelApplication::Instance();
		$this->DisplayQueries = 0;
		$this->DisplayErrors = 0;
		parent::__construct($Id, $Owner);
	}
//cr_id, cr_name, cr_level, cr_hours, cr_created, cr_modified
	function &GetValidator($name, $type, $error_msg = null) {
		switch ($name) { /*
			case 'cr_level':
				$validator = new IntegerValidator($name, $this, $error_msg);
				$validator->minValue = 1;
				$validator->maxValue = 12;
			break;
			case 'cr_hours':
				$validator = new IntegerValidator($name, $this, $error_msg);
				$validator->minValue = 1;
			break;
			default:
				$validator =& parent::GetValidator($name, $type, $error_msg);*/
		}
		return $validator;
	}

	function &GetFormatter($name, $type, $format = null) {
		switch ($name) {/*
			case 'pt_created':
			case 'pt_modified':
				$formatter = new DateFormatter($name, $this);
				$formatter->format = 'd.m.Y';*/
			default:
				$formatter =& parent::GetFormatter($name, $type);
			break;
		}
		return $formatter;
	}
	
	function Update($fields_list=NULL) {
		$this->SetDBField('pc_modified', time());
		return parent::Update();
	}

	function Create() {
		$this->SetDBField('pc_created', time());
		$this->SetDBField('pc_modified', time());

		return parent::Create();
	}
}

class PersonCoursesList extends EFilteredDBList {			
	function __construct($sql, $query_now=0, $owner=null) {
		parent::__construct($sql, $query_now, $owner);

		$class = $this->App->GetVar('classId');
		$lesson = $this->App->GetVar('lessonId');

		$prefix = $owner->prefix;

		switch($prefix) {/*
			case 'levelcourses':
				$this->sql = 'SELECT
												cr.cr_id,
												cr.cr_name
											FROM courses cr';
				$this->AddFilter(new EqualsFilter('cr.cr_level', intval($level)), 'onlyp', 1);
			break;*/
			default:
			$this->sql = 'SELECT
											ps.ps_id pc_ps_id,
											ps.ps_firstname pc_ps_firstname,
											ps.ps_lastname pc_ps_lastname,
											pc.pc_id
										FROM persons ps
										LEFT JOIN `classcourses` cc ON ps.ps_cl_id=cc.cc_cl_id AND cc.cc_cr_id='.intval($lesson).'
										LEFT JOIN `personcourses` pc ON pc.pc_ps_id=ps.ps_id AND pc.pc_cc_id=cc.cc_id';
			$this->AddFilter(new EqualsFilter('ps.ps_cl_id', intval($class)), 'personcourses', 1);
			//$this->AddFilter(new EqualsFilter('cc.cc_cr_id', intval($lesson)), 'courseId', 1);
		}
		$this->DisplayQueries = 0;
	}
	
	function &NewItem() {
		$new = new PersonCourse(NULL, $this->Owner);
		return $new;
	}
}
?>