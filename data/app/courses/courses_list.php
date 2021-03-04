<?php
include_once(APP_PATH.'/e_item.php');

class Course extends EDBItem {

	function __construct($Id, $Owner) {
		$this->table_name = 'courses';
		$this->App =& KernelApplication::Instance();
		$this->DisplayQueries = 0;
		$this->DisplayErrors = 0;
		parent::__construct($Id, $Owner);
	}
//cr_id, cr_name, cr_level, cr_hours, cr_created, cr_modified
	function &GetValidator($name, $type, $error_msg = null) {
		switch ($name) {
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
				$validator =& parent::GetValidator($name, $type, $error_msg);
		}
		return $validator;
	}
	
	function Update($fields_list=NULL) {
		$this->SetDBField('cr_modified', time());
		return parent::Update();
	}

	function Create() {
		$this->SetDBField('cr_created', time());
		$this->SetDBField('cr_modified', time());

		return parent::Create();
	}
}

class CoursesList extends EFilteredDBList {			
	function __construct($sql, $query_now=0, $owner=null) {
		parent::__construct($sql, $query_now, $owner);

		$level = $this->App->GetVar('classLvl');

		$prefix = $owner->prefix;

		switch($prefix) {
			case 'levelcourses':
				$this->sql = 'SELECT
												cr.cr_id,
												cr.cr_name
											FROM courses cr';
				$this->AddFilter(new EqualsFilter('cr.cr_level', intval($level)), 'onlyp', 1);
			break;
			default:
//cl_id, cl_level, cl_postfix, cl_year, cl_teacher, cl_created, cl_modified
			$this->sql = 'SELECT
											cr.cr_id,
											cr.cr_name,
											cr.cr_level,
											cr.cr_hours,
											cr.cr_created,
											cr.cr_modified
										FROM courses cr';
			}
		$this->DisplayQueries = 0;
	}
	
	function &NewItem() {
		$new = new Course(NULL, $this->Owner);
		return $new;
	}
}
?>