<?php
include_once(APP_PATH.'/e_controller.php');
include_once(APP_PATH.'/classcourses/classcourses_list.php');
include_once(APP_PATH.'/classcourses/classcourses_view.php');

class ClassCoursesGridController extends EGridController {
	function CreateView() {
		$this->AddView(new ClassCoursesGridView($this));
	}

	function CreateModel() {
		$this->Grid = new ClassCoursesList('', 0, $this);
	}
}

class ClassCoursesItemController extends EItemController {
	function CreateView() {
		$this->AddView(new ClassCoursesItemView($this));
	}
	
	function CreateModel() {
		$this->Item = new ClassCourse(null, $this);
	}

	function actUpdate($notskip=0) {
		if(parent::actUpdate($notskip)) 
			$this->updatePersonsInCourseGroup();
	}

	function actCreate($notskip=0) {
		if(parent::actCreate($notskip))
			$this->updatePersonsInCourseGroup();
	}
	
	function updatePersonsInCourseGroup() {
		$itm =& $this->Item;

		if($itm->GetDBField('cc_statuss') != 2) 
			return false;

		$ids = json_decode($this->App->GetVar('ids'), true);
		if(!is_array($ids)) $ids = Array();
		$sql = 'SELECT
							ps.ps_id,
							pc.pc_id
						FROM persons ps
						LEFT JOIN `personcourses` pc ON pc.pc_ps_id=ps.ps_id AND pc.pc_cc_id='.$itm->GetId().'
						WHERE ps.ps_cl_id='.$itm->GetDBField('cc_cl_id').' AND ps.ps_ut_id=1';
		//echo "$sql\n";
		$rs = $this->Conn->Execute($sql);

		while( $rs && ($row = $rs->FetchRow())) {
			if(in_array($row['ps_id'], $ids) && !$row['pc_id']) {
				$sql = 'INSERT INTO personcourses SET pc_cc_id='.intval($itm->GetId()).', pc_ps_id='.$row['ps_id'].', pc_modified=UNIX_TIMESTAMP(), pc_created=UNIX_TIMESTAMP()';
				//echo "$sql\n";
				$this->Conn->Execute($sql);
			} elseif (!in_array($row['ps_id'], $ids) && $row['pc_id']) {
				$sql = 'DELETE FROM personcourses WHERE pc_cc_id='.intval($itm->GetId()).' AND pc_ps_id='.$row['ps_id'];
				//echo "$sql\n";
				$this->Conn->Execute($sql);
			}
		}
	}
}
?>