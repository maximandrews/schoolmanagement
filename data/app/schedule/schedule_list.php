<?php
include_once(APP_PATH.'/e_item.php');

class Schedule extends EDBItem {

	function __construct($Id, $Owner) {
		$this->table_name = 'schedule';
		$this->App =& KernelApplication::Instance();
		$this->DisplayQueries = 0;
		$this->DisplayErrors = 0;
		parent::__construct($Id, $Owner);
	}
// sc_id, sc_date, sc_from, sc_until, sc_cl_id, sc_cr_id, sc_cc_id, sc_lessontheme, sc_hometask,
// sc_parent, sc_selected, sc_freqcount, sc_freqperiod, sc_created, sc_modified
	function &GetValidator($name, $type, $error_msg = null) {
		switch ($name) {
			case 'sc_cr_id':
			case 'sc_cc_id':
			case 'sc_cl_id':
				$validator = new OptionsValidator($name, $this, $error_msg);
			break;
			case 'sc_date':
			case 'sc_till':
			case 'sc_from':
			case 'sc_until':
				$validator = new Validator($name, $this, $error_msg);
			break;
			default:
				$validator =& parent::GetValidator($name, $type, $error_msg);
		}
		return $validator;
	}

// sc_id, sc_date, sc_from, sc_until, sc_cl_id, sc_cr_id, sc_cc_id, sc_lessontheme, sc_hometask,
// sc_parent, sc_selected, sc_freqcount, sc_freqperiod, sc_created, sc_modified
	function &GetFormatter($name, $type, $format = null) {
		switch ($name) {
			case 'sc_cr_id':
			case 'sc_cc_id':
			case 'sc_cl_id':
				$formatter = new OptionsFormatter($name, $this);

				if($name == 'sc_cr_id')
					$sql = 'SELECT cr_id, cr_name FROM courses';
				elseif($name == 'sc_cc_id')
					$sql = 'SELECT cc_id, cc_id FROM classcourses';
				else
					$sql = 'SELECT cl_id, CONCAT(cl_level,\'.\', cl_postfix) cl FROM classes';

				$options = $this->CustomOptions($sql);

				$formatter->SetOptions($options);
			break;
			case 'sc_from':
			case 'sc_until':
				$formatter = new DateFormatter($name, $this);
				$formatter->format = 'H:i';
			break;
			case 'sc_date':
			case 'sc_till':
			case 'sc_created':
			case 'sc_modified':
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
		$this->SetDBField('sc_modified', time());
		return parent::Update();
	}

	function Create() {
		$this->SetDBField('sc_created', time());
		$this->SetDBField('sc_modified', time());

		return parent::Create();
	}
}

class ScheduleList extends EFilteredDBList {			
	function __construct($sql, $query_now=0, $owner=null) {
		parent::__construct($sql, $query_now, $owner);

// sc_id, sc_date, sc_from, sc_until, sc_cl_id, sc_cr_id, sc_cc_id, sc_lessontheme, sc_hometask,
// sc_parent, sc_selected, sc_freqcount, sc_freqperiod, sc_created, sc_modified
		$this->sql = 'SELECT
						sc.sc_id,
						sc.sc_date,
						DAYOFWEEK(sc.sc_date)-1 sc_wday,
						sc.sc_from,
						sc.sc_until,
						sc.sc_cl_id,
						CONCAT(cl.cl_level,\'.\', cl.cl_postfix) sc_cl_txt,
						sc.sc_cr_id,
						cr.cr_name sc_cr_txt,
						sc.sc_cc_id,
						sc.sc_lessontheme,
						sc.sc_hometask,
						sc.sc_parent,
						sc.sc_selected,
						sc.sc_freqcount,
						sc.sc_freqperiod,
						sc.sc_till,
						sc.sc_created,
						sc.sc_modified
					FROM schedule sc
					LEFT JOIN `classes` cl ON cl.cl_id=sc.sc_cl_id
					LEFT JOIN `courses` cr ON cr.cr_id=sc.sc_cr_id';
		$this->AddStdFilter('datefrom', 'sc_date', $this->App->GetVar('sc_date_from'), false, 1);
		$this->AddStdFilter('dateto', 'sc_date', $this->App->GetVar('sc_date_to'), false, 1);
		$this->AddStdFilter('equals', 'sc_cl_id', intval($this->App->GetVar('sc_cl_id')), false, 1);
		$this->DisplayQueries = 0;
	}
	
	function &NewItem() {
		$new = new Schedule(NULL, $this->Owner);
		return $new;
	}
}
?>