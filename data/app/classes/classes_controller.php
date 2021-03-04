<?php
include_once(APP_PATH.'/e_controller.php');
include_once(APP_PATH.'/classes/classes_list.php');
include_once(APP_PATH.'/classes/classes_view.php');

class ClassesGridController extends EGridController {
	function CreateView() {
		$this->AddView(new ClassesGridView($this));
	}

	function actAddChildClass() {
		$class = $this->App->GetVar('classId');
		$children = json_decode($this->App->GetVar('cls'), true);
		if(!is_array($children) || !count($children) || !$class) {
			$this->errors[] = 'Nav datu';
			return false;
		}

		$sql = 'SELECT cl_id FROM classes WHERE cl_id='.intval($class);
		//echo $sql."\n";
		$class = $this->Conn->GetOne($sql);
		if(!$class || !($class > 0)) {
			$this->errors[] = 'Klase izvçlçta nepareizi';
			return false;
		}

		$cl = Array();
		foreach($children as $chn) {
			$cl[] = intval($chn);
		}

		if(!count($cl)) {
			$this->errors[] = 'Skolçni nav izvçlçti';
			return false;
		}

		$sql = 'UPDATE persons SET ps_cl_id='.intval($class).', ps_modified=UNIX_TIMESTAMP()
						WHERE ps_id IN ('.implode(',', $cl).') AND ps_ut_id = 1 AND ps_cl_id <>'.intval($class);
		//echo $sql."\n";
		$this->Conn->Execute($sql);
	}

	function CreateModel() {
		$this->Grid = new ClassesList('', 0, $this);
	}
}

class ClassesItemController extends EItemController {
	function CreateView() {
		$this->AddView(new ClassesItemView($this));
	}
	
	function CreateModel() {  
		$this->Item = new Classes(null, $this);
	}
}
?>